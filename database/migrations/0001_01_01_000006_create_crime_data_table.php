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
        Schema::create('crime_data', function (Blueprint $table) {
            $table->id();
            $table->char('kode_provinsi', 2);
            $table->char('kode_kabupaten_kota', 2);
            $table->char('kode_kecamatan', 3);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('jenis_kriminal');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->foreign('kode_provinsi')->references('kode')->on('provinsi')->onDelete('restrict');
            $table->foreign(['kode_provinsi', 'kode_kabupaten_kota'])
                ->references(['kode_provinsi', 'kode'])
                ->on('kabupaten_kota')->onDelete('restrict');
            $table->foreign(['kode_provinsi', 'kode_kabupaten_kota', 'kode_kecamatan'], 'fk_crime_kecamatan')
                ->references(['kode_provinsi', 'kode_kabupaten_kota', 'kode'])
                ->on('kecamatan')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crime_data');
    }
}; 