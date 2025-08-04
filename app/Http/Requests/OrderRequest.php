<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow public access for creating orders (customer interface)
        // Staff can manage orders
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'table_number' => 'nullable|string|max:10',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\-\(\)\s]+$/', // Allow phone number formats
            ],
            'notes' => 'nullable|string|max:500',

            // Order items validation
            'items' => $isUpdate ? 'nullable|array|min:1' : 'required|array|min:1',
            'items.*.menu_id' => [
                'required',
                'integer',
                'exists:menus,id',
                function ($attribute, $value, $fail) {
                    $menu = \App\Models\Menu::find($value);
                    if ($menu && !$menu->is_available) {
                        $fail("The menu item '{$menu->name}' is not available.");
                    }
                },
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:99',
            ],
            'items.*.notes' => 'nullable|string|max:255',

            // Status validation (for staff updates)
            'status' => [
                'sometimes',
                'string',
                'in:pending,confirmed,preparing,ready,completed,cancelled',
                function ($attribute, $value, $fail) {
                    if (!auth()->check() || !auth()->user()->isStaff()) {
                        $fail('Only staff can update order status.');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.menu_id.required' => 'Menu item is required.',
            'items.*.menu_id.exists' => 'Selected menu item does not exist.',
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Quantity cannot exceed 99.',
            'customer_phone.regex' => 'Please enter a valid phone number.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
            'items.*.notes.max' => 'Item notes cannot exceed 255 characters.',
            'status.in' => 'Invalid order status.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'table_number' => 'table number',
            'customer_name' => 'customer name',
            'customer_phone' => 'customer phone',
            'items.*.menu_id' => 'menu item',
            'items.*.quantity' => 'quantity',
            'items.*.notes' => 'item notes',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('items')) {
                // Check for duplicate menu items
                $menuIds = collect($this->items)->pluck('menu_id');
                if ($menuIds->count() !== $menuIds->unique()->count()) {
                    $validator->errors()->add('items', 'Duplicate menu items are not allowed. Please combine quantities instead.');
                }

                // Validate total order value
                $totalAmount = 0;
                foreach ($this->items as $index => $item) {
                    $menu = \App\Models\Menu::find($item['menu_id']);
                    if ($menu) {
                        $totalAmount += $menu->price * $item['quantity'];
                    }
                }

                // Set minimum order amount (optional business rule)
                $minOrderAmount = 10000; // Rp 10,000
                if ($totalAmount < $minOrderAmount) {
                    $validator->errors()->add('items', "Minimum order amount is Rp " . number_format($minOrderAmount, 0, ',', '.'));
                }

                // Set maximum order amount (optional business rule)
                $maxOrderAmount = 1000000; // Rp 1,000,000
                if ($totalAmount > $maxOrderAmount) {
                    $validator->errors()->add('items', "Maximum order amount is Rp " . number_format($maxOrderAmount, 0, ',', '.'));
                }
            }
        });
    }
}
