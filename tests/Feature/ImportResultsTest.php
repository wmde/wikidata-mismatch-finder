<?php

namespace Tests\Feature;

use App\Models\Mismatch;
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
        $user = User::factory()->uploader()->create();
        $import = ImportMeta::factory()->for($user)->create();

        $mismatches = Mismatch::factory(3)
            ->for($import)
            ->create();

        Storage::fake('local');
        $filename = 'temp_test.csv';

        Storage::put($filename, '');
        $path = Storage::path($filename);

        $this->travelTo(now()); // stop the clock

        $mismatchValues = array_map(function ($mismatch) {
            return [
                $mismatch['item_id'],
                $mismatch['statement_guid'],
                $mismatch['property_id'],
                $mismatch['wikidata_value'],
                $mismatch['meta_wikidata_value'],
                $mismatch['external_value'],
                $mismatch['external_url'],
                $mismatch['review_status']
            ];
        }, $mismatches->toArray());

        // Add CSV headers
        $mismatchesWithKeys = array_merge(
            [config('imports.results.column_keys')],
            $mismatchValues
        );
        $expected = $this->formatCsv($mismatchesWithKeys);

        $results = new ImportResults();
        $results->generateCSV($path, $import->id);

        $actualCSV = Storage::get($filename);

        $this->assertSame($expected, $actualCSV);

        $this->travelBack(); // resumes the clock
    }
    /**
     * @return void
     */
    private function formatCsv(array $mismatches): string
    {
        $csv = fopen('php://memory', 'r+');

        foreach ($mismatches as $row) {
            fputcsv($csv, $row);
        }

        rewind($csv);

        return stream_get_contents($csv);
    }
}
