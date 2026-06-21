<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DigitalEpisodeResource;
use App\Models\DigitalEpisode;
use App\Models\DigitalSeries;
use Illuminate\Http\Request;

class DigitalEpisodeController extends Controller
{
    public function index(Request $request, DigitalSeries $digitalSeries)
    {
        return DigitalEpisodeResource::collection(
            $digitalSeries->episodes()
                ->orderBy('episode_number')
                ->paginate($this->perPage($request))
        );
    }

    public function store(Request $request, DigitalSeries $digitalSeries)
    {
        $episode = $digitalSeries->episodes()->create($this->validateEpisode($request));

        return (new DigitalEpisodeResource($episode))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, DigitalEpisode $digitalEpisode)
    {
        $digitalEpisode->update($this->validateEpisode($request, true));

        return new DigitalEpisodeResource($digitalEpisode->fresh('digitalSeries'));
    }

    public function destroy(DigitalEpisode $digitalEpisode)
    {
        $digitalEpisode->delete();

        return response()->noContent();
    }

    private function validateEpisode(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'episode_number' => [$partial ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'title' => ['nullable', 'string', 'max:255'],
            'release_date' => ['nullable', 'date'],
            'episode_url' => ['nullable', 'url', 'max:2048'],
            'is_free' => ['nullable', 'boolean'],
            'is_locked' => ['nullable', 'boolean'],
        ]);
    }
}
