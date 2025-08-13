<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to modify the column safely
        DB::statement('ALTER TABLE monitoring_data MODIFY COLUMN kecamatan_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Use raw SQL to modify the column back to NOT NULL
        DB::statement('ALTER TABLE monitoring_data MODIFY COLUMN kecamatan_id BIGINT UNSIGNED NOT NULL');
    }
};
