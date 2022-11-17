<?php

namespace App\Jobs;

use App\Rules\StatementGuidValue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ImportMeta;
use App\Exceptions\ImportValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Rules\WikidataValue;
use App\Rules\MetaWikidataValue;
use App\Services\CSVImportReader;
use App\Exceptions\ImportParserException;
use Throwable;
use App\Models\ImportFailure;
use Illuminate\Support\Facades\Log;

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
    public function handle(
        CSVImportReader    $reader,
        StatementGuidValue $statementGuidValidator,
        WikidataValue      $valueValidator,
        MetaWikidataValue  $metaValueValidator
    ) {
        $filepath = Storage::disk('local')
            ->path('mismatch-files/' . $this->meta->filename);

        $reader->lines($filepath)
            ->each(function ($mismatch, $i) use ($statementGuidValidator, $valueValidator, $metaValueValidator) {
                $error = $this->checkFieldErrors($mismatch)
                    ?? $this->checkStatementGuidErrors($mismatch, $statementGuidValidator)
                    ?? $this->checkValueErrors($mismatch, $valueValidator)
                    ?? $this->checkMetaValueErrors($mismatch, $metaValueValidator);

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
            Log::error('Import #' . $this->meta->id . ' failed with error: ' . $e->getMessage());
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
            'item_id' => [
                'required',
                'max:' . $rules['item_id']['max_length'],
                'regex:' . $rules['item_id']['format']
            ],
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
            ],
            'meta_wikidata_value' => [
                'max:' . $rules['meta_wikidata_value']['max_length'],
                'regex:' . $rules['meta_wikidata_value']['format']
            ]
        ]);


        if ($validator->stopOnFirstFailure()->fails()) {
            return $validator->errors()->first();
        }

        return null;
    }

    private function checkStatementGuidErrors($mismatch, StatementGuidValue $statementGuidRule): ?string
    {
        $validator = Validator::make([
            'item_id' => $mismatch['item_id'],
            'statement_guid' => $mismatch['statement_guid'],
        ], [
            'statement_guid' => [$statementGuidRule],
        ]);

        if ($validator->fails()) {
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

    private function checkMetaValueErrors($mismatch, MetaWikidataValue  $metaRule): ?string
    {
        $validator = Validator::make([
            'meta_wikidata_value' => [
                'property' => $mismatch['property_id'],
                'meta_wikidata_value' => $mismatch['meta_wikidata_value']
            ]
        ], [
            'meta_wikidata_value' => [$metaRule]
        ]);

        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        return null;
    }
}
