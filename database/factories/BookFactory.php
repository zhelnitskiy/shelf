<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * @var list<string>
     */
    private const TITLE_NOUNS = [
        'Blood',
        'Bone',
        'Drift',
        'Dust',
        'Fever',
        'Flood',
        'Grief',
        'Hunger',
        'Plague',
        'Scar',
        'Stone',
        'Thorn',
        'Tide',
        'Veil',
    ];

    /**
     * @var list<string>
     */
    private const TITLE_PLACES = [
        'Big Sur',
        'Catalina',
        'El Capitan',
        'Mavericks',
        'Mojave',
        'Monterey',
        'Sequoia',
        'Sierra',
        'Sonoma',
        'Ventura',
        'Yosemite',
    ];

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Book>
     */
    protected $model = Book::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->generateTitle(),
            'publisher_id' => null,
            'published_at' => fake()->date(),
            'word_count' => fake()->numberBetween(1_000, 2_100_000),
            'price' => fake()->randomFloat(2, 1, 500),
            'currency' => 'USD',
        ];
    }

    /**
     * @return Factory<Book>
     */
    public function complete(): Factory
    {
        return $this
            ->hasAttached(Author::factory(), [], 'authors')
            ->hasAttached(Genre::factory(), [], 'genres');
    }

    /**
     * @return Factory<Book>
     */
    public function withPublisher(): Factory
    {
        return $this->for(Publisher::factory());
    }

    private function generateTitle(): string
    {
        /** @var list<string> $patterns */
        $patterns = [
            'The %s',
            'The %s of the %s',
            '%s of the %s',
            'Beyond the %s',
            'The Last %s',
            'Return to %s',
            '%s and %s',
        ];

        $pattern = fake()->randomElement($patterns);

        if ($pattern === 'Return to %s') {
            return sprintf($pattern, $this->titlePlace());
        }

        if (substr_count($pattern, '%s') === 1) {
            return sprintf($pattern, $this->titleNoun());
        }

        return sprintf($pattern, $this->titleNoun(), $this->titleNoun());
    }

    private function titleNoun(): string
    {
        return fake()->randomElement(self::TITLE_NOUNS);
    }

    private function titlePlace(): string
    {
        return fake()->randomElement(self::TITLE_PLACES);
    }
}
