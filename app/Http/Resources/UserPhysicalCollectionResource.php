<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPhysicalCollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'physical_volume_id' => $this->physical_volume_id,
            'ownership_status' => $this->ownership_status,
            'reading_status' => $this->reading_status,
            'purchase_date' => $this->purchase_date?->toDateString(),
            'purchase_price' => $this->purchase_price,
            'purchase_currency' => $this->purchase_currency,
            'store' => $this->store,
            'is_travel_memory' => $this->is_travel_memory,
            'purchase_country' => $this->purchase_country,
            'location' => $this->location,
            'notes' => $this->notes,
            'physical_volume' => new PhysicalVolumeResource($this->whenLoaded('physicalVolume')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
