<?php

namespace App\Http\Controllers;

class ImportsResultsController extends Controller
{
    public function showResultsCsv(string $import_id)
    {
        $filename = strtr(config('imports.results.filename_template'), [
            ':datetime' => now()->format('Y-m-d_H-i-s')
        ]);

        $headers = [ 'Content-Type' => 'text/csv; charset=utf-8' ];

        return response()->streamDownload(function () use ($import_id) {
            // make the file happen somehow
        }, $filename, $headers);
    }
}
