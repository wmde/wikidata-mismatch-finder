<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\ApiSettingsPage;
use App\Models\User;

class ExampleTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * A guest can log in
     *
     * @return void
     */
    public function testGuestCanLogIn()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new ApiSettingsPage)
                ->assertGuest()
                ->assertSeeLink('Log in')
                ->assertNotPresent('.token-container')
                ->assertNotPresent('.no-tokens');
        });
    }

    /**
     * A guest doesn't see tokens
     *
     * @return void
     */
    public function testGuestSeesNoTokens()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new ApiSettingsPage)
                ->assertGuest()
                ->assertNotPresent('.token-container')
                ->assertNotPresent('.no-tokens');
        });
    }

     /**
     * A guest doesn't see tokens
     *
     * @return void
     */
    public function testUserCanCreateTokens()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new ApiSettingsPage)
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
