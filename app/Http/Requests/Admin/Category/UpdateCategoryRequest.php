<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_administrator') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|array',
            'name.*' => 'required_with:name|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:categories,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'description' => 'sometimes|array',
            'description.*' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ];
    }
}
