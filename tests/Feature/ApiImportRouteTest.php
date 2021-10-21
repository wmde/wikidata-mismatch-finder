<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Http\Controllers\ImportController;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\ImportMeta;
use App\Jobs\ValidateCSV;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ImportCSV;
use App\Models\ImportFailure;

class ApiImportRouteTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private const IMPORTS_ROUTE = 'api/' . ImportController::RESOURCE_NAME;

    /**
     * Test the /api/imports route' POST method
     *
     *  @return void
     */
    public function test_post_import_upload_file()
    {
        $user = User::factory()->uploader()->create();
        $file = UploadedFile::fake()->create('mismatchFile.csv');

        Bus::fake();
        Storage::fake('local');
        Sanctum::actingAs($user);

        $this->travelTo(now()); // freezes time to ensure correct filenames
        $filename = strtr(config('imports.upload.filename_template'), [
            ':datetime' => now()->format('Ymd_His'),
            ':userid' => $user->getAttribute('mw_userid')
        ]);

        $response = $this->postJson(
            self::IMPORTS_ROUTE,
            [
                'mismatch_file' => $file,
                'description' => 'some description',
                'external_source' => 'some source',
                'external_source_url' => 'some source_url'
            ]
        );
        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'description',
                'external_source',
                'external_source_url',
                'expires',
                'created',
                'uploader' => ['username']
            ])->assertJson([
                'description' => 'some description',
                'external_source' => 'some source',
                'external_source_url' => 'some source_url'
            ]);

        $this->assertDatabaseHas('import_meta', [
            'filename' => $filename
        ]);

        Storage::disk('local')->assertExists('mismatch-files/' . $filename);

        Bus::assertChained([
            ValidateCSV::class,
            ImportCSV::class
        ]);

        $this->travelBack(); // resumes the clock
    }

     /**
     * Test unauthorized POST /api/imports
     *
     *  @return void
     */
    public function test_unauthorized_import()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson(self::IMPORTS_ROUTE);

        $response->assertForbidden();
    }

    /**
     * Test invalid file size in /api/imports
     *
     *  @return void
     */
    public function test_import_file_too_big()
    {
        $maxSize = config('filesystems.uploads.max_size');
        $sizeInKilobytes = $maxSize + 10;
        $user = User::factory()->uploader()->create();
        $file = UploadedFile::fake()->create('mismatchFile.csv', $sizeInKilobytes);

        Storage::fake('local');
        Sanctum::actingAs($user);

        $response = $this->postJson(self::IMPORTS_ROUTE, ['mismatch_file' => $file]);

        $response
            ->assertJsonValidationErrors([
                'mismatch_file' => __('validation.max.file', [
                    'attribute' => 'mismatch file',
                    'max' => $maxSize
                ])
            ]);
    }

     /**
     * Test invalid file format in /api/imports
     *
     *  @return void
     */
    public function test_import_wrong_file_format()
    {
        $user = User::factory()->uploader()->create();
        $file = UploadedFile::fake()->create('mismatchFile.xls');

        Storage::fake('local');
        Sanctum::actingAs($user);

        $response = $this->postJson(self::IMPORTS_ROUTE, ['mismatch_file' => $file]);

        $response
            ->assertJsonValidationErrors([
                'mismatch_file' => __('validation.mimes', [
                    'attribute' => 'mismatch file',
                    'values' => 'csv, txt'
                ])
            ]);
    }

    /**
     * Test missing file field in /api/imports
     *
     *  @return void
     */
    public function test_import_missing_file()
    {
        $user = User::factory()->uploader()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(self::IMPORTS_ROUTE);

        $response
            ->assertJsonValidationErrors([
                'mismatch_file' => __('validation.required', [
                    'attribute' => 'mismatch file'
                ])
            ]);
    }

    /**
     * Test missing external_source field in /api/imports
     *
     *  @return void
     */
    public function test_import_missing_external_source()
    {
        $user = User::factory()->uploader()->create();
        $file = UploadedFile::fake()->create('mismatchFile.csv');

        Bus::fake();
        Storage::fake('local');
        Sanctum::actingAs($user);

        $response = $this->postJson(self::IMPORTS_ROUTE, ['mismatch_file' => $file]);

        $response
            ->assertJsonValidationErrors([
                'external_source' => __('validation.required', [
                    'attribute' => 'external source'
                ])
            ]);
    }

    /**
     * Test long external_source field in /api/imports
     *
     *  @return void
     */
    public function test_import_long_external_source()
    {
        $maxLength = config('imports.external_source.max_length');
        $user = User::factory()->uploader()->create();
        $file = UploadedFile::fake()->create('mismatchFile.csv');

        Bus::fake();
        Storage::fake('local');
        Sanctum::actingAs($user);

        $response = $this->postJson(
            self::IMPORTS_ROUTE,
            [
                'mismatch_file' => $file,
                'external_source' => $this->faker->realTextBetween($maxLength + 10, $maxLength + 100),
            ]
        );

        $response
            ->assertJsonValidationErrors([
                'external_source' => __('validation.max.string', [
                    'attribute' => 'external source',
                    'max' => $maxLength
                ])
            ]);
    }

    /**
     * Test long description field in /api/imports
     *
     *  @return void
     */
    public function test_import_long_description()
    {
        $maxLength = config('imports.description.max_length');
        $user = User::factory()->uploader()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(self::IMPORTS_ROUTE, [
            'description' => $this->faker->realTextBetween($maxLength + 10, $maxLength + 100)
        ]);

        $response
            ->assertJsonValidationErrors([
                'description' => __('validation.max.string', [
                    'attribute' => 'description',
                    'max' => $maxLength
                ])
            ]);
    }

    /**
     * Test expired best before field in /api/imports
     *
     *  @return void
     */
    public function test_import_expired_date()
    {
        $user = User::factory()->uploader()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(self::IMPORTS_ROUTE, [
            'expires' => '1986-05-04'
        ]);

        $response
            ->assertJsonValidationErrors([
                'expires' => __('validation.after', [
                    'attribute' => 'expires',
                    'date' => config('imports.expires.after')
                ])
            ]);
    }

    /**
     * Test get single import
     *
     *  @return void
     */
    public function test_get_single_import()
    {
        $user = User::factory()->uploader()->create();
        $import = ImportMeta::factory()->for($user)->create();

        $response = $this->getJson(self::IMPORTS_ROUTE . '/' . $import->id);

        $response
            ->assertSuccessful()
            ->assertJson([
                'id' => $import->id,
                'status' => $import->status,
                'description' => $import->description,
                'external_source' => $import->external_source,
                'external_source_url' => $import->external_source_url,
                'expires' => $import->expires->toJSON(),
                'created' => $import->created_at->toJSON(),
                'uploader' => [
                    'username' => $import->user->username
                ]
            ]);
    }

    /**
     * Test get failed import
     *
     *  @return void
     */
    public function test_get_failed_import()
    {
        $user = User::factory()->uploader()->create();
        $import = ImportMeta::factory()->for($user)->create([
            'status' => 'failed'
        ]);
        $failure = ImportFailure::factory()->for($import)->create();

        $response = $this->getJson(self::IMPORTS_ROUTE . '/' . $import->id);

        $response
            ->assertSuccessful()
            ->assertJson(['error' => $failure->message]);
    }

    /**
     * Test get list of all imports
     *
     *  @return void
     */
    public function test_get_import_list()
    {
        $user = User::factory()->uploader()->create();
        $import = ImportMeta::factory()->for($user)->create();

        $response = $this->getJson(self::IMPORTS_ROUTE);

        // response should contain a list with $import as element
        $response
            ->assertSuccessful()
            ->assertJson([
                [
                    'id' => $import->id,
                    'status' => $import->status,
                    'expires' => $import->expires->toJSON(),
                    'created' => $import->created_at->toJSON(),
                    'uploader' => [
                        'username' => $import->user->username
                    ]
                ]
            ]);
    }
}
