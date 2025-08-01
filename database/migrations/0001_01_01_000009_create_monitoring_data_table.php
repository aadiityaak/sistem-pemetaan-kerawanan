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
    Schema::create('monitoring_data', function (Blueprint $table) {
      $table->id();
      $table->foreignId('provinsi_id')->constrained('provinsi')->onDelete('restrict');
      $table->foreignId('kabupaten_kota_id')->constrained('kabupaten_kota')->onDelete('restrict');
      $table->foreignId('kecamatan_id')->constrained('kecamatan')->onDelete('restrict');
      $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
      $table->foreignId('sub_category_id')->constrained('sub_categories')->onDelete('restrict');
      $table->decimal('latitude', 10, 7);
      $table->decimal('longitude', 10, 7);
      $table->string('title'); // Judul kejadian/data
      $table->text('description')->nullable(); // Deskripsi detail
      $table->json('additional_data')->nullable(); // Data tambahan (fleksibel per kategori)
      $table->enum('severity_level', ['low', 'medium', 'high', 'critical'])->default('medium');
      $table->enum('status', ['active', 'resolved', 'monitoring', 'archived'])->default('active');
      $table->timestamp('incident_date')->nullable(); // Tanggal kejadian
      $table->string('source')->nullable(); // Sumber data
      $table->timestamps();

      // Index untuk performa
      $table->index(['category_id', 'sub_category_id']);
      $table->index(['provinsi_id', 'kabupaten_kota_id', 'kecamatan_id']);
      $table->index(['incident_date', 'status']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('monitoring_data');
  }
};
