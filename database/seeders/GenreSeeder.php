<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Support\GenreCatalog;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = array_map(
            static fn (string $name): array => ['name' => $name],
            GenreCatalog::all(),
        );

        Genre::query()->upsert($genres, ['name'], ['name']);
    }
}
