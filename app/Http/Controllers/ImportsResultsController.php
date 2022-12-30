<?php

namespace App\Http\Controllers;

use App\Models\ImportMeta;
use App\Services\ImportResults;

class ImportsResultsController extends Controller
{
    public function showResultsCsv(int $import_id, ImportResults $results)
    {
        $import = ImportMeta::findOrFail($import_id);

        $filename = strtr(config('imports.results.filename_template'), [
            ':id' => $import_id,
            ':datetime' => now()->format('Y-m-dTH:i:s')
        ]);

        $headers = [ 'Content-Type' => 'text/csv; charset=utf-8' ];

        return response()->streamDownload(function () use ($import, $results) {
            $results->generateCSV('php://output', $import);
        }, $filename, $headers);
    }
}
