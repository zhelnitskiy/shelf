<?php

namespace Tests\Feature;

use App\Models\Publisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublisherTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishers_are_listed_in_name_order(): void
    {
        $publisherA = Publisher::factory()->create(['name' => 'Beta Press']);
        $publisherB = Publisher::factory()->create(['name' => 'Alpha Press']);

        $this->getJson('/api/v1/publishers')
            ->assertOk()
            ->assertJsonPath('data.0.id', $publisherB->id)
            ->assertJsonPath('data.1.id', $publisherA->id);
    }
}
