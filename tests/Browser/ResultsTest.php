<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\ResultsPage;

class ResultsTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_shows_results()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new ResultsPage('Q1|Q2'))
                ->assertSee('Q1')
                ->assertSee('Q2');
        });
    }
}
