<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_are_listed_with_relations(): void
    {
        $this->createBooksWithSharedRelations(16);

        $newestBook = Book::query()->latest('id')->firstOrFail();

        $response = $this->getJson('/api/v1/books');

        $response
            ->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('data.0.id', $newestBook->id)
            ->assertJsonPath('data.0.publisher.id', $newestBook->publisher_id)
            ->assertJsonCount(1, 'data.0.authors')
            ->assertJsonCount(1, 'data.0.genres')
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 16);
    }

    public function test_books_list_respects_custom_per_page(): void
    {
        $this->createBooksWithSharedRelations(3);

        $response = $this->getJson('/api/v1/books?per_page=2');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3);
    }

    public function test_books_list_rejects_too_large_per_page(): void
    {
        $response = $this->getJson('/api/v1/books?per_page=999');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_book_is_created_from_flat_payload(): void
    {
        $publisher = Publisher::factory()->create();
        $authors = Author::factory()->count(2)->create();
        $genres = Genre::factory()->count(2)->create();

        $payload = [
            'title' => 'Domain-Driven Shelf',
            'publisher_id' => $publisher->id,
            'author_ids' => $authors->modelKeys(),
            'genre_ids' => $genres->modelKeys(),
            'published_at' => '2024-05-01',
            'word_count' => 120000,
            'price' => '19.99',
            'currency' => 'USD',
        ];

        $response = $this->postJson('/api/v1/books', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', $payload['title'])
            ->assertJsonPath('data.publisher.id', $publisher->id)
            ->assertJsonCount(2, 'data.authors')
            ->assertJsonCount(2, 'data.genres');

        $this->assertDatabaseHas('books', [
            'title' => $payload['title'],
            'publisher_id' => $publisher->id,
            'currency' => 'USD',
        ]);
    }

    public function test_book_is_created_without_publisher(): void
    {
        $authors = Author::factory()->count(2)->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/books', [
            'title' => 'Publisherless Book',
            'publisher_id' => null,
            'author_ids' => $authors->modelKeys(),
            'genre_ids' => $genres->modelKeys(),
            'published_at' => '2024-05-01',
            'word_count' => 50000,
            'price' => '9.99',
            'currency' => 'USD',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Publisherless Book')
            ->assertJsonPath('data.publisher', null)
            ->assertJsonCount(2, 'data.authors')
            ->assertJsonCount(2, 'data.genres');

        $this->assertDatabaseHas('books', [
            'title' => 'Publisherless Book',
            'publisher_id' => null,
        ]);
    }

    public function test_book_create_accepts_price_with_comma_decimal_separator(): void
    {
        $author = Author::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->postJson('/api/v1/books', [
            'title' => 'Comma Price Book',
            'author_ids' => [$author->id],
            'genre_ids' => [$genre->id],
            'published_at' => '2024-05-01',
            'word_count' => 50000,
            'price' => '19,99',
            'currency' => 'USD',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.price', '19.99');

        $this->assertDatabaseHas('books', [
            'title' => 'Comma Price Book',
            'price' => '19.99',
        ]);
    }

    public function test_book_create_rejects_empty_payload(): void
    {
        $response = $this->postJson('/api/v1/books', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title',
                'author_ids',
                'genre_ids',
                'published_at',
                'word_count',
                'price',
                'currency',
            ]);
    }

    public function test_book_create_rejects_word_count_above_upper_bound(): void
    {
        $author = Author::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->postJson('/api/v1/books', [
            'title' => 'Too Long Book',
            'author_ids' => [$author->id],
            'genre_ids' => [$genre->id],
            'published_at' => '2024-05-01',
            'word_count' => 16777216,
            'price' => '19.99',
            'currency' => 'USD',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['word_count']);
    }

    public function test_book_create_rejects_duplicate_author_ids(): void
    {
        $author = Author::factory()->create();
        $genres = Genre::factory()->count(1)->create();

        $response = $this->postJson('/api/v1/books', [
            'title' => 'Duplicate Authors',
            'author_ids' => [$author->id, $author->id],
            'genre_ids' => $genres->modelKeys(),
            'published_at' => '2024-05-01',
            'word_count' => 120000,
            'price' => '19.99',
            'currency' => 'USD',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['author_ids.1']);
    }

    public function test_book_create_rejects_duplicate_genre_ids(): void
    {
        $authors = Author::factory()->count(1)->create();
        $genre = Genre::factory()->create();

        $response = $this->postJson('/api/v1/books', [
            'title' => 'Duplicate Genres',
            'author_ids' => $authors->modelKeys(),
            'genre_ids' => [$genre->id, $genre->id],
            'published_at' => '2024-05-01',
            'word_count' => 120000,
            'price' => '19.99',
            'currency' => 'USD',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['genre_ids.1']);
    }

    public function test_book_is_shown_with_relations(): void
    {
        $book = $this->createCompleteBook();

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $book->id)
            ->assertJsonPath('data.publisher.id', $book->publisher_id)
            ->assertJsonCount(1, 'data.authors')
            ->assertJsonCount(1, 'data.genres');
    }

    public function test_show_returns_not_found_for_missing_book(): void
    {
        $this->getJson('/api/v1/books/999999')
            ->assertNotFound();
    }

    public function test_book_is_updated_partially(): void
    {
        $book = $this->createCompleteBook();
        $newPublisher = Publisher::factory()->create();
        $authors = Author::factory()->count(2)->create();

        $response = $this->patchJson("/api/v1/books/{$book->id}", [
            'title' => 'Refactoring the Catalog',
            'publisher_id' => $newPublisher->id,
            'author_ids' => $authors->modelKeys(),
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.title', 'Refactoring the Catalog')
            ->assertJsonPath('data.publisher.id', $newPublisher->id)
            ->assertJsonCount(2, 'data.authors')
            ->assertJsonCount(1, 'data.genres');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Refactoring the Catalog',
            'publisher_id' => $newPublisher->id,
        ]);
    }

    public function test_book_update_replaces_authors(): void
    {
        $book = $this->createCompleteBook();
        $originalAuthorIds = $book->authors()->pluck('authors.id')->all();
        $replacementAuthors = Author::factory()->count(2)->create();

        $response = $this->patchJson("/api/v1/books/{$book->id}", [
            'author_ids' => $replacementAuthors->modelKeys(),
        ]);

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data.authors');

        $book->refresh();

        $this->assertSameSorted(
            $replacementAuthors->modelKeys(),
            $book->authors()->pluck('authors.id')->all(),
        );

        foreach ($originalAuthorIds as $authorId) {
            $this->assertDatabaseMissing('author_book', [
                'book_id' => $book->id,
                'author_id' => $authorId,
            ]);
        }
    }

    public function test_book_update_keeps_relations_when_not_provided(): void
    {
        $book = $this->createCompleteBook();
        $authorIds = $book->authors()->pluck('authors.id')->all();
        $genreIds = $book->genres()->pluck('genres.id')->all();

        $response = $this->patchJson("/api/v1/books/{$book->id}", [
            'title' => 'Only Title Changed',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.title', 'Only Title Changed')
            ->assertJsonCount(1, 'data.authors')
            ->assertJsonCount(1, 'data.genres');

        $book->refresh();

        $this->assertSameSorted($authorIds, $book->authors()->pluck('authors.id')->all());
        $this->assertSameSorted($genreIds, $book->genres()->pluck('genres.id')->all());
    }

    public function test_book_update_accepts_price_with_comma_decimal_separator(): void
    {
        $book = $this->createCompleteBook();

        $response = $this->patchJson("/api/v1/books/{$book->id}", [
            'price' => '14,50',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.price', '14.50');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'price' => '14.50',
        ]);
    }

    public function test_book_update_rejects_price_with_thousands_separator(): void
    {
        $book = $this->createCompleteBook();

        $response = $this->patchJson("/api/v1/books/{$book->id}", [
            'price' => '15,242.99',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'errors.price.0',
                'The price must be a positive decimal amount like 19.99 or 19,99. Thousands separators are not allowed.',
            );
    }

    public function test_update_returns_not_found_for_missing_book(): void
    {
        $this->patchJson('/api/v1/books/999999', [
            'title' => 'Missing Book',
        ])->assertNotFound();
    }

    public function test_book_is_deleted(): void
    {
        $book = $this->createCompleteBook();

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_delete_returns_not_found_for_missing_book(): void
    {
        $this->deleteJson('/api/v1/books/999999')
            ->assertNotFound();
    }

    private function createCompleteBook(): Book
    {
        return Book::factory()
            ->withPublisher()
            ->withGeneratedRelations()
            ->create();
    }

    private function createBooksWithSharedRelations(int $count): void
    {
        $publisher = Publisher::factory()->create();
        $author = Author::factory()->create();
        $genre = Genre::factory()->create();

        Book::factory()
            ->count($count)
            ->for($publisher)
            ->hasAttached($author, [], 'authors')
            ->hasAttached($genre, [], 'genres')
            ->create();
    }

    /**
     * @param  list<int>  $expected
     * @param  list<int>  $actual
     */
    private function assertSameSorted(array $expected, array $actual): void
    {
        $sortedExpected = $expected;
        $sortedActual = $actual;

        sort($sortedExpected);
        sort($sortedActual);

        $this->assertSame($sortedExpected, $sortedActual);
    }
}
