<?php

namespace App\Http\Controllers\Api\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminShowController extends Controller
{
    public function show(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();

        if (! $actor || ! $actor->hasRole('super-admin')) {
            return response()->json([
                'message' => 'Brak uprawnień.',
            ], 403);
        }

        if (! $user->hasAnyRole(['admin', 'super-admin'])) {
            return response()->json([
                'message' => 'Użytkownik nie jest administratorem.',
            ], 404);
        }

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
