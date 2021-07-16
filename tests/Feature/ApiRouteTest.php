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

class ApiRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test non authenticated api/user route
     *
     *  @return void
     */
    public function test_non_authenticated_api_user_will_redirect()
    {
        $response = $this->get('/api/user');
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

        $response = $this->get('/api/user');
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
     * Test the /api/import route's GET method
     *
     *  @return void
     */
    public function test_get_import_wrong_method()
    {
        $response = $this->get('/api/import');
        $response->assertStatus(405);
    }

    /**
     * Test the /api/import route' POST method
     *
     *  @return void
     */
    public function test_post_import_upload_file()
    {


        $user = $this->createFakeUploader();
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
     * Test unauthorized POST /api/import
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
     * Test invalid file size in /api/upload
     *
     *  @return void
     */
    public function test_upload_file_too_big()
    {
        $maxSize = config('filesystems.uploads.max_size');
        $sizeInKilobytes = $maxSize + 10;
        $user = $this->createFakeUploader();
        $file = UploadedFile::fake()->create('mismatchFile.csv', $sizeInKilobytes);

        Storage::fake('local');
        Sanctum::actingAs($user);

        $response = $this->makePostImportApiRequest(['mismatchFile' => $file]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'mismatchFile' => [__('validation.max.file', [
                        'attribute' => 'mismatch file',
                        'max' => $maxSize
                    ])]
                ]
            ]);
    }

    private function createFakeUploader(): Model
    {
        $user = User::factory()->createOne();
        UploadUser::factory()->createOne([
            'username' => $user->getAttribute('username')
        ]);

        return $user;
    }

    private function makePostImportApiRequest( array $payload ): TestResponse
    {
        return $this->withHeaders([
            'Accept' => 'application/json'
        ])->post('/api/import', $payload);
    }
}
