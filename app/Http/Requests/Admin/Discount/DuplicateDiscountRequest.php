<?php

namespace App\Http\Requests\Admin\Discount;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for duplicating an existing discount.
 *
 * This request handles the validation for duplicating a discount,
 * allowing the user to override specific fields like name and code
 * while copying all other values from the original discount.
 *
 * Validation rules ensure data integrity and proper formatting before
 * the discount is duplicated.
 */
class DuplicateDiscountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * All authenticated users are authorized to duplicate discounts.
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
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:discounts,code',
        ];
    }
}
