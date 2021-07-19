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

class ApiRouteTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private const USER_ROUTE = 'api/user';
    private const IMPORTS_ROUTE = 'api/' . ImportController::RESOURCE_NAME;

    /**
     * @var Model|null
     */
    private $fakeUploader = null;

    /**
     * Test non authenticated api/user route
     *
     *  @return void
     */
    public function test_non_authenticated_api_user_will_redirect()
    {
        $response = $this->get(self::USER_ROUTE);
        $response->assertStatus(302);
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

        $response = $this->get(self::USER_ROUTE);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'username',
                'mw_userid',
                'updated_at',
                'created_at',
                'id'
            ]);
    }

    /**
     * Test the /api/imports route's GET method
     *
     *  @return void
     */
    public function test_get_import_wrong_method()
    {
        $response = $this->get(self::IMPORTS_ROUTE);
        $response->assertStatus(405);
    }

    /**
     * Test the /api/imports route' POST method
     *
     *  @return void
     */
    public function test_post_import_upload_file()
    {
        $user = $this->getFakeUploader();
        $file = UploadedFile::fake()->create('mismatchFile.csv');

        Storage::fake('local');
        Sanctum::actingAs($user);

        $this->travelTo(now()); // freezes time to ensure correct filenames
        $filename = now()->format('Ymd_His') . '-mismatch-upload.' . $user->getAttribute('mw_userid') . '.csv';

        $response = $this->makePostImportApiRequest(['mismatchFile' => $file]);
        $response->assertStatus(201);

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

        $response = $this->makePostImportApiRequest(['mismatchFile' => $file]);

        $response->assertStatus(403);
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
        $user = $this->getFakeUploader();
        $file = UploadedFile::fake()->create('mismatchFile.csv', $sizeInKilobytes);

        Storage::fake('local');
        Sanctum::actingAs($user);

        $response = $this->makePostImportApiRequest(['mismatchFile' => $file]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.mismatchFile', [
                __('validation.max.file', [
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
        $user = $this->getFakeUploader();
        $file = UploadedFile::fake()->create('mismatchFile.xls');

        Storage::fake('local');
        Sanctum::actingAs($user);

        $response = $this->makePostImportApiRequest(['mismatchFile' => $file]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.mismatchFile', [
                __('validation.mimes', [
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
        $user = $this->getFakeUploader();

        Sanctum::actingAs($user);

        $response = $this->makePostImportApiRequest();

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.mismatchFile', [
                __('validation.required', ['attribute' => 'mismatch file'])
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
        $user = $this->getFakeUploader();

        Sanctum::actingAs($user);

        $response = $this->makePostImportApiRequest([
            'description' => $this->faker->realText($maxLength + 10)
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.description', [
                __('validation.max.string', [
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
        $user = $this->getFakeUploader();

        Sanctum::actingAs($user);

        $response = $this->makePostImportApiRequest([
            'bestBefore' => '1986-05-04'
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.bestBefore', [
                __('validation.after', [
                    'attribute' => 'best before',
                    'date' => config('imports.best_before.after')
                ])
            ]);
    }

    private function getFakeUploader(): Model
    {
        if (!$this->fakeUploader) {
            $this->fakeUploader = User::factory()->uploader()->create();
        }

        return $this->fakeUploader;
    }

    private function makePostImportApiRequest(array $payload = []): TestResponse
    {
        return $this->withHeaders([
            'Accept' => 'application/json'
        ])->post(self::IMPORTS_ROUTE, $payload);
    }
}
