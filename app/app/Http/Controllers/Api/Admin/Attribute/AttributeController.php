<?php

namespace App\Http\Controllers\Api\Admin\Attribute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\AttributeResource;
use App\Http\Requests\Admin\Attribute\StoreAttributeRequest;
use App\Http\Requests\Admin\Attribute\UpdateAttributeRequest;
use App\Models\Attribute;

class AttributeController extends Controller
{
    public function index(Request $request)
    {
        $query = Attribute::query();

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        $attributes = $query->latest()->paginate($request->integer('per_page', 15));

        return AttributeResource::collection($attributes);
    }

    public function store(StoreAttributeRequest $request)
    {
        $attribute = Attribute::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return new AttributeResource($attribute);
    }

    public function show(Attribute $attribute)
    {
        return new AttributeResource($attribute);
    }

    public function update(UpdateAttributeRequest $request, Attribute $attribute)
    {
        $data = $request->only(['name', 'slug', 'is_active']);

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $attribute->update($data);

        return new AttributeResource($attribute);
    }

    public function destroy(Attribute $attribute)
    {
        if ($attribute->products()->exists()) {
            return response()->json([
                'message' => 'Nie można usunąć atrybutu, ponieważ jest przypisany do produktów.'
            ], 422);
        }

        $attribute->delete();

        return response()->json([
            'message' => 'Atrybut został usunięty.'
        ]);
    }

    public function select()
    {
        $attributes = Attribute::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($attributes);
    }
}
