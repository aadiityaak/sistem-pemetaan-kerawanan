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
            if (!Schema::hasColumn('partai_politik', 'foto_ketua')) {
                $table->string('foto_ketua', 255)->nullable()->after('logo_path');
            }
            if (!Schema::hasColumn('partai_politik', 'nama_ketua')) {
                $table->string('nama_ketua', 100)->nullable()->after('foto_ketua');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partai_politik', function (Blueprint $table) {
            $table->dropColumn(['foto_ketua', 'nama_ketua']);
        });
    }
};
