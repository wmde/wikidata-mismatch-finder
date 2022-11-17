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
use Illuminate\Database\Eloquent\Factories\Sequence;

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

        $mismatches = Mismatch::factory(3)
            ->for($import)
            ->state(new Sequence(
                ['review_status' => 'pending'],
                ['review_status' => 'wikidata'],
                ['review_status' => 'none'],
            ))->create();

        Storage::fake('local');
        $filename = 'temp_test.csv';

        Storage::put($filename, '');
        $path = Storage::path($filename);

        $total = $mismatches->count();
        $pending = $mismatches->where('review_status', 'pending')->count();

        $this->travelTo(now()); // stop the clock

        $expected = $this->formatCsv([
            config('imports.report.headers'),
            [
                $import->id, // Import ID
                $import->status, // Import status
                $total, // Mismatch count
                $mismatches->where('review_status', 'wikidata')->count(), // Error on wikidata count
                $mismatches->where('review_status', 'missing')->count(), // Missing on wikidata count
                $mismatches->where('review_status', 'external')->count(), // Error on external count
                $mismatches->where('review_status', 'both')->count(), // Error on both count
                $mismatches->where('review_status', 'none')->count(), // Error on none count
                $pending, // Pending count
                100 - ($pending / $total * 100), // % completed
                $import->expires, // Expiry date
                $import->expires < now() ? 'y' : 'n' // Expired
            ]
        ]);

        $report = new ImportReport();
        $report->generateCSV($path);

        $result = Storage::get($filename);

        $this->assertSame($expected, $result);

        $this->travelBack(); // resumes the clock
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
