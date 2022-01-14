<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use App\Services\ImportReport;

class ImportsOverviewController extends Controller
{
    public function downloadCsv(ImportReport $report)
    {
        $filename = strtr(config('imports.report.filename_template'), [
            ':datetime' => now()->format('Y-m-d_H-i-s')
        ]);

        $headers = [ 'Content-Type' => 'text/csv; charset=utf-8' ];

        return response()->streamDownload(function() use ($report) {
            $report->generateCSV('php://output');
        }, $filename, $headers);
    }
}
