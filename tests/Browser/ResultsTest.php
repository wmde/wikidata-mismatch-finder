<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\ResultsPage;

class ResultsTest extends DuskTestCase
{
    public function test_shows_results()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new ResultsPage)
                ->assertSee('Results');
        });
    }
}
