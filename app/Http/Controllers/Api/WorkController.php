<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkResource;
use App\Models\Work;
use Illuminate\Http\Request;

class WorkController extends Controller
{
    public function index(Request $request)
    {
        $works = Work::query()
            ->when($request->query('search') ?? $request->query('query'), function ($query, $value): void {
                $query->where(function ($query) use ($value): void {
                    $query
                        ->where('title', 'like', '%'.$value.'%')
                        ->orWhere('original_title', 'like', '%'.$value.'%')
                        ->orWhere('author', 'like', '%'.$value.'%');
                });
            })
            ->when($request->query('type'), fn ($query, $value) => $query->where('type', $value))
            ->when($request->query('status'), fn ($query, $value) => $query->where('status', $value))
            ->when($request->query('author'), fn ($query, $value) => $query->where('author', 'like', '%'.$value.'%'))
            ->latest()
            ->paginate($this->perPage($request));

        return WorkResource::collection($works);
    }

    public function store(Request $request)
    {
        $work = Work::create($this->validateWork($request));

        return (new WorkResource($work))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Work $work)
    {
        return new WorkResource($work->load(['physicalVolumes', 'digitalSeries.platform']));
    }

    public function update(Request $request, Work $work)
    {
        $work->update($this->validateWork($request, true));

        return new WorkResource($work->fresh());
    }

    public function destroy(Work $work)
    {
        $work->delete();

        return response()->noContent();
    }

    private function validateWork(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'title' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'original_title' => ['nullable', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'in:manga,manhwa,manhua,novel,other'],
            'status' => ['sometimes', 'string', 'in:ongoing,completed,hiatus,unknown'],
            'total_volumes' => ['nullable', 'integer', 'min:0'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
