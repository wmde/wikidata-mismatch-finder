<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\HomePage;

class ItemsFormTest extends DuskTestCase
{
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
}
