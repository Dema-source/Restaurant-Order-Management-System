<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Discount resource transformation.
 *
 * This resource transforms the discount model into a JSON representation
 * suitable for API responses, including all discount fields and
 * computed properties like validity status.
 */
class DiscountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The incoming request
     * @return array<string, mixed> The transformed resource
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'is_valid' => $this->isValid(),
            'is_expired' => $this->isExpired(),
            'is_not_started' => $this->isNotStarted(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
