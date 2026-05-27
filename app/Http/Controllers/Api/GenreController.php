<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class GenreController extends Controller
{
    #[OA\Get(
        path: '/api/v1/genres',
        operationId: 'listGenres',
        summary: 'List genres dictionary',
        tags: ['Genres'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Genre dictionary',
                content: new OA\JsonContent(ref: '#/components/schemas/GenreCollection'),
            ),
        ],
    )]
    public function index(): AnonymousResourceCollection
    {
        $genres = Genre::query()
            ->orderBy('name')
            ->get();

        return GenreResource::collection($genres);
    }
}
