<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ApiRouteTest extends TestCase
{
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

    use RefreshDatabase;

    /**
     * Test non authenticated api/user route
     *
     *  @return void
     */
    public function test_nonAuthenticated_api_user_willRedirect()
    {
        $response = $this->get('/api/user');
        $response->assertStatus(302);
    }

    /**
     * Test the /api/upload route's get method
     *
     *  @return void
     */
    public function test_get_upload_wrongMethod()
    {
        $response = $this->get('/api/upload');
        $response->assertStatus(405);
    }

    /**
     * Test the /api/user route
     *
     *  @return void
     */
    // phpcs:ignore
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

    // TODO: re-work after merging of
    // https://github.com/wmde/wikidata-mismatch-finder/pull/12

    /**
     * Test the /api/upload route
     *
     *  @return void
     */
    // phpcs:ignore
    public function test_upload_file()
    {
        $this->travelTo(now()); // freezes time to ensure correct filenames

        $user = User::factory()->create();

        Storage::fake('local');
        $file = UploadedFile::fake()->create('mismatchFile.csv');

        Sanctum::actingAs($user);
        
        $response = $this->post('/api/upload', ['mismatchFile' => $file]);

        $response->assertStatus(201);

        $filename = now()->format('Ymd_His') . '-mismatch-upload.' . $user->getAttribute('mw_userid') . '.csv';

        Storage::disk('local')->assertExists('mismatch-files/' . $filename);

        $this->travelBack(); // resumes the clock
    }

    /**
     * Test the /api/upload route
     *
     *  @return void
     */
    // phpcs:ignore
    public function test_upload_file_not_bigger_10Mb()
    {
        $user = User::factory()->create();

        Storage::fake('mismatchFiles');
        $sizeInKilobytes = 12000; //maximum file size is 10000

        $file = UploadedFile::fake()->create('mismatchFile.csv', $sizeInKilobytes);

        Sanctum::actingAs($user);

        $response = $this->post('/api/upload', ['mismatchFile' => $file]);

        $response->assertStatus(302);
    }
}
