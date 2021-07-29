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
            ['/auth/createToken'],
            ['/auth/revokeToken']
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
     * Test the non authenticated /auth/token route
     *
     *  @return void
     */
    public function test_token_nonAuthenticated_returnsWelcome()
    {
        $response = $this->get('/auth/token');

        $response->assertStatus(200);
        $response->assertViewIs('welcome');
    }

    /**
     * Test the authenticated /auth/token route
     *
     *  @return void
     */
    public function test_token_authenticated_returnsShowToken()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
                         ->get('/auth/token');

        $response->assertStatus(200);
        $response->assertViewIs('showToken');
    }

    /**
     * Test the /auth/token route when no token exists
     *
     *  @return void
     */
    public function test_createTokenWhenNoneExists_returnsNewToken()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
                         ->get('/auth/createToken');

        $response->assertStatus(200);
        $response->assertViewIs('newToken');
        $response->assertViewHas('newToken');

        // check token by loading the User from db
        $this->assertCount(1, User::find($user->id)->tokens);
    }

    /**
     * Test the /auth/createToken route when a token exists already
     *
     *  @return void
     */
    public function test_createTokenWhenOneExists_returnsRedirect()
    {
        $user = User::factory()->create();
        $user->createToken('testToken');

        $response = $this->actingAs($user)
                         ->get('/auth/createToken');

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
                         ->get('/auth/revokeToken?id=' . $user->tokens->first()->id);

        $response->assertStatus(302);

        // check tokens by loading the User from db
        $this->assertEmpty(User::find($user->id)->tokens);
    }
}
