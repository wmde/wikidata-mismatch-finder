<?php

namespace Tests\Feature;

use App\Models\ImportMeta;
use App\Models\Mismatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
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
     * Test that sending a review decision for a non-existing mismatch
     * will return a 404 "Not found" response
     *
     * @return void
     */
    public function test_non_existing_mismatch_returns_404()
    {
        $this->actingAs(User::factory()->create())
            ->put(
                route('mismatch-review'),
                [
                    '42' => [
                        'id' => '42',
                        'item_id' => 'Q123',
                        'review_status' => 'wikidata'
                    ]
                ]
            )->assertStatus(404);
    }

    /**
     * Test that invalid input generates validation errors
     *
     * @return void
     */
    public function test_invalid_input_yields_validation_error()
    {
        Mismatch::factory()
            ->for(
                ImportMeta::factory()
                    ->for(User::factory()->uploader())
                    ->create()
            )
            ->create();

        $this->actingAs(User::factory()->create())
            ->put(
                route('mismatch-review'),
                [
                    '1' => [
                        'item_id' => 'Q123',
                        'review_status' => 'invalid'
                    ]
                ]
            )
            ->assertSessionHasErrors(['1.id'])
            ->assertSessionHasErrors(['1.review_status']);
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

        $this->actingAs($reviewer)
            ->put(
                route('mismatch-review'),
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
                ]
            )->assertSessionHasNoErrors()
            ->assertSuccessful();


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
            )->assertSuccessful();

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
                        "meta_wikidata_value" => $mismatch->meta_wikidata_value,
                        "external_value" => $mismatch->external_value,
                        "review_status_old" => 'pending',
                        "review_status_new" => 'wikidata',
                        "time" => $mismatch['updated_at']
                    ]);
                return $assertMessage && $assertContext;
            });
    }

    /**
     * Test mismatch review not found response handling
     *
     * @return void
     */
    public function test_mismatch_review_mismatch_not_found()
    {
        // start from results
        $this->actingAs(User::factory()->create())
            ->get(route('results', ['ids' => 'Q1']));
        $this->put(
            route('mismatch-review'),
            [ "some" => "data" ],
            ['X-Mismatch-Finder-Not-Found' => 'mismatch-review']  // force not-found response on this path
        )->assertStatus(404);
    }

    /**
     * Test mismatch review unknown error response handling
     *
     * @return void
     */
    public function test_mismatch_review_unknown_error_response_handling()
    {
        // start from results
        $this->actingAs(User::factory()->create())
            ->get(route('results', ['ids' => 'Q1']));
        $this->put(
            route('mismatch-review'),
            [ "some" => "data" ],
            ['X-Mismatch-Finder-Error' => 'mismatch-review']  // force error response on this path
        )->assertStatus(500);
    }
}
