<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\Assert;

class WebReviewRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test mismatch review error response handling
     *
     * @return void
     */
    public function test_mismatch_review_error_response_handling()
    {
        // start from results
        $this->get(route('results', ['ids' => 'Q1']));
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
