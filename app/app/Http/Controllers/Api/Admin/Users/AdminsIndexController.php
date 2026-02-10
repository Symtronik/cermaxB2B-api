<?php

namespace App\Http\Controllers\Api\Admin\Users;

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
     *       "roles": ["admin"],
     *       "created_at": "2025-12-01T10:00:00Z",
     *       "last_login_at": "2026-01-05T09:30:00Z",
     *       "is_active": true,
     *       "blocked_at": null
     *     }
     *   ],
     *   "current_page": 1,
     *   "per_page": 25,
     *   "total": 2
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 100);
        $perPage = max(1, min(100, $perPage));

        $builder = User::query()
            ->select([
                'id',
                'name',
                'email',
                'created_at',
                'last_login_at',
                'blocked_at',
            ])
            ->role(['admin', 'super-admin'])
            ->with('roles')
            ->when($q !== '', function ($sql) use ($q) {
                $sql->where(function ($s) use ($q) {
                    $s->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id');

        $paginator = $builder->paginate($perPage);

        $data = $paginator->getCollection()->map(function (User $u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'roles' => $u->getRoleNames()->values()->all(),

                'created_at' => $u->created_at?->toISOString(),
                'last_login_at' => $u->last_login_at?->toISOString(),
                'blocked_at' => $u->blocked_at?->toISOString(),


                'is_active' => $u->blocked_at === null,

            ];
        })->values()->all();

        return response()->json([
            'data' => $data,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]);
    }
}
