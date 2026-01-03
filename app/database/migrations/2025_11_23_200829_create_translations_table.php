<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('translations', function (Blueprint $table) {
        $table->id();
        $table->string('namespace')->default('default'); // np. dashboard, auth, cart itd.
        $table->string('key'); // np. home, orders, filter_label
        $table->string('lang'); // pl, en, de...
        $table->text('value'); // "Zamówienia", "Orders"
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
