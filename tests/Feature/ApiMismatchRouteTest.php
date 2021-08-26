<?php

namespace Tests\Feature;

use App\Http\Controllers\MismatchController;
use App\Models\ImportMeta;
use App\Models\Mismatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class ApiMismatchRouteTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private const MISMATCH_ROUTE = 'api/' . MismatchController::RESOURCE_NAME;

    private $pendingMismatches;
    private $reviewedMismatches;
    private $expiredMismatches;
    private $expiredReviewedMismatches;

    /**
     * Test the /api/user route
     *
     *  @return void
     */
    public function test_single_mismatch_returns_correct_data()
    {
        $import = ImportMeta::factory()
           ->for(User::factory()->uploader())
           ->create();

        $reviewer = User::factory()->create();

        $mismatch = Mismatch::factory()
            ->for($import)
            ->reviewed()
            ->for($reviewer)
            ->create();

        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            [
                 'ids' => $mismatch->item_id,
                 'include_reviewed' => 'true'
            ]
        );

        $response->assertSuccessful()
            ->assertJsonStructure(
                [[  // array of mismatch items
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
                        'mw_userid',
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
                ]]
            )->assertJson(
                [[
                    'import' => [
                        'status' => $import->status
                    ],
                    'reviewer' => [
                        'id' => $reviewer->id
                        ]
                ]]
            );
    }

    public function test_multiple_mismatches_do_not_return_edited_or_expired()
    {
        $this->seedMismatches();

        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            [
                'ids' => $this->getSeededMismatchIds()
            ]
        );

        $response->assertSuccessful()
            ->assertJsonCount($this->pendingMismatches->count());
    }

    public function test_query_including_reviewed_returns_reviewed_mismatches()
    {
        $this->seedMismatches();

        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            [
                'ids' => $this->getSeededMismatchIds(),
                'include_reviewed' => 'true'
            ]
        );

        $response->assertSuccessful()
            ->assertJsonCount(
                $this->pendingMismatches->count() +
                $this->reviewedMismatches->count()
            );
    }

    public function test_query_including_reviewed_false_does_not_return_reviewed_mismatches()
    {
        $this->seedMismatches();

        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            [
                'ids' => $this->getSeededMismatchIds(),
                'include_reviewed' => 'false'
            ]
        );

        $response->assertSuccessful()
            ->assertJsonCount(
                $this->pendingMismatches->count()
            );
    }

    public function test_query_including_expired_returns_expired_mismatches()
    {
        $this->seedMismatches();

        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            [
                'ids' => $this->getSeededMismatchIds(),
                'include_expired' => 'true'
            ]
        );

        $response->assertSuccessful()
            ->assertJsonCount(
                $this->pendingMismatches->count() +
                $this->expiredMismatches->count()
            );
    }

    public function test_query_including_expired_false_does_not_return_expired_mismatches()
    {
        $this->seedMismatches();

        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            [
                'ids' => $this->getSeededMismatchIds(),
                'include_expired' => 'false'
            ]
        );

        $response->assertSuccessful()
            ->assertJsonCount(
                $this->pendingMismatches->count()
            );
    }

    public function test_query_including_reviewed_and_expired_returns_all_mismatches()
    {
        $this->seedMismatches();

        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            [
                'ids' => $this->getSeededMismatchIds(),
                'include_reviewed' => 'true',
                'include_expired' => 'true'
            ]
        );

        $response->assertSuccessful()
            ->assertJsonCount(
                $this->pendingMismatches->count() +
                $this->reviewedMismatches->count() +
                $this->expiredMismatches->count() +
                $this->expiredReviewedMismatches->count()
            );
    }

    public function test_put_review_status_returns_updated_mismatch_with_updated_review_status_value()
    {

        $import = ImportMeta::factory()
           ->for(User::factory()->uploader())
           ->create();

        $reviewer = User::factory()->create();

        $mismatch = Mismatch::factory()
            ->for($import)
            ->reviewed()
            ->for($reviewer)
            ->create();

        $review_status = 'wikidata';

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
                    'reviewer' => [],
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
                    'review_status' => $review_status
                ]
            );
    }

    public function test_put_review_status_with_invalid_mismatch_id_returns_validation_error()
    {

        $review_status = 'wikidata';

        $mismatchId =  'not_a_valid_id';
        $response = $this->json(
            'PUT',
            self::MISMATCH_ROUTE . '/' . $mismatchId,
            [ 'review_status' => $review_status ]
        );

        //TODO: return proper error, 404?
        $response->assertStatus(500);
    }

    public function test_missing_item_ids_returns_validation_error()
    {
        $response = $this->json('GET', self::MISMATCH_ROUTE);  // ids missing
        $response->assertJsonValidationErrors([
            'ids' => __('validation.required', [
                'attribute' => 'ids'
            ])
        ]);
    }

    public function test_too_many_item_ids_returns_validation_error()
    {
        $maxItemIds = config('mismatches.validation.ids.max');

        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            ['ids' => str_repeat('Q1|', $maxItemIds + 1)]  // too many
        );

        $response->assertJsonValidationErrors([
            'ids' => __('validation.max.array', [
                'attribute' => 'ids',
                'max' => $maxItemIds
            ])
        ]);
    }

    public function test_invalid_item_id_returns_validation_error()
    {
        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            [ 'ids' => 'P31' ]  // wrong format
        );

        $response->assertJsonValidationErrors([
            'ids.0' => __('validation.regex', [
                'attribute' => 'ids.0'
            ])
        ]);
    }

    public function test_invalid_review_status_returns_validation_error()
    {

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
    public function test_parameter_other_than_review_status_in_put_request_returns_validation_error($key, $parameter)
    {

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

    private function generateSingleMismatch()
    {

        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        $reviewer = User::factory()->create();

        $mismatch = Mismatch::factory()
            ->for($import)
            ->reviewed()
            ->for($reviewer)
            ->create();

        return $mismatch;
    }

    private function seedMismatches()
    {
        $import = ImportMeta::factory()
            ->for(User::factory()->uploader())
            ->create();

        $expiredImport = ImportMeta::factory()
            ->for(User::factory()->uploader())
            ->expired()
            ->create([
                'status' => 'completed'
            ]);

        $this->pendingMismatches = Mismatch::factory(3)->for($import)->create();
        $this->reviewedMismatches = Mismatch::factory(3)->for($import)->reviewed()->create();
        $this->expiredMismatches = Mismatch::factory(3)->for($expiredImport)->create();
        $this->expiredReviewedMismatches = Mismatch::factory(3)->for($expiredImport)->reviewed()->create();
    }

    private function getSeededMismatchIds()
    {
        return $this->pendingMismatches->implode('item_id', '|') . '|' .
            $this->reviewedMismatches->implode('item_id', '|') . '|' .
            $this->expiredMismatches->implode('item_id', '|') . '|' .
            $this->expiredReviewedMismatches->implode('item_id', '|');
    }
}
