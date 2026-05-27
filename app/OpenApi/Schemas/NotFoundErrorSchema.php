<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotFoundError',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Requested resource was not found.'),
    ],
    type: 'object',
)]
final class NotFoundErrorSchema {}
