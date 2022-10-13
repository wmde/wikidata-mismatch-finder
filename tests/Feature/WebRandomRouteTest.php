<?php

namespace Tests\Feature;

use App\Models\ImportMeta;
use App\Models\Mismatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use App\Models\User;
use Illuminate\Support\Collection;

class WebRandomRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the /random route
     *
     *  @return void
     */
    public function test_random_route_when_no_unreviewed_ids_available()
    {

        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        $mismatch = Mismatch::factory()
            ->for($import)
            ->state([
                'review_status' => 'wikidata'
            ])
            ->create();

        $response = $this->get(route('random'));

        $response->assertSuccessful();
        $response->assertViewIs('app')
            ->assertInertia(function (Assert $page) {
                $page->component('Results')
                    ->missing('item_ids')
                    ->missing('results');
            });
    }

     /**
     * Test the /random route redirects to /results when there are items to review
     *
     *  @return void
     */
    public function test_random_route_redirects_when_unreviewed_items_exist()
    {
        
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        $mismatch = Mismatch::factory()
            ->for($import)
            ->state([
                'review_status' => 'pending'
            ])
            ->create();
        
        $redirect = $this->get(route('random'))
            ->assertRedirect(route('results', ['ids' => $mismatch->item_id]));

        // follow the redirect
        $this->get($redirect->headers->get('Location'))
            ->assertInertia(function (Assert $page) {
                $page->component('Results')
                ->has('item_ids')
                ->has('results');
            });
    }
}
