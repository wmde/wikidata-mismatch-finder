<?php

namespace Tests\Feature;

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
}
