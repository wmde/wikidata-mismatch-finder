<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Database\Eloquent\Model;
use App\Models\UploadUser;
use Illuminate\Http\Testing\File;
use Illuminate\Testing\TestResponse;
use App\Http\Controllers\ImportController;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\ImportMeta;
use App\Http\Resources\ImportMetaResource;
use Illuminate\Testing\Fluent\AssertableJson;

class ApiRouteTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private const USER_ROUTE = 'api/user';
    private const IMPORTS_ROUTE = 'api/' . ImportController::RESOURCE_NAME;

    /**
     * Test non authenticated api/user route
     *
     *  @return void
     */
    public function test_unauthorized_api_user()
    {
        $response = $this->getJson(self::USER_ROUTE);
        $response->assertUnauthorized();
    }

    /**
     * Test the /api/user route
     *
     *  @return void
     */
    public function test_user_returns_user_data()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson(self::USER_ROUTE);
        $response->assertSuccessful()
            ->assertJsonStructure([
                'username',
                'mw_userid',
                'updated_at',
                'created_at',
                'id'
            ]);
    }

    /**
     * Test the /api/imports route' POST method
     *
     *  @return void
     */
    public function test_post_import_upload_file()
    {
        $user = User::factory()->uploader()->create();
        $file = UploadedFile::fake()->create('mismatchFile.csv');

        Storage::fake('local');
        Sanctum::actingAs($user);

        $this->travelTo(now()); // freezes time to ensure correct filenames
        $filename = strtr(config('imports.upload.filename_template'), [
            ':datetime' => now()->format('Ymd_His'),
            ':userid' => $user->getAttribute('mw_userid')
        ]);

        $response = $this->postJson(self::IMPORTS_ROUTE, ['mismatchFile' => $file]);
        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'description',
                'expires',
                'created',
                'uploader' => ['username']
            ]);

        $this->assertDatabaseHas('import_meta', [
            'filename' => $filename
        ]);
        Storage::disk('local')->assertExists('mismatch-files/' . $filename);

        $this->travelBack(); // resumes the clock
    }

     /**
     * Test unauthorized POST /api/imports
     *
     *  @return void
     */
    public function test_unauthorized_import()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('mismatchFile.csv');

        Sanctum::actingAs($user);

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

        $response = $this->postJson(self::IMPORTS_ROUTE, ['mismatchFile' => $file]);

        $response
            ->assertJsonValidationErrors([
                'mismatchFile' => __('validation.max.file', [
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

        $response = $this->postJson(self::IMPORTS_ROUTE, ['mismatchFile' => $file]);

        $response
            ->assertJsonValidationErrors([
                'mismatchFile' => __('validation.mimes', [
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
                'mismatchFile' => __('validation.required', [
                    'attribute' => 'mismatch file'
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
            'description' => $this->faker->realText($maxLength + 10)
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
     * Test single import
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
                'expires' => $import->expires->toJSON(),
                'created' => $import->created_at->toJSON(),
                'uploader' => [
                    'username' => $import->user->username
                ]
            ]);
    }

    /**
     * Test import list
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
                    'expires' => $import->expires,
                    'created' => $import->created_at->toJSON(),
                    'uploader' => [
                        'username' => $import->user->username
                    ]
                ]
            ]);
    }
}
