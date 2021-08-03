<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTokenTest extends TestCase
{

    use RefreshDatabase;

    public function authRoutesProvider()
    {
        return [
            ['/auth/create_token'],
            ['/auth/revoke_token']
        ];
    }

    /**
     * Test non authenticated auth routes
     *
     *  @return void
     *  @dataProvider authRoutesProvider
     */
    public function test_nonAuthenticated_willRedirect($authRoute)
    {
        $response = $this->get($authRoute);
        $response->assertStatus(302);
    }

    /**
     * Test the non authenticated /auth/api.settings route
     *
     *  @return void
     */
    public function test_token_nonAuthenticated_returnsWelcome()
    {
        $response = $this->get('/auth/api_settings');

        $response->assertStatus(200);
        $response->assertViewIs('welcome');
    }

    /**
     * Test the authenticated /auth/api.settings route
     *
     *  @return void
     */
    public function test_token_authenticated_returnsShowToken()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
                         ->get('/auth/api_settings');

        $response->assertStatus(200);
        $response->assertViewIs('showToken');
    }

    /**
     * Test the /auth/api_settings route when no token exists
     *
     *  @return void
     */
    public function test_createTokenWhenNoneExists_returnsNewToken()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
                         ->get('/auth/create_token');

        $response->assertStatus(302);
        $response->assertRedirect('auth/api_settings');
        $response->assertSessionHas('flashToken');

        // check token by loading the User from db
        $this->assertCount(1, User::find($user->id)->tokens);
    }

    /**
     * Test the /auth/create_token route when a token exists already
     *
     *  @return void
     */
    public function test_createTokenWhenOneExists_returnsRedirect()
    {
        $user = User::factory()->create();
        $user->createToken('testToken');

        $response = $this->actingAs($user)
                         ->get('/auth/create_token');

        $response->assertStatus(302);

        // check token by loading the User from db
        $this->assertCount(1, User::find($user->id)->tokens);
    }

    /**
     * Test the /auth/revokeToken route
     *
     *  @return void
     */
    public function test_revokeToken_returnsRedirect()
    {
        $user = User::factory()->create();
        $user->createToken('testToken');
        $response = $this->actingAs($user)
                         ->get('/auth/revoke_token?id=' . $user->tokens->first()->id);

        $response->assertStatus(302);

        // check tokens by loading the User from db
        $this->assertEmpty(User::find($user->id)->tokens);
    }
}
