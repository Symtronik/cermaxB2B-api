<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vats', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();            // np. "23%", "8%", "ZW"
            $table->decimal('rate', 5, 2)->default(0);   // 23.00, 8.00, 0.00
            $table->string('code')->nullable()->unique();// np. "VAT23", "ZW" (nullable)
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vats');
    }
};
