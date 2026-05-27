<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class AuthorController extends Controller
{
    #[OA\Get(
        path: '/api/v1/authors',
        operationId: 'listAuthors',
        summary: 'List authors',
        tags: ['Authors'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Author collection',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthorCollection'),
            ),
        ],
    )]
    public function index(): AnonymousResourceCollection
    {
        $authors = Author::query()
            ->orderBy('name')
            ->get();

        return AuthorResource::collection($authors);
    }
}
