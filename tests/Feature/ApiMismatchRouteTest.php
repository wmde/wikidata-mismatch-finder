<?php

namespace Tests\Feature;

use App\Http\Controllers\MismatchController;
use App\Models\ImportMeta;
use App\Models\Mismatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;

class ApiMismatchRouteTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private const MISMATCH_ROUTE = 'api/' . MismatchController::RESOURCE_NAME;

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

        $mismatch = Mismatch::factory()->for($import)->create();

        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            [ 'ids' => $mismatch->item_id ]
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
            );
    }

    public function test_multiple_mismatches_returns_correct_number_of_items()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        $mismatches = Mismatch::factory(3)->for($import)->create();
        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            ['ids' => $mismatches->implode('item_id', '|')]
        );

        $response->assertSuccessful()
            ->assertJsonCount($mismatches->count());
    }

    public function test_missing_item_ids_returns_validation_error()
    {
        $response = $this->json('GET', self::MISMATCH_ROUTE);  // ids missing
        $response->assertJsonValidationErrors([
                'ids.0' => __('validation.required', [
                    'attribute' => 'ids.0'
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
}
