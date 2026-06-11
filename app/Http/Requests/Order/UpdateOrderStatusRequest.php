<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = auth()->user();
        $allowedStatuses = [];

        // Super admin can change to any status
        if ($user->hasRole('super_administrator')) {
            $allowedStatuses = ['new', 'preparing', 'ready', 'delivered', 'cancelled'];
        }
        // Kitchen staff can only change to preparing or ready
        elseif ($user->hasRole('Kitchen_staff')) {
            $allowedStatuses = ['preparing', 'ready'];
        }
        // Cashier can only change to delivered or cancelled
        elseif ($user->hasRole('Cashier')) {
            $allowedStatuses = ['delivered', 'cancelled', 'out_for_delivery'];
        }

        return [
            'status' => 'required|string|in:' . implode(',', $allowedStatuses),
        ];
    }
}
