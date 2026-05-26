<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublisherResource;
use App\Models\Publisher;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublisherController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $publishers = Publisher::query()
            ->orderBy('name')
            ->get();

        return PublisherResource::collection($publishers);
    }
}
