<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use App\Models\ImportMeta;
use App\Models\Mismatch;
use Carbon\Carbon;

class CsvController extends Controller
{
    
    public function get_csv()
    {

        $imports = ImportMeta::get();
        $currentTime = Carbon::now()->format('Y-m-d');

        $headers = array(
            'Content-Type' => 'text/csv; charset=utf-8'
        );

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

        return Response::download($filename, "import-stats-" . $currentTime . ".csv", $headers);
    }
}
