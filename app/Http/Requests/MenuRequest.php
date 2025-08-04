<?php

namespace App\Http\Requests;

use App\Rules\ImageValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuRequest extends FormRequest
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
        $menuId = $this->route('menu')?->id;

        return [
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    $category = \App\Models\Category::find($value);
                    if ($category && !$category->is_active) {
                        $fail('The selected category is not active.');
                    }
                },
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('menus', 'name')->ignore($menuId),
            ],
            'description' => 'nullable|string|max:1000',
            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
                'regex:/^\d+(\.\d{1,2})?$/', // Allow up to 2 decimal places
            ],
            'image' => [
                'nullable',
                'file',
                new ImageValidation(
                    maxSize: 3 * 1024 * 1024, // 3MB
                    allowedTypes: ['image/jpeg', 'image/png', 'image/webp'],
                    minWidth: 300,
                    minHeight: 300,
                    maxWidth: 2000,
                    maxHeight: 2000
                ),
            ],
            'is_available' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'name.required' => 'Menu name is required.',
            'name.unique' => 'Menu name already exists.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price must be at least 0.',
            'price.max' => 'Price cannot exceed 999,999.99.',
            'price.regex' => 'Price can have at most 2 decimal places.',
            'description.max' => 'Description cannot exceed 1000 characters.',
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
            'category_id' => 'category',
            'is_available' => 'availability status',
            'sort_order' => 'sort order',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'is_available' => $this->boolean('is_available', true),
            'sort_order' => $this->integer('sort_order', 0),
        ]);

        // Format price to ensure proper decimal places
        if ($this->has('price')) {
            $this->merge([
                'price' => number_format((float)$this->price, 2, '.', ''),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional business logic validation can be added here
            if ($this->has('category_id') && $this->has('name')) {
                // Check for duplicate menu name within the same category
                $query = \App\Models\Menu::where('category_id', $this->category_id)
                    ->where('name', $this->name);

                if ($this->route('menu')) {
                    $query->where('id', '!=', $this->route('menu')->id);
                }

                if ($query->exists()) {
                    $validator->errors()->add('name', 'A menu with this name already exists in the selected category.');
                }
            }
        });
    }
}
