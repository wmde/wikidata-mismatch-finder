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

        foreach ($mismatches as $mismatch) {
            fputcsv($handle, [
            $mismatch
            ]);
        }

        fclose($handle);
    }
}
