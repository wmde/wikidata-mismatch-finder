<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use App\Services\ImportReport;

class CsvController extends Controller
{
    public function download_csv(ImportReport $report)
    {
        $currentTime = now()->format('Y-m-d_H-i-s');
        $filename = "import-stats-$currentTime.csv";

        $headers = [ 'Content-Type' => 'text/csv; charset=utf-8' ];

        $filepath = $report->generateCSV($filename);

        return Response::download($filepath, $filename, $headers);
    }
}
