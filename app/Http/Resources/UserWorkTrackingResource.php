<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserWorkTrackingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'work_id' => $this->work_id,
            'follow_physical_releases' => $this->follow_physical_releases,
            'preferred_language' => $this->preferred_language,
            'preferred_country' => $this->preferred_country,
            'preferred_publisher' => $this->preferred_publisher,
            'last_owned_volume_number' => $this->last_owned_volume_number,
            'notify_new_volume' => $this->notify_new_volume,
            'notes' => $this->notes,
            'work' => new WorkResource($this->whenLoaded('work')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
