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
     * Guests see guest greeting
     *
     * @return void
     */
    public function testGuestsSeeGuestGreeting()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                    ->assertGuest()
                    ->assertSee('Hello, Guest!');
        });
    }

    /**
     * User see user greeting
     *
     * @return void
     */
    public function testUsersSeeUserGreeting()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new HomePage)
                    ->assertSee('Hello, ' . $user->username);
        });
    }
}
