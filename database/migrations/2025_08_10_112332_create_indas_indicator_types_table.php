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
        Schema::create('indas_indicator_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Jumlah Toko, Tempat Wisata, dll
            $table->enum('category', ['ekonomi', 'pariwisata', 'sosial']); // Kategori utama
            $table->string('unit')->default('unit'); // unit, %, dll
            $table->decimal('weight_factor', 3, 2)->default(1.0); // Bobot dalam kalkulasi
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indas_indicator_types');
    }
};
