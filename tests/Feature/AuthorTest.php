<?php

namespace Tests\Feature;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use RefreshDatabase;

    public function test_authors_are_listed_in_name_order(): void
    {
        $authorA = Author::factory()->create(['name' => 'B Author']);
        $authorB = Author::factory()->create(['name' => 'A Author']);

        $this->getJson('/api/v1/authors')
            ->assertOk()
            ->assertJsonPath('data.0.id', $authorB->id)
            ->assertJsonPath('data.1.id', $authorA->id);
    }
}
