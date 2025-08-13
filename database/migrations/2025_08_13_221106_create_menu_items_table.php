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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Menu title
            $table->string('icon')->nullable(); // Icon name
            $table->string('path')->nullable(); // URL path for redirect
            $table->boolean('is_active')->default(true); // On/Off toggle
            $table->integer('sort_order')->default(0); // Menu order
            $table->unsignedBigInteger('parent_id')->nullable(); // For sub-menus
            $table->boolean('admin_only')->default(false); // Admin only access
            $table->json('permissions')->nullable(); // Additional permissions
            $table->text('description')->nullable(); // Menu description
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('menu_items')->onDelete('cascade');
            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
