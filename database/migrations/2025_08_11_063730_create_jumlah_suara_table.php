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
        Schema::create('jumlah_suara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partai_id')->constrained('partai_politik')->onDelete('cascade');
            $table->year('tahun_pemilu');
            $table->bigInteger('jumlah_suara')->unsigned();
            $table->timestamps();

            $table->unique(['partai_id', 'tahun_pemilu']);
            $table->index(['tahun_pemilu', 'jumlah_suara']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jumlah_suara');
    }
};
