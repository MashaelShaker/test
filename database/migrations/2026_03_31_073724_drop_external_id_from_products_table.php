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
            // FIXED: Dropping the external_id column
            $table->dropColumn('external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // FIXED: Rollback logic to recreate the column if migration is reversed.
            $table->string('external_id')->nullable()->unique()->after('id');
        });
    }
};
