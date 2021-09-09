<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\StorePage;

class ApiSettingsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Guests can log in
     *
     * @return void
     */
    public function testGuestCanLogIn()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new StorePage)
                ->assertGuest()
                ->assertSeeLink('Log in')
                ->assertNotPresent('.token-container')
                ->assertNotPresent('.no-tokens');
        });
    }

    /**
     * Guests don't see tokens
     *
     * @return void
     */
    public function testGuestSeesNoTokens()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new StorePage)
                ->assertGuest()
                ->assertNotPresent('.token-container')
                ->assertNotPresent('.no-tokens');
        });
    }

     /**
     * Guests can create tokens
     *
     * @return void
     */
    public function testUserCanCreateTokens()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new StorePage)
                ->assertPresent('.no-tokens')
                ->assertSeeLink('Create')
                ->clickLink('Create')
                ->assertRouteIs('store.api-settings')
                ->assertPresent('.token')
                ->assertSeeLink('Regenerate')
                ->assertSeeLink('Delete');
        });
    }
}
