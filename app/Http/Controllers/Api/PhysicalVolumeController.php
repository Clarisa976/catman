<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhysicalVolumeResource;
use App\Models\PhysicalVolume;
use App\Models\Work;
use Illuminate\Http\Request;

class PhysicalVolumeController extends Controller
{
    public function index(Request $request, Work $work)
    {
        $volumes = $work->physicalVolumes()
            ->with('work')
            ->when($request->query('search'), function ($query, $value): void {
                $query->where(function ($query) use ($value): void {
                    $query
                        ->where('title', 'like', '%'.$value.'%')
                        ->orWhere('isbn', 'like', '%'.$value.'%')
                        ->orWhere('ean', 'like', '%'.$value.'%');
                });
            })
            ->when($request->query('work_id'), fn ($query, $value) => $query->where('work_id', $value))
            ->when($request->query('language'), fn ($query, $value) => $query->where('language', $value))
            ->when($request->query('country'), fn ($query, $value) => $query->where('country', $value))
            ->when($request->query('publisher'), fn ($query, $value) => $query->where('publisher', 'like', '%'.$value.'%'))
            ->when($request->query('release_from'), fn ($query, $value) => $query->whereDate('release_date', '>=', $value))
            ->when($request->query('release_to'), fn ($query, $value) => $query->whereDate('release_date', '<=', $value))
            ->orderBy('volume_number')
            ->paginate($this->perPage($request));

        return PhysicalVolumeResource::collection($volumes);
    }

    public function store(Request $request, Work $work)
    {
        $volume = $work->physicalVolumes()->create($this->validateVolume($request));

        return (new PhysicalVolumeResource($volume))
            ->response()
            ->setStatusCode(201);
    }

    public function show(PhysicalVolume $physicalVolume)
    {
        return new PhysicalVolumeResource($physicalVolume->load('work'));
    }

    public function update(Request $request, PhysicalVolume $physicalVolume)
    {
        $physicalVolume->update($this->validateVolume($request, true));

        return new PhysicalVolumeResource($physicalVolume->fresh('work'));
    }

    public function destroy(PhysicalVolume $physicalVolume)
    {
        $physicalVolume->delete();

        return response()->noContent();
    }

    private function validateVolume(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'volume_number' => [$partial ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'title' => ['nullable', 'string', 'max:255'],
            'language' => ['sometimes', 'string', 'max:80'],
            'country' => ['sometimes', 'string', 'max:80'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'edition_name' => ['nullable', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:32'],
            'ean' => ['nullable', 'string', 'max:32'],
            'release_date' => ['nullable', 'date'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'metadata_source' => ['nullable', 'string', 'max:80'],
            'metadata_provider_id' => ['nullable', 'string', 'max:255'],
            'metadata_url' => ['nullable', 'url', 'max:2048'],
            'metadata_fetched_at' => ['nullable', 'date'],
            'raw_metadata' => ['nullable', 'array'],
        ]);
    }
}
