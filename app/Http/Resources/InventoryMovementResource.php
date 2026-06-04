<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryMovementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * This method transforms the inventory movement model into a JSON response,
     * including all relevant fields and relationships.
     *
     * @param Request $request The incoming request
     * @return array<string, mixed> The transformed resource
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_item_id' => $this->menu_item_id,
            'order_id' => $this->order_id,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,

            // Relationships
            'menu_item' => $this->whenLoaded('menuItem'),
            'order' => $this->whenLoaded('order'),
            'created_by_user' => $this->whenLoaded('createdBy'),
        ];
    }
}
