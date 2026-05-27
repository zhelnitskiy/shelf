<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Book',
    required: [
        'id',
        'title',
        'published_at',
        'word_count',
        'price',
        'currency',
        'publisher',
        'authors',
        'genres',
        'created_at',
        'updated_at',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Domain-Driven Shelf'),
        new OA\Property(property: 'published_at', type: 'string', format: 'date', example: '2024-05-01'),
        new OA\Property(property: 'word_count', type: 'integer', example: 120000),
        new OA\Property(
            property: 'price',
            type: 'string',
            pattern: '^\d+\.\d{2}$',
            description: 'Price formatted with a dot decimal separator and exactly two fractional digits.',
            example: '19.99',
        ),
        new OA\Property(property: 'currency', type: 'string', minLength: 3, maxLength: 3, example: 'USD'),
        new OA\Property(
            property: 'publisher',
            oneOf: [
                new OA\Schema(ref: '#/components/schemas/Publisher'),
                new OA\Schema(type: 'null'),
            ],
        ),
        new OA\Property(
            property: 'authors',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Author'),
        ),
        new OA\Property(
            property: 'genres',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Genre'),
        ),
        new OA\Property(
            property: 'created_at',
            oneOf: [
                new OA\Schema(type: 'string', format: 'date-time'),
                new OA\Schema(type: 'null'),
            ],
            example: '2026-05-27T10:00:00+00:00',
        ),
        new OA\Property(
            property: 'updated_at',
            oneOf: [
                new OA\Schema(type: 'string', format: 'date-time'),
                new OA\Schema(type: 'null'),
            ],
            example: '2026-05-27T10:00:00+00:00',
        ),
    ],
    type: 'object',
)]
final class BookSchema {}
