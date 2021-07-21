<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use App\Models\ImportMeta;
use App\Jobs\ImportCSV;
use Illuminate\Support\Str;
use App\Models\User;

class ImportCSVTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Ensure import persists mismatches to database
     */
    public function test_creates_mismatches(): void
    {
        $filename = 'creates-mismatches-test.csv';
        $user = User::factory()->uploader()->create();
        $import = ImportMeta::factory()->for($user)->create([
            'filename' => $filename
        ]);

        Storage::fake('local');
        $path = Storage::putFileAs(
            'mismatch-files',
            new File(__DIR__ . '/../file-fixtures/successful-import.csv'),
            $filename
        );

        $lines = Str::of(Storage::get($path))->trim()->explode("\n");
        $line = Str::of($lines->get(1))->explode(',')->all();
        $keys = [
            'statement_guid',
            'property_id',
            'wikidata_value',
            'external_value',
            'external_url'
        ];

        $expected = array_combine($keys, $line);

        ImportCSV::dispatch($import);

        $this->assertDatabaseCount('mismatches', $lines->count() - 1); // Except table headers
        $this->assertDatabaseHas('mismatches', [
            'import_id' => $import->id
        ]);
        $this->assertDatabaseHas('mismatches', $expected);
    }
}
