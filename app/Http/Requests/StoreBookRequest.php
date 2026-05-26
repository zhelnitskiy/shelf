<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'publisher_id' => ['nullable', 'integer', Rule::exists('publishers', 'id')],
            'author_ids' => ['required', 'array', 'min:1'],
            'author_ids.*' => ['integer', 'distinct', Rule::exists('authors', 'id')],
            'genre_ids' => ['required', 'array', 'min:1'],
            'genre_ids.*' => ['integer', 'distinct', Rule::exists('genres', 'id')],
            'published_at' => ['required', 'date'],
            'word_count' => ['required', 'integer', 'min:1', 'max:16777215'],
            'price' => ['required', 'decimal:0,2', 'min:0'],
            'currency' => ['required', 'string', 'size:3', 'uppercase'],
        ];
    }
}
