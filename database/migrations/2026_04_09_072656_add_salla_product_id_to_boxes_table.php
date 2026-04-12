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
    Schema::table('boxes', function (Blueprint $table) {
        // Explicitly naming it salla_product_id
        $table->string('salla_product_id')->nullable()->after('store_id');
    });
}

public function down(): void
{
    Schema::table('boxes', function (Blueprint $table) {
        $table->dropColumn('salla_product_id');
    });
}
};
