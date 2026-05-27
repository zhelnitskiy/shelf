<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class BookController extends Controller
{
    #[OA\Get(
        path: '/api/v1/books',
        operationId: 'listBooks',
        summary: 'List books',
        tags: ['Books'],
        parameters: [
            new OA\QueryParameter(
                name: 'per_page',
                description: 'Number of books per page.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100),
            ),
            new OA\QueryParameter(
                name: 'page',
                description: 'Pagination page number.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated book collection',
                content: new OA\JsonContent(ref: '#/components/schemas/BookCollection'),
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $books = Book::query()
            ->with(['publisher', 'authors', 'genres'])
            ->latest('id')
            ->paginate($validated['per_page'] ?? 15)
            ->withQueryString();

        return BookResource::collection($books);
    }

    #[OA\Post(
        path: '/api/v1/books',
        operationId: 'createBook',
        summary: 'Create a book',
        tags: ['Books'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/BookStoreRequest'),
                example: [
                    'title' => 'Domain-Driven Shelf',
                    'publisher_id' => 1,
                    'author_ids' => [1, 2],
                    'genre_ids' => [3, 4],
                    'published_at' => '2024-05-01',
                    'word_count' => 120000,
                    'price' => '19.99',
                    'currency' => 'USD',
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Book created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Book'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ],
    )]
    public function store(StoreBookRequest $request): BookResource
    {
        $book = DB::transaction(function () use ($request): Book {
            $book = Book::query()->create($request->safe()->except(['author_ids', 'genre_ids']));

            $book->authors()->sync($request->validated('author_ids'));
            $book->genres()->sync($request->validated('genre_ids'));

            return $book;
        });

        return new BookResource($book->load(['publisher', 'authors', 'genres']));
    }

    #[OA\Get(
        path: '/api/v1/books/{book}',
        operationId: 'showBook',
        summary: 'Show a book',
        tags: ['Books'],
        parameters: [
            new OA\PathParameter(
                name: 'book',
                description: 'Book id.',
                required: true,
                schema: new OA\Schema(type: 'integer', minimum: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Book details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Book'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 404,
                description: 'Book not found',
                content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'),
            ),
        ],
    )]
    public function show(Book $book): BookResource
    {
        return new BookResource($book->load(['publisher', 'authors', 'genres']));
    }

    #[OA\Patch(
        path: '/api/v1/books/{book}',
        operationId: 'updateBook',
        summary: 'Partially update a book',
        description: 'Only provided fields are updated. author_ids and genre_ids replace existing relations only when those fields are present in the request.',
        tags: ['Books'],
        parameters: [
            new OA\PathParameter(
                name: 'book',
                description: 'ID of the book to update.',
                required: true,
                schema: new OA\Schema(type: 'integer', minimum: 1),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/BookUpdateRequest'),
                example: [
                    'title' => 'Refactoring the Catalog',
                    'publisher_id' => 2,
                    'author_ids' => [1, 2],
                    'genre_ids' => [3, 4],
                    'published_at' => '2024-05-02',
                    'word_count' => 95000,
                    'price' => '14.99',
                    'currency' => 'USD',
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Book updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Book'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 404,
                description: 'Book not found',
                content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'),
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ],
    )]
    public function update(UpdateBookRequest $request, Book $book): BookResource
    {
        DB::transaction(function () use ($request, $book): void {
            $book->fill($request->safe()->except(['author_ids', 'genre_ids']));

            if ($book->isDirty()) {
                $book->save();
            }

            if ($request->has('author_ids')) {
                $book->authors()->sync($request->validated('author_ids'));
            }

            if ($request->has('genre_ids')) {
                $book->genres()->sync($request->validated('genre_ids'));
            }
        });

        return new BookResource($book->load(['publisher', 'authors', 'genres']));
    }

    #[OA\Delete(
        path: '/api/v1/books/{book}',
        operationId: 'deleteBook',
        summary: 'Delete a book',
        tags: ['Books'],
        parameters: [
            new OA\PathParameter(
                name: 'book',
                description: 'Book id.',
                required: true,
                schema: new OA\Schema(type: 'integer', minimum: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Book deleted',
            ),
            new OA\Response(
                response: 404,
                description: 'Book not found',
                content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'),
            ),
        ],
    )]
    public function destroy(Book $book): Response
    {
        $book->delete();

        return response()->noContent();
    }
}
