<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_company_id_to_users_and_orders.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'company_id')) {
                $table->foreignId('company_id')->nullable()
                    ->constrained('company_profiles')->nullOnDelete()->after('id');
            }
        });

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'company_id')) {
                    $table->foreignId('company_id')->nullable()
                        ->constrained('company_profiles')->nullOnDelete()->after('user_id');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'company_id')) {
                    $table->dropConstrainedForeignId('company_id');
                }
            });
        }
    }
};
