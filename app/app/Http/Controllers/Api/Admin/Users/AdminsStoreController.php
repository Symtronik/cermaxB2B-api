<?php

namespace App\Http\Controllers\Api\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AdminsStoreController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $actor = $request->user();

        if (! $actor || ! $actor->hasRole('super-admin')) {
            return response()->json(['message' => 'Brak uprawnień.'], 403);
        }

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'  => ['nullable', 'in:admin,super-admin'],
        ]);

        $role = $data['role'] ?? 'admin';

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make(Str::random(64));
        $user->blocked_at = null;
        $user->must_reset_password = true;
        $user->save();

        $user->syncRoles([$role]);

        $status = Password::sendResetLink([
            'email' => $user->email,
        ]);

        $responseData = [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'roles'         => $user->getRoleNames()->values()->all(),
            'created_at'    => optional($user->created_at)->toISOString(),
            'last_login_at' => optional($user->last_login_at)->toISOString(),
            'is_active'     => $user->blocked_at === null,
            'blocked_at'    => optional($user->blocked_at)->toISOString(),
        ];

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Utworzono administratora, ale nie udało się wysłać maila resetu hasła.',
                'data' => $responseData,
                'reset_link_sent' => false,
            ], 201);
        }

        return response()->json([
            'data' => $responseData,
            'reset_link_sent' => true,
        ], 201);
    }
}
