<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublisherResource;
use App\Models\Publisher;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class PublisherController extends Controller
{
    #[OA\Get(
        path: '/api/v1/publishers',
        operationId: 'listPublishers',
        summary: 'List publishers',
        tags: ['Publishers'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Publisher collection',
                content: new OA\JsonContent(ref: '#/components/schemas/PublisherCollection'),
            ),
        ],
    )]
    public function index(): AnonymousResourceCollection
    {
        $publishers = Publisher::query()
            ->orderBy('name')
            ->get();

        return PublisherResource::collection($publishers);
    }
}
