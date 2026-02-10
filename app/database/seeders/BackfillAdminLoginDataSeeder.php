<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class BackfillAdminLoginDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          User::whereHas('roles', fn ($q) =>
                $q->whereIn('name', ['admin', 'super-admin'])
            )
            ->whereNull('last_login_at')
            ->update([
                'last_login_at' => \DB::raw('created_at'),
            ]);
    }
}
