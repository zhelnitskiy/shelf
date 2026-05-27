<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_swagger_ui(): void
    {
        $this->get('/')
            ->assertRedirect(route('l5-swagger.default.api'));
    }

    public function test_api_not_found_is_rendered_as_json_for_generic_accept_header(): void
    {
        $this->call('DELETE', '/api/v1/books/999999', server: [
            'HTTP_ACCEPT' => '*/*',
        ])
            ->assertNotFound()
            ->assertHeader('content-type', 'application/json')
            ->assertExactJson([
                'message' => 'Requested resource was not found.',
            ]);
    }
}
