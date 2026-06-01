<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_administrator') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ];
    }
}
