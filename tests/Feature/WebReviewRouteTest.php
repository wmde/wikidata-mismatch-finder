<?php

namespace Tests\Feature;

use App\Models\ImportMeta;
use App\Models\Mismatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Inertia\Testing\Assert;
use TiMacDonald\Log\LogFake;

class WebReviewRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test mismatch review error response handling
     *
     * @return void
     */
    public function test_unauthenticated_mismatch_review_is_redirected_to_login()
    {
        $this->put(
            route('mismatch-review'),
            [ '1' => [ 'some' => 'review decisions' ] ]
        )->assertRedirect(route('login'));
    }

    /**
     * Test store mismatch review decisions
     *
     * @return void
     */
    public function test_store_mismatch_reviews()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        $mismatch1 = Mismatch::factory()->for($import)->create();
        $mismatch2 = Mismatch::factory()->for($import)->create();

        $reviewer = User::factory()->create();

        $putRequestPayload =
            [
                $mismatch1->id => [
                    'id' => $mismatch1->id,
                    'item_id' => $mismatch1->item_id,
                    'review_status' => 'wikidata'
                ],
                $mismatch2->id => [
                    'id' => $mismatch2->id,
                    'item_id' => $mismatch2->item_id,
                    'review_status' => 'both'
                ]
            ];

        $this->actingAs($reviewer)
            ->put(
                route('mismatch-review'),
                $putRequestPayload
            )->assertRedirect();

        $mismatch1->refresh();
        $this->assertNotEmpty($mismatch1->user);
        $this->assertEquals($reviewer->username, $mismatch1->user->username);
        $this->assertEquals('wikidata', $mismatch1->review_status);

        $mismatch2->refresh();
        $this->assertNotEmpty($mismatch2->user);
        $this->assertEquals($reviewer->username, $mismatch2->user->username);
        $this->assertEquals('both', $mismatch2->review_status);
    }

    /**
     * Test file logging upon storing a mismatch review
     *
     * @return void
     */
    public function test_review_log_message()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        $mismatch = Mismatch::factory()
            ->for($import)
            ->state(
                ['updated_at' => now()->subDay()] // created yesterday
            )
            ->create();
        $reviewer = User::factory()->create();

        Log::swap(new LogFake);

        $this->actingAs($reviewer)
            ->put(
                route('mismatch-review'),
                [
                    $mismatch->id => [
                        'id' => $mismatch->id,
                        'item_id' => $mismatch->item_id,
                        'review_status' => 'wikidata'
                    ]
                ]
            )->assertRedirect();

        // refresh mismatch to get correct updated_at timestamp
        $mismatch->refresh();

        Log::channel('mismatch_updates')
            ->assertLogged('info', function ($message, $context) use ($reviewer, $mismatch) {
                $assertMessage = ($message == __('logging.mismatch-updated'));
                $assertContext =
                    ($context == [
                        "username" => $reviewer->username,
                        "mw_userid" => $reviewer->mw_userid,
                        "mismatch_id" => $mismatch->id,
                        "item_id" => $mismatch['item_id'],
                        "property_id" => $mismatch->property_id,
                        "statement_guid" => $mismatch['statement_guid'],
                        "wikidata_value" => $mismatch->wikidata_value,
                        "external_value" => $mismatch->external_value,
                        "review_status_old" => 'pending',
                        "review_status_new" => 'wikidata',
                        "time" => $mismatch['updated_at']
                    ]);
                return $assertMessage && $assertContext;
            });
    }

    /**
     * Test mismatch review error response handling
     *
     * @return void
     */
    public function test_mismatch_review_error_response_handling()
    {
        // start from results
        $this->actingAs(User::factory()->create())
            ->get(route('results', ['ids' => 'Q1']));
        $redirect = $this
            ->put(
                route('mismatch-review'),
                [ "some" => "data" ],
                ['X-Mismatch-Finder-Error' => 'mismatch-review']  // force error response on this path
            )->assertRedirect(route('results', ['ids' => 'Q1']));

        // follow the redirect
        $this->get($redirect->headers->get('Location'))
            ->assertInertia(function (Assert $page) {
                $page->component('Results')
                    ->where('flash.errors', [ 'unexpected' => 'Unexpected error']);
            });
    }
}
