<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\RegisterAdminRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RegisterAdminController extends Controller
{
     /**
     * @group Admin
     * Admin Registration
     *
     * Creates a new admin user and assigns the specified roles.
     * Only accessible to users with the `super-admin` role.
     *
     * @authenticated
     * @bodyParam name string required The admin user's full name. Example: Anna Nowak
     * @bodyParam email string required A unique email address for the admin. Example: anna.admin@example.com
     * @bodyParam password string required The account password (minimum 8 characters). Example: test1234
     * @bodyParam password_confirmation string required Password confirmation. Example: test1234
     * @bodyParam roles array optional A list of roles to assign to the user. Example: ["admin"]
     *
     * @response 201 {
     *   "id": 15,
     *   "email": "anna.admin@example.com",
     *   "roles": ["admin"]
     * }
     */
    public function createAdmin(RegisterAdminRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Zbierz role z requestu i ZAWSZE dołóż 'admin'
        $roles = $data['roles'];
       // $roles[] = 'admin';
        //$roles = array_values(array_unique($roles));

        $user = DB::transaction(function () use ($data, $roles) {
            // 1) Utwórz użytkownika
            $u = \App\Models\User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // 2) Upewnij się, że każda rola istnieje (tworzy, jeśli brak)
            foreach ($roles as $r) {
                Role::findOrCreate($r,'web'); // (opcjonalnie) z guardem: , 'web'
            }

            // 3) Przypisz pełny zestaw ról (zawiera 'admin')
            $u->syncRoles($roles);

            return $u;
        });

        return response()->json([
            'id'    => $user->id,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
        ], 201);
    }


    /**
     * @group Admin
     * Update User Roles
     *
     * Updates (synchronizes) the roles assigned to a specific user.
     * This operation replaces all existing roles with the provided list.
     * Only accessible to users with the `super-admin` role.
     * The system prevents removing the last remaining `super-admin`.
     *
     * @authenticated
     * @urlParam userId integer required The ID of the user whose roles are being updated. Example: 7
     * @bodyParam roles array required A list of roles to assign to the user. Example: ["admin", "manager"]
     *
     * @response 200 {
     *   "id": 7,
     *   "roles": ["admin", "manager"]
     * }
     *
     * @response 422 {
     *   "message": "Cannot remove the super-admin role. This is the last super-admin."
     * }
     */
    public function syncRoles(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'roles'   => 'required|array',
            'roles.*' => 'string',
        ]);

        $user = User::findOrFail($userId);

        // Jeżeli usuwasz super-admina, chroń ostatniego
        $removingSuper = ! in_array('super-admin', $request->roles ?? []) && $user->hasRole('super-admin');
        if ($removingSuper && $this->countSuperAdmins() <= 1) {
            return response()->json(['message' => 'Nie można usunąć roli super-admin. To ostatni super-admin.'], 422);
            // Alternatywnie: abort(422, '...');
        }

        foreach ($request->roles as $r) {
            Role::findOrCreate($r);
        }
        $user->syncRoles($request->roles);

        return response()->json([
            'id'    => $user->id,
            'roles' => $user->getRoleNames(),
        ]);
    }



    private function countSuperAdmins(): int
    {
        return User::role('super-admin')->count();
    }
}
