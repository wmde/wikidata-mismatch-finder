<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Inertia\Testing\Assert;

class WebRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the / route
     *
     *  @return void
     */
    public function test_home_route()
    {
        $response = $this->get('/');

        $response->assertSuccessful();
        $response->assertViewIs('app')
            ->assertInertia(function (Assert $page) {
                $page->component('Home');
            });
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
     * Test the authenticated /store/api-settings route
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
     * Test the store/imports route
     *
     *  @return void
     */
    public function test_importStatus_route()
    {
        $response = $this->get('/store/imports');

        $response->assertSuccessful();
        $response->assertViewIs('importStatus');
    }
}
