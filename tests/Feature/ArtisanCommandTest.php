<?php

namespace Tests\Feature;

use App\Models\ImportMeta;
use App\Models\UploadUser;
use App\Models\User;
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

    public function test_show_upload(): void
    {
        $imports = ImportMeta::factory(5)
        ->for(User::factory()->uploader())
        ->state(['status' => 'completed'])
        ->create()
        ->map(function ($import) {
            return [
                $import->id,
                $import->created_at->toDateString(),
                $import->external_source,
                $import->user->username,
                $import->expires->toDateString(),
                0
            ];
        });

        $this->artisan('uploads:show')
            ->expectsTable(
                ['ID', 'Upload Date', 'External Source', 'User', 'Expires at', '# of Mismatches'],
                $imports
            )
            ->assertExitCode(0);
    }

}
