<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ImportMeta;
use Illuminate\Support\LazyCollection;

class ValidateCSV implements ShouldQueue
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
    public function handle()
    {
        $filepath = storage_path('app/mismatch-files/' . $this->meta->filename);

        LazyCollection::make(function () use ($filepath) {
            $file = fopen($filepath, 'r');

            while ($data = fgetcsv($file)) {
                yield $data;
            }
        })->skip(1)->each(function ($row) {
        });
    }
}
