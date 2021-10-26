<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class SetUploadUsersTest extends TestCase
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

    public function test_fails_on_not_found()
    {
        $filename = 'nonexistant.txt';

        $this->artisan('uploadUsers:set', ['allowlist' => $filename])
            ->expectsOutput(__('admin.uploaders:reading', ['file' => $filename]))
            ->expectsOutput(__('admin.uploaders:not_found'))
            ->assertExitCode(1);
    }
}
