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
            ['/auth/create-token'],
            ['/auth/revoke-token']
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
     * Test the /auth/create-token route when no token exists
     *
     *  @return void
     */
    public function test_createTokenWhenNoneExists_returnsNewToken()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
                         ->get('/auth/create-token');

        $response->assertStatus(302);
        $response->assertRedirect('store/api-settings');
        $response->assertSessionHas('flashToken');

        // check token by loading the User from db
        $this->assertCount(1, User::find($user->id)->tokens);
    }

    /**
     * Test the /auth/create-token route when a token exists already
     *
     *  @return void
     */
    public function test_createTokenWhenOneExists_returnsRedirect()
    {
        $user = User::factory()->create();
        $user->createToken('testToken');

        $response = $this->actingAs($user)
                         ->get('/auth/create-token');

        $response->assertStatus(302);

        // check token by loading the User from db
        $this->assertCount(1, User::find($user->id)->tokens);
    }

    /**
     * Test the /auth/revoke-token route
     *
     *  @return void
     */
    public function test_revokeToken_returnsRedirect()
    {
        $user = User::factory()->create();
        $user->createToken('testToken');
        $response = $this->actingAs($user)
                         ->get('/auth/revoke-token?id=' . $user->tokens->first()->id);

        $response->assertStatus(302);

        // check tokens by loading the User from db
        $this->assertEmpty(User::find($user->id)->tokens);
    }
}
