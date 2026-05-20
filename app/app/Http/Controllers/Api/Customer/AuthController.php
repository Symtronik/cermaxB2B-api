<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Auth\RegisterCustomerRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * @group Customer
 */
class AuthController extends Controller
{
    public function register(RegisterCustomerRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => mb_strtolower($data['email']),
                'password' => Hash::make($data['password']),
            ]);

            $role = Role::firstOrCreate([
                'name' => 'customer',
                'guard_name' => 'web',
            ]);

            $user->assignRole($role);

            $user->companyProfile()->create([
                'company_name'  => $data['company_name'],
                'vat_id'        => $data['vat_id'] ?? null,
                'regon'         => $data['regon'] ?? null,
                'address_line1' => $data['address_line1'],
                'address_line2' => $data['address_line2'] ?? null,
                'postal_code'   => $data['postal_code'],
                'city'          => $data['city'],
                'country'       => $data['country'] ?? 'PL',
                'phone'         => $data['phone'] ?? null,
                'description'   => $data['description'] ?? null,
            ]);

            return $user;
        });

        $token = $user->createToken('customer-app', ['*'])->plainTextToken;

        return response()->json([
            'message' => 'Konto zostało utworzone pomyślnie. Po weryfikacji otrzymasz dostęp do platformy B2B Cermax.',
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::where('email', mb_strtolower($validated['email']))->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Nieprawidłowy e-mail lub hasło.',
            ], 401);
        }

        $token = $user->createToken('customer-app', ['*'])->plainTextToken;
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

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Wylogowano pomyślnie.',
        ]);
    }

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

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Podaj aktualne hasło.',
            'password.required' => 'Podaj nowe hasło.',
            'password.min' => 'Nowe hasło musi mieć minimum 8 znaków.',
            'password.confirmed' => 'Powtórzone hasło nie jest takie samo.',
        ]);

        $user = $request->user();

        if (! $user || ! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Aktualne hasło jest nieprawidłowe.'],
            ]);
        }

        if (Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Nowe hasło musi być inne niż aktualne.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        $user->tokens()
            ->where('id', '!=', optional($request->user()->currentAccessToken())->id)
            ->delete();

        return response()->json([
            'message' => 'Hasło zostało zmienione.',
        ]);
    }
}
