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

        DB::transaction(function () use ($reader, $filepath) {
            $reader->lines($filepath)->each(function ($mismatchLine) {
                $mismatch = Mismatch::make($mismatchLine);
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
     * @param \Throwable  $exception
     *
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
