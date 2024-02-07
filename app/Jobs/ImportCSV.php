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

        $db_mismatches_by_current_user = DB::select(
            'select * from users JOIN mismatches ON mismatches.user_id = users.id
            WHERE mw_userid = :mw_userid',
            ['mw_userid' =>$this->meta->user->mw_userid]
        );

        DB::transaction(function () use ($reader, $filepath, $db_mismatches_by_current_user) {


            $reader->lines($filepath)->each(function ($mismatchLine) use ($db_mismatches_by_current_user) {

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

                $new_mismatch = Mismatch::make($mismatchLine);

                $collection = collect($new_mismatch->getAttributes());
                $collection->forget('review_status');
                $newArray = [];
                $collection->map(function ($item, $key) use (&$newArray) {
                    if ($key != 'type') { // key can be empty in the file but in the db always has statement by default
                        $newArray[] = [$key, $item];
                    }
                });

                if (!DB::table('mismatches')->where($newArray)->exists() // checks all fields at the same time
                   // || count($db_mismatches_by_current_user) == 0 //take this out of the lines check. if there are not imports by the current user we import
                    ) {
                    if ($new_mismatch->type == null) {
                        $new_mismatch->type = 'statement';
                    }
                    $new_mismatch->importMeta()->associate($this->meta);
                    $new_mismatch->save();
                    var_dump('we imported row that doesnt exist');
                }

                // case 1. there are not mismatches from user in the DB
                if (count($db_mismatches_by_current_user) == 0) {
                    if ($new_mismatch->type == null) {
                        $new_mismatch->type = 'statement';
                    }
                    $new_mismatch->importMeta()->associate($this->meta);
                    $new_mismatch->save();
                    var_dump('we imported all rows because the user hasnt uploaded any mismatches');
                }
                // else {
                foreach ($db_mismatches_by_current_user as $db_mismatch) {
                    // we keep all the mismatches that are pending regardless of values in other columns
                    // and stop checking this line
                    if ($db_mismatch->review_status == 'pending') {
                        $new_mismatch->importMeta()->associate($this->meta);
                        $new_mismatch->save();
                        var_dump('we reimported any mismatches still marked as pending in the DB');
                    }
                }

                // original
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
