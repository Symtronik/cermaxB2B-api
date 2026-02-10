<?php

namespace App\Http\Controllers\Api\Admin\Series;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Series\SeriesStoreRequest;
use App\Http\Requests\Admin\Series\SeriesUpdateRequest;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeriesController extends Controller
{
    public function index(Request $request)
    {
        $q = Series::query()
            ->with('category')
            ->orderBy('name');

        if ($categoryId = $request->integer('category_id')) {
            $q->where('category_id', $categoryId);
        }

        if ($search = $request->string('search')->toString()) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('slug', 'like', "%{$search}%");
        }

        return response()->json([
            'data' => $q->paginate((int) $request->get('per_page', 20)),
        ]);
    }

    public function store(SeriesStoreRequest $request)
    {
        $data = $request->validated();

        $slug = $data['slug'] ?? Str::slug($data['name']);
        $data['slug'] = $this->uniqueSlug($slug);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('series', 'public');
        }

        $series = Series::create($data);

        return response()->json([
            'data' => $this->toDto($series->load('category')),
        ], 201);
    }

    public function show(Series $series)
    {
        return response()->json([
            'data' => $this->toDto($series->load('category')),
        ]);
    }

    public function update(SeriesUpdateRequest $request, Series $series)
    {
        $data = $request->validated();

        if (array_key_exists('slug', $data)) {
            $candidate = $data['slug'] ?: Str::slug($data['name'] ?? $series->name);
            $data['slug'] = $this->uniqueSlug($candidate, $series->id);
        }

        if ($request->hasFile('image')) {
            if ($series->image_path) {
                Storage::disk('public')->delete($series->image_path);
            }
            $data['image_path'] = $request->file('image')->store('series', 'public');
        }

        $series->update($data);

        return response()->json([
            'data' => $this->toDto($series->fresh()->load('category')),
        ]);
    }

    public function destroy(Series $series)
    {
        if ($series->image_path) {
            Storage::disk('public')->delete($series->image_path);
        }

        $series->delete();

        return response()->json(['ok' => true]);
    }

    /* ===================================================== */

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        if ($slug === '') $slug = 'series';

        $i = 0;
        while (true) {
            $candidate = $i === 0 ? $slug : "{$slug}-{$i}";
            $exists = Series::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists();

            if (! $exists) return $candidate;
            $i++;
        }
    }

    private function toDto(Series $series): array
    {
        return [
            'id' => $series->id,
            'category_id' => $series->category_id,
            'category_name' => $series->category?->name,

            'name' => $series->name,
            'slug' => $series->slug,

            'seo_title' => $series->seo_title,
            'seo_description' => $series->seo_description,

            'image_url' => $series->image_path
                ? Storage::disk('public')->url($series->image_path)
                : null,

            'created_at' => $series->created_at?->toIso8601String(),
            'updated_at' => $series->updated_at?->toIso8601String(),
        ];
    }
}
