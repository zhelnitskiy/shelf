<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'price' => $this->normalizePrice($this->input('price')),
        ]);
    }

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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'price.decimal' => 'The price must be a positive decimal amount like 19.99 or 19,99. Thousands separators are not allowed.',
        ];
    }

    protected function normalizePrice(mixed $price): mixed
    {
        if (! is_string($price)) {
            return $price;
        }

        $price = trim($price);

        if (preg_match('/^\d+,\d{1,2}$/', $price) !== 1) {
            return $price;
        }

        return str_replace(',', '.', $price);
    }
}
