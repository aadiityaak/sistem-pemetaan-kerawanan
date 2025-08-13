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
        Schema::table('monitoring_data', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['kecamatan_id']);
            
            // Make the column nullable
            $table->unsignedBigInteger('kecamatan_id')->nullable()->change();
            
            // Re-add foreign key constraint
            $table->foreign('kecamatan_id')->references('id')->on('kecamatan')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitoring_data', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['kecamatan_id']);
            
            // Make the column required again
            $table->unsignedBigInteger('kecamatan_id')->nullable(false)->change();
            
            // Re-add foreign key constraint
            $table->foreign('kecamatan_id')->references('id')->on('kecamatan')->onDelete('restrict');
        });
    }
};
