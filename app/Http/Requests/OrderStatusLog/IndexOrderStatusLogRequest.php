<?php

namespace App\Http\Requests\OrderStatusLog;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for filtering order status logs.
 *
 * This request handles the validation for filtering status logs by
 * search term, order ID, status, and date range.
 */
class IndexOrderStatusLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if authorized, false otherwise
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['super_administrator']) ?? false;
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
            'order_id' => 'nullable|integer|exists:orders,id',
            'status' => 'nullable|string|in:new,preparing,ready,delivered,out_for_delivery,cancelled',
            'created_at_from' => 'nullable|date|date_format:Y-m-d',
            'created_at_to' => 'nullable|date|date_format:Y-m-d|after_or_equal:created_at_from',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
