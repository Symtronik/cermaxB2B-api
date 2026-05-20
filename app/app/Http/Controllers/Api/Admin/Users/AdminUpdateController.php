<?php

namespace App\Http\Controllers\Api\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUpdateController extends Controller
{
    public function update(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();

        if (! $actor || ! $actor->hasRole('super-admin')) {
            return response()->json(['message' => 'Brak uprawnień.'], 403);
        }

        if (! $user->hasAnyRole(['admin', 'super-admin'])) {
            return response()->json(['message' => 'Użytkownik nie jest administratorem.'], 422);
        }

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role'  => ['required', 'in:admin,super-admin'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        $user->syncRoles([$data['role']]);

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
