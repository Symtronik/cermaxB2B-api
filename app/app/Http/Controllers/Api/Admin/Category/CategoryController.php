<?php

namespace App\Http\Controllers\Api\Admin\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Products\CategoryStoreRequest;
use App\Http\Requests\Admin\Products\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
  public function index(Request $request)
  {
    $q = Category::query()->orderBy('name');

    if ($search = $request->string('search')->toString()) {
      $q->where('name', 'like', "%{$search}%")
        ->orWhere('slug', 'like', "%{$search}%");
    }

    return response()->json([
      'data' => $q->paginate((int)($request->get('per_page', 20))),
    ]);
  }

  public function store(CategoryStoreRequest $request)
  {
    $data = $request->validated();

    $slug = $data['slug'] ?? Str::slug($data['name']);
    $data['slug'] = $this->uniqueSlug($slug);

    if ($request->hasFile('image')) {
      $data['image_path'] = $request->file('image')->store('categories', 'public');
    }

    $category = Category::create($data);

    return response()->json([
      'data' => $this->toDto($category),
    ], 201);
  }

  public function show(Category $category)
  {
    return response()->json(['data' => $this->toDto($category)]);
  }

  public function update(CategoryUpdateRequest $request, Category $category)
  {
    $data = $request->validated();

    if (array_key_exists('slug', $data)) {
      $candidate = $data['slug'] ?: Str::slug($data['name'] ?? $category->name);
      $data['slug'] = $this->uniqueSlug($candidate, $category->id);
    }

    if ($request->hasFile('image')) {
      if ($category->image_path) {
        Storage::disk('public')->delete($category->image_path);
      }
      $data['image_path'] = $request->file('image')->store('categories', 'public');
    }

    $category->update($data);

    return response()->json(['data' => $this->toDto($category)]);
  }

  public function destroy(Category $category)
  {
    if ($category->image_path) {
      Storage::disk('public')->delete($category->image_path);
    }

    $category->delete();

    return response()->json(['ok' => true]);
  }

  private function uniqueSlug(string $base, ?int $ignoreId = null): string
  {
    $slug = Str::slug($base);
    if ($slug === '') $slug = 'category';

    $i = 0;
    while (true) {
      $candidate = $i === 0 ? $slug : "{$slug}-{$i}";
      $exists = Category::query()
        ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
        ->where('slug', $candidate)
        ->exists();

      if (!$exists) return $candidate;
      $i++;
    }
  }

  private function toDto(Category $category): array
  {
    return [
      'id' => $category->id,
      'name' => $category->name,
      'slug' => $category->slug,
      'seo_title' => $category->seo_title,
      'seo_description' => $category->seo_description,
      'image_url' => $category->image_path ? Storage::disk('public')->url($category->image_path) : null,
      'created_at' => $category->created_at?->toIso8601String(),
      'updated_at' => $category->updated_at?->toIso8601String(),
    ];
  }
}
