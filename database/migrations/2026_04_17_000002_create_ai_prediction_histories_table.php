<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_prediction_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('ai_provider_key', 32);
            $table->string('ai_provider_name', 128);

            $table->unsignedBigInteger('category_id');
            $table->string('category_name', 191);

            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->string('sub_category_name', 191)->nullable();

            $table->string('time_period', 16);
            $table->string('data_period', 64);

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->unsignedInteger('total_analyzed')->default(0);
            $table->string('data_fingerprint', 64);

            $table->longText('analysis');
            $table->json('statistics')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->unique(['user_id', 'ai_provider_key', 'category_id', 'sub_category_id', 'time_period', 'data_fingerprint'], 'ai_prediction_histories_cache_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_prediction_histories');
    }
};

