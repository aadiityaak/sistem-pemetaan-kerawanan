<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class IndasSeeder extends Seeder
{
    /**
     * Seed the INDAS (Intelligence Data Analysis System) data.
     * This seeder is specifically for INDAS-related tables.
     */
    public function run(): void
    {
        $this->call([
            IndasIndicatorTypeSeeder::class,
        ]);
        
        $this->command->info('INDAS seeding completed successfully!');
        $this->command->info('- Created comprehensive indicator types for Economic, Tourism, and Social categories');
        $this->command->info('- Generated sample monthly data for 10 regions over the last 6 months');
        $this->command->info('- Ready for INDAS analysis and dashboard visualization');
    }
}