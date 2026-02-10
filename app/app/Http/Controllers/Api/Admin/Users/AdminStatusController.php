<?php

namespace App\Http\Controllers\Api\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminStatusController extends Controller
{
    public function update(Request $request, User $user): JsonResponse
    {
        // ✅ tylko super-admin może zmieniać adminów
        $actor = $request->user();
        if (! $actor || ! $actor->hasRole('super-admin')) {
            return response()->json(['message' => 'Brak uprawnień.'], 403);
        }

        // ✅ tylko userzy admin/super-admin
        if (! $user->hasAnyRole(['super-admin'])) {
            return response()->json(['message' => 'Użytkownik nie jest administratorem.'], 422);
        }

        // ✅ nie pozwól zablokować samego siebie
        if ((int) $actor->id === (int) $user->id) {
            return response()->json(['message' => 'Nie możesz dezaktywować własnego konta.'], 422);
        }

        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $isActive = (bool) $data['is_active'];

        // Źródło prawdy: blocked_at
        $user->blocked_at = $isActive ? null : Carbon::now();

        // jeśli masz kolumnę is_active w DB - ustaw też ją
        if (\Schema::hasColumn($user->getTable(), 'is_active')) {
            $user->is_active = $isActive;
        }

        $user->save();

        return response()->json([
            'data' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'roles'         => $user->getRoleNames()->values()->all(),
                'created_at'    => optional($user->created_at)->toISOString(),
                'last_login_at' => optional($user->last_login_at)->toISOString(),
                'is_active'     => $user->blocked_at === null,
                'blocked_at'    => optional($user->blocked_at)->toISOString(),
            ],
        ]);
    }
}
