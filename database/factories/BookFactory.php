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
    private const TITLE_ADJECTIVES = [
        'Cold',
        'Dark',
        'Deep',
        'Lost',
        'Pale',
        'Red',
        'Silent',
        'Still',
        'Wild',
    ];

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
        'Passage',
        'Plague',
        'Ridge',
        'Road',
        'Scar',
        'Shore',
        'Sky',
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
    public function withGeneratedRelations(): Factory
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
        /** @var 'noun'|'adjective_noun'|'noun_noun'|'noun_place'|'place' $pattern */
        $pattern = fake()->randomElement([
            'noun',
            'noun',
            'adjective_noun',
            'adjective_noun',
            'noun_noun',
            'noun_noun',
            'noun_place',
            'place',
        ]);

        return match ($pattern) {
            'noun' => sprintf(
                fake()->randomElement([
                    'The %s',
                    'Beneath the %s',
                ]),
                $this->titleNoun(),
            ),
            'adjective_noun' => sprintf(
                fake()->randomElement([
                    'The %s %s',
                    'A %s %s',
                ]),
                $this->titleAdjective(),
                $this->titleNoun(),
            ),
            'noun_noun' => sprintf(
                fake()->randomElement([
                    '%s and %s',
                    '%s of the %s',
                ]),
                ...$this->twoDistinctTitleNouns(),
            ),
            'noun_place' => sprintf(
                'A %s in %s',
                $this->titleNoun(),
                $this->titlePlace(),
            ),
            'place' => sprintf(
                'Return to %s',
                $this->titlePlace(),
            ),
        };
    }

    private function titleAdjective(): string
    {
        return fake()->randomElement(self::TITLE_ADJECTIVES);
    }

    private function titleNoun(): string
    {
        return fake()->randomElement(self::TITLE_NOUNS);
    }

    private function titlePlace(): string
    {
        return fake()->randomElement(self::TITLE_PLACES);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function twoDistinctTitleNouns(): array
    {
        $nouns = self::TITLE_NOUNS;

        shuffle($nouns);

        return [$nouns[0], $nouns[1]];
    }
}
