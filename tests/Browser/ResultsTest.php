<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\ResultsPage;
use App\Models\Mismatch;
use App\Models\ImportMeta;
use App\Models\User;

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

    public function test_shows_tables_for_existing_items()
    {
        $import = ImportMeta::factory()
        ->for(User::factory()->uploader())
        ->create();

        Mismatch::factory(2)
            ->for($import)
            ->create();

        $mismatches = Mismatch::all();

        $this->browse(function (Browser $browser) use ($mismatches) {
            $idsQuery = $mismatches->implode('item_id', '|');
            $browser->visit(new ResultsPage($idsQuery));

            foreach ($mismatches as $mismatch) {
                $browser->assertSee($mismatch->item_id)
                    ->assertSee($mismatch->property_id)
                    ->assertSee($mismatch->wikidata_value)
                    ->assertSee($mismatch->external_value)
                    ->assertSee($mismatch->importMeta->user->username)
                    ->assertSee($mismatch->importMeta->created_at->toDateString());
            }
        });
    }
}
