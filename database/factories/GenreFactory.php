<?php

namespace Database\Factories;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Genre>
 */
class GenreFactory extends Factory
{
    /**
     * Fixed genre catalog used by tests and seeders.
     *
     * @var list<string>
     */
    private const GENRES = [
        'Science',
        'Poetry',
        'Drama',
        'Crime',
        'Detective',
        'Romance',
        'Fantasy',
        'Science Fiction',
        'Action',
        'Adventure',
        'Horror',
        'Literary Fiction',
        'Comedy',
        'Historical Fiction',
    ];

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Genre>
     */
    protected $model = Genre::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(self::GENRES),
        ];
    }
}
