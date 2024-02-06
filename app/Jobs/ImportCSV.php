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
use Illuminate\Support\Facades\Log;

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

        DB::transaction(function () use ($reader, $filepath) {

            $reader->lines($filepath)->each(function ($mismatchLine) {

                // TODO: question, should we list all columns one by one or try to do something like
                // Schema::getColumnListing('mismatches'); // where is mismatches is the table name
                // and then we remove the column names we dont need... id, username, mw_userid, created at, etc.
                // too many.
                // or this is the best option but it needs to be instantiated already to be able to get the attributes
                // or not?
                // get attributes from model instance
                // $column_names = $new_mismatch->getAttributes();
                // remove column because we dont want to compare with review_status
                // unset($column_names['review_status']);

                $db_mismatches_by_current_user = DB::select(
                    'select * from users JOIN mismatches ON mismatches.user_id = users.id
                        WHERE mw_userid = :mw_userid',
                        ['mw_userid' =>$this->meta->user->mw_userid]
                );

                // compare column by column
                foreach ($db_mismatches_by_current_user as $db_mismatch) {
                    $new_mismatch = Mismatch::make($mismatchLine);
                    $mismatch_column_names = $new_mismatch->getAttributes(); // or should we use getFillable?
                    unset($column_names['review_status']); // remove review status from the columns we want to check
                    Log::info("mismatch attributes " . $mismatch_column_names);

                    foreach ($mismatch_column_names as $column) {
                        if ($db_mismatch[$column] == $new_mismatch[$column]) {
                            continue;
                        } else {
                            // break;
                        }
                    }
                    // we keep the mismatches that are pending
                    if ($db_mismatch->review_status == 'pending') {
                        // and check that not all fields are equal
                    }
                }

                // $new_mismatch = Mismatch::make($mismatchLine);
                // if ($new_mismatch->type == null) {
                //     $new_mismatch->type = 'statement';
                // }
                // $new_mismatch->importMeta()->associate($this->meta);
                // $new_mismatch->save();
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
}
