<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WebRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the /auth/api_settings route
     *
     *  @return void
     */
    public function test_welcome_route()
    {
        $response = $this->get('/');

        $response->assertStatus(302);
    }

    /**
     * Test the /store route
     *
     *  @return void
     */
    public function test_store_route()
    {
        $response = $this->get('/store');

        $response->assertRedirect(route('store.api-settings'));
    }

    /**
     * Test the authenticated /auth/api-settings route
     *
     *  @return void
     */
    public function test_api_settings_returnsShowToken()
    {
        $response = $this->get('/store/api-settings');

        $response->assertSuccessful();
        $response->assertViewIs('showToken');
    }

    /**
     * Test the /importStatus route
     *
     *  @return void
     */
    public function test_importStatus_route()
    {
        $response = $this->get('/imports');

        $response->assertStatus(200);
        $response->assertViewIs('importStatus');
    }
}
