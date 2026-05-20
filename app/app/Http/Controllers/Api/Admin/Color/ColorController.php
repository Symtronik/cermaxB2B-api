<?php

namespace App\Http\Controllers\Api\Admin\Color;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $query = Color::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:colors,name'],
            'hex' => ['nullable', 'string', 'max:20'],
        ]);

        $color = Color::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'hex' => $validated['hex'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Kolor został dodany.',
            'data' => $color,
        ], 201);
    }
}
