<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:100',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
            'include' => 'sometimes|string|max:255',
            'sort_by' => ['sometimes', Rule::in(['name', 'created_at', 'updated_at', 'price'])],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' => 'Search term must be a valid string.',
            'search.max' => 'Search term cannot exceed 255 characters.',
            'category.string' => 'Category must be a valid string.',
            'category.max' => 'Category cannot exceed 100 characters.',
            'per_page.integer' => 'Items per page must be a number.',
            'per_page.min' => 'Must show at least 1 item per page.',
            'per_page.max' => 'Cannot show more than 100 items per page.',
            'page.integer' => 'Page number must be a number.',
            'page.min' => 'Page number must be at least 1.',
            'include.string' => 'Include parameter must be a valid string.',
            'sort_by.in' => 'Invalid sort field selected.',
            'sort_direction.in' => 'Sort direction must be either asc or desc.',
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Set defaults
        $this->merge([
            'per_page' => $this->per_page ?? 15,
            'page' => $this->page ?? 1,
            'sort_by' => $this->sort_by ?? 'created_at',
            'sort_direction' => $this->sort_direction ?? 'desc',
        ]);

        // Normalize search term
        if ($this->has('search')) {
            $this->merge([
                'search' => trim($this->search),
            ]);
        }
    }
}
