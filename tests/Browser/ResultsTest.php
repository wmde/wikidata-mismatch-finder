<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\ResultsPage;
use App\Models\Mismatch;
use App\Models\ImportMeta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;

class ResultsTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_shows_item_ids()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new ResultsPage('Q1|Q2'))
                ->assertSee('Q1')
                ->assertSee('Q2');
        });
    }

    public function test_shows_message_for_non_existing_item_ids()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new ResultsPage('Q1|Q2'))
                ->assertSee('No mismatches have been found for')
                ->assertSeeLink('Q1')
                ->assertSeeLink('Q2');
        });
    }

    public function test_shows_tables_for_existing_items_and_message_for_non_existing_items()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        Mismatch::factory(2)
            ->for($import)
            ->state(new Sequence(
                [
                    'statement_guid' => 'Q2$a2b48f1f-426d-91b3-1e0e-1d3c7b236bd0',
                    'property_id' => 'P610',
                    'wikidata_value' => 'Q513'
                ],
                [
                    'statement_guid' => 'q111$fc6ebc3f-4ea3-3cc8-0f09-a5608474754c',
                    'property_id' => 'P571',
                    'wikidata_value' => '4540 million years BCE'
                ]
            ))
            ->create();

        $expected = [
            [
                'item_label' => 'Earth (Q2)',
                'property_label' => 'highest point',
                'wikidata_value' => 'Mount Everest'
            ],
            [
                'item_label' => 'Mars (Q111)',
                'property_label' => 'inception',
                'wikidata_value' => '4540 million years BCE'
            ]
        ];

        $mismatches = Mismatch::all();

        $this->browse(function (Browser $browser) use ($mismatches, $expected) {
            $idsQuery = $mismatches->implode('item_id', '|');
            $idsWithNotFound = $idsQuery . '|Q1|Q666';
            $browser->visit(new ResultsPage($idsWithNotFound));

            foreach ($mismatches as $i => $mismatch) {
                $browser->assertSeeLink($expected[$i]['item_label'])
                    ->assertSeeLink($expected[$i]['property_label'])
                    ->assertSeeLink($expected[$i]['wikidata_value'])
                    ->assertSee($mismatch->external_value)
                    ->assertSeeLink($mismatch->importMeta->user->username)
                    ->assertSee($mismatch->importMeta->created_at->toDateString());
            }

            $browser->assertSee('No mismatches have been found for')
                ->assertSeeLink('Q1')
                ->assertSeeLink('Q666');
        });
    }

    public function test_shows_tables_for_existing_items()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        Mismatch::factory(2)
            ->for($import)
            ->state(new Sequence(
                [
                    'statement_guid' => 'Q2$a2b48f1f-426d-91b3-1e0e-1d3c7b236bd0',
                    'property_id' => 'P610',
                    'wikidata_value' => 'Q513'
                ],
                [
                    'statement_guid' => 'q111$fc6ebc3f-4ea3-3cc8-0f09-a5608474754c',
                    'property_id' => 'P571',
                    'wikidata_value' => '4540 million years BCE'
                ]
            ))
            ->create();

        $expected = [
            [
                'item_label' => 'Earth (Q2)',
                'property_label' => 'highest point',
                'wikidata_value' => 'Mount Everest'
            ],
            [
                'item_label' => 'Mars (Q111)',
                'property_label' => 'inception',
                'wikidata_value' => '4540 million years BCE'
            ]
        ];

        $mismatches = Mismatch::all();

        $this->browse(function (Browser $browser) use ($mismatches, $expected) {
            $idsQuery = $mismatches->implode('item_id', '|');
            $browser->visit(new ResultsPage($idsQuery));

            foreach ($mismatches as $i => $mismatch) {
                $browser->assertSeeLink($expected[$i]['item_label'])
                    ->assertSeeLink($expected[$i]['property_label'])
                    ->assertSeeLink($expected[$i]['wikidata_value'])
                    ->assertSee($mismatch->external_value)
                    ->assertSeeLink($mismatch->importMeta->user->username)
                    ->assertSee($mismatch->importMeta->created_at->toDateString());
            }
        });
    }
}
