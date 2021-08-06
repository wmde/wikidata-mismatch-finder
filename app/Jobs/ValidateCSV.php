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
use App\Rules\WikidataValue;
use App\Services\CSVImportReader;
use App\Exceptions\ImportParserException;
use Throwable;
use App\Models\ImportFailure;

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
    public function handle(WikidataValue $valueValidator, CSVImportReader $reader)
    {
        $filepath = Storage::disk('local')
        ->path('mismatch-files/' . $this->meta->filename);

        $reader->lines($filepath)
            ->each(function ($mismatch, $i) use ($valueValidator) {
                $error = $this->checkFieldErrors($mismatch)
                    ?? $this->checkValueErrors($mismatch, $valueValidator);

                if ($error) {
                    throw new ImportValidationException($i, $error);
                }
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
        // We re-throw the exception in order to pattern match on it's instance
        try {
            throw $exception;
        } catch (ImportValidationException | ImportParserException $e) {
            $context = $e->context();
            $failure = ImportFailure::make([
                'line' => $context['csv_line'],
                'message' => $e->getMessage()
            ])->importMeta()->associate($this->meta);

            $failure->save();
        } catch (Throwable $e) {
            $failure = ImportFailure::make([
                'message' => __('errors.unexpected')
            ])->importMeta()->associate($this->meta);

            $failure->save();
        }

        $this->meta->status = 'failed';
        $this->meta->save();
    }

    private function checkFieldErrors($mismatch): ?string
    {
        $rules = config('mismatches.validation');

        $validator = Validator::make($mismatch, [
            'statement_guid' => [
                'required',
                'max:' . $rules['guid']['max_length'],
                'regex:' . $rules['guid']['format']
            ],
            'property_id' => [
                'required',
                'max:' . $rules['pid']['max_length'],
                'regex:' . $rules['pid']['format']
            ],
            'wikidata_value' => [
                'required',
                'max:' . $rules['wikidata_value']['max_length']
            ],
            'external_value' => [
                'required',
                'max:' . $rules['external_value']['max_length']
            ],
            'external_url' => [
                'url',
                'max:' . $rules['external_url']['max_length']
            ]
        ]);

        if ($validator->stopOnFirstFailure()->fails()) {
            return $validator->errors()->first();
        }

        return null;
    }

    private function checkValueErrors($mismatch, WikidataValue $valueRule): ?string
    {
        // We require a separate validator for wikidata value formatting,
        // as it must be validated alongside the wikidata property id.
        $validator = Validator::make([
            'wikidata_value' => [
                'property' => $mismatch['property_id'],
                'value' => $mismatch['wikidata_value']
            ]
        ], [
            'wikidata_value' => [$valueRule]
        ]);

        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        return null;
    }
}
