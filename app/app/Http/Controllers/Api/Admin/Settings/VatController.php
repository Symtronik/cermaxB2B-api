<?php

namespace App\Http\Controllers\Api\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Admin\Settings\StoreVatRequest;
use App\Http\Requests\Admin\Settings\UpdateVatRequest;
use App\Http\Resources\VatResource;
use App\Models\Vat;

class VatController extends Controller
{
     /**
     * GET /vats
     * Lista aktywnych stawek (do selecta w produkcie)
     */
    public function index(Request $request)
    {
        $items = Vat::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('rate')
            ->get();

        return response()->json([
            'data' => VatResource::collection($items),
        ]);
    }

    /**
     * GET /vats/manage
     * Lista dla panelu (wszystkie, paginacja, wyszukiwanie)
     */
    public function manage(Request $request)
    {
        $q = trim((string) $request->query('search', ''));
        $perPage = (int) ($request->query('per_page', 25));
        $perPage = max(1, min(200, $perPage));

        $query = Vat::query();

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $p = $query
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->orderBy('rate')
            ->paginate($perPage);

        // Format jak u Ciebie w listingu: { data: { current_page, data, ... } }
        return response()->json([
            'data' => [
                'current_page' => $p->currentPage(),
                'data' => VatResource::collection($p->items()),
                'last_page' => $p->lastPage(),
                'per_page' => $p->perPage(),
                'total' => $p->total(),
            ],
        ]);
    }

    /**
     * POST /vats
     */
    public function store(StoreVatRequest $request)
    {
        $data = $request->validated();

        $vat = DB::transaction(function () use ($data) {
            $isDefault = (bool)($data['is_default'] ?? false);

            if ($isDefault) {
                Vat::query()->update(['is_default' => false]);
            }

            return Vat::create([
                'name' => $data['name'],
                'rate' => $data['rate'],
                'code' => $data['code'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'is_default' => $isDefault,
            ]);
        });

        return response()->json([
            'data' => new VatResource($vat),
        ], 201);
    }

    /**
     * PUT /vats/{vat}
     */
    public function update(UpdateVatRequest $request, Vat $vat)
    {
        $data = $request->validated();

        $vat = DB::transaction(function () use ($data, $vat) {
            if (array_key_exists('is_default', $data) && (bool)$data['is_default'] === true) {
                Vat::query()->update(['is_default' => false]);
                $vat->is_default = true;
            } elseif (array_key_exists('is_default', $data) && (bool)$data['is_default'] === false) {
                // pozwalamy zdjąć "default" – ale upewniamy się że zawsze jakiś będzie (poniżej)
                $vat->is_default = false;
            }

            if (array_key_exists('name', $data)) $vat->name = $data['name'];
            if (array_key_exists('rate', $data)) $vat->rate = $data['rate'];
            if (array_key_exists('code', $data)) $vat->code = $data['code'];
            if (array_key_exists('is_active', $data)) $vat->is_active = (bool) $data['is_active'];

            $vat->save();

            // gwarancja: zawsze jest domyślny VAT
            if (!Vat::query()->where('is_default', true)->exists()) {
                Vat::query()->orderBy('id')->limit(1)->update(['is_default' => true]);
            }

            return $vat;
        });

        return response()->json([
            'data' => new VatResource($vat),
        ]);
    }

    /**
     * DELETE /vats/{vat}
     * (bez soft delete – jak wolisz, możemy zrobić is_active=false zamiast kasowania)
     */
    public function destroy(Vat $vat)
    {
        DB::transaction(function () use ($vat) {
            $wasDefault = $vat->is_default;
            $vat->delete();

            if ($wasDefault && !Vat::query()->where('is_default', true)->exists()) {
                Vat::query()->orderBy('id')->limit(1)->update(['is_default' => true]);
            }
        });

        return response()->json(['ok' => true]);
    }

    /**
     * PATCH /vats/{vat}/default
     */
    public function setDefault(Vat $vat)
    {
        DB::transaction(function () use ($vat) {
            Vat::query()->update(['is_default' => false]);
            $vat->update(['is_default' => true]);
        });

        return response()->json([
            'data' => new VatResource($vat->fresh()),
        ]);
    }

    /**
     * PATCH /vats/{vat}/toggle
     */
    public function toggle(Vat $vat)
    {
        $vat->update(['is_active' => !$vat->is_active]);

        return response()->json([
            'data' => new VatResource($vat->fresh()),
        ]);
    }

    public function show(Vat $vat)
    {
        return response()->json([
            'data' => $vat
        ]);
    }
}
