<?php

namespace App\Http\Requests\Discount;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for filtering discounts.
 *
 * This request handles the validation for filtering discounts by
 * search term, active status, and date range.
 *
 * Validation rules ensure only valid filter parameters are accepted.
 */
class IndexDiscountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Only admin and cashier users are authorized to view discounts.
     *
     * @return bool True if authorized, false otherwise
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['super_administrator', 'Cashier']) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> The validation rules
     */
    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'created_at_from' => 'nullable|date|date_format:Y-m-d',
            'created_at_to' => 'nullable|date|date_format:Y-m-d|after_or_equal:created_at_from',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
