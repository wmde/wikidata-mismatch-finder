<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use App\Models\ImportMeta;
use App\Services\CSVImportStatsFileGenerator;
use Carbon\Carbon;

class CsvController extends Controller
{
    
    public function download_csv()
    {

        $imports = ImportMeta::get();
        $currentTime = Carbon::now()->format('Y-m-d');

        $headers = array(
            'Content-Type' => 'text/csv; charset=utf-8'
        );

        $csvStatsFileGenerator = new CSVImportStatsFileGenerator();

        $filename = $csvStatsFileGenerator->generateCSV($imports, $currentTime);

        return Response::download($filename, "import-stats-" . $currentTime . ".csv", $headers);
    }
}
