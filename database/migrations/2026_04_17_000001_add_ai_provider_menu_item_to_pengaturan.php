<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('menu_items')) {
            return;
        }

        $pengaturan = DB::table('menu_items')->whereNull('parent_id')->where('title', 'PENGATURAN')->first();
        if (!$pengaturan) {
            return;
        }

        $exists = DB::table('menu_items')
            ->where('parent_id', $pengaturan->id)
            ->where('path', '/settings/ai')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('menu_items')->insert([
            'title' => 'AI Provider',
            'icon' => 'Brain',
            'path' => '/settings/ai',
            'is_active' => 1,
            'sort_order' => 2,
            'parent_id' => $pengaturan->id,
            'admin_only' => 1,
            'permissions' => null,
            'description' => 'Pengaturan AI provider dan API key',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('menu_items')) {
            return;
        }

        DB::table('menu_items')->where('path', '/settings/ai')->delete();
    }
};

