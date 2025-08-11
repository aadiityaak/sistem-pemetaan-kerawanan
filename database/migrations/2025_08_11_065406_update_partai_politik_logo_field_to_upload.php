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
        Schema::table('partai_politik', function (Blueprint $table) {
            $table->renameColumn('logo_url', 'logo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partai_politik', function (Blueprint $table) {
            $table->renameColumn('logo_path', 'logo_url');
        });
    }
};
