<?php

namespace App\Http\Controllers\Api\Admin\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\CategoryStoreRequest;
use App\Http\Requests\Admin\Category\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        if (!array_key_exists('is_active', $data)) {
            $data['is_active'] = true;
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->saveImageToPublicStorage($request->file('image'));
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

        if (array_key_exists('slug', $data)) {
            $base = $data['slug'] ?: Str::slug($data['name'] ?? $category->name);
            $data['slug'] = $this->uniqueSlug($base, $category->id);
        } elseif (array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug(Str::slug($data['name']), $category->id);
        }

        $oldImagePath = $category->image_path;

        if ($request->boolean('remove_image')) {
            $data['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->saveImageToPublicStorage($request->file('image'));
        }

        DB::transaction(function () use ($category, $data) {
            $category->update($data);
        });

        $newImagePath = $category->fresh()->image_path;

        if ($oldImagePath && $oldImagePath !== $newImagePath) {
            $this->deleteImageFromPublicStorage($oldImagePath);
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

    $destinationPath = public_path('storage/categories');

    if (!file_exists($destinationPath)) {
        mkdir($destinationPath, 0755, true);
    }

    $sourcePath = $file->getRealPath();
    $imageInfo = getimagesize($sourcePath);

    if (!$imageInfo) {
        $file->move($destinationPath, $fileName);
        return 'storage/categories/' . $fileName;
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
            return 'storage/categories/' . $fileName;
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

    return 'storage/categories/' . $fileName;
}

    private function deleteImageFromPublicStorage(?string $path): void
    {
        if (!$path) {
            return;
        }

        $fullPath = public_path($path);

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
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

            if (!$exists) {
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
            'is_active' => (bool) $category->is_active,
            'seo_title' => $category->seo_title,
            'seo_description' => $category->seo_description,
            'image_path' => $category->image_path,
            'image_url' => $category->image_path
                ? asset($category->image_path)
                : null,
            'created_at' => $category->created_at?->toIso8601String(),
            'updated_at' => $category->updated_at?->toIso8601String(),
        ];
    }
}
