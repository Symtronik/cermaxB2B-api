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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attribute_id')
                ->nullable()
                ->constrained('attributes')
                ->nullOnDelete();

            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->string('ean')->nullable()->unique();
            $table->text('description')->nullable();

            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('diameter', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->string('color')->nullable();
            $table->decimal('weight', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
