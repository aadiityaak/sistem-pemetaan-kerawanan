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
    Schema::create('sub_categories', function (Blueprint $table) {
      $table->id();
      $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
      $table->string('name'); // Radikalisme, Korupsi, Inflasi, etc.
      $table->string('slug')->unique(); // radikalisme, korupsi, inflasi, etc.
      $table->string('description')->nullable();
      $table->string('icon', 10)->nullable(); // Icon name for UI (max 10 chars)
      $table->string('color')->nullable(); // Color code for UI (inherit from parent if null)
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
    Schema::dropIfExists('sub_categories');
  }
};
