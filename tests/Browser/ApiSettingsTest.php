<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\StorePage;

class ApiSettingsTest extends DuskTestCase
{
    /**
     * Guests can see log in link
     *
     * @return void
     */
    public function testGuestSeesLoginLink()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new StorePage)
                ->assertGuest()
                ->assertSeeLink('Log in');
        });
    }
}
