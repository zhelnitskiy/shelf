<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PaginationLinks',
    required: ['first', 'last', 'prev', 'next'],
    properties: [
        new OA\Property(
            property: 'first',
            oneOf: [
                new OA\Schema(type: 'string', format: 'uri'),
                new OA\Schema(type: 'null'),
            ],
        ),
        new OA\Property(
            property: 'last',
            oneOf: [
                new OA\Schema(type: 'string', format: 'uri'),
                new OA\Schema(type: 'null'),
            ],
        ),
        new OA\Property(
            property: 'prev',
            oneOf: [
                new OA\Schema(type: 'string', format: 'uri'),
                new OA\Schema(type: 'null'),
            ],
        ),
        new OA\Property(
            property: 'next',
            oneOf: [
                new OA\Schema(type: 'string', format: 'uri'),
                new OA\Schema(type: 'null'),
            ],
        ),
    ],
    type: 'object',
)]
final class PaginationLinksSchema {}
