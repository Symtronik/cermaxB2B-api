<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\ShippingPoint;
use Illuminate\Http\Request;

class ShippingPointController extends Controller
{
    public function index(Request $request)
{
    $user = $request->user();

    $query = ShippingPoint::query();

    if ($user->hasRole('customer')) {
        $query->where('user_id', $user->id);

        // klient w koszyku widzi tylko aktywne
        if ($request->query('status') !== 'all') {
            $query->where('is_active', true);
        }
    }

    if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        $status = $request->query('status', 'all');

        if ($status === 'active') {
            $query->where('is_active', true);
        }

        if ($status === 'inactive') {
            $query->where('is_active', false);
        }
    }

    $points = $query
        ->orderByDesc('is_active')
        ->orderByDesc('is_default')
        ->orderBy('name')
        ->get();

    return response()->json([
        'data' => $points,
    ]);
}
    public function store(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'street' => ['required', 'string', 'max:255'],
            'building_number' => ['nullable', 'string', 'max:50'],
            'apartment_number' => ['nullable', 'string', 'max:50'],
            'postal_code' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
            $rules['user_id'] = ['required', 'integer', 'exists:users,id'];
        }

        $data = $request->validate($rules);

        if ($user->hasRole('customer')) {
            $data['user_id'] = $user->id;
        }

        $data['country'] = $data['country'] ?? 'Polska';
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        if ($data['is_default']) {
            ShippingPoint::query()
                ->where('user_id', $data['user_id'])
                ->update([
                    'is_default' => false,
                ]);
        }

        $point = ShippingPoint::create($data);

        return response()->json([
            'message' => 'Punkt wysyłki został dodany.',
            'data' => $point,
        ], 201);
    }

    public function update(Request $request, ShippingPoint $shippingPoint)
    {
        $user = $request->user();

        if ($user->hasRole('customer')) {
            abort_unless($shippingPoint->user_id === $user->id, 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'street' => ['required', 'string', 'max:255'],
            'building_number' => ['nullable', 'string', 'max:50'],
            'apartment_number' => ['nullable', 'string', 'max:50'],
            'postal_code' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['country'] = $data['country'] ?? 'Polska';

        if ($request->has('is_default')) {
            $data['is_default'] = (bool) $data['is_default'];
        } else {
            unset($data['is_default']);
        }

        if ($request->has('is_active')) {
            $data['is_active'] = (bool) $data['is_active'];
        } else {
            unset($data['is_active']);
        }

        if (($data['is_default'] ?? false) === true) {
            ShippingPoint::query()
                ->where('user_id', $shippingPoint->user_id)
                ->where('id', '!=', $shippingPoint->id)
                ->update([
                    'is_default' => false,
                ]);
        }

        if (($data['is_active'] ?? $shippingPoint->is_active) === false) {
            $data['is_default'] = false;
        }

        $shippingPoint->update($data);

        return response()->json([
            'message' => 'Punkt wysyłki został zaktualizowany.',
            'data' => $shippingPoint->fresh(),
        ]);
    }

    public function destroy(Request $request, ShippingPoint $shippingPoint)
    {
        $user = $request->user();

        if ($user->hasRole('customer')) {
            abort_unless($shippingPoint->user_id === $user->id, 403);
        }

        $shippingPoint->update([
            'is_active' => false,
            'is_default' => false,
        ]);

        return response()->json([
            'message' => 'Punkt wysyłki został zablokowany.',
        ]);
    }
}
