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
        Schema::create('indas_analysis_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('indas_regions')->onDelete('cascade');
            $table->tinyInteger('month'); // 1-12
            $table->year('year'); // 2024, 2025, dst
            
            // Skor per kategori (0-100)
            $table->decimal('economic_score', 5, 2)->default(0);
            $table->decimal('tourism_score', 5, 2)->default(0);
            $table->decimal('social_score', 5, 2)->default(0);
            $table->decimal('total_score', 5, 2)->default(0);
            
            // Tren (persentase perubahan dari bulan sebelumnya)
            $table->decimal('economic_trend', 6, 2)->nullable(); // bisa negatif
            $table->decimal('tourism_trend', 6, 2)->nullable();
            $table->decimal('social_trend', 6, 2)->nullable();
            $table->decimal('total_trend', 6, 2)->nullable();
            
            $table->json('calculation_details')->nullable(); // Detail perhitungan untuk debugging
            $table->timestamps();
            
            // Unique constraint per region per bulan
            $table->unique(['region_id', 'month', 'year']);
            $table->index(['month', 'year']);
            $table->index('total_score'); // untuk ranking
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indas_analysis_results');
    }
};
