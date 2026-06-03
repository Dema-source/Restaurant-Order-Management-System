<?php

namespace App\Http\Requests\Admin\Discount;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for updating an existing discount.
 *
 * This request handles the validation for updating a discount,
 * including name, code, type, value, validity period, and active status.
 *
 * Validation rules ensure data integrity and proper formatting before
 * the discount is updated in the database.
 */
class UpdateDiscountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * All authenticated users are authorized to update discounts.
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
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:discounts,code,' . $this->route('id'),
            'type' => 'sometimes|in:percentage,fixed',
            'value' => 'sometimes|numeric|min:0',
            'start_date' => 'sometimes|date|after_or_equal:today',
            'end_date' => 'sometimes|date|after:start_date',
            'is_active' => 'boolean',
            'description' => 'sometimes|string|max:1000',
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
        $validator->sometimes('value', 'max:100', function ($input) {
            return $input->type === 'percentage';
        });
    }
}
