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
        Schema::create('indas_monthly_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('indas_regions')->onDelete('cascade');
            $table->foreignId('indicator_type_id')->constrained('indas_indicator_types')->onDelete('cascade');
            $table->decimal('value', 10, 2); // Nilai data (jumlah toko, dll)
            $table->tinyInteger('month'); // 1-12
            $table->year('year'); // 2024, 2025, dst
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // User yang input/update
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->timestamps();
            
            // Unique constraint untuk mencegah duplicate data per bulan
            $table->unique(['region_id', 'indicator_type_id', 'month', 'year']);
            $table->index(['month', 'year']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indas_monthly_data');
    }
};
