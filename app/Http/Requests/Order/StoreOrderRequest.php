<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['super_administrator', 'Cashier']) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer' => 'required|array',
            'customer.phone' => 'required|string|max:20',
            'customer.name' => 'nullable|string|max:255',
            'customer.address' => 'nullable|string|max:500',
            'customer.alternate_phone' => 'nullable|string|max:20',
            'customer.notes' => 'nullable|string|max:1000',

            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|integer|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:500',

            'delivery_address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer.phone.required' => 'Customer phone number is required',
            'items.required' => 'At least one item is required',
            'items.*.menu_item_id.exists' => 'The selected menu item does not exist',
            'items.*.quantity.min' => 'Quantity must be at least 1',
        ];
    }
}
