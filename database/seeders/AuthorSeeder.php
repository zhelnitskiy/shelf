<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;
use RuntimeException;

class AuthorSeeder extends Seeder
{
    private const AUTHOR_COUNT = 50;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Author::query()->exists()) {
            throw new RuntimeException(
                'AuthorSeeder expects an empty authors table. Rebuild the demo database and run the full seed flow instead, for example `make seed-fresh`.',
            );
        }

        Author::factory()
            ->count(self::AUTHOR_COUNT)
            ->create();
    }
}
