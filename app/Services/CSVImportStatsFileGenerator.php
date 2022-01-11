<?php

namespace App\Services;

use App\Models\Mismatch;
use Illuminate\Support\Facades\File;

class CSVImportStatsFileGenerator
{
    public function generateCSV(object $imports, string $currentTime)
    {
        if (!File::exists(storage_path("/import_stats"))) {
            File::makeDirectory(storage_path("/import_stats"));
        }

        //creating the download file
        $filename =  storage_path("/import_stats/import-stats-" . $currentTime . ".csv");
        $handle = fopen($filename, 'w');

        //adding the first row
        fputcsv($handle, [
            "upload",
            "status",
            "mismatches",
            "decisions with an error on Wikidata",
            "decisions with an error in external source",
            "decisions with an error in both",
            "decisions with an error in neither",
            "undecided mismatches",
            "% completed",
        ]);

        foreach ($imports as $each_import) {
            $mismatches = Mismatch::where('import_id', $each_import->id)->get();
            $mismatches_count = $mismatches->count();
            $review_statuses = array( 'wikidata', 'external', 'both','none', 'pending' );
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
                $percent_completed
            ]);
        }
        fclose($handle);

        return $filename;
    }
}
