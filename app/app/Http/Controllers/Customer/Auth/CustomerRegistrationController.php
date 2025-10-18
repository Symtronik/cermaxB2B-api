<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Customer\Auth\RegisterCustomerRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Http\JsonResponse;

class CustomerRegistrationController extends Controller
{
     /**
     * @group Customer
     * User Registration
     *
     * Creates a new customer account and returns an API token.
     *
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required A unique email address. Example: john@example.com
     * @bodyParam password string required The account password (minimum 8 characters). Example: test1234
     * @bodyParam password_confirmation string required Password confirmation. Example: test1234
     *
     * @response 201 {
     *   "message": "The account has been successfully created.",
     *   "user": {
     *       "id": 12,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "roles": ["customer"]
     *   },
     *   "token": "1|abc123xyz..."
     * }
     */

    public function register(RegisterCustomerRequest $request): JsonResponse
        {

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
