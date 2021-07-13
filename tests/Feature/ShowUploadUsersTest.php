<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\UploadUser;

class ShowUploadUsersTest extends TestCase
{
    use RefreshDatabase;

    // phpcs:ignore
    public function test_shows_upload_users_table(): void
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
