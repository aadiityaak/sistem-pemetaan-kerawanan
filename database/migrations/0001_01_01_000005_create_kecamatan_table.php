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
        Schema::create('kecamatan', function (Blueprint $table) {
            $table->char('kode_provinsi', 2);
            $table->char('kode_kabupaten_kota', 2);
            $table->char('kode', 3);
            $table->string('nama');
            $table->timestamps();

            $table->primary(['kode_provinsi', 'kode_kabupaten_kota', 'kode']);
            $table->foreign(['kode_provinsi', 'kode_kabupaten_kota'])
                ->references(['kode_provinsi', 'kode'])
                ->on('kabupaten_kota')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kecamatan');
    }
}; 