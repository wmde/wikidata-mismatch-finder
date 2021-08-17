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
                    'status',
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

    public function test_multiple_mismatches_do_not_return_edited_or_expired()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        $expiredImport = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->expired()
        ->create();

        $pendingMismatches = Mismatch::factory(3)->for($import)->create();
        $reviewedMismatches = Mismatch::factory(3)->for($import)->reviewed()->create();
        $expiredMismatches = Mismatch::factory(3)->for($expiredImport)->create();
        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            ['ids' =>
                $pendingMismatches->implode('item_id', '|') . '|' .
                $reviewedMismatches->implode('item_id', '|') . '|' .
                $expiredMismatches->implode('item_id', '|')
            ]
        );

        $response->assertSuccessful()
            ->assertJsonCount($pendingMismatches->count());
    }

    public function test_query_including_reviewed()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        $expiredImport = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->expired()
        ->create();

        $pendingMismatches = Mismatch::factory(3)->for($import)->create();
        $reviewedMismatches = Mismatch::factory(3)->for($import)->reviewed()->create();
        $expiredMismatches = Mismatch::factory(3)->for($expiredImport)->create();
        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            [
                'ids' => $pendingMismatches->implode('item_id', '|') . '|' .
                         $reviewedMismatches->implode('item_id', '|') . '|' .
                         $expiredMismatches->implode('item_id', '|'),
                'include_reviewed' => true
            ]
        );

        $response->assertSuccessful()
            ->assertJsonCount($pendingMismatches->count() + $reviewedMismatches->count());
    }

    public function test_query_including_expired()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        $expiredImport = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->expired()
        ->create();

        $pendingMismatches = Mismatch::factory(3)->for($import)->create();
        $reviewedMismatches = Mismatch::factory(3)->for($import)->reviewed()->create();
        $expiredMismatches = Mismatch::factory(3)->for($expiredImport)->create();
        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            [
                'ids' => $pendingMismatches->implode('item_id', '|') . '|' .
                         $reviewedMismatches->implode('item_id', '|') . '|' .
                         $expiredMismatches->implode('item_id', '|'),
                'include_expired' => true
            ]
        );

        $response->assertSuccessful()
            ->assertJsonCount($pendingMismatches->count() + $expiredMismatches->count());
    }

    public function test_query_including_reviewed_and_expired()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        $expiredImport = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->expired()
        ->create();

        $pendingMismatches = Mismatch::factory(3)->for($import)->create();
        $reviewedMismatches = Mismatch::factory(3)->for($import)->reviewed()->create();
        $expiredMismatches = Mismatch::factory(3)->for($expiredImport)->create();
        $expiredReviewedMismatches = Mismatch::factory(3)->for($expiredImport)->reviewed()->create();
        $response = $this->json(
            'GET',
            self::MISMATCH_ROUTE,
            [
                'ids' => $pendingMismatches->implode('item_id', '|') . '|' .
                         $reviewedMismatches->implode('item_id', '|') . '|' .
                         $expiredMismatches->implode('item_id', '|') . '|' .
                         $expiredReviewedMismatches->implode('item_id', '|'),
                'include_reviewed' => true,
                'include_expired' => true
            ]
        );

        $response->assertSuccessful()
            ->assertJsonCount(
                $pendingMismatches->count() +
                $reviewedMismatches->count() +
                $expiredMismatches->count() +
                $expiredReviewedMismatches->count()
            );
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
