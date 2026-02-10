<?php

namespace App\Http\Controllers\Api\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
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

        // ✅ tylko super-admin może tworzyć adminów
        if (! $actor || ! $actor->hasRole('super-admin')) {
            return response()->json(['message' => 'Brak uprawnień.'], 403);
        }

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'  => ['nullable', 'in:admin,super-admin'], // domyślnie admin
        ]);

        $role = $data['role'] ?? 'admin';

        // ✅ tworzymy usera "bez hasła"
        // (technicznie w DB musi coś być, więc ustawiamy losowy hash)
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make(Str::random(64)); // losowe, nieznane nikomu

        // status startowy
        if (\Schema::hasColumn($user->getTable(), 'blocked_at')) {
            $user->blocked_at = null;
        }
        if (\Schema::hasColumn($user->getTable(), 'is_active')) {
            $user->is_active = true;
        }
        if (\Schema::hasColumn($user->getTable(), 'must_reset_password')) {
            $user->must_reset_password = true;
        }

        $user->save();

        // role
        $user->syncRoles([$role]);

        // ✅ wysyłka linku resetu hasła
        // Uwaga: Password::sendResetLink używa brokera "users"
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            // user utworzony, ale mail nie poszedł
            return response()->json([
                'message' => 'Utworzono administratora, ale nie udało się wysłać maila resetu hasła.',
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
            ], 201);
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
            'reset_link_sent' => true,
        ], 201);
    }
}
