<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_root_endpoint_returns_ok_status(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
