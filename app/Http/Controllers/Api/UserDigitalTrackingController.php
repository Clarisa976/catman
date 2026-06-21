<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDigitalTracking;
use Illuminate\Http\Request;

class UserDigitalTrackingController extends Controller
{
    public function index(Request $request)
    {
        return UserDigitalTracking::with('digitalSeries.platform')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate();
    }

    public function store(Request $request)
    {
        $tracking = UserDigitalTracking::create($this->validateTracking($request) + [
            'user_id' => $request->user()->id,
        ]);

        return response()->json($tracking->load('digitalSeries.platform'), 201);
    }

    public function update(Request $request, UserDigitalTracking $tracking)
    {
        abort_unless($tracking->user_id === $request->user()->id, 404);

        $tracking->update($this->validateTracking($request, true));

        return $tracking->fresh('digitalSeries.platform');
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
