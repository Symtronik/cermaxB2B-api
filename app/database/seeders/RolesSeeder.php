<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Wyczyść cache ról i uprawnień
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Ustal guard (spójny z config/permission.php i config/auth.php)
        $guard = config('permission.defaults.guard') ?? config('auth.defaults.guard', 'web');

        $perms = [
            // zamówienia
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.cancel',

            // produkty
            'products.view',
            'products.create',
            'products.update',
            'products.delete',

            // klienci/użytkownicy
            'users.view',
            'users.manage', // tworzenie/edycja/dezaktywacja

            // billing/faktury
            'billing.view',
            'billing.manage',

            // konfiguracja
            'settings.view',
            'settings.manage',
        ];

        // Tworzenie uprawnień z właściwym guardem
        foreach ($perms as $p) {
            Permission::firstOrCreate(
                [
                    'name'       => $p,
                    'guard_name' => $guard,
                ]
            );
        }

        // Role z tym samym guardem
        $customer = Role::firstOrCreate([
            'name'       => 'customer',
            'guard_name' => $guard,
        ]);

        $admin = Role::firstOrCreate([
            'name'       => 'admin',
            'guard_name' => $guard,
        ]);

        $super = Role::firstOrCreate([
            'name'       => 'super-admin',
            'guard_name' => $guard,
        ]);

        // Klient B2B – widzi produkty, może składać zamówienia
        $customer->syncPermissions([
            'products.view',
            'orders.view',
            'orders.create',
            'orders.cancel',
            'billing.view',
        ]);

        // Admin (bazowo minimalne, resztę będziesz dokładać per-user)
        $admin->syncPermissions([
            'products.view',
            'orders.view',
            'users.view',
            'settings.view',
        ]);

        // Super-admin – wszystkie permissiony w tym guardzie
        $super->syncPermissions(
            Permission::where('guard_name', $guard)->get()
        );
    }
}
