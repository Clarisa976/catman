<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDigitalEpisodeRead;
use Illuminate\Http\Request;

class UserDigitalEpisodeReadController extends Controller
{
    public function store(Request $request)
    {
        $read = UserDigitalEpisodeRead::create($this->validateRead($request) + [
            'user_id' => $request->user()->id,
        ]);

        return response()->json($read->load('digitalEpisode.digitalSeries'), 201);
    }

    public function update(Request $request, UserDigitalEpisodeRead $read)
    {
        abort_unless($read->user_id === $request->user()->id, 404);

        $read->update($this->validateRead($request, true));

        return $read->fresh('digitalEpisode.digitalSeries');
    }

    public function destroy(Request $request, UserDigitalEpisodeRead $read)
    {
        abort_unless($read->user_id === $request->user()->id, 404);

        $read->delete();

        return response()->noContent();
    }

    private function validateRead(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'digital_episode_id' => [$partial ? 'sometimes' : 'required', 'exists:digital_episodes,id'],
            'read_status' => ['sometimes', 'string', 'in:not_started,reading,read,skipped'],
            'read_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
