<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('series_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedInteger('pack_qty')->nullable();
            $table->unsignedInteger('stock_qty')->nullable();

            $table->decimal('vat_rate', 8, 2)->nullable();
            $table->decimal('net_unit', 10, 2)->nullable();
            $table->decimal('net_pack', 10, 2)->nullable();
            $table->decimal('gross_unit', 10, 2)->nullable();
            $table->decimal('gross_pack', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropConstrainedForeignId('series_id');

            $table->dropColumn([
                'pack_qty',
                'stock_qty',
                'vat_rate',
                'net_unit',
                'net_pack',
                'gross_unit',
                'gross_pack',
            ]);
        });
    }
};
