<?php

namespace App\Http\Requests\InventoryMovement;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for inventory movements.
 *
 * This request handles validation for all inventory movement types:
 * restock, waste, and adjustment. Validation rules are applied
 * dynamically based on the movement type.
 *
 * Only admin and cashier users are authorized to perform inventory movements.
 */
class InventoryMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Only admin and cashier users are authorized to manage inventory.
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
     * Validation rules are applied dynamically based on the movement type:
     * - restock/waste: quantity must be positive (min:1)
     * - adjustment: quantity can be positive or negative (not_in:0)
     *
     * @return array<string, mixed> The validation rules
     */
    public function rules(): array
    {
        $rules = [
            'menu_item_id' => 'required|integer|exists:menu_items,id',
            'quantity' => 'required|integer',
            'notes' => 'nullable|string|max:500',
        ];

        // Apply dynamic validation based on movement type (from route method name)
        $movementType = $this->route()->getActionMethod() ?? 'restock';

        if ($movementType === 'adjustment') {
            $rules['quantity'] .= '|not_in:0';
        } else {
            $rules['quantity'] .= '|min:1';
        }

        return $rules;
    }
}
