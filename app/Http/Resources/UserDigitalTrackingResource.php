<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDigitalTrackingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'digital_series_id' => $this->digital_series_id,
            'follow_updates' => $this->follow_updates,
            'notify_new_episode' => $this->notify_new_episode,
            'reading_status' => $this->reading_status,
            'last_episode_read' => $this->last_episode_read,
            'last_episode_seen' => $this->last_episode_seen,
            'started_at' => $this->started_at?->toDateString(),
            'finished_at' => $this->finished_at?->toDateString(),
            'notes' => $this->notes,
            'digital_series' => new DigitalSeriesResource($this->whenLoaded('digitalSeries')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
