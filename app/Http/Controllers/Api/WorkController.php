<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Work;
use Illuminate\Http\Request;

class WorkController extends Controller
{
    public function index()
    {
        return Work::query()
            ->when(request('query'), fn ($query, $value) => $query->where('title', 'like', '%'.$value.'%'))
            ->latest()
            ->paginate();
    }

    public function store(Request $request)
    {
        $work = Work::create($this->validateWork($request));

        return response()->json($work, 201);
    }

    public function show(Work $work)
    {
        return $work->load(['physicalVolumes', 'digitalSeries.platform']);
    }

    public function update(Request $request, Work $work)
    {
        $work->update($this->validateWork($request, true));

        return $work->fresh();
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
