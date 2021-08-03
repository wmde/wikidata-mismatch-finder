<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WebRouteTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test the /auth/api_settings route
     *
     *  @return void
     */
    public function test_welcome_route()
    {
        $response = $this->get('/');

        $response->assertStatus(302);
    }

    /**
     * Test the /importStatus route
     *
     *  @return void
     */
    public function test_importStatus_route()
    {
        $response = $this->get('/imports');

        $response->assertStatus(200);
        $response->assertViewIs('importStatus');
    }
}
