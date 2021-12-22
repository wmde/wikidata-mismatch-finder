<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\HomePage;
use Tests\Browser\Pages\ResultsPage;

class ItemsFormTest extends DuskTestCase
{

    use DatabaseMigrations;

    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function test_can_enter_list_of_item_ids()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                    ->keys('@items-input', 'Q1', '{return_key}', 'Q2')
                    ->assertInputValue('@items-input', "Q1\nQ2");
        });
    }

    public function test_can_submit_list_of_item_ids()
    {
        $this->browse(function (Browser $browser) {

            $browser->visit(new HomePage)
                    ->keys('@items-input', 'Q23', '{return_key}', 'Q42')
                    ->press('.submit-ids')
                    ->waitFor('.results-page')
                    ->assertTitle('Mismatch Finder - Results')
                    ->assertSee("Q23")
                    ->assertSee("Q42");
        });
    }

    public function test_empty_item_list_yields_warning()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                    ->press('.submit-ids')
                    ->assertSee('Please provide the Item identifiers in order to perform the check.');

            $this->assertStringContainsString('--warning', $browser->attribute('@items-input', 'class'));
        });
    }

    public function test_empty_list_warning_resolves()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                    ->press('.submit-ids')
                    ->assertSee('Please provide the Item identifiers in order to perform the check.')
                    ->keys('@items-input', 'Q1', '{return_key}', 'Q2')
                    ->press('.submit-ids')
                    ->assertDontSee('Please provide the Item identifiers in order to perform the check.');
        });
    }

    public function test_invalid_item_list_yields_error()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                    ->keys('@items-input', 'Q1234-invalid')
                    ->press('.submit-ids')
                    ->assertSee('One or more Item identifiers couldn\'t be processed.');

            $this->assertStringContainsString('--error', $browser->attribute('@items-input', 'class'));
        });
    }

    public function test_sends_sanitized_input_when_given_empty_line_breaks()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                    ->keys('@items-input', '{return_key}', 'Q100', '{return_key}', '{return_key}', 'Q2')
                    ->press('.submit-ids')
                    ->waitFor('.results-page')
                    ->assertPathIs('/results')
                    ->assertQueryStringHas('ids', 'Q100|Q2');
        });
    }

    public function test_retains_text_after_submittal()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                ->keys('@items-input', 'Q23', '{return_key}', 'Q42')
                ->press('.submit-ids')
                ->waitFor('.results-page')
                ->press('.back-button')
                ->waitFor('.home-page')
                ->assertInputValue('@items-input', "Q23\nQ42"); //double quotes needed here
                // See: https://stackoverflow.com/q/67690990#comment119647867_67690990
        });
    }

    public function test_retains_text_from_direct_results_url()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new ResultsPage('Q23|Q42'))
                ->press('.back-button')
                ->waitFor('.home-page')
                // the HomePage's selector doesn't work here, because it is not loaded
                ->assertInputValue('#items-form textarea', "Q23\nQ42");
                // See: https://stackoverflow.com/q/67690990#comment119647867_67690990
        });
    }

    public function test_shows_error_message_from_results_url_with_invalid_input()
    {
        $this->browse(function (Browser $browser) {
            // browse to Homepage so that Laravel has something to redirect back to
            $browser->visit('/');

            $browser->visit('/results?ids=Q42|L123')
                ->waitFor('.home-page')
                ->assertVisible('#message-section .wikit-Message--error.wikit');
        });
    }
}
