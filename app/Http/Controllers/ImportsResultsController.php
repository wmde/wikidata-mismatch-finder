<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImportsResultsController extends Controller
{
    public function showResultsCsv(string $import_id)
    {
        $filename = strtr(config('imports.results.filename_template'), [
            ':datetime' => now()->format('Y-m-d_H-i-s')
        ]);

        $headers = [ 'Content-Type' => 'text/csv; charset=utf-8' ];

        return response()->streamDownload(function () use ($import_id) {
            $report->generateCSV('php://output');
        }, $filename, $headers);
    }
}
