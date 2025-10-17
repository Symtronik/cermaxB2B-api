<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

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

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // Role
        $customer = Role::firstOrCreate(['name' => 'customer']);
        $admin    = Role::firstOrCreate(['name' => 'admin']);
        $super    = Role::firstOrCreate(['name' => 'super-admin']);

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

        // Super-admin – wszystko
        $super->syncPermissions(Permission::all());

    }
}
