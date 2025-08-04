<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
            'order_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:orders,id',
                function ($attribute, $value, $fail) {
                    $order = \App\Models\Order::find($value);
                    if ($order && $order->status === 'cancelled') {
                        $fail('Cannot process payment for cancelled order.');
                    }
                },
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'regex:/^\d+(\.\d{1,2})?$/', // Allow up to 2 decimal places
                function ($attribute, $value, $fail) {
                    if ($this->has('order_id')) {
                        $order = \App\Models\Order::find($this->order_id);
                        if ($order) {
                            $remainingAmount = app(\App\Services\PaymentService::class)->getRemainingAmount($order);
                            if ($value > $remainingAmount) {
                                $fail("Payment amount cannot exceed remaining balance of Rp " . number_format($remainingAmount, 0, ',', '.'));
                            }
                        }
                    }
                },
            ],
            'method' => [
                'required',
                'string',
                'in:cash,qris,bank_transfer,e_wallet',
            ],
            'status' => [
                'sometimes',
                'string',
                'in:pending,completed,failed,refunded',
            ],
            'transaction_id' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($value && $this->method === 'cash') {
                        $fail('Transaction ID is not required for cash payments.');
                    }
                    if (!$value && in_array($this->method, ['qris', 'bank_transfer', 'e_wallet'])) {
                        $fail('Transaction ID is required for digital payments.');
                    }
                },
            ],
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'order_id.required' => 'Order is required.',
            'order_id.exists' => 'Selected order does not exist.',
            'amount.required' => 'Payment amount is required.',
            'amount.numeric' => 'Payment amount must be a valid number.',
            'amount.min' => 'Payment amount must be at least Rp 0.01.',
            'amount.max' => 'Payment amount cannot exceed Rp 999,999.99.',
            'amount.regex' => 'Payment amount can have at most 2 decimal places.',
            'method.required' => 'Payment method is required.',
            'method.in' => 'Invalid payment method. Allowed methods: cash, qris, bank_transfer, e_wallet.',
            'status.in' => 'Invalid payment status.',
            'transaction_id.max' => 'Transaction ID cannot exceed 255 characters.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'order_id' => 'order',
            'transaction_id' => 'transaction ID',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default status based on method
        if (!$this->has('status')) {
            $defaultStatus = $this->method === 'cash' ? 'completed' : 'pending';
            $this->merge(['status' => $defaultStatus]);
        }

        // Format amount to ensure proper decimal places
        if ($this->has('amount')) {
            $this->merge([
                'amount' => number_format((float)$this->amount, 2, '.', ''),
            ]);
        }

        // Set processed_by to current user
        $this->merge([
            'processed_by' => auth()->id(),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional business logic validation
            if ($this->has('method') && $this->has('amount')) {
                // Validate minimum amount for digital payments
                if (in_array($this->method, ['qris', 'bank_transfer', 'e_wallet']) && $this->amount < 1000) {
                    $validator->errors()->add('amount', 'Minimum amount for digital payments is Rp 1,000.');
                }

                // Validate maximum amount for cash payments
                if ($this->method === 'cash' && $this->amount > 500000) {
                    $validator->errors()->add('amount', 'Maximum cash payment amount is Rp 500,000.');
                }
            }

            // Validate transaction ID format for specific methods
            if ($this->has('transaction_id') && $this->transaction_id) {
                switch ($this->method) {
                    case 'qris':
                        if (!preg_match('/^QR[0-9A-Z]{10,20}$/', $this->transaction_id)) {
                            $validator->errors()->add('transaction_id', 'Invalid QRIS transaction ID format.');
                        }
                        break;
                    case 'bank_transfer':
                        if (!preg_match('/^TF[0-9]{10,15}$/', $this->transaction_id)) {
                            $validator->errors()->add('transaction_id', 'Invalid bank transfer transaction ID format.');
                        }
                        break;
                    case 'e_wallet':
                        if (!preg_match('/^EW[0-9A-Z]{8,15}$/', $this->transaction_id)) {
                            $validator->errors()->add('transaction_id', 'Invalid e-wallet transaction ID format.');
                        }
                        break;
                }
            }
        });
    }
}
