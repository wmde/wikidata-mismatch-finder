<?php

namespace Tests\Feature;

use App\Models\ImportMeta;
use App\Models\Mismatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use App\Models\User;
use Illuminate\Support\Collection;

class WebResultsRouteTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_results_page_has_user()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('results', [
            'ids' => 'Q1|Q2'
        ]));

        $response->assertSuccessful();
        $response->assertViewIs('app')
            ->assertInertia(function (Assert $page) use ($user) {
                $page->component('Results')
                    ->where('user.id', $user->mw_userid)
                    ->where('user.name', $user->username);
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

        $noMismatchQid = 'Q999979999';
        $dateMismatch = Mismatch::factory()
            ->for($import)
            ->datatypeTime()
            ->create();
        $dateMismatchQid = $dateMismatch->item_id;
        do {
            $itemMismatch = Mismatch::factory()
                ->for($import)
                ->datatypeWikibaseItem()
                ->create();
            $itemMismatchQid = $itemMismatch->item_id;
        } while ($itemMismatchQid === $dateMismatchQid);
        do {
            $stringMismatch = Mismatch::factory()
                ->for($import)
                ->datatypeString()
                ->create();
            $stringMismatchQid = $stringMismatch->item_id;
        } while ($stringMismatchQid === $dateMismatchQid || $stringMismatchQid === $itemMismatchQid);

        $isMismatch = function (Mismatch $mismatch) use ($import) {
            return function (Assert $data) use ($mismatch, $import) {
                $data->whereAll([
                    'id' => $mismatch->id,
                    // Casting values to string, as it seems that the inertia
                    // testing helper also converts all values to strings
                    'item_id' => (string) $mismatch->item_id,
                    'statement_guid' => (string) $mismatch->statement_guid,
                    'property_id' => (string) $mismatch->property_id,
                    'wikidata_value' => (string) $mismatch->wikidata_value,
                    'external_value' => (string) $mismatch->external_value,
                    'review_status' => (string) $mismatch->review_status,
                    'import_meta.external_source' => (string) $import->external_source,
                    'import_meta.user.username' => (string) $import->user->username,
                    'import_meta.created_at' => $import->created_at->toISOString(),
                    'import_meta.description' => $import->description
                ])->etc();
            };
        };

        $assertLabels = function (Collection $labels) use (
            $noMismatchQid,
            $dateMismatch,
            $dateMismatchQid,
            $itemMismatch,
            $itemMismatchQid,
            $stringMismatch,
            $stringMismatchQid
        ) {
            return $labels->has([
                // labels should include item + property id of each mismatch
                $noMismatchQid,
                $dateMismatchQid,
                $dateMismatch->property_id,
                $itemMismatchQid,
                $itemMismatch->property_id,
                $stringMismatchQid,
                $stringMismatch->property_id,
                // as well as item id of an item mismatch value
                $itemMismatch->wikidata_value,
            ]);
        };

        $assertDate = function (string $formatted) {
            // a formatted date (day precision) will contain a space,
            // whereas the unformatted value (timestamp) would not
            return str_contains($formatted, ' ');
        };

        $withResultsPage = function (Assert $page) use (
            $noMismatchQid,
            $dateMismatch,
            $dateMismatchQid,
            $itemMismatch,
            $itemMismatchQid,
            $stringMismatch,
            $stringMismatchQid,
            $isMismatch,
            $assertLabels,
            $assertDate
        ) {
            $page->component('Results')
                ->has("results.$dateMismatchQid.0", $isMismatch($dateMismatch))
                ->has("results.$itemMismatchQid.0", $isMismatch($itemMismatch))
                ->has("results.$stringMismatchQid.0", $isMismatch($stringMismatch))
                ->where("labels", $assertLabels)
                ->where("formatted_values.{$dateMismatch->property_id}.{$dateMismatch->wikidata_value}", $assertDate);
        };

        $response = $this->get(route('results', [
            'ids' => "$dateMismatchQid|$itemMismatchQid|$stringMismatchQid|$noMismatchQid"
        ]));

        $response->assertSuccessful();
        $response->assertViewIs('app')->assertInertia($withResultsPage);
    }

    /**
     * Test that the /results response contains only pending mismatches
     *
     *  @return void
     */
    public function test_results_route_does_not_retrieve_reviewed_mismatches()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        $mismatch = Mismatch::factory()
            ->for($import)
            ->state([
                'statement_guid' => 'Q1$a2b48f1f-426d-91b3-1e0e-1d3c7b236bd0',
                'review_status' => 'wikidata'
            ])
            ->create();

        $this->get(route('results', [ 'ids' => $mismatch->item_id ]))
            ->assertSuccessful()
            ->assertViewIs('app')
            ->assertInertia(function (Assert $page) {
                $page->component('Results')
                    ->missing('results');
            });
    }

    /**
     * Test that the /results response does not contain expired mismatches
     *
     *  @return void
     */
    public function test_results_route_does_not_retrieve_expired_mismatches()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->expired()
        ->create();

        $mismatch = Mismatch::factory()
            ->for($import)
            ->state([
                'statement_guid' => 'Q1$a2b48f1f-426d-91b3-1e0e-1d3c7b236bd0'
            ])
            ->create();

        $this->get(route('results', [ 'ids' => $mismatch->item_id ]))
            ->assertSuccessful()
            ->assertViewIs('app')
            ->assertInertia(function (Assert $page) {
                $page->component('Results')
                    ->missing('results');
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
     * Test results error response handling
     *
     * @return void
     */
    public function test_results_error_response_handling()
    {
        $redirect = $this
            ->get(
                route('results', ['ids' => 'Q1']),
                ['X-Mismatch-Finder-Error' => 'results']  // force error response on this path
            )->assertRedirect(route('home'));

        // follow the redirect
        $this->get($redirect->headers->get('Location'))
            ->assertInertia(function (Assert $page) {
                $page->component('Home')
                    ->where('flash.errors', [ 'unexpected' => 'Unexpected error']);
            });
    }
}
