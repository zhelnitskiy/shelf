<?php

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\PublisherController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::apiResource('books', BookController::class)->except(['update']);
    Route::patch('books/{book}', [BookController::class, 'update']);
    Route::get('authors', [AuthorController::class, 'index']);
    Route::get('genres', [GenreController::class, 'index']);
    Route::get('publishers', [PublisherController::class, 'index']);
});
