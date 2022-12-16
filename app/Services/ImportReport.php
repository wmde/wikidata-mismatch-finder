<?php

namespace App\Services;

use App\Models\ImportMeta;
use Illuminate\Support\Facades\DB;

class ImportReport
{
    public function generateCSV(string $path): void
    {
        $imports = ImportMeta::get();

        // Creating the output stream
        $handle = fopen($path, 'w');

        // write column headers
        fputcsv($handle, config('imports.report.headers'));

        $res = DB::table('mismatches')
            ->select('import_id', 'review_status')
            ->selectRaw('COUNT(*)')
            ->groupBy('import_id', 'review_status')
            ->get();

        $review_statuses = config('mismatches.validation.review_status.accepted_values');

        foreach ($imports as $each_import) {
            foreach ($review_statuses as $status) {
                ${"mismatches_" . $status} = 0;
            }
            $mismatches_count = 0;
            $percent_completed = 0;

            $importStatusCountRows = $res->where('import_id', '=', $each_import->id);
            foreach ($importStatusCountRows as $importStatusCountRow) {
                $count = $importStatusCountRow->{'COUNT(*)'};
                ${"mismatches_" . $importStatusCountRow->review_status} = $count;
                $mismatches_count += $count;
            }

            if ($mismatches_count > 0) {
                $percent_completed = (($mismatches_count - $mismatches_pending) / $mismatches_count) * 100;
            }

            fputcsv($handle, [
                $each_import->id,
                $each_import->status,
                $mismatches_count,
                $mismatches_wikidata,
                $mismatches_missing,
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
