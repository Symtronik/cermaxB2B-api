<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Users\StoreCompanyUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * @group Customer
 */
class CompanyUserController extends Controller
{
    /**
     * List company users
     *
     * Returns a paginated list of users that belong to the same company as the requester.
     *
     * @authenticated
     * @queryParam q string Filter by name or email. Example: anna
     * @queryParam per_page integer Results per page (1–100). Default: 25
     *
     * @response 200 {
     *   "data": [
     *     {"id": 5, "name":"Anna", "email":"anna@acme.com", "roles":["buyer"]}
     *   ],
     *   "current_page": 1, "per_page": 25, "total": 1
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;
        $q         = trim((string)$request->query('q', ''));
        $perPage   = max(1, min((int)$request->query('per_page', 25), 100));

        $paginator = User::query()
            ->where('company_id', $companyId)
            ->when($q !== '', fn($sql) => $sql->where(function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            }))
            ->with('roles')
            ->orderByDesc('id')
            ->paginate($perPage);

        $paginator->getCollection()->transform(function (User $u) use ($request) {
            // Jeśli masz Spatie teams, możesz użyć getRoleNames($request->user()->company)
            return [
                'id'    => $u->id,
                'name'  => $u->name,
                'email' => $u->email,
                'roles' => method_exists($u, 'getRoleNames') ? $u->getRoleNames() : [],
            ];
        });

        return response()->json($paginator);
    }

    /**
     * Create a company sub-user
     *
     * Creates a new user in the same company as the requester.
     * Default role: `buyer` (if none provided).
     * Only users with appropriate privileges (e.g., `customer-admin`) should access this endpoint.
     *
     * @authenticated
     * @bodyParam name string required Full name. Example: Anna Kowalska
     * @bodyParam email string required Unique email. Example: anna@acme.com
     * @bodyParam password string required Min 8 chars. Example: Test1234!
     * @bodyParam password_confirmation string required Example: Test1234!
     * @bodyParam roles array Roles to assign. Default: ["buyer"]. Example: ["buyer"]
     *
     * @response 201 {"id": 21, "email":"anna@acme.com", "roles":["buyer"]}
     */
    public function store(StoreCompanyUserRequest $request): JsonResponse
    {
        $data    = $request->validated();
        $company = $request->user()->company;
        $roles   = array_values(array_unique($data['roles'] ?? ['buyer']));

        $user = DB::transaction(function () use ($data, $company, $roles) {
            // 1) Create the user in the same company
            $u = User::create([
                'name'       => $data['name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'company_id' => $company->id,
            ]);

            // 2) Ensure roles exist (guard: web)
            foreach ($roles as $r) {
                Role::findOrCreate($r, config('auth.defaults.guard') ?? 'web');
            }

            // 3) Assign roles
            // Jeśli używasz Spatie teams:
            // foreach ($roles as $r) { $u->assignRole($r, $company); }
            // Bez teams (globalnie):
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
     * Remove a company sub-user
     *
     * Deletes a user that belongs to the same company as the requester.
     * (Prevents deleting yourself.)
     *
     * @authenticated
     * @urlParam userId integer required The ID of the sub-user to delete. Example: 21
     * @response 204 {}
     */
    public function destroy(Request $request, int $userId): JsonResponse
    {
        $current = $request->user();
        if ($current->id === $userId) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $user = User::where('company_id', $current->company_id)->findOrFail($userId);
        $user->delete();

        return response()->json([], 204);
    }
}
