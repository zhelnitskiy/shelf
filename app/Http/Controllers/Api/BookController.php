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

class BookController extends Controller
{
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

    public function show(Book $book): BookResource
    {
        return new BookResource($book->load(['publisher', 'authors', 'genres']));
    }

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

    public function destroy(Book $book): Response
    {
        $book->delete();

        return response()->noContent();
    }
}
