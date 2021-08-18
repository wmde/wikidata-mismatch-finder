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
        $response = $this->get('/');

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

        $response = $this->actingAs($user)->get('/');

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
