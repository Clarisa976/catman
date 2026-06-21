<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserWorkTracking;
use Illuminate\Http\Request;

class UserWorkTrackingController extends Controller
{
    public function index(Request $request)
    {
        return UserWorkTracking::with('work')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate();
    }

    public function store(Request $request)
    {
        $tracking = UserWorkTracking::create($this->validateTracking($request) + [
            'user_id' => $request->user()->id,
        ]);

        return response()->json($tracking->load('work'), 201);
    }

    public function update(Request $request, UserWorkTracking $tracking)
    {
        abort_unless($tracking->user_id === $request->user()->id, 404);

        $tracking->update($this->validateTracking($request, true));

        return $tracking->fresh('work');
    }

    public function destroy(Request $request, UserWorkTracking $tracking)
    {
        abort_unless($tracking->user_id === $request->user()->id, 404);

        $tracking->delete();

        return response()->noContent();
    }

    private function validateTracking(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'work_id' => [$partial ? 'sometimes' : 'required', 'exists:works,id'],
            'follow_physical_releases' => ['sometimes', 'boolean'],
            'preferred_language' => ['sometimes', 'string', 'max:80'],
            'preferred_country' => ['sometimes', 'string', 'max:80'],
            'preferred_publisher' => ['nullable', 'string', 'max:255'],
            'last_owned_volume_number' => ['nullable', 'numeric', 'min:0'],
            'notify_new_volume' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
