<?php

namespace App\Http\Requests\InventoryMovement;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for filtering inventory movements.
 *
 * This request handles the validation for filtering inventory movements by
 * search term, type, reason, menu item, order, and date range.
 *
 * Validation rules ensure only valid filter parameters are accepted.
 */
class IndexInventoryMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * All authenticated users are authorized to view inventory movements.
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
            'type' => 'nullable|in:in,out',
            'reason' => 'nullable|in:order,restock,waste,adjustment',
            'menu_item_id' => 'nullable|integer|exists:menu_items,id',
            'order_id' => 'nullable|integer|exists:orders,id',
            'created_at_from' => 'nullable|date|date_format:Y-m-d',
            'created_at_to' => 'nullable|date|date_format:Y-m-d|after_or_equal:created_at_from',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
