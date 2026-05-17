<?php

namespace App\Http\Controllers\Api\Admin\Series;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Series\SeriesStoreRequest;
use App\Http\Requests\Admin\Series\SeriesUpdateRequest;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeriesController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        $q = Series::query()
            ->with('categories')
            ->orderBy('name');

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
            $imagePath = $this->saveImageToPublicStorage($request->file('image'));
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

        if (array_key_exists('slug', $data)) {
            $candidate = $data['slug'] ?: Str::slug($data['name'] ?? $series->name);
            $data['slug'] = $this->uniqueSlug($candidate, $series->id);
        } elseif (array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug(Str::slug($data['name']), $series->id);
        }

        $oldImagePath = $series->image_path;

        if ($request->boolean('remove_image')) {
            $data['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->saveImageToPublicStorage($request->file('image'));
        }

        DB::transaction(function () use ($series, $data) {
            $categoryIds = $data['category_ids'] ?? null;

            unset($data['category_ids']);

            $series->update($data);

            if (is_array($categoryIds)) {
                $series->categories()->sync($categoryIds);
            }
        });

        $newImagePath = $series->fresh()->image_path;

        if ($oldImagePath && $oldImagePath !== $newImagePath) {
            $this->deleteImageFromPublicStorage($oldImagePath);
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
            $this->deleteImageFromPublicStorage($imagePath);
        }

        return response()->json(['ok' => true]);
    }

    private function saveImageToPublicStorage($file): string
{
    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

    $cleanName = Str::slug($originalName);

    $random = Str::random(6);

    $fileName = $cleanName . '-' . $random . '.jpg';

    $destinationPath = public_path('storage/series');

    if (!file_exists($destinationPath)) {
        mkdir($destinationPath, 0755, true);
    }

    $sourcePath = $file->getRealPath();
    $imageInfo = getimagesize($sourcePath);

    if (!$imageInfo) {
        $file->move($destinationPath, $fileName);

        return 'series/' . $fileName;
    }

    [$originalWidth, $originalHeight] = $imageInfo;

    $mime = $imageInfo['mime'];

    $newWidth = 400;
    $newHeight = (int) round(($originalHeight / $originalWidth) * $newWidth);

    switch ($mime) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;

        case 'image/png':
            $sourceImage = imagecreatefrompng($sourcePath);
            break;

        case 'image/webp':
            $sourceImage = imagecreatefromwebp($sourcePath);
            break;

        default:
            $file->move($destinationPath, $fileName);

            return 'series/' . $fileName;
    }

    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    imagecopyresampled(
        $newImage,
        $sourceImage,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $originalWidth,
        $originalHeight
    );

    imagejpeg($newImage, $destinationPath . '/' . $fileName, 82);

    imagedestroy($sourceImage);
    imagedestroy($newImage);

    return 'series/' . $fileName;
}

   private function deleteImageFromPublicStorage(?string $path): void
{
    if (!$path) {
        return;
    }

    $fullPath = public_path('storage/' . ltrim($path, '/'));

    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);

        if ($slug === '') {
            $slug = 'series';
        }

        $i = 0;

        while (true) {
            $candidate = $i === 0 ? $slug : "{$slug}-{$i}";

            $exists = Series::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists();

            if (!$exists) {
                return $candidate;
            }

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
                ? asset($series->image_path)
                : null,
            'created_at' => $series->created_at?->toIso8601String(),
            'updated_at' => $series->updated_at?->toIso8601String(),
        ];
    }
}
