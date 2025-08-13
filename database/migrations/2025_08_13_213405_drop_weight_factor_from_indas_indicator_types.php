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
        Schema::table('indas_indicator_types', function (Blueprint $table) {
            $table->dropColumn('weight_factor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indas_indicator_types', function (Blueprint $table) {
            $table->decimal('weight_factor', 5, 2)->default(1.0);
        });
    }
};
