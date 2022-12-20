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

        $importMeta = ImportMeta::find($import_id);
        $mismatches = $importMeta->mismatches();

        foreach ($mismatches as $mismatch) {
            fputcsv($handle, [
            $mismatch
            ]);
        }
    }
}
