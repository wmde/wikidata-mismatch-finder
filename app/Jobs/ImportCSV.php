<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ImportMeta;
use App\Models\Mismatch;
use Illuminate\Support\Facades\Log;
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
        Log::info("======= Start import CSV =======");
        $start = hrtime(true);
        $maxTimespan = 0.0;
        $minTimespan = 999999999.0;

        $totalTimespan = 0.0;
        $iterationCount = 10;
        for ($i = 0; $i < $iterationCount; $i++) {
            $startIteration = hrtime(true);
            $this->handleOld($reader);
            $endIteration = (hrtime(true) - $startIteration) / 1000000;
            if ($endIteration > $maxTimespan) {
                $maxTimespan = $endIteration;
            }
            if ($endIteration < $minTimespan) {
                $minTimespan = $endIteration;
            }
            $totalTimespan += $endIteration;
        }
        $meanTimespan = $totalTimespan / $iterationCount;
        $timespan = (hrtime(true) - $start) / 1000000;
        Log::info("Iteration count:\t {$iterationCount}");
        Log::info("Shortest timespan:\t {$minTimespan}ms");
        Log::info("Longest timespan:\t {$maxTimespan}ms");
        Log::info("Average timespan:\t {$meanTimespan}ms");
        Log::info("======= End import CSV in {$timespan}ms =======");
    }

    private function handleOld(CSVImportReader $reader)
    {
        $filepath = Storage::disk('local')
            ->path('mismatch-files/' . $this->meta->filename);

        $mismatch_attrs = (new Mismatch())->getFillable();

        DB::transaction(function () use ($reader, $filepath, $mismatch_attrs) {
            $reader->lines($filepath)->each(function ($mismatchLine) use ($mismatch_attrs) {
                $mismatches_per_upload_user = DB::table('mismatches')
                    ->join('import_meta', 'mismatches.import_id', '=', 'import_meta.id')
                    ->where('import_meta.user_id', '=', $this->meta->user->id)
                    ->select($mismatch_attrs);

                $new_mismatch = Mismatch::make($mismatchLine);

                $collection = collect($new_mismatch->getAttributes());
                $collection->forget('review_status');
                $newArray = [];
                $collection->map(function ($item, $key) use (&$newArray) {
                    if ($key != 'type') { // key can be empty in the file but in the db always has statement by default
                        $newArray[] = [$key, $item];
                    }
                });

//                $count = $mismatches_per_upload_user->count();
                $row_in_db = $mismatches_per_upload_user->where($newArray)
                    ->where('review_status', '!=', 'pending');

                if ($row_in_db->doesntExist()) {
                    $this->saveMismatch($new_mismatch);
                }
            });

            $this->meta->status = 'completed';
            $this->meta->save();
        });
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
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
     * @param  \Mismatch  $new_mismatch
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
