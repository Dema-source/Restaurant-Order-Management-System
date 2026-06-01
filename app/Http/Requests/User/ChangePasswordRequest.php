<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'password' => 'required|string|min:8|confirmed',
        ];

        // Only require old_password if user is changing their own password
        if ($this->user()?->id === $this->route('id')) {
            $rules['old_password'] = 'required|string';
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate old_password if provided (for self password change)
            if ($this->filled('old_password') && !Hash::check($this->old_password, $this->user()->password)) {
                $validator->errors()->add('old_password', 'The current password is incorrect.');
            }
        });
    }
}
