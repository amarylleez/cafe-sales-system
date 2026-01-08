<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'Waffle', 'description' => 'Various waffle flavors', 'expiry_hours' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aneka Mee/Bihun', 'description' => 'Noodles and vermicelli', 'expiry_hours' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aneka Roti', 'description' => 'Bread and sandwiches', 'expiry_hours' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pau Kukus', 'description' => 'Steamed buns', 'expiry_hours' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Spaghetti', 'description' => 'Pasta dishes', 'expiry_hours' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kuih-Muih', 'description' => 'Traditional cakes', 'expiry_hours' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Goreng-goreng', 'description' => 'Fried snacks', 'expiry_hours' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lain-lain', 'description' => 'Other foods', 'expiry_hours' => 24, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Air Cup', 'description' => 'Beverages', 'expiry_hours' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}