<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;

class ApiUserRouteTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private const USER_ROUTE = 'api/user';

    /**
     * Test non authenticated api/user route
     *
     *  @return void
     */
    public function test_unauthorized_api_user()
    {
        $response = $this->getJson(self::USER_ROUTE);
        $response->assertUnauthorized();
    }

    /**
     * Test the /api/user route
     *
     *  @return void
     */
    public function test_user_returns_user_data()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson(self::USER_ROUTE);
        $response->assertSuccessful()
            ->assertJsonStructure([
                'username',
                'mw_userid',
                'updated_at',
                'created_at',
                'id'
            ]);
    }
}
