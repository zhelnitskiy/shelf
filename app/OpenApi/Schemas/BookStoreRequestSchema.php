<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BookStoreRequest',
    required: [
        'title',
        'author_ids',
        'genre_ids',
        'published_at',
        'word_count',
        'price',
        'currency',
    ],
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Domain-Driven Shelf'),
        new OA\Property(
            property: 'publisher_id',
            oneOf: [
                new OA\Schema(type: 'integer', example: 1),
                new OA\Schema(type: 'null'),
            ],
        ),
        new OA\Property(
            property: 'author_ids',
            type: 'array',
            minItems: 1,
            items: new OA\Items(type: 'integer'),
            example: [1, 2],
        ),
        new OA\Property(
            property: 'genre_ids',
            type: 'array',
            minItems: 1,
            items: new OA\Items(type: 'integer'),
            example: [3, 4],
        ),
        new OA\Property(property: 'published_at', type: 'string', format: 'date', example: '2024-05-01'),
        new OA\Property(property: 'word_count', type: 'integer', minimum: 1, maximum: 16777215, example: 120000),
        new OA\Property(
            property: 'price',
            type: 'string',
            pattern: '^\d+(?:[.,]\d{1,2})?$',
            description: 'Price accepts a dot or comma decimal separator with up to two fractional digits.',
            example: '19.99',
        ),
        new OA\Property(property: 'currency', type: 'string', minLength: 3, maxLength: 3, example: 'USD'),
    ],
    type: 'object',
)]
final class BookStoreRequestSchema {}
