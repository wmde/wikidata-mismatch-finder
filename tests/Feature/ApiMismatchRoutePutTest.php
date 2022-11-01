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
use TiMacDonald\Log\LogFake;

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
                    'meta_wikidata_value',
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
                    ],
                    'updated_at'
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

        $response->assertStatus(422);
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
            ['wikidata_value', 'Potato'],
            ['meta_wikidata_value', ''],
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

        Log::swap(new LogFake);

        $this->json(
            'PUT',
            self::MISMATCH_ROUTE . '/' . $mismatch->id,
            [ 'review_status' => 'wikidata' ]
        );

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
                        "item_id" => $mismatch->item_id,
                        "property_id" => $mismatch->property_id,
                        "statement_guid" => $mismatch->statement_guid,
                        "wikidata_value" => $mismatch->wikidata_value,
                        "meta_wikidata_value" => $mismatch->meta_wikidata_value,
                        "external_value" => $mismatch->external_value,
                        "review_status_old" => 'pending',
                        "review_status_new" => 'wikidata',
                        "time" => $mismatch->updated_at
                    ]);
                return $assertMessage && $assertContext;
            });
    }

    private function generateSingleMismatch()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        $mismatch = Mismatch::factory()
            ->for(User::factory()->create())
            ->for($import)
            ->state([
                 'updated_at' => now()->subDay() // created yesterday
                 ])
            ->create();

        return $mismatch;
    }
}
