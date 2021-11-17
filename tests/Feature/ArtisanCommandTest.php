<?php

namespace Tests\Feature;

use App\Models\UploadUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class ArtisanCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sets_upload_users_list()
    {
        $filename = 'uploaders.txt';
        $contents = "TinkyWinky\nDipsy\nLaaLaa\nPo";

        Storage::fake('allowlist');
        Storage::disk('allowlist')->put($filename, $contents);

        $this->artisan('uploadUsers:set', ['allowlist' => $filename])
            ->expectsOutput(__('admin.uploaders:reading', ['file' => $filename]))
            ->doesntExpectOutput(__('admin.uploaders:not_found'))
            ->expectsOutput(__('admin.uploaders:success', ['count' => 4]))
            ->assertExitCode(0);
    }

    public function test_fails_on_upload_users_file_not_found()
    {
        $filename = 'nonexistent.txt';

        $this->artisan('uploadUsers:set', ['allowlist' => $filename])
            ->expectsOutput(__('admin.uploaders:reading', ['file' => $filename]))
            ->expectsOutput(__('admin.uploaders:not_found'))
            ->assertExitCode(1);
    }

    public function test_shows_upload_users_table(): void
    {
        $uploaders = UploadUser::factory(5)->create();

        $this->artisan('uploadUsers:show')
            ->expectsTable(
                ['ID', 'Username'],
                $uploaders->map->only('id', 'username')->toArray()
            )
            ->assertExitCode(0);
    }
}
