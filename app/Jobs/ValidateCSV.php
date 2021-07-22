<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ImportMeta;
use Illuminate\Support\LazyCollection;
use App\Exceptions\ImportValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
        $filepath = Storage::disk('local')
            ->path('mismatch-files/' . $this->meta->filename);

        LazyCollection::make(function () use ($filepath) {
            $file = fopen($filepath, 'r');

            while ($data = fgetcsv($file)) {
                yield $data;
            }
        })->skip(1)->each(function ($row, $i) {
            if(count($row) !== config('imports.upload.col_count')){
                $this->failImport($i, __('validation.import.columns', [
                    'amount' => config('imports.upload.col_count')
                ]));
            }

            $keys = [
                'statement_guid',
                'property_id',
                'wikidata_value',
                'external_value',
                'external_url'
            ];

            $rules = config('mismatches.validation');

            $validator = Validator::make(array_combine($keys, $row), [
                'statement_guid' => [
                    'required',
                    'max:' . $rules['guid']['max_length'],
                    'regex:' . $rules['guid']['format']
                ]
            ]);

            if ($validator->stopOnFirstFailure()->fails()) {
                $this->failImport($i, $validator->errors()->first());
            }
        });
    }

    private function failImport(int $line, string $message): void
    {
        $this->meta->status = 'failed';
        $this->meta->save();

        throw new ImportValidationException($this->meta, $line, $message);
    }
}
