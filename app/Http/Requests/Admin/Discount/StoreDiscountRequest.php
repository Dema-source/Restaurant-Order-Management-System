<?php

namespace App\Http\Requests\Admin\Discount;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for storing a new discount.
 *
 * This request handles the validation for creating a new discount,
 * including name, code, type, value, validity period, and active status.
 *
 * Validation rules ensure data integrity and proper formatting before
 * the discount is persisted to the database.
 */
class StoreDiscountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * All authenticated users are authorized to create discounts.
     *
     * @return bool True if authorized, false otherwise
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_administrator') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> The validation rules
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:discounts,name',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'minimum_order_amount' => 'nullable|numeric|min:0',
            'weekday' => 'nullable|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * This method adds custom validation logic to ensure that percentage
     * discounts do not exceed 100%.
     *
     * @param \Illuminate\Validation\Validator $validator The validator instance
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->sometimes('discount_value', 'max:100', function ($input) {
            return $input->discount_type === 'percentage';
        });
    }
}
