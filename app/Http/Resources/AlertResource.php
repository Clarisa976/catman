<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'alert_type' => $this->alert_type,
            'work_id' => $this->work_id,
            'physical_volume_id' => $this->physical_volume_id,
            'digital_series_id' => $this->digital_series_id,
            'digital_episode_id' => $this->digital_episode_id,
            'title' => $this->title,
            'message' => $this->message,
            'status' => $this->status,
            'read_at' => $this->read_at?->toISOString(),
            'work' => new WorkResource($this->whenLoaded('work')),
            'physical_volume' => new PhysicalVolumeResource($this->whenLoaded('physicalVolume')),
            'digital_series' => new DigitalSeriesResource($this->whenLoaded('digitalSeries')),
            'digital_episode' => new DigitalEpisodeResource($this->whenLoaded('digitalEpisode')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
