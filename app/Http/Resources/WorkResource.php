<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'original_title' => $this->original_title,
            'author' => $this->author,
            'type' => $this->type,
            'status' => $this->status,
            'total_volumes' => $this->total_volumes,
            'cover_url' => $this->cover_url,
            'notes' => $this->notes,
            'physical_volumes' => PhysicalVolumeResource::collection($this->whenLoaded('physicalVolumes')),
            'digital_series' => DigitalSeriesResource::collection($this->whenLoaded('digitalSeries')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
