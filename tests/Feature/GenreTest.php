<?php

namespace Tests\Feature;

use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_genres_are_listed_in_name_order(): void
    {
        $genreA = Genre::factory()->create(['name' => 'Fantasy']);
        $genreB = Genre::factory()->create(['name' => 'Crime']);

        $this->getJson('/api/v1/genres')
            ->assertOk()
            ->assertJsonPath('data.0.id', $genreB->id)
            ->assertJsonPath('data.1.id', $genreA->id);
    }
}
