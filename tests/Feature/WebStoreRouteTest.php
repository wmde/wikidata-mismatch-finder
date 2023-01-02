<?php

namespace Tests\Feature;

use App\Models\ImportMeta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebStoreRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the /store route
     *
     *  @return void
     */
    public function test_store_home()
    {
        $response = $this->get(route('store.home'));

        $response->assertRedirect(route('store.api-settings'));
    }

    /**
     * Test the authenticated /store/api-settings route
     *
     *  @return void
     */
    public function test_store_api_settings()
    {
        $response = $this->get(route('store.api-settings'));

        $response->assertSuccessful();
        $response->assertViewIs('showToken');
    }

    /**
     * Test the store/imports route
     *
     *  @return void
     */
    public function test_store_import_status()
    {
        $response = $this->get(route('store.import-status'));

        $response->assertSuccessful();
        $response->assertViewIs('importStatus');
    }

    /**
     * Test the store imports report csv download route
     *
     *  @return void
     */
    public function test_store_import_csv_stats()
    {
        $filename = strtr(config('imports.report.filename_template'), [
            ':datetime' => now()->format('Y-m-d_H-i-s')
        ]);

        $response = $this->get(route('store.imports-overview'));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->assertDownload($filename);
    }

    public function test_store_import_results_route_import_id_does_not_exist()
    {
        $response = $this->get(route('store.import-results', ['123123123']));
        $response->assertStatus(404);
    }

    public function test_store_import_results_route()
    {

        $import = ImportMeta::factory()
            ->for(User::factory()->uploader())
            ->create();

        $filename = strtr(config('imports.results.filename_template'), [
            ':id' => $import->id,
            ':datetime' => now()->format('Y-m-dTH-i-s')
        ]);

        $response = $this->call('GET', '/store/imports/' . $import->id . '/');

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->assertDownload($filename);
    }
}
