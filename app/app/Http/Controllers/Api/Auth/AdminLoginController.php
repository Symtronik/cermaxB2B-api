<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminLoginController extends Controller
{
    /**
     * @group Admin
     * Admin Login
     *
     * Loguje użytkownika do panelu admina (tylko role `admin` lub `super-admin`).
     *
     * Zwraca token Sanctum oraz podstawowe dane użytkownika i jego role.
     *
     * @bodyParam email string required Adres e-mail użytkownika. Example: superadmin@example.com
     * @bodyParam password string required Hasło użytkownika. Example: password123
     * @bodyParam device_name string optional Nazwa urządzenia / klienta (do nazwania tokenu). Example: admin-panel
     *
     * @response 200 {
     *   "token": "1|AlaMaKota...",
     *   "user": {
     *     "id": 1,
     *     "name": "Main Super Admin",
     *     "email": "superadmin@example.com",
     *     "roles": ["super-admin"]
     *   }
     * }
     *
     * @response 422 {
     *   "message": "Nieprawidłowe dane logowania."
     * }
     *
     * @response 403 {
     *   "message": "Brak uprawnień do panelu administratora."
     * }
     */

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Nieprawidłowe dane logowania.',
            ], 422);
        }

        if (! $user->hasAnyRole(['admin', 'super-admin'])) {
            return response()->json([
                'message' => 'Brak uprawnień do panelu administratora.',
            ], 403);
        }

        // ✅ jeśli wdrażasz blokowanie dostępów
        if (!empty($user->blocked_at)) {
            return response()->json([
                'message' => 'Konto jest zablokowane.',
            ], 403);
        }

        // ✅ KLUCZ: aktualizuj last_login_at (u Ciebie event się nie odpala)
        $user->forceFill(['last_login_at' => now()])->save();

        $tokenName = $data['device_name'] ?? 'admin-panel';

        // opcjonalnie: wyczyść stare tokeny dla panelu admina (1 aktywny token)
        // $user->tokens()->where('name', $tokenName)->delete();

        $token = $user->createToken($tokenName);

        return response()->json([
            'token' => $token->plainTextToken,
            'user'  => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'roles'       => $user->getRoleNames()->values()->all(),
                'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
            ],
        ]);
    }
}
