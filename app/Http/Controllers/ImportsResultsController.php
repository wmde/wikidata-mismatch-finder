<?php

namespace App\Http\Controllers;

use App\Services\ImportResults;

class ImportsResultsController extends Controller
{
    public function showResultsCsv(int $import_id)
    {
        $filename = strtr(config('imports.results.filename_template'), [
            ':datetime' => now()->format('Y-m-d_H-i-s')
        ]);

        $headers = [ 'Content-Type' => 'text/csv; charset=utf-8' ];

        return response()->streamDownload(function () use ($import_id) {
            // make the file happen somehow
            ImportResults::class->generateCSV('php://output', $import_id);
        }, $filename, $headers);
    }
}
