<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Inertia\Testing\Assert;
use App\Models\User;

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
        $response = $this->get(route('home'));

        $response->assertSuccessful();
        $response->assertViewIs('app')
            ->assertInertia(function (Assert $page) {
                $page->component('Home');
            });
    }

     /**
     * Test the / route when authenticated
     *
     *  @return void
     */
    public function test_home_page_has_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertSuccessful();
        $response->assertViewIs('app')
            ->assertInertia(function (Assert $page) use ($user) {
                $page->component('Home')
                    ->where('user.name', $user->username);
            });
    }

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
}
