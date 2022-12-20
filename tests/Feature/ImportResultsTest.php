<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\ImportResults;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\ImportMeta;

class ImportResultsTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    /**
     * @return void
     */
    public function test_generate_csv_writes_to_path(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $user = User::factory()->uploader()->create();
        $import = ImportMeta::factory()->for($user)->create(
            ['status' => 'completed']
        );
        $expiredImportWithoutMismatches = ImportMeta::factory()
            ->for($user)
            ->expired()
            ->create();

        $importMeta = ImportMeta::find($import->id);
        $mismatches = $importMeta->mismatches();

        Storage::fake('local');
        $filename = 'temp_test.csv';

        Storage::put($filename, '');
        $path = Storage::path($filename);

        $this->travelTo(now()); // stop the clock

        $expected = $this->formatCsv($mismatches);

        $results = new ImportResults();
        $results->generateCSV($path, $import->id);

        $actualCSV = Storage::get($filename);

        $this->assertSame($expected, $actualCSV);

        $this->travelBack(); // resumes the clock
    }
    /**
     * @return void
     */
    private function formatCsv($mismatches): string
    {
        $csv = fopen('php://memory', 'r+');

        foreach ($mismatches as $row) {
            fputcsv($csv, $row);
        }

        rewind($csv);

        return stream_get_contents($csv);
    }
}
