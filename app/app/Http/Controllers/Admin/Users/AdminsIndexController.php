<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminsIndexController extends Controller
{
    /**
     * @group Admin
     * List Admin and Super Admin Users
     *
     * Retrieves a paginated list of users who have either the `admin` or `super-admin` role.
     * Only accessible by users with the `super-admin` role.
     *
     * @authenticated
     * @queryParam q string Filter results by name or email. Example: sylwia
     * @queryParam per_page integer The number of results per page (1–100). Default: 25. Example: 10
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Sylwia",
     *       "email": "sylwia@example.com",
     *       "roles": ["admin"]
     *     },
     *     {
     *       "id": 2,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "roles": ["super-admin","admin"]
     *     }
     *   ],
     *   "current_page": 1,
     *   "per_page": 25,
     *   "total": 2
     * }
     */

    public function index(Request $request): JsonResponse
    {
        $q        = trim((string)$request->query('q', ''));
        $perPage  = (int) $request->query('per_page', 25);
        $perPage  = max(1, min($perPage, 100));

        // Pobieramy tylko userów mających którąkolwiek z ról: admin/super-admin
        $builder = User::query()
            ->role(['admin', 'super-admin'])   // wymaga Spatie HasRoles na modelu
            ->with('roles')
            ->when($q !== '', function ($sql) use ($q) {
                $sql->where(function ($s) use ($q) {
                    $s->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id');

        $paginator = $builder->paginate($perPage);

        // Sformatuj odpowiedź: dodaj tablicę nazw ról
        $paginator->getCollection()->transform(function (User $u) {
            return [
                'id'    => $u->id,
                'name'  => $u->name,
                'email' => $u->email,
                'roles' => $u->getRoleNames()->values(), // ["admin","super-admin",...]
            ];
        });

        return response()->json($paginator);
    }
}
