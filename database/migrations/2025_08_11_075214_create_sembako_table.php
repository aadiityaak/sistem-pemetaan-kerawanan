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
        Schema::create('sembako', function (Blueprint $table) {
            $table->id();
            $table->string('nama_komoditas');
            $table->string('satuan'); // kg, liter, pcs, dll
            $table->decimal('harga', 10, 2);
            $table->foreignId('kabupaten_kota_id')->constrained('kabupaten_kota')->onDelete('cascade');
            $table->date('tanggal_pencatatan');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            $table->index(['kabupaten_kota_id', 'tanggal_pencatatan']);
            $table->index(['nama_komoditas', 'tanggal_pencatatan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sembako');
    }
};
