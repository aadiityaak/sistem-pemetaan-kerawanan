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
        Schema::create('partai_politik', function (Blueprint $table) {
            $table->id();
            $table->string('nama_partai', 100);
            $table->string('singkatan', 50);
            $table->integer('nomor_urut');
            $table->string('logo_url', 255)->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            $table->unique('nomor_urut');
            $table->index('status_aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partai_politik');
    }
};
