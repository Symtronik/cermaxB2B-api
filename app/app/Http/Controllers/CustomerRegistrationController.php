<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterCustomerRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Http\JsonResponse;

class CustomerRegistrationController extends Controller
{
    public function register(RegisterCustomerRequest $request): JsonResponse
    {
        // 1️⃣ Utwórz użytkownika
        $user = User::create([
            'name'     => $request->string('name'),
            'email'    => $request->string('email'),
            'password' => Hash::make($request->string('password')),

            // jeśli masz inne pola np. company_id, dodaj tu
        ]);

        // 2️⃣ Nadaj rolę customer
        $role = Role::firstOrCreate(['name' => 'customer']);
        $user->assignRole($role);

        // 3️⃣ Wygeneruj token Sanctum
        $token = $user->createToken('customer-app', ['*'])->plainTextToken;

        // 4️⃣ Zwróć odpowiedź JSON
        return response()->json([
            'message' => 'Konto zostało utworzone pomyślnie.',
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token' => $token,
        ], 201);
    }
}
