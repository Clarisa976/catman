<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DigitalSeriesResource;
use App\Models\DigitalSeries;
use Illuminate\Http\Request;

class DigitalSeriesController extends Controller
{
    public function index(Request $request)
    {
        $series = DigitalSeries::with(['platform', 'work'])
            ->when($request->query('search') ?? $request->query('query'), fn ($query, $value) => $query->where('title', 'like', '%'.$value.'%'))
            ->latest()
            ->paginate($this->perPage($request));

        return DigitalSeriesResource::collection($series);
    }

    public function store(Request $request)
    {
        $series = DigitalSeries::create($this->validateSeries($request));

        return (new DigitalSeriesResource($series->load(['platform', 'work'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(DigitalSeries $digitalSeries)
    {
        return new DigitalSeriesResource($digitalSeries->load(['platform', 'work', 'episodes']));
    }

    public function update(Request $request, DigitalSeries $digitalSeries)
    {
        $digitalSeries->update($this->validateSeries($request, true));

        return new DigitalSeriesResource($digitalSeries->fresh(['platform', 'work']));
    }

    public function destroy(DigitalSeries $digitalSeries)
    {
        $digitalSeries->delete();

        return response()->noContent();
    }

    private function validateSeries(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'work_id' => ['nullable', 'exists:works,id'],
            'platform_id' => [$partial ? 'sometimes' : 'required', 'exists:digital_platforms,id'],
            'title' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'platform_url' => ['nullable', 'url', 'max:2048'],
            'language' => ['sometimes', 'string', 'max:80'],
            'status' => ['sometimes', 'string', 'in:ongoing,completed,hiatus,unknown'],
            'latest_episode_detected' => ['nullable', 'numeric', 'min:0'],
            'last_checked_at' => ['nullable', 'date'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
