<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DigitalSeriesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'work_id' => $this->work_id,
            'platform_id' => $this->platform_id,
            'title' => $this->title,
            'platform_url' => $this->platform_url,
            'language' => $this->language,
            'status' => $this->status,
            'latest_episode_detected' => $this->latest_episode_detected,
            'last_checked_at' => $this->last_checked_at?->toISOString(),
            'cover_url' => $this->cover_url,
            'notes' => $this->notes,
            'platform' => new DigitalPlatformResource($this->whenLoaded('platform')),
            'work' => new WorkResource($this->whenLoaded('work')),
            'episodes' => DigitalEpisodeResource::collection($this->whenLoaded('episodes')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
