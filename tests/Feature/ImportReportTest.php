<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\ImportReport;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\ImportMeta;
use App\Models\Mismatch;

class ImportReportTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    /**
     * @return void
     */
    public function test_generate_csv_writes_to_path(): void
    {
        $user = User::factory()->uploader()->create();
        $import = ImportMeta::factory()->for($user)->create(
            ['status' => 'completed']
        );
        Mismatch::factory()->for($import)->create();

        Storage::fake('local');
        $filename = 'temp_test.csv';

        Storage::put($filename, '');
        $path = Storage::path($filename);

        $expected = $this->formatCsv([
            config('imports.report.headers'),
            [
                1, // Import ID
                'completed', // Import status
                1, // Mismatch count
                0, // Error on wikidata count
                0, // Error on external count
                0, // Error on both count
                0, // Error on none count
                1, // Pending count
                0, // % completed
            ]
        ]);

        $report = new ImportReport();
        $report->generateCSV($path);

        $result = Storage::get($filename);

        $this->assertSame($expected, $result);
    }
    /**
     * @return void
     */
    private function formatCsv(array $array): string
    {
        $csv = fopen('php://memory', 'r+');

        foreach ($array as $row) {
            fputcsv($csv, $row);
        }

        rewind($csv);

        return stream_get_contents($csv);
    }
}
