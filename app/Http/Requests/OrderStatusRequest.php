<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isStaff();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                'in:pending,confirmed,preparing,ready,completed,cancelled',
                function ($attribute, $value, $fail) {
                    $order = $this->route('order');
                    if ($order) {
                        $validTransitions = [
                            'pending' => ['confirmed', 'cancelled'],
                            'confirmed' => ['preparing', 'cancelled'],
                            'preparing' => ['ready'],
                            'ready' => ['completed'],
                            'completed' => [],
                            'cancelled' => [],
                        ];

                        $allowedStatuses = $validTransitions[$order->status] ?? [];
                        if (!in_array($value, $allowedStatuses)) {
                            $fail("Cannot change status from '{$order->status}' to '{$value}'.");
                        }
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
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status. Allowed statuses: pending, confirmed, preparing, ready, completed, cancelled.',
        ];
    }
}
