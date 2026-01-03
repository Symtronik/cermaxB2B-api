<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Requests\Customer\Auth\RegisterCustomerRequest;
use Spatie\Permission\Models\Role;
use Illuminate\Http\JsonResponse;

/**
 * @group Customer
 */
class AuthController extends Controller
{
    /**
     * Register a new customer account.
     *
     * Creates a new user with the "customer" role and returns an API token.
     *
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required A unique email address. Example: john@example.com
     * @bodyParam password string required The account password (minimum 8 characters). Example: test1234
     * @bodyParam password_confirmation string required Must match the password field. Example: test1234
     *
     * @response 201 {
     *   "message": "Konto zostało utworzone pomyślnie.",
     *   "user": {
     *     "id": 12,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "roles": ["customer"]
     *   },
     *   "token": "1|abc123xyz..."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["The email has already been taken."]
     *   }
     * }
     *
     * @return JsonResponse
     */
    public function register(RegisterCustomerRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->string('name'),
            'email'    => $request->string('email'),
            'password' => Hash::make($request->string('password')),
        ]);

        $role = Role::firstOrCreate(['name' => 'customer']);
        $user->assignRole($role);

        $token = $user->createToken('customer-app', ['*'])->plainTextToken;

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

    /**
     * Log in an existing customer.
     *
     * Validates user credentials and returns an access token if successful.
     *
     * @bodyParam email string required The registered email address. Example: john@example.com
     * @bodyParam password string required The account password. Example: test1234
     *
     * @response 200 {
     *   "message": "Zalogowano pomyślnie.",
     *   "user": {
     *     "id": 12,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "roles": ["customer"]
     *   },
     *   "token": "1|abc123xyz..."
     * }
     *
     * @response 401 {
     *   "message": "Nieprawidłowy e-mail lub hasło."
     * }
     *
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string','min:8'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Nieprawidłowy e-mail lub hasło.',
            ], 401);
        }

        $token = $user->createToken('api')->plainTextToken;
        $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : [];

        return response()->json([
            'message' => 'Zalogowano pomyślnie.',
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $roles,
            ],
            'token' => $token,
        ], 200);
    }

    /**
     * Log out the current authenticated customer.
     *
     * Deletes the currently active access token.
     *
     * @authenticated
     *
     * @response 200 {
     *   "message": "Wylogowano pomyślnie."
     * }
     *
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Wylogowano pomyślnie.',
        ]);
    }

    /**
     * Get the authenticated user's profile.
     *
     * Returns information about the currently authenticated customer.
     *
     * @authenticated
     *
     * @response 200 {
     *   "user": {
     *     "id": 12,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "roles": ["customer"]
     *   }
     * }
     *
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : [];

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $roles,
            ],
        ]);
    }
}
