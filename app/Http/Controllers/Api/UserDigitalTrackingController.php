<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserDigitalTrackingResource;
use App\Models\UserDigitalTracking;
use Illuminate\Http\Request;

class UserDigitalTrackingController extends Controller
{
    public function index(Request $request)
    {
        $trackings = UserDigitalTracking::with('digitalSeries.platform')
            ->where('user_id', $request->user()->id)
            ->when($request->query('reading_status'), fn ($query, $value) => $query->where('reading_status', $value))
            ->when($request->query('platform_id'), fn ($query, $value) => $query->whereHas('digitalSeries', fn ($query) => $query->where('platform_id', $value)))
            ->when($request->has('follow_updates'), fn ($query) => $query->where('follow_updates', $request->boolean('follow_updates')))
            ->when($request->query('search'), fn ($query, $value) => $query->whereHas('digitalSeries', fn ($query) => $query->where('title', 'like', '%'.$value.'%')))
            ->latest()
            ->paginate($this->perPage($request));

        return UserDigitalTrackingResource::collection($trackings);
    }

    public function store(Request $request)
    {
        $tracking = UserDigitalTracking::create($this->validateTracking($request) + [
            'user_id' => $request->user()->id,
        ]);

        return (new UserDigitalTrackingResource($tracking->load('digitalSeries.platform')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, UserDigitalTracking $tracking)
    {
        abort_unless($tracking->user_id === $request->user()->id, 404);

        $tracking->update($this->validateTracking($request, true));

        return new UserDigitalTrackingResource($tracking->fresh('digitalSeries.platform'));
    }

    public function destroy(Request $request, UserDigitalTracking $tracking)
    {
        abort_unless($tracking->user_id === $request->user()->id, 404);

        $tracking->delete();

        return response()->noContent();
    }

    private function validateTracking(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'digital_series_id' => [$partial ? 'sometimes' : 'required', 'exists:digital_series,id'],
            'follow_updates' => ['sometimes', 'boolean'],
            'notify_new_episode' => ['sometimes', 'boolean'],
            'reading_status' => ['sometimes', 'string', 'in:reading,paused,completed,dropped,plan_to_read'],
            'last_episode_read' => ['nullable', 'numeric', 'min:0'],
            'last_episode_seen' => ['nullable', 'numeric', 'min:0'],
            'started_at' => ['nullable', 'date'],
            'finished_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
