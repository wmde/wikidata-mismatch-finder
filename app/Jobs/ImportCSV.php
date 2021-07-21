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
use App\Models\Mismatch;
use Illuminate\Support\Facades\Storage;

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
    public function handle()
    {
        $filepath = Storage::disk('local')
            ->path('mismatch-files/' . $this->meta->filename);

        LazyCollection::make(function () use ($filepath) {
            $file = fopen($filepath, 'r');

            while ($data = fgetcsv($file)) {
                yield $data;
            }
        })->skip(1)->each(function ($row) {
            $mismatch = Mismatch::make([
                'statement_guid' => $row[0],
                'property_id' => $row[1],
                'wikidata_value' => $row[2],
                'external_value' => $row[3],
                'external_url'  => $row[4]
            ]);

            $mismatch->importMeta()->associate($this->meta);

            $mismatch->save();
        });

        $this->meta->status = 'completed';
        $this->meta->save();
    }
}
