<?php

namespace App\Http\Controllers\Api\Admin\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Products\CategoryStoreRequest;
use App\Http\Requests\Admin\Products\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        $search = trim((string) $request->get('search', ''));

        $q = Category::query()
            ->orderBy('name')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('slug', 'like', "%{$search}%");
                });
            });

        return response()->json([
            'data' => $q->paginate($perPage),
        ]);
    }

    public function store(CategoryStoreRequest $request)
    {
        $data = $request->validated();

        $baseSlug = $data['slug'] ?? Str::slug($data['name'] ?? '');
        $data['slug'] = $this->uniqueSlug($baseSlug);

        // jeśli nie przyszło, zostanie default z DB; ale zostawiamy defensywnie:
        if (!array_key_exists('is_active', $data)) {
            $data['is_active'] = true;
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        return response()->json([
            'data' => $this->toDto($category->fresh()),
        ], 201);
    }

    public function show(Category $category)
    {
        return response()->json([
            'data' => $this->toDto($category),
        ]);
    }

    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $data = $request->validated();

        // Slug handling:
        // - if "slug" present: recalc unique from slug or name fallback
        // - else if "name" present: recalc slug from name (admin-friendly default)
        if (array_key_exists('slug', $data)) {
            $base = $data['slug'] ?: Str::slug($data['name'] ?? $category->name);
            $data['slug'] = $this->uniqueSlug($base, $category->id);
        } elseif (array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug(Str::slug($data['name']), $category->id);
        }

        // ✅ zapamiętaj stary obrazek przed zmianą
        $oldImagePath = $category->image_path;

        // ✅ remove_image (usuń bez wgrywania nowego)
        // frontend wysyła remove_image=1 gdy user kliknie "Usuń"
        if ($request->boolean('remove_image')) {
            $data['image_path'] = null;
        }

        // ✅ nowy obrazek nadpisuje wszystko (i anuluje remove_image)
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        DB::transaction(function () use ($category, $data) {
            $category->update($data);
        });

        // ✅ usuń stary plik, jeśli został zmieniony lub usunięty
        $newImagePath = $category->fresh()->image_path;

        if ($oldImagePath && $oldImagePath !== $newImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return response()->json([
            'data' => $this->toDto($category->fresh()),
        ]);
    }

    public function destroy(Category $category)
    {
        $imagePath = $category->image_path;

        $category->delete();

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        return response()->json(['ok' => true]);
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        if ($slug === '') {
            $slug = 'category';
        }

        $i = 0;

        while (true) {
            $candidate = $i === 0 ? $slug : "{$slug}-{$i}";

            $exists = Category::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists();

            if (! $exists) {
                return $candidate;
            }

            $i++;
        }
    }

    private function toDto(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'is_active' => (bool) $category->is_active, // ✅ DODANE
            'seo_title' => $category->seo_title,
            'seo_description' => $category->seo_description,
            'image_path' => $category->image_path,
            'image_url' => $category->image_path
                ? Storage::disk('public')->url($category->image_path)
                : null,
            'created_at' => $category->created_at?->toIso8601String(),
            'updated_at' => $category->updated_at?->toIso8601String(),
        ];
    }
}
