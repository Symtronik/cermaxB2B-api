<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
   public function run()
    {


        // potem role (które używają tych uprawnień)
        $this->call(RolesSeeder::class);

        $this->seedSuperAdmin();

        // a dopiero potem nasze tłumaczenia itd.
        $this->call(TranslationsSeeder::class);
    }

    private function seedSuperAdmin(): void
    {
        $email = 'm.kobialka@kobcode.com';

        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name'     => 'Michal',
                'email'    => $email,
                'password' => Hash::make('miki2025!'), // <-- Możesz zmienić na własne
            ]);
        }

        // przypisz rolę super-admin (tylko jeśli nie ma)
        if (! $user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }

        // informacyjnie – przy seedingach warto mieć taki log:
        $this->command->info("Super admin ready: {$email}");
    }
}
