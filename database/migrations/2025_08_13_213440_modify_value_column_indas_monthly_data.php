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
        Schema::table('indas_monthly_data', function (Blueprint $table) {
            // Change value column to support larger numbers (up to 99 billion)
            $table->decimal('value', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indas_monthly_data', function (Blueprint $table) {
            $table->decimal('value', 10, 2)->change();
        });
    }
};
