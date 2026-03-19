<?php

namespace App\Http\Controllers\Api\Admin\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['attributes', 'images']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('ean', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%");
            });
        }

        if ($request->filled('attribute_id')) {
            $attributeId = $request->integer('attribute_id');

            $query->whereHas('attributes', function ($q) use ($attributeId) {
                $q->where('attributes.id', $attributeId);
            });
        }

        $products = $query->latest()->paginate($request->integer('per_page', 15));

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        $attributes = $data['attributes'] ?? [];
        unset($data['attributes'], $data['images'], $data['main_image_index']);

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

        $product->load(['attributes', 'images']);

        return new ProductResource($product);
    }

    public function show(Product $product)
    {
        $product->load(['attributes', 'images']);

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        $attributes = $data['attributes'] ?? null;
        unset($data['attributes'], $data['images'], $data['main_image_index']);

        DB::transaction(function () use ($request, $product, $data, $attributes) {
            $product->update($data);

            if (is_array($attributes)) {
                $product->attributes()->sync($this->formatAttributesForSync($attributes));
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
        });

        $product->load(['attributes', 'images']);

        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                Storage::disk($image->disk)->delete($image->path);
            }

            $product->attributes()->detach();
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

    private function storeUploadedImages(
        Product $product,
        array $uploadedImages,
        int $mainImageIndex = 0,
        bool $append = false
    ): void {
        if (empty($uploadedImages)) {
            return;
        }

        $startOrder = $append ? ((int) $product->images()->max('sort_order') + 1) : 0;

        if (!$append) {
            $product->images()->delete();
        }

        foreach ($uploadedImages as $index => $file) {
            $path = $file->store('products', 'public');

            $product->images()->create([
                'path' => $path,
                'disk' => 'public',
                'sort_order' => $startOrder + $index,
                'is_main' => $index === $mainImageIndex,
            ]);
        }

        if ($product->images()->where('is_main', true)->count() === 0) {
            $firstImage = $product->images()->orderBy('sort_order')->first();

            if ($firstImage) {
                $product->images()->update(['is_main' => false]);
                $firstImage->update(['is_main' => true]);
            }
        }
    }
}
