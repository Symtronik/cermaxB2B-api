<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vat;

class VatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Vat::query()->updateOrCreate(
            ['name' => '23%'],
            ['rate' => 23.00, 'code' => 'VAT23', 'is_active' => true, 'is_default' => true]
        );

        Vat::query()->updateOrCreate(
            ['name' => '8%'],
            ['rate' => 8.00, 'code' => 'VAT8', 'is_active' => true, 'is_default' => false]
        );

        Vat::query()->updateOrCreate(
            ['name' => '0%'],
            ['rate' => 0.00, 'code' => 'VAT0', 'is_active' => true, 'is_default' => false]
        );
    }
}
