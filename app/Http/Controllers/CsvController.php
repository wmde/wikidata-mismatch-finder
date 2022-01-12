<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use App\Services\ImportReport;
use Carbon\Carbon;

class CsvController extends Controller
{

    public function download_csv(ImportReport $report)
    {
        $currentTime = Carbon::now()->format('Y-m-d');

        $headers = array(
            'Content-Type' => 'text/csv; charset=utf-8'
        );

        $filename = $report->generateCSV($currentTime);

        return Response::download($filename, "import-stats-" . $currentTime . ".csv", $headers);
    }
}
