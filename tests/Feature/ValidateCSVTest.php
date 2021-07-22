<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\ImportMeta;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ValidateCSV;
use App\Exceptions\ImportValidationException;

class ValidateCSVTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure validation fails on lines with too few columns
     */
    public function test_fails_on_too_few_columns(): void
    {
        $colCount = config('imports.upload.col_count');

        $import = $this->createMockImport(
            'too-few-columns.csv',
            str_repeat(',', $colCount - 2) // Emulate one column too few
        );

        $this->expectValidationException(__('validation.import.columns', [
            'amount' => $colCount
        ]));

        ValidateCSV::dispatch($import);

        $this->assertFailedImport($import);
    }

    /**
     * Ensure validation fails on lines with too many columns
     */
    public function test_fails_on_too_many_columns(): void
    {
        $colCount = config('imports.upload.col_count');

        $import = $this->createMockImport(
            'too-many-columns.csv',
            str_repeat(',', $colCount) // Emulate one column too many
        );

        $this->expectValidationException(__('validation.import.columns', [
            'amount' => $colCount
        ]));

        ValidateCSV::dispatch($import);

        $this->assertFailedImport($import);
    }



    private function createMockImport(string $filename, string $content): ImportMeta
    {
        Storage::fake('local');
        Storage::put(
            'mismatch-files/' . $filename,
            "\n" . $content // Emulate empty header line
        );

        $user = User::factory()->uploader()->create();
        return ImportMeta::factory()->for($user)->create([
            'filename' => $filename
        ]);
    }

    private function expectValidationException(string $message): void
    {
        $this->expectException(ImportValidationException::class);
        $this->expectExceptionMessage($message);
    }

    private function assertFailedImport(ImportMeta $import): void
    {
        $this->assertDatabaseCount('failed_jobs', 1);
        $this->assertDatabaseHas('import_meta',[
            'id' => $import->id,
            'status' => 'failed'
        ]);
    }
}
