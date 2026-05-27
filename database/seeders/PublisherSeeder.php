<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;
use RuntimeException;

class PublisherSeeder extends Seeder
{
    private const PUBLISHER_COUNT = 20;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Publisher::query()->exists()) {
            throw new RuntimeException(
                'PublisherSeeder expects an empty publishers table. Rebuild the demo database and run the full seed flow instead, for example `make seed-fresh`.',
            );
        }

        Publisher::factory()
            ->count(self::PUBLISHER_COUNT)
            ->create();
    }
}
