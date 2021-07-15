<?php

namespace Tests\Feature;

use Tests\TestCase;

class WebRouteTest extends TestCase
{
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

    /**
     * Test the /auth/token route
     *
     *  @return void
     */
    public function test_welcome_route()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('welcome');
    }
}
