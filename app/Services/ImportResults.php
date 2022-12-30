<?php

namespace App\Services;

use App\Models\ImportMeta;
use App\Models\Mismatch;

class ImportResults
{
    public function generateCSV(string $path, ImportMeta $import): void
    {
        $mismatches = Mismatch::get();

        // Creating the output stream
        $handle = fopen($path, 'w');

        // Get the mismatches as a LazyCollection
        $mismatches = Mismatch::whereBelongsTo($import)->cursor();

        // write column headers
        $keys = config('imports.results.column_keys');
        fputcsv($handle, $keys);

        // map mismatch values to correspond to column keys
        $mismatches->map(function ($mismatch) use ($keys) {
            return array_map(function ($key) use ($mismatch) {
                return $mismatch[$key];
            }, $keys);
        })->each(function ($row) use ($handle) {
            fputcsv($handle, $row);
        });

        fclose($handle);
    }
}
