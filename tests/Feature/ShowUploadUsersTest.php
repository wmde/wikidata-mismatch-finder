<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\UploadUser;

class ShowUploadUsersTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_shows_upload_users_table()
    {
        $uploaders = UploadUser::factory(5)->create();

        $this->artisan('uploadUsers:show')
            ->expectsTable(
                ['ID', 'Username'],
                $uploaders->map->only('id', 'username')
            )
            ->assertExitCode(0);
    }
}
