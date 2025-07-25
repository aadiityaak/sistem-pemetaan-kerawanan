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
        Schema::create('kabupaten_kota', function (Blueprint $table) {
            $table->char('kode_provinsi', 2);
            $table->char('kode', 2);
            $table->string('nama');
            $table->timestamps();

            $table->primary(['kode_provinsi', 'kode']);
            $table->foreign('kode_provinsi')->references('kode')->on('provinsi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kabupaten_kota');
    }
}; 