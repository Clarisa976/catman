<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhysicalVolumeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'work_id' => $this->work_id,
            'volume_number' => $this->volume_number,
            'title' => $this->title,
            'language' => $this->language,
            'country' => $this->country,
            'publisher' => $this->publisher,
            'edition_name' => $this->edition_name,
            'isbn' => $this->isbn,
            'ean' => $this->ean,
            'release_date' => $this->release_date?->toDateString(),
            'price' => $this->price,
            'currency' => $this->currency,
            'cover_url' => $this->cover_url,
            'metadata_source' => $this->metadata_source,
            'metadata_provider_id' => $this->metadata_provider_id,
            'metadata_url' => $this->metadata_url,
            'metadata_fetched_at' => $this->metadata_fetched_at?->toISOString(),
            'raw_metadata' => $this->raw_metadata,
            'work' => new WorkResource($this->whenLoaded('work')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
