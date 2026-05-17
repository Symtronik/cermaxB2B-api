<?php

namespace App\Http\Controllers\Api\Admin\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;

class ProductController extends Controller
{
    public function index(Request $request)
{
    $query = Product::with([
        'category',
        'series',
        'attributes',
        'images',
    ]);

    $search = trim((string) $request->input('search', ''));

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('ean', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('color', 'like', "%{$search}%");
        });
    }

    if ($request->filled('attribute_id')) {
        $query->whereHas('attributes', function ($q) use ($request) {
            $q->where('attributes.id', (int) $request->input('attribute_id'));
        });
    }

    if ($request->filled('category_id')) {
        $query->where('category_id', (int) $request->input('category_id'));
    }

    if ($request->filled('series_id')) {
        $query->where('series_id', (int) $request->input('series_id'));
    }

    $products = $query
        ->latest()
        ->paginate((int) $request->input('per_page', 15));

    return ProductResource::collection($products);
}

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        $attributes = $data['attributes'] ?? [];

        unset(
            $data['attributes'],
            $data['images'],
            $data['main_image_index']
        );

        $data['is_active'] = $request->boolean('is_active', true);

        $product = DB::transaction(function () use ($request, $data, $attributes) {
            $product = Product::create($data);

            if (!empty($attributes)) {
                $product->attributes()->sync($this->formatAttributesForSync($attributes));
            }

            $this->storeUploadedImages(
                product: $product,
                uploadedImages: $request->file('images', []),
                mainImageIndex: (int) $request->input('main_image_index', 0)
            );

            return $product;
        });

        $product->load(['category', 'series', 'attributes', 'images']);

        return new ProductResource($product);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'series', 'attributes', 'images']);

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        $attributes = $data['attributes'] ?? null;
        $existingImages = $data['existing_images'] ?? null;

        unset(
            $data['attributes'],
            $data['images'],
            $data['existing_images'],
            $data['main_image_index']
        );

        $data['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($request, $product, $data, $attributes, $existingImages) {
            $product->update($data);

            if (is_array($attributes)) {
                $product->attributes()->sync($this->formatAttributesForSync($attributes));
            }

            if (is_array($existingImages)) {
                $this->syncExistingImages($product, $existingImages);
            }

            $uploadedImages = $request->file('images', []);

            if (!empty($uploadedImages)) {
                $this->storeUploadedImages(
                    product: $product,
                    uploadedImages: $uploadedImages,
                    mainImageIndex: (int) $request->input('main_image_index', 0),
                    append: true
                );
            }

            $this->ensureMainImage($product);
        });

        $product->load(['category', 'series', 'attributes', 'images']);

        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                $this->deleteImageFromPublicHtml($image->path);
            }

            $product->attributes()->detach();
            $product->images()->delete();
            $product->delete();
        });

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    private function formatAttributesForSync(array $attributes): array
    {
        $syncData = [];

        foreach ($attributes as $item) {
            if (!isset($item['attribute_id']) || empty($item['attribute_id'])) {
                continue;
            }

            $syncData[(int) $item['attribute_id']] = [
                'value' => array_key_exists('value', $item) ? (string) $item['value'] : null,
            ];
        }

        return $syncData;
    }

    private function syncExistingImages(Product $product, array $existingImages): void
    {
        $keptIds = collect($existingImages)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $imagesToDelete = $product->images()
            ->whereNotIn('id', $keptIds)
            ->get();

        foreach ($imagesToDelete as $image) {
            $this->deleteImageFromPublicHtml($image->path);
            $image->delete();
        }

        foreach ($existingImages as $index => $item) {
            if (empty($item['id'])) {
                continue;
            }

            $product->images()
                ->where('id', (int) $item['id'])
                ->update([
                    'sort_order' => isset($item['sort_order']) ? (int) $item['sort_order'] : $index,
                    'is_main' => !empty($item['is_main']),
                ]);
        }

        if (!empty($keptIds)) {
            $mainExists = $product->images()
                ->whereIn('id', $keptIds)
                ->where('is_main', true)
                ->exists();

            if (!$mainExists) {
                $firstImage = $product->images()
                    ->whereIn('id', $keptIds)
                    ->orderBy('sort_order')
                    ->first();

                if ($firstImage) {
                    $product->images()->update(['is_main' => false]);
                    $firstImage->update(['is_main' => true]);
                }
            }
        }
    }

    private function storeUploadedImages(
        Product $product,
        array $uploadedImages,
        int $mainImageIndex = 0,
        bool $append = false
    ): void {
        if (empty($uploadedImages)) {
            return;
        }

        $startOrder = $append
            ? ((int) $product->images()->max('sort_order') + 1)
            : 0;

        if (!$append) {
            foreach ($product->images as $image) {
                $this->deleteImageFromPublicHtml($image->path);
            }

            $product->images()->delete();
        }

        $hasMainImage = $product->images()->where('is_main', true)->exists();

        foreach ($uploadedImages as $index => $file) {
            $path = $this->saveImageToPublicHtml($file);

            $product->images()->create([
                'path' => $path,
                'disk' => 'public',
                'sort_order' => $startOrder + $index,
                'is_main' => !$hasMainImage && $index === $mainImageIndex,
            ]);
        }

        $this->ensureMainImage($product);
    }

    private function ensureMainImage(Product $product): void
    {
        $images = $product->images()->orderBy('sort_order')->get();

        if ($images->isEmpty()) {
            return;
        }

        $mainImages = $images->where('is_main', true);

        if ($mainImages->count() === 1) {
            return;
        }

        $product->images()->update(['is_main' => false]);

        $images->first()->update([
            'is_main' => true,
        ]);
    }

    private function saveImageToPublicHtml($file): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $cleanName = Str::slug($originalName);
        $random = Str::random(6);

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');

        $fileName = $cleanName . '-' . $random . '.' . $extension;

        $destinationPath = public_path('storage/products');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $file->move($destinationPath, $fileName);

        return 'products/' . $fileName;
    }

    private function deleteImageFromPublicHtml(?string $path): void
    {
        if (!$path) {
            return;
        }

        $fullPath = public_path('storage/' . ltrim($path, '/'));

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
