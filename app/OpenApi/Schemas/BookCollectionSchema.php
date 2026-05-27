<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BookCollection',
    required: ['data', 'links', 'meta'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Book'),
        ),
        new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ],
    type: 'object',
)]
final class BookCollectionSchema {}
