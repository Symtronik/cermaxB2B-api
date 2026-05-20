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
       Schema::create('shipping_points', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name'); // np. Sklep Kraków, Magazyn, Punkt Warszawa
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();

            $table->string('street');
            $table->string('building_number')->nullable();
            $table->string('apartment_number')->nullable();
            $table->string('postal_code');
            $table->string('city');
            $table->string('country')->default('Polska');

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_points');
    }
};
