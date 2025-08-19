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
        // Update existing roles to new system:
        // 'admin' -> 'super_admin' (current admin becomes super admin)
        // 'user' -> 'admin' (current user becomes admin with regional access)
        
        DB::table('users')
            ->where('role', 'admin')
            ->update(['role' => 'super_admin']);
            
        DB::table('users')
            ->where('role', 'user')
            ->update(['role' => 'admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to old system
        DB::table('users')
            ->where('role', 'super_admin')
            ->update(['role' => 'admin']);
            
        DB::table('users')
            ->where('role', 'admin')
            ->update(['role' => 'user']);
    }
};
