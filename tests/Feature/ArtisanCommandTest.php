<?php

namespace Tests\Feature;

use App\Models\ImportMeta;
use App\Models\Mismatch;
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

    public function test_list_imports(): void
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

        $this->artisan('import:list')
            ->expectsTable(
                ['ID', 'Import Date', 'External Source', 'User', 'Expires at', '# of Mismatches'],
                $imports
            )
            ->assertExitCode(0);
    }

    public function test_drop_import_success(): void
    {
        // seed two imports with mismatches, one to be deleted
        $imports = ImportMeta::factory(2)
        ->for(User::factory()->uploader())
        ->create()
        ->each(function ($import) {
            Mismatch::factory(2)->for($import)->create();
        });

        $idToDelete = $imports[0]->id;
        $this->artisan('import:drop', ["id" => $idToDelete])
            ->expectsOutput(__('admin.dropImport:dropping', ['id' => $idToDelete, 'mismatches' => 2]))
            ->expectsConfirmation(__('admin.dropImport:confirm'), 'yes')
            ->expectsOutput(__('admin.dropImport:success', ['id' => $idToDelete, 'mismatches' => 2]))
            ->assertExitCode(0);

            // make sure the first import has been deleted with all its mismatches
            $this->assertDatabaseMissing('import_meta', [ 'id' => $idToDelete]);
            $this->assertDatabaseMissing('mismatches', ['import_id' => $idToDelete]);

            // make sure the second import is still there with all its mismatches
            $this->assertDatabaseHas('import_meta', [ 'id' => $imports[1]->id]);
            $this->assertDatabaseHas('mismatches', [ 'import_id' => $imports[1]->id]);
    }

    public function test_drop_import_notFound(): void
    {
        $this->artisan('import:drop', ['id' => 'nonexistent'])
            ->expectsOutput(__('admin.dropImport:notFound', ['id' => 'nonexistent']))
            ->assertExitCode(1);
    }
}
