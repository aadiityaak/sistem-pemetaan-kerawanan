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
    Schema::create('categories', function (Blueprint $table) {
      $table->id();
      $table->string('name'); // Ideologi, Politik, Ekonomi, Sosial Budaya, Keamanan
      $table->string('slug')->unique(); // ideologi, politik, ekonomi, sosial-budaya, keamanan
      $table->string('description')->nullable();
      $table->string('icon')->nullable(); // Icon name for UI
      $table->string('color')->default('#6B7280'); // Color code for UI
      $table->boolean('is_active')->default(true);
      $table->integer('sort_order')->default(0);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('categories');
  }
};
