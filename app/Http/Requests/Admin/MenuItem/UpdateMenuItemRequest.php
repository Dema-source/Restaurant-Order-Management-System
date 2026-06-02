<?php

namespace App\Http\Requests\Admin\MenuItem;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for updating an existing menu item.
 *
 * This request handles the validation for updating a menu item,
 * including translatable fields (name and description), category association,
 * pricing, image upload, and availability status.
 *
 * Validation rules ensure data integrity and proper formatting before
 * the menu item is updated in the database.
 */
class UpdateMenuItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Only users with the super_administrator role are authorized to update menu items.
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
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|array',
            'name.*' => 'required_with:name|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:menu_items,slug,' . $this->route('id') . '|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'description' => 'sometimes|array',
            'description.*' => 'nullable|string|max:1000',
            'price' => 'sometimes|numeric|min:0',
            'image' => 'sometimes|string|max:500',
            'is_available' => 'boolean',
        ];
    }
}
