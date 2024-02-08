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

        // $db_mismatches_by_current_user = DB::select(
        //     'select * from users JOIN mismatches ON mismatches.user_id = users.id
        //     WHERE mw_userid = :mw_userid',
        //     ['mw_userid' =>$this->meta->user->mw_userid]
        // );

        $mismatches_per_upload_user = DB::table('mismatches');
            // ->join('users', 'mismatches.user_id', '=', 'users.id')
            // ->where('mw_userid', '=', $this->meta->user->mw_userid);

        // var_dump($mismatches_per_upload_user->first());

        $mismatches_per_upload_user_get = $mismatches_per_upload_user->get();
        Log::info('$mismatches_per_upload_user:' . json_encode($mismatches_per_upload_user_get));

        $mismatch_attrs = (new Mismatch())->getFillable();
        Log::info('mismatch_attrs', $mismatch_attrs);
        // ["item_id","statement_guid","property_id","wikidata_value","meta_wikidata_value","external_value","external_url","review_status","type"]

        // $collection = collect($mismatch_attrs);
        // $collection->forget('review_status');
        // $newArray = [];
        // $collection->map(function ($item, $key) use (&$newArray) {
        //     if ($key != 'type') { // key can be empty in the file but in the db always has statement by default
        //         $newArray[] = [$key, $item];
        //     }
        // });

        // Log::info('$newArray', $newArray);

        DB::transaction(function () use ($reader, $filepath, $mismatch_attrs) {

            $reader->lines($filepath)->each(function ($mismatchLine) use ($mismatch_attrs) {
                $mismatches_per_upload_user = DB::table('mismatches');
                $new_mismatch = Mismatch::make($mismatchLine);

                $collection = collect($new_mismatch->getAttributes());
                $collection->forget('review_status');
                $newArray = [];
                $collection->map(function ($item, $key) use (&$newArray) {
                    if ($key != 'type') { // key can be empty in the file but in the db always has statement by default
                        $newArray[] = [$key, $item];
                    }
                });


                // we add first because there might be duplicates already, so this might return more than 1 result
                $row_in_db = $mismatches_per_upload_user->select($mismatch_attrs)->where($newArray);
                $row_in_db_get = $row_in_db->get();
//                dump($newArray);
//                dump($row_in_db->toSql());
//                dump($row_in_db_get->count());
                Log::info("row_in_db: " . json_encode($row_in_db_get));

                // var_dump($mismatches_per_upload_user->get()->count());

                // if ($mismatches_per_upload_user->get()->count() == 0) {
                //     if ($new_mismatch->type == null) {
                //         $new_mismatch->type = 'statement';
                //     }
                //     $new_mismatch->importMeta()->associate($this->meta);
                //     $new_mismatch->save();
                //     var_dump('we imported all rows because the user hasnt uploaded any mismatches');
                // }
                if ($row_in_db->doesntExist()
                //|| ( $row_in_db->exists() && $row_in_db->first()->review_status == 'pending'))
                ) {
                    $this->saveMismatch($new_mismatch);
                    var_dump('we imported row that doesnt exist');
                }

                // case 1. there are not mismatches from user in the DB
                // if ($mismatches_per_upload_user->count() == 0) {
                //     if ($new_mismatch->type == null) {
                //         $new_mismatch->type = 'statement';
                //     }
                //     $new_mismatch->importMeta()->associate($this->meta);
                //     $new_mismatch->save();
                //     var_dump('we imported all rows because the user hasnt uploaded any mismatches');
                // }
                // else {
                // foreach ($db_mismatches_by_current_user as $db_mismatch) {
                //     // we keep all the mismatches that are pending regardless of values in other columns
                //     // and stop checking this line
                //     if ($db_mismatch->review_status == 'pending') {
                //         $new_mismatch->importMeta()->associate($this->meta);
                //         $new_mismatch->save();
                //         var_dump('we reimported any mismatches still marked as pending in the DB');
                //     }
                // }

                // original
                // $new_mismatch = Mismatch::make($mismatchLine);
                // if ($new_mismatch->type == null) {
                //     $new_mismatch->type = 'statement';
                // }
                // $new_mismatch->importMeta()->associate($this->meta);
                // $new_mismatch->save();
            });

            // $this->meta->status = 'completed';
            // $this->meta->save();
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
