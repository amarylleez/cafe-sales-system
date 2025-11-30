<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'Waffle', 'description' => 'Various waffle flavors', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aneka Nasi', 'description' => 'Rice dishes', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aneka Mee/Bihun', 'description' => 'Noodles and vermicelli', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aneka Roti', 'description' => 'Bread and sandwiches', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pau Kukus', 'description' => 'Steamed buns', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Spaghetti', 'description' => 'Pasta dishes', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kuih-Muih', 'description' => 'Traditional cakes', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Goreng-goreng', 'description' => 'Fried snacks', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lain-lain', 'description' => 'Other foods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Air Cup', 'description' => 'Beverages', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}