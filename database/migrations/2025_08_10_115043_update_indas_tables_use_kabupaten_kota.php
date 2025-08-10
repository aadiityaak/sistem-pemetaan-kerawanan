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
        // Drop existing tables that used region-based structure
        Schema::dropIfExists('indas_analysis_results');
        Schema::dropIfExists('indas_monthly_data');
        Schema::dropIfExists('indas_regions');
        
        // Recreate indas_monthly_data with direct kabupaten_kota_id reference
        Schema::create('indas_monthly_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kabupaten_kota_id')->constrained('kabupaten_kota')->onDelete('cascade');
            $table->foreignId('indicator_type_id')->constrained('indas_indicator_types')->onDelete('cascade');
            $table->decimal('value', 10, 2);
            $table->tinyInteger('month'); // 1-12
            $table->year('year'); // 2024, 2025, dst
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint untuk mencegah duplikat data per periode
            $table->unique(['kabupaten_kota_id', 'indicator_type_id', 'month', 'year'], 'indas_monthly_unique');
            $table->index(['month', 'year']);
        });
        
        // Recreate indas_analysis_results with direct kabupaten_kota_id reference
        Schema::create('indas_analysis_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kabupaten_kota_id')->constrained('kabupaten_kota')->onDelete('cascade');
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
            
            // Unique constraint per kabupaten_kota per bulan
            $table->unique(['kabupaten_kota_id', 'month', 'year'], 'indas_analysis_unique');
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
        Schema::dropIfExists('indas_monthly_data');
        
        // Recreate original tables structure if needed
        // (This would be the reverse of the changes)
    }
};
