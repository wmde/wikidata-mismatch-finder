<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\HomePage;
use App\Models\Mismatch;
use App\Models\ImportMeta;
use App\Models\User;

class RandomButtonTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_clicking_random_mismatches_button_redirects_and_shows_results_when_mismatches_available()
    {
        $import = ImportMeta::factory()
            ->for(User::factory()->uploader())
            ->create();

        $mismatch = Mismatch::factory()
            ->for($import)
            ->create();

        $this->browse(function (Browser $browser) use ($mismatch) {
            $browser->visit(new HomePage)
                ->press('Random mismatches')
                ->waitForLocation('/results')
                ->assertPathIs('/results')
                ->assertQueryStringHas('ids', $mismatch->item_id)
                ->assertSeeLink($mismatch->item_label)
                ->assertSeeLink($mismatch->item_id);
        });
    }

    public function test_clicking_random_mismatches_button_shows_not_available_mismatches_message_when_no_mismatches()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                ->press('Random mismatches')
                ->waitForLocation('/random')
                ->assertPathIs('/random')
                ->assertSee('There are currently no mismatches available for review.');
        });
    }
}
