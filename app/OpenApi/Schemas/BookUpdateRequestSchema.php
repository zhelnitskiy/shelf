<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BookUpdateRequest',
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Refactoring the Catalog'),
        new OA\Property(
            property: 'publisher_id',
            oneOf: [
                new OA\Schema(type: 'integer', example: 2),
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
        new OA\Property(property: 'published_at', type: 'string', format: 'date', example: '2024-05-02'),
        new OA\Property(property: 'word_count', type: 'integer', minimum: 1, maximum: 16777215, example: 95000),
        new OA\Property(
            property: 'price',
            type: 'string',
            pattern: '^\d+(?:[.,]\d{1,2})?$',
            description: 'Price accepts a dot or comma decimal separator with up to two fractional digits.',
            example: '14.99',
        ),
        new OA\Property(property: 'currency', type: 'string', minLength: 3, maxLength: 3, example: 'USD'),
    ],
    type: 'object',
)]
final class BookUpdateRequestSchema {}
