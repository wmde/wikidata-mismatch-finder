<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\Assert;
use App\Models\User;
use Illuminate\Support\Facades\App;

class WebHomeRouteTest extends TestCase
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
     * Test the / route with a language code
     *
     *  @return void
     */
    public function test_home_page_accepts_translated_locales()
    {
        // qqq should always exist
        $response = $this->get(route('home', ['uselang' => 'qqq']));

        $response->assertSuccessful();
        $this->assertSame(App::currentLocale(), 'qqq');
    }


    /**
     * Test the / route with a non translated language code
     *
     *  @return void
     */
    public function test_home_page_locale_falls_back_to_english()
    {
        // xtravagent-test is not a language
        $response = $this->get(route('home', ['uselang' => 'xtravagent-test']));

        $response->assertSuccessful();
        $this->assertSame(App::currentLocale(), 'en');
    }
}
