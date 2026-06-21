<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertResource;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $alerts = Alert::with(['work', 'physicalVolume', 'digitalSeries', 'digitalEpisode'])
            ->where('user_id', $request->user()->id)
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('alert_type'), fn ($query, $type) => $query->where('alert_type', $type))
            ->latest()
            ->paginate($this->perPage($request));

        return AlertResource::collection($alerts);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'alert_type' => ['required', 'string', 'in:physical_release,digital_update,missing_volume,system'],
            'work_id' => ['nullable', 'exists:works,id'],
            'physical_volume_id' => ['nullable', 'exists:physical_volumes,id'],
            'digital_series_id' => ['nullable', 'exists:digital_series,id'],
            'digital_episode_id' => ['nullable', 'exists:digital_episodes,id'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'status' => ['sometimes', 'string', 'in:unread,read,dismissed'],
        ]);

        $alert = Alert::create($data + [
            'user_id' => $request->user()->id,
            'status' => $data['status'] ?? 'unread',
        ]);

        return (new AlertResource($alert))
            ->response()
            ->setStatusCode(201);
    }

    public function markRead(Request $request, Alert $alert)
    {
        abort_unless($alert->user_id === $request->user()->id, 404);

        $alert->update([
            'status' => 'read',
            'read_at' => now(),
        ]);

        return new AlertResource($alert->fresh());
    }

    public function dismiss(Request $request, Alert $alert)
    {
        abort_unless($alert->user_id === $request->user()->id, 404);

        $alert->update(['status' => 'dismissed']);

        return new AlertResource($alert->fresh());
    }

    public function destroy(Request $request, Alert $alert)
    {
        abort_unless($alert->user_id === $request->user()->id, 404);

        $alert->delete();

        return response()->noContent();
    }
}
