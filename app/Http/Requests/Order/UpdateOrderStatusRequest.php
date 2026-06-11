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
            $allowedStatuses = ['new', 'preparing', 'ready', 'delivered', 'cancelled', 'out_for_delivery'];
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
            'status' => [
                'required',
                'string',
                'in:' . implode(',', $allowedStatuses),
                function ($attribute, $value, $fail) {
                    $orderId = $this->route('id');  // Get the order ID from route parameter
                    if (!$orderId) {
                        return;
                    }

                    // Get the order from database
                    $order = \App\Models\Order::find($orderId);
                    if (!$order) {
                        return;
                    }

                    $currentStatus = $order->status;
                    $newStatus = $value;

                    // Define valid status transitions
                    $validTransitions = [
                        null => ['new'],  // null can only go to new
                        'new' => ['preparing', 'cancelled'],  // new can go to preparing or cancelled
                        'preparing' => ['ready'],  // preparing can go to ready
                        'ready' => ['out_for_delivery'],  // ready can go to out_for_delivery
                        'out_for_delivery' => ['delivered'],  // out_for_delivery can go to delivered
                        'delivered' => [],  // delivered is final, no transitions allowed
                        'cancelled' => [],  // cancelled is final, no transitions allowed
                    ];

                    // Check if the transition is valid
                    if (!isset($validTransitions[$currentStatus])) {
                        $fail("Invalid current status: {$currentStatus}");
                        return;
                    }

                    if (!in_array($newStatus, $validTransitions[$currentStatus])) {
                        $fail("Cannot change status from {$currentStatus} to {$newStatus}. Valid transitions are: " . implode(', ', $validTransitions[$currentStatus]));
                    }
                },
            ],
        ];
    }
}
