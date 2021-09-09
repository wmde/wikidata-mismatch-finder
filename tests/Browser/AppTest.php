<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\HomePage;
use App\Models\User;

class AppTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Guests see "Log in"
     *
     * @return void
     */
    public function testGuestSeesLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                    ->assertGuest()
                    ->assertSee('Log in');
        });
    }

    /**
     * Users see their username and "Log out"
     *
     * @return void
     */
    public function testUserSeesLogout()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new HomePage)
                    ->assertSee($user->username)
                    ->assertSee('Log out');
        });
    }
}
