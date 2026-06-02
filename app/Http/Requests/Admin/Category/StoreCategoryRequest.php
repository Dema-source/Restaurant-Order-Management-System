<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_administrator') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ];
    }
}
