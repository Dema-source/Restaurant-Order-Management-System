<?php

namespace App\Http\Requests\Admin\MenuItem;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for storing a new menu item.
 *
 * This request handles the validation for creating a new menu item,
 * including translatable fields (name and description), category association,
 * pricing, image upload, and availability status.
 *
 * Validation rules ensure data integrity and proper formatting before
 * the menu item is persisted to the database.
 */
class StoreMenuItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Only users with the super_administrator role are authorized to create menu items.
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
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:menu_items,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|file|image|mimes:jpg,png,jpeg|max:2024',
            'is_available' => 'boolean',
        ];
    }
}
