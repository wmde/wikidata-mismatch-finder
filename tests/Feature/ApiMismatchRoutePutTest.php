<?php

namespace Tests\Feature;

use App\Http\Controllers\MismatchController;
use App\Models\ImportMeta;
use App\Models\Mismatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;

class ApiMismatchRoutePutTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private const MISMATCH_ROUTE = 'api/' . MismatchController::RESOURCE_NAME;

    public function test_unauthenticated_put_returns_401()
    {
        $response = $this->json(
            'PUT',
            self::MISMATCH_ROUTE . '/1',
            [ 'review_status' => 'wikidata' ]
        );

        $response->assertStatus(401);
    }

    public function test_put_non_existing_mismatch_returns_404()
    {
        Sanctum::actingAs(User::factory()->create());
        $response = $this->json(
            'PUT',
            self::MISMATCH_ROUTE . '/31415',
            [ 'review_status' => 'wikidata' ]
        );

        $response->assertStatus(404);
    }

    public function test_successful_update()
    {
        $mismatch = $this->generateSingleMismatch();

        $reviewer = User::factory()->create();
        $review_status = 'wikidata';

        Sanctum::actingAs($reviewer);
        $this->travelTo(now()); // freezes time to ensure correct updated_at timestamp

        $mismatchId =  $mismatch->id;
        $response = $this->json(
            'PUT',
            self::MISMATCH_ROUTE . '/' . $mismatchId,
            [ 'review_status' => $review_status ]
        );

        $response->assertSuccessful()
            ->assertJsonStructure(
                [  // array of mismatch items
                    'id',
                    'item_id',
                    'statement_guid',
                    'property_id',
                    'wikidata_value',
                    'external_value',
                    'external_url',
                    'review_status',
                    'reviewer' => [
                        'id',
                        'username',
                        'mw_userid'
                    ],
                    'import' => [
                        'id',
                        'status',
                        'description',
                        'created',
                        'expires',
                        'uploader' => [
                            'id',
                            'username',
                            'mw_userid'
                        ]
                    ]
                ]
            )->assertJson(
                [
                    'review_status' => $review_status,
                    'reviewer' => [
                        'id' => $reviewer->id,
                        'username' => $reviewer->username,
                        'mw_userid' => $reviewer->mw_userid
                    ],
                    'updated_at' => now()->millisecond(0)->toISOString()
                ]
            );
    }

    public function test_invalid_mismatch_id_returns_validation_error()
    {
        Sanctum::actingAs(User::factory()->create());
        $review_status = 'wikidata';

        $mismatchId =  'not_a_valid_id';
        $response = $this->json(
            'PUT',
            self::MISMATCH_ROUTE . '/' . $mismatchId,
            [ 'review_status' => $review_status ]
        );

        $response->assertStatus(404);
    }

    public function test_invalid_review_status_returns_validation_error()
    {
        Sanctum::actingAs(User::factory()->create());
        $mismatch =  $this->generateSingleMismatch();
        $response = $this->json(
            'PUT',
            self::MISMATCH_ROUTE . '/' . $mismatch->id,
            [ 'review_status' => 'potato' ]
        );

        $response->assertJsonValidationErrors([
                'review_status' => __('validation.in', [
                    'attribute' => 'review status'
                ])
            ]);
    }

    public function fillablePropertyOtherThanReviewStatus()
    {
        return [
            ['property_id' , 'P1234'],
            ['statement_guid', 'Q111$1234'],
            ['property_id', 'P666'],
            ['wikidata_value', 'Potato'],
            ['external_value', 'Tomato'],
            ['external_url', 'http://potato.com']
        ];
    }

    /**
     *  Test parameters other than review status
     *
     *  @return void
     *  @dataProvider fillablePropertyOtherThanReviewStatus
     */
    public function test_prohibited_parameters_return_validation_error($key, $parameter)
    {
        Sanctum::actingAs(User::factory()->create());
        $mismatch = $this->generateSingleMismatch();
        $response = $this->json(
            'PUT',
            self::MISMATCH_ROUTE . '/' . $mismatch->id,
            [
                'review_status' => 'wikidata',
                 $key => $parameter
            ]
        );

        $response->assertJsonValidationErrors([
                $key => __('validation.prohibited', [
                    'attribute' => str_replace('_', ' ', $key)
                ])
            ]);
    }

    public function test_review_log_message()
    {
        $reviewer = User::factory()->create();
        $mismatch = $this->generateSingleMismatch();
        Sanctum::actingAs($reviewer);

        Log::shouldReceive('info')
            ->once()
            ->withArgs([
                "Mismatch review_status changed:",
                [
                    "username" => $reviewer->username,
                    "mw_userid" => $reviewer->mw_userid,
                    "old" => $mismatch->review_status,
                    "new" => 'wikidata',
                    "time" => $mismatch->updated_at
                ]
            ]);

        $this->json(
            'PUT',
            self::MISMATCH_ROUTE . '/' . $mismatch->id,
            [ 'review_status' => 'wikidata' ]
        );
    }

    private function generateSingleMismatch()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        $mismatch = Mismatch::factory()
            ->for(User::factory()->create())
            ->reviewed()
            ->for($import)
            ->create();

        return $mismatch;
    }
}
