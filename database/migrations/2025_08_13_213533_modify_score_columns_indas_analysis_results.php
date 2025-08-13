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
        Schema::table('indas_analysis_results', function (Blueprint $table) {
            // Change score columns to support larger numbers (up to 99 billion)
            $table->decimal('economic_score', 15, 2)->default(0)->change();
            $table->decimal('tourism_score', 15, 2)->default(0)->change();
            $table->decimal('social_score', 15, 2)->default(0)->change();
            $table->decimal('total_score', 15, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indas_analysis_results', function (Blueprint $table) {
            $table->decimal('economic_score', 5, 2)->default(0)->change();
            $table->decimal('tourism_score', 5, 2)->default(0)->change();
            $table->decimal('social_score', 5, 2)->default(0)->change();
            $table->decimal('total_score', 5, 2)->default(0)->change();
        });
    }
};
