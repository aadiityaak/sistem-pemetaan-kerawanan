<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_price_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint');
            $table->json('params')->nullable();
            $table->string('params_hash', 64)->nullable();
            $table->json('payload');
            $table->timestamp('fetched_at')->index();
            $table->date('date_key')->index();
            $table->timestamps();
            $table->unique(['endpoint', 'date_key', 'params_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_price_snapshots');
    }
};

