<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ImportMeta;
use App\Models\Mismatch;
use Illuminate\Support\Facades\Storage;
use App\Services\CSVImportReader;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Models\ImportFailure;

class ImportCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Information about the current import.
     *
     * @var ImportMeta
     */
    protected $meta;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ImportMeta $meta)
    {
        $this->meta = $meta;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CSVImportReader $reader)
    {
        $filepath = Storage::disk('local')
            ->path('mismatch-files/' . $this->meta->filename);

        $mismatch_attrs = (new Mismatch())->getFillable();

        DB::transaction(function () use ($reader, $filepath, $mismatch_attrs) {
            $new_mismatches = [];
            $where_clauses = [];

            $mismatches_per_upload_user = DB::table('mismatches')
                ->select($mismatch_attrs)
                ->join('import_meta', 'mismatches.import_id', '=', 'import_meta.id')
                ->where('import_meta.user_id', '=', $this->meta->user->id);

            $reader->lines($filepath)->each(function ($mismatchLine) use (
                $mismatches_per_upload_user,
                &$new_mismatches,
                &$where_clauses
            ) {

                $new_mismatch = Mismatch::make($mismatchLine);
                $new_mismatches[] = $new_mismatch;
                $collection = collect($new_mismatch->getAttributes());
                $collection->forget('review_status');
                $newArray = [['review_status', '!=', 'pending']];
                $collection->map(function ($item, $key) use (&$newArray) {
                    if ($key != 'type') { // key can be empty in the file but in the db always has statement by default
                        $newArray[] = [$key, $item];
                    }
                });

                $where_clauses[] = $newArray;
            });

            $mismatches_per_upload_user->where(function ($query) use ($where_clauses) {
                foreach ($where_clauses as $where_clause) {
                    $query->orWhere(function ($query) use ($where_clause) {
                        $query->where($where_clause);
                    });
                }
            });

            $existing_mismatches = $mismatches_per_upload_user->get();

            foreach ($new_mismatches as $new_mismatch) {
                $isDuplicate = function ($value) use ($new_mismatch) {
                    $metaAttrs = $new_mismatch->getAttributes();
                    foreach ($metaAttrs as $attrKey => $attr) {
                        if ($attrKey != 'review_status' && $value->{$attrKey} != $attr) {
                            return false;
                        }
                    }
                    return true;
                };

                if (!$existing_mismatches->contains($isDuplicate)) {
                    $this->saveMismatch($new_mismatch);
                }
            }

            $this->meta->status = 'completed';
            $this->meta->save();
        });
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $failure = ImportFailure::make([
            'message' => __('errors.unexpected')
        ])->importMeta()->associate($this->meta);

        $failure->save();

        $this->meta->status = 'failed';
        $this->meta->save();
    }

    /**
     * Save mismatch to database
     *
     * @param \Mismatch $new_mismatch
     * @return void
     */
    private function saveMismatch($new_mismatch)
    {
        if ($new_mismatch->type == null) {
            $new_mismatch->type = 'statement';
        }
        // if review_status == pending -> save
        $new_mismatch->importMeta()->associate($this->meta);
        $new_mismatch->save();
    }
}
