<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PublisherCollection',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Publisher'),
        ),
    ],
    type: 'object',
)]
final class PublisherCollectionSchema {}
