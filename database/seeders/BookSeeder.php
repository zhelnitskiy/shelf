<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use Illuminate\Database\Seeder;
use RuntimeException;

class BookSeeder extends Seeder
{
    private const BOOK_COUNT = 120;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Book::query()->exists()) {
            throw new RuntimeException(
                'BookSeeder expects an empty books table. Rebuild the demo database and run the full seed flow instead, for example `make seed-fresh`.',
            );
        }

        $authors = Author::query()->get();
        $genres = Genre::query()->get();
        $publishers = Publisher::query()->get();

        if ($authors->isEmpty()) {
            throw new RuntimeException('BookSeeder requires authors. Run AuthorSeeder first.');
        }

        if ($genres->isEmpty()) {
            throw new RuntimeException('BookSeeder requires genres. Run GenreSeeder first.');
        }

        if ($publishers->isEmpty()) {
            throw new RuntimeException('BookSeeder requires publishers. Run PublisherSeeder first.');
        }

        Book::factory()
            ->count(self::BOOK_COUNT)
            ->create()
            ->each(function (Book $book) use ($authors, $genres, $publishers): void {
                $publisher = fake()->boolean(95)
                    ? $publishers->random()
                    : null;

                $book->publisher()->associate($publisher);
                $book->save();

                $book->authors()->sync(
                    $authors->random(fake()->boolean(80) ? 1 : fake()->numberBetween(2, 3))->modelKeys(),
                );

                $book->genres()->sync(
                    $genres->random(fake()->boolean(70) ? 1 : 2)->modelKeys(),
                );
            });
    }
}
