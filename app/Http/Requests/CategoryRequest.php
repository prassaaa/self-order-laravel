<?php

namespace App\Http\Requests;

use App\Rules\ImageValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'image' => [
                'nullable',
                'file',
                new ImageValidation(
                    maxSize: 2 * 1024 * 1024, // 2MB
                    allowedTypes: ['image/jpeg', 'image/png', 'image/webp'],
                    minWidth: 200,
                    minHeight: 200,
                    maxWidth: 2000,
                    maxHeight: 2000
                ),
            ],
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.unique' => 'Category name already exists.',
            'slug.unique' => 'Category slug already exists.',
            'slug.regex' => 'Slug can only contain lowercase letters, numbers, and hyphens.',
            'image.file' => 'Image must be a valid file.',
            'sort_order.min' => 'Sort order must be at least 0.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'is_active' => 'active status',
            'sort_order' => 'sort order',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug if not provided
        if (!$this->has('slug') && $this->has('name')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name),
            ]);
        }

        // Set default values
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'sort_order' => $this->integer('sort_order', 0),
        ]);
    }
}
