<?php

namespace App\Services;

use App\Models\ImportMeta;
use App\Models\Mismatch;

class ImportResults
{
    public function generateCSV(string $path, int $import_id): void
    {
        $mismatches = Mismatch::get();

        // Creating the output stream
        $handle = fopen($path, 'w');

        $import = ImportMeta::find($import_id);
        $mismatches = Mismatch::whereBelongsTo($import)->get();

        $mismatchValues = array_map(function ($mismatch) {
            return [
                $mismatch['item_id'],
                $mismatch['statement_guid'],
                $mismatch['property_id'],
                $mismatch['wikidata_value'],
                $mismatch['meta_wikidata_value'],
                $mismatch['external_value'],
                $mismatch['external_url'],
                $mismatch['review_status']
            ];
        }, $mismatches->toArray());

        // Add column keys as CSV headers
        $mismatchesWithKeys = [config('imports.results.column_keys')
        ]+$mismatchValues;

        foreach ($mismatchesWithKeys as $mismatch) {
            fputcsv($handle, $mismatch);
        }

        fclose($handle);
    }
}
