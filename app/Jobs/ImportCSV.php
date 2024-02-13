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

    protected $iterationCount;

    protected $useOldApproach;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ImportMeta $meta, bool $useOldApproach = false, int $iterationCount = 10)
    {
        $this->meta = $meta;
        $this->useOldApproach = $useOldApproach;
        $this->iterationCount = $iterationCount;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CSVImportReader $reader)
    {
        Log::info("======= Start import CSV =======");
        $filepath = Storage::disk('local')
            ->path('mismatch-files/' . $this->meta->filename);
        $lineCount = $reader->lines($filepath)->count();

        $start = hrtime(true);
        $maxTimespan = 0.0;
        $minTimespan = 999999999.0;

        $totalTimespan = 0.0;
        $iterationCount = $this->iterationCount;
        for ($i = 0; $i < $iterationCount; $i++) {
            $startIteration = hrtime(true);
            if (!$this->useOldApproach) {
                $this->handleNew($reader);
            } else {
                $this->handleOld($reader);
            }
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
        $approach = $this->useOldApproach ? 'Old' : 'New';
        Log::info("Approach:\t\t $approach");
        Log::info("Mismatch count:\t $lineCount");
        Log::info("Iteration count:\t $iterationCount");
        Log::info("Shortest timespan:\t {$minTimespan}ms");
        Log::info("Longest timespan:\t {$maxTimespan}ms");
        Log::info("Average timespan:\t {$meanTimespan}ms");
        Log::info("======= End import CSV in {$timespan}ms =======");
    }

    private function handleNew(CSVImportReader $reader)
    {
        $filepath = Storage::disk('local')
            ->path('mismatch-files/' . $this->meta->filename);



        DB::transaction(function () use ($filepath, $reader) {
            $mismatch_attrs = (new Mismatch())->getFillable();

            $fileLines = [];
            $whereClause = [];

            $mismatches_per_upload_user = DB::table('mismatches')
                ->select($mismatch_attrs)
                ->join('import_meta', 'mismatches.import_id', '=', 'import_meta.id')
                ->where('import_meta.user_id', '=', $this->meta->user->id);
            $reader->lines($filepath)->each(function ($mismatchLine) use (
                $mismatch_attrs,
                &$fileLines,
                $mismatches_per_upload_user,
                &$whereClause
            ) {

                $new_mismatch = Mismatch::make($mismatchLine);
                $fileLines[] = $new_mismatch;
                $collection = collect($new_mismatch->getAttributes());
                $collection->forget('review_status');
                $newArray = [['review_status', '!=', 'pending']];
//                dd($mismatch_attrs, $collection, $new_mismatch->getAttributes());
                $collection->map(function ($item, $key) use (&$newArray) {
                    if ($key != 'type') { // key can be empty in the file but in the db always has statement by default
                        $newArray[] = [$key, $item];
                    }
                });


                $whereClause[] = $newArray;

//                $count = $mismatches_per_upload_user->count();
//                $row_in_db = $mismatches_per_upload_user->orWhere(function ($query) use ($newArray) {
//                    $query->orWhere($newArray);
//                });
//
//                $start = hrtime(true);
//                if ($row_in_db->doesntExist()) {
//                    $timespan = (hrtime(true) - $start) / 1000000;
//                    Log::info("DB check timespan:\t {$timespan}ms");
//                    $start = hrtime(true);
////                    $this->saveMismatch($new_mismatch);
//                    $timespan = (hrtime(true) - $start) / 1000000;
//                    Log::info("DB save timespan:\t {$timespan}ms");
//                }
            });

            $mismatches_per_upload_user->where(function ($query) use ($whereClause) {
                foreach ($whereClause as $where) {
                    $query->orWhere(function ($query) use ($where) {
                        $query->where($where);
                    });
                }
            });
            $result = $mismatches_per_upload_user->get();

            foreach ($fileLines as $fileLine) {
                if ($result->contains(function ($value, $key) use ($fileLine) {
                    $metaAttrs = $fileLine->getAttributes();
                    foreach ($metaAttrs as $attrKey => $attr) {
                        $value = (array)$value;
                        if ($attrKey != 'review_status' && $value[$attrKey] != $attr) {
                            return false;
                        }
                    }
                    return true;
                })) {
                    $this->saveMismatch($fileLine);
                }
            }

            $this->meta->status = 'completed';
            $this->meta->save();
        });
    }


    private function handleOld(CSVImportReader $reader)
    {
        $filepath = Storage::disk('local')
            ->path('mismatch-files/' . $this->meta->filename);

        DB::transaction(function () use ($reader, $filepath) {
            $reader->lines($filepath)->each(function ($mismatchLine) {
                $mismatch = Mismatch::make($mismatchLine);
                if ($mismatch->type == null) {
                    $mismatch->type = 'statement';
                }
                $mismatch->importMeta()->associate($this->meta);
                $mismatch->save();
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
