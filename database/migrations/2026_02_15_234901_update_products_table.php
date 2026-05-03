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
        Schema::table('products', function (Blueprint $table) {
        // Add external_id
            $table->string('external_id')->nullable()->unique()->after('id');
            // Change salla_product_id type
            $table->unsignedBigInteger('salla_product_id')->change();
            // Add unique constraint
            $table->unique('salla_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->dropUnique(['salla_product_id']);
            $table->dropColumn('external_id');

            $table->string('salla_product_id')->change();
        });
    }
};
