<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GenreCollection',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Genre'),
        ),
    ],
    type: 'object',
)]
final class GenreCollectionSchema {}
