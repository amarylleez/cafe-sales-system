<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('branches')->insert([
            [
                'name' => 'Perodua Global Manufacturing (PGMSB)',
                'address' => 'Beg Berkunci 224, Kampung Sungai Choh, 48009 Rawang, Selangor',
                'phone' => '019-6548026',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Perodua Manufacturing (PMSB)',
                'address' => 'Lot 1896, Locked Bag No. 226 Jalan Sungai Choh mukim serendah, 48009 Rawang',
                'phone' => '019-6548026',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Masjid Perodua',
                'address' => 'Lot 2 Gerai Masjid, Masjid Perodua, Sg Choh, 48000 Rawang, Selangor',
                'phone' => '019-6548026',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}