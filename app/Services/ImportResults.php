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

        // write column headers
        fputcsv($handle, config('imports.results.column_keys'));

        $mismatches->map(function ($mismatch) {
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
        })->each(function ($row) use ($handle) {
            fputcsv($handle, $row);
        });

        fclose($handle);
    }
}
