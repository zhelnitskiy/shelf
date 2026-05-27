<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Shelf API',
    description: 'REST API for managing books and related reference data.',
)]
#[OA\Server(
    url: '/',
    description: 'Application server',
)]
#[OA\Tag(
    name: 'Books',
    description: 'CRUD operations for books.',
)]
#[OA\Tag(
    name: 'Genres',
    description: 'Genre dictionary endpoints.',
)]
#[OA\Tag(
    name: 'Authors',
    description: 'Author list endpoints.',
)]
#[OA\Tag(
    name: 'Publishers',
    description: 'Publisher list endpoints.',
)]
final class OpenApiSpec {}
