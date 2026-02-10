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
        Schema::create('series', function (Blueprint $table) {
            $table->id();

        $table->foreignId('category_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('name');
        $table->string('slug')->unique();

        // ✅ seria ma JEDNO zdjęcie
        $table->string('image_path')->nullable();

        $table->string('seo_title')->nullable();
        $table->string('seo_description', 180)->nullable();

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
