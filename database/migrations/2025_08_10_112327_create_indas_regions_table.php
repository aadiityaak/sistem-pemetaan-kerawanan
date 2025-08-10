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
        Schema::create('indas_regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kabupaten_kota_id')->constrained('kabupaten_kota')->onDelete('cascade');
            $table->string('nama');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('kabupaten_kota_id');
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indas_regions');
    }
};
