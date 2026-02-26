<?php

namespace App\Http\Controllers\Api\Admin\Series;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Series\SeriesStoreRequest;
use App\Http\Requests\Admin\Series\SeriesUpdateRequest;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeriesController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        $q = Series::query()
            ->with('categories') // ✅
            ->orderBy('name');

        // ✅ filtr po kategorii (seria może należeć do wielu)
        if ($categoryId = $request->integer('category_id')) {
            $q->whereHas('categories', fn ($qq) => $qq->where('categories.id', $categoryId));
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // jeśli chcesz DTO także w index:
        // $page = $q->paginate($perPage);
        // $page->getCollection()->transform(fn($s) => $this->toDto($s));
        // return response()->json(['data' => $page]);

        return response()->json([
            'data' => $q->paginate($perPage),
        ]);
    }

    public function store(SeriesStoreRequest $request)
    {
        $data = $request->validated();

        $baseSlug = $data['slug'] ?? Str::slug($data['name'] ?? '');
        $slug = $this->uniqueSlug($baseSlug);

        $isActive = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('series', 'public');
        }

        $series = DB::transaction(function () use ($data, $slug, $isActive, $imagePath) {
            $series = Series::create([
                'name' => $data['name'],
                'slug' => $slug,
                'is_active' => $isActive,
                'seo_title' => $data['seo_title'] ?? null,
                'seo_description' => $data['seo_description'] ?? null,
                'image_path' => $imagePath,
            ]);

            // ✅ przypisanie do wielu kategorii (obowiązkowe)
            $series->categories()->sync($data['category_ids']);

            return $series;
        });

        return response()->json([
            'data' => $this->toDto($series->fresh()->load('categories')),
        ], 201);
    }

    public function show(Series $series)
    {
        return response()->json([
            'data' => $this->toDto($series->load('categories')),
        ]);
    }

    public function update(SeriesUpdateRequest $request, Series $series)
    {
        $data = $request->validated();

        // slug jeśli przesłany (albo pusty -> z name)
        if (array_key_exists('slug', $data)) {
            $candidate = $data['slug'] ?: Str::slug($data['name'] ?? $series->name);
            $data['slug'] = $this->uniqueSlug($candidate, $series->id);
        } elseif (array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug(Str::slug($data['name']), $series->id);
        }

        $oldImagePath = $series->image_path;

        // remove_image: usuń bez wgrywania nowego
        if ($request->boolean('remove_image')) {
            $data['image_path'] = null;
        }

        // nowy obrazek nadpisuje wszystko
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('series', 'public');
        }

        DB::transaction(function () use ($series, $data) {
            $categoryIds = $data['category_ids'] ?? null;
            unset($data['category_ids']); // ✅ nie jest kolumną

            $series->update($data);

            if (is_array($categoryIds)) {
                $series->categories()->sync($categoryIds);
            }
        });

        $newImagePath = $series->fresh()->image_path;

        if ($oldImagePath && $oldImagePath !== $newImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return response()->json([
            'data' => $this->toDto($series->fresh()->load('categories')),
        ]);
    }

    public function destroy(Series $series)
    {
        $imagePath = $series->image_path;

        $series->delete();

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

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
        $categories = $series->relationLoaded('categories')
            ? $series->categories
            : collect();

        return [
            'id' => $series->id,

            // ✅ pod many-to-many
            'category_ids' => $categories->pluck('id')->values(),
            'categories' => $categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
            ])->values(),

            'name' => $series->name,
            'slug' => $series->slug,

            'is_active' => (bool) $series->is_active,

            'seo_title' => $series->seo_title,
            'seo_description' => $series->seo_description,

            'image_path' => $series->image_path,
            'image_url' => $series->image_path
                ? Storage::disk('public')->url($series->image_path)
                : null,

            'created_at' => $series->created_at?->toIso8601String(),
            'updated_at' => $series->updated_at?->toIso8601String(),
        ];
    }
}
