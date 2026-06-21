<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DigitalEpisodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'digital_series_id' => $this->digital_series_id,
            'episode_number' => $this->episode_number,
            'title' => $this->title,
            'release_date' => $this->release_date?->toDateString(),
            'episode_url' => $this->episode_url,
            'is_free' => $this->is_free,
            'is_locked' => $this->is_locked,
            'digital_series' => new DigitalSeriesResource($this->whenLoaded('digitalSeries')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
