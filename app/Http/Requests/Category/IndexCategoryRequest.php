<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for filtering categories.
 *
 * This request handles the validation for filtering categories by
 * search term, active status, and date range.
 *
 * Validation rules ensure only valid filter parameters are accepted.
 */
class IndexCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * All authenticated users are authorized to view categories.
     *
     * @return bool True if authorized, false otherwise
     */
    public function authorize(): bool
    {
        return true;
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
