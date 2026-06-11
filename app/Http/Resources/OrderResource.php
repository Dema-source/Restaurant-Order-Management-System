<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'delivery_address' => $this->delivery_address,
            'notes' => $this->notes,
            'ordered_at' => $this->ordered_at,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'discounts' => DiscountResource::collection($this->whenLoaded('discounts')),
            'status_logs' => OrderStatusLogResource::collection($this->whenLoaded('statusLogs')),
        ];
    }
}
