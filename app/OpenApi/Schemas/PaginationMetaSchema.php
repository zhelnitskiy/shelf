<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PaginationMeta',
    required: ['current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'],
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(
            property: 'from',
            oneOf: [
                new OA\Schema(type: 'integer', example: 1),
                new OA\Schema(type: 'null'),
            ],
        ),
        new OA\Property(property: 'last_page', type: 'integer', example: 2),
        new OA\Property(
            property: 'links',
            type: 'array',
            items: new OA\Items(
                required: ['url', 'label', 'page', 'active'],
                properties: [
                    new OA\Property(property: 'url', oneOf: [new OA\Schema(type: 'string', format: 'uri'), new OA\Schema(type: 'null')]),
                    new OA\Property(property: 'label', type: 'string', example: '&laquo; Previous'),
                    new OA\Property(property: 'page', oneOf: [new OA\Schema(type: 'integer'), new OA\Schema(type: 'null')], example: 1),
                    new OA\Property(property: 'active', type: 'boolean', example: false),
                ],
                type: 'object',
            ),
        ),
        new OA\Property(property: 'path', type: 'string', format: 'uri', example: 'http://localhost/api/v1/books'),
        new OA\Property(property: 'per_page', type: 'integer', example: 15),
        new OA\Property(
            property: 'to',
            oneOf: [
                new OA\Schema(type: 'integer', example: 15),
                new OA\Schema(type: 'null'),
            ],
        ),
        new OA\Property(property: 'total', type: 'integer', example: 16),
    ],
    type: 'object',
)]
final class PaginationMetaSchema {}
