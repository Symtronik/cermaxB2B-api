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
    Schema::create('company_profiles', function (Illuminate\Database\Schema\Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();

        $table->string('company_name');
        $table->string('vat_id')->nullable();       // NIP/VAT
        $table->string('regon')->nullable();        // opcjonalnie
        $table->string('address_line1');
        $table->string('address_line2')->nullable();
        $table->string('postal_code');
        $table->string('city');
        $table->string('country', 2)->default('PL'); // ISO 3166-1 alpha-2
        $table->string('phone')->nullable();
        $table->string('description');

        $table->timestamps();

        $table->unique('user_id'); // 1:1 z użytkownikiem
        $table->index(['company_name', 'vat_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
