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
    use RefreshDatabase, WithFaker;

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


    /**
     * Ensure validation fails on missing statement guids
     */
    public function test_fails_on_missing_statement_guids(): void
    {
        $import = $this->createMockImport(
            'missing_guid.csv',
            ',P569,3 April 1934,some_date,from_some_URL' // Emulate missing guid
        );

        $this->expectValidationException(__('validation.required', [
            'attribute' => 'statement guid'
        ]));

        ValidateCSV::dispatch($import);

        $this->assertFailedImport($import);
    }

    /**
     * Ensure validation fails on long statement guids
     */
    public function test_fails_on_long_statement_guids(): void
    {
        $maxLength = config('mismatches.validation.guid.max_length');
        $longQID= $this->faker->numerify('Q' . str_repeat('#', $maxLength));

        $import = $this->createMockImport(
            'long_guid.csv',
            $longQID . '$' . $this->faker->uuid() . ',' // Emulate long guid
            . 'P569,3 April 1934,some_date,from_some_URL' // Ensure correct columns
        );

        $this->expectValidationException(__('validation.max.string', [
            'attribute' => 'statement guid',
            'max' => $maxLength
        ]));

        ValidateCSV::dispatch($import);

        $this->assertFailedImport($import);
    }

     /**
     * Ensure validation fails on malformed statement guids
     */
    public function test_fails_on_malformed_statement_guids(): void
    {
        $import = $this->createMockImport(
            'long_guid.csv',
            'some-malformed-guid,' // Emulate malformed guid
            . 'P569,3 April 1934,some_date,from_some_URL' // Ensure correct columns
        );

        $this->expectValidationException(__('validation.regex', [
            'attribute' => 'statement guid'
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
