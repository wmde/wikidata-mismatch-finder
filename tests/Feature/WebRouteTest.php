<?php

namespace Tests\Feature;

use App\Models\ImportMeta;
use App\Models\Mismatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\Assert;
use App\Models\User;
use Illuminate\Support\Facades\App;

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
     * Test the /results route
     *
     *  @return void
     */
    public function test_results_route()
    {
        $response = $this->get(route('results', [
            'ids' => 'Q1|Q2'
        ]));

        $response->assertSuccessful();
        $response->assertViewIs('app')
            ->assertInertia(function (Assert $page) {
                $page->component('Results');
            });
    }

    /**
     * Test that the /results response contains mismatch data
     *
     *  @return void
     */
    public function test_results_route_retrieves_mismatches()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        $mismatch = Mismatch::factory()
            ->for($import)
            ->create();

        $response = $this->get(route('results', [
            'ids' => $mismatch->item_id
        ]));

        $response->assertSuccessful();
        $response->assertViewIs('app')
            ->assertInertia(function (Assert $page) use ($mismatch, $import) {
                $page->component('Results')
                    ->has('results.0', 11) // result object size
                    ->where('results.0.id', $mismatch->id)
                    ->where('results.0.reviewer', null)
                    ->where('results.0.import.id', $import->id);
            });
    }

    /**
     * Test the /results redirects when no ids provided
     *
     *  @return void
     */
    public function test_results_route_redirects_on_missing_ids()
    {
        $response = $this->get(route('results'));

        $response->assertRedirect(route('home'));
    }

    /**
     * Test the /results route accepts lowercase QIDs
     *
     *  @return void
     */
    public function test_results_route_accepts_lowercase_ids()
    {
        $response = $this->get(route('results', [
            'ids' => 'q1|q2'
        ]));

        $response->assertSuccessful();
        $response->assertViewIs('app')
            ->assertInertia(function (Assert $page) {
                $page->component('Results');
            });
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
     * Test error response handling
     *
     * @return void
     */
    public function test_error_response_handling()
    {
        $redirect = $this
            ->get(
                route('results', ['ids' => 'Q1']),
                ['X-Mismatch-Results-Error' => 'true']
            );

        $redirect->assertRedirect(route('home'));
        $response = $this->get($redirect->headers->get('Location'));
        $response->assertInertia(function (Assert $page) {
                $page->component('Home')
                    ->where('flash.errors', [ 'unexpected' => 'Unexpected error']);
        });
    }
}
