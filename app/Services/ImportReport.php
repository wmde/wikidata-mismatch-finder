<?php

namespace App\Services;

use App\Models\Mismatch;
use App\Models\ImportMeta;

class ImportReport
{
    public function generateCSV(string $path): void
    {
        $imports = ImportMeta::get();

        // Creating the output stream
        $handle = fopen($path, 'w');

        // write column headers
        fputcsv($handle, config('imports.report.headers'));

        foreach ($imports as $each_import) {
            $mismatches = Mismatch::where('import_id', $each_import->id)->get();
            $mismatches_count = $mismatches->count();
            $review_statuses = config('mismatches.validation.review_status.accepted_values');
            $percent_completed = 0;

            foreach ($review_statuses as $review_status) {
                ${"mismatches_" . $review_status} = $mismatches->where('review_status', $review_status)->count();
            }

            if ($mismatches_count > 0) {
                $percent_completed = (($mismatches_count - $mismatches_pending) / $mismatches_count) * 100;
            }

            fputcsv($handle, [
                $each_import->id,
                $each_import->status,
                $mismatches_count,
                $mismatches_wikidata,
                $mismatches_external,
                $mismatches_both,
                $mismatches_none,
                $mismatches_pending,
                $percent_completed,
                $each_import->expires,
                $each_import->expires < now() ? 'y' : 'n'
            ]);
        }

        fclose($handle);
    }
}
