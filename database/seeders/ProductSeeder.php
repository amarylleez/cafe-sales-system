<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Waffle (category_id: 1) - Cost ~50% of price
            ['category_id' => 1, 'name' => 'Waffle Peanut', 'price' => 4.00, 'cost_price' => 1.80],
            ['category_id' => 1, 'name' => 'Waffle Coklat', 'price' => 4.00, 'cost_price' => 1.90],
            ['category_id' => 1, 'name' => 'Waffle Strawberry', 'price' => 4.00, 'cost_price' => 2.00],
            ['category_id' => 1, 'name' => 'Waffle Kaya', 'price' => 4.00, 'cost_price' => 1.70],
            ['category_id' => 1, 'name' => 'Waffle Butter', 'price' => 4.00, 'cost_price' => 1.60],
            
            // Aneka Nasi (category_id: 2) - Cost ~45-55% of price
            ['category_id' => 2, 'name' => 'Nasi Lemak Bungkus', 'price' => 4.00, 'cost_price' => 2.00],
            ['category_id' => 2, 'name' => 'Nasi Lemak Telur Mata', 'price' => 5.00, 'cost_price' => 2.50],
            ['category_id' => 2, 'name' => 'Nasi Lemak Paru', 'price' => 8.00, 'cost_price' => 4.00],
            ['category_id' => 2, 'name' => 'Nasi Lemak Ayam', 'price' => 8.00, 'cost_price' => 4.20],
            ['category_id' => 2, 'name' => 'Nasi Daging', 'price' => 10.00, 'cost_price' => 5.50],
            ['category_id' => 2, 'name' => 'Nasi Beriyani', 'price' => 10.00, 'cost_price' => 5.00],
            ['category_id' => 2, 'name' => 'Nasi Kak Wok', 'price' => 8.00, 'cost_price' => 4.00],
            ['category_id' => 2, 'name' => 'Nasi Khao Mok', 'price' => 10.00, 'cost_price' => 5.20],
            ['category_id' => 2, 'name' => 'Nasi Goreng Ayam', 'price' => 8.00, 'cost_price' => 3.80],
            ['category_id' => 2, 'name' => 'Nasi Ayam', 'price' => 8.00, 'cost_price' => 4.00],
            ['category_id' => 2, 'name' => 'Nasi Tomato', 'price' => 8.00, 'cost_price' => 3.80],
            ['category_id' => 2, 'name' => 'Nasi Kerabu', 'price' => 8.00, 'cost_price' => 4.20],
            
            // Aneka Mee/Bihun (category_id: 3) - Cost ~40-50% of price
            ['category_id' => 3, 'name' => 'Mee Goreng', 'price' => 5.00, 'cost_price' => 2.20],
            ['category_id' => 3, 'name' => 'Bihun Goreng', 'price' => 5.00, 'cost_price' => 2.00],
            ['category_id' => 3, 'name' => 'Maggi Goreng', 'price' => 5.00, 'cost_price' => 2.30],
            ['category_id' => 3, 'name' => 'Mee Sizzling', 'price' => 6.00, 'cost_price' => 2.80],
            ['category_id' => 3, 'name' => 'Mee Kari', 'price' => 5.00, 'cost_price' => 2.50],
            ['category_id' => 3, 'name' => 'Laksa', 'price' => 5.00, 'cost_price' => 2.60],
            ['category_id' => 3, 'name' => 'Bihun Sup', 'price' => 5.00, 'cost_price' => 2.20],
            ['category_id' => 3, 'name' => 'Kerabu Maggi', 'price' => 5.00, 'cost_price' => 2.30],
            
            // Aneka Roti (category_id: 4) - Cost ~40-50% of price
            ['category_id' => 4, 'name' => 'Sandwich', 'price' => 3.00, 'cost_price' => 1.30],
            ['category_id' => 4, 'name' => 'Tortilla', 'price' => 4.00, 'cost_price' => 1.80],
            ['category_id' => 4, 'name' => 'Roti Gulung Sardine', 'price' => 3.50, 'cost_price' => 1.60],
            ['category_id' => 4, 'name' => 'Roti Gulung Sosej', 'price' => 3.50, 'cost_price' => 1.50],
            ['category_id' => 4, 'name' => 'Burger Ayam Crispy', 'price' => 5.50, 'cost_price' => 2.80],
            ['category_id' => 4, 'name' => 'Roti Bakar Mushroom Soup', 'price' => 4.00, 'cost_price' => 1.90],
            ['category_id' => 4, 'name' => 'Roti Bakar Kaya', 'price' => 3.00, 'cost_price' => 1.20],
            ['category_id' => 4, 'name' => 'Kaya Ball', 'price' => 1.50, 'cost_price' => 0.60],
            ['category_id' => 4, 'name' => 'Wanpaku', 'price' => 5.00, 'cost_price' => 2.50],
            
            // Pau Kukus (category_id: 5) - Cost ~45% of price
            ['category_id' => 5, 'name' => 'Pau Kacang Merah', 'price' => 3.00, 'cost_price' => 1.30],
            ['category_id' => 5, 'name' => 'Pau Kari Ayam', 'price' => 4.00, 'cost_price' => 1.80],
            ['category_id' => 5, 'name' => 'Pau Kari Daging', 'price' => 4.00, 'cost_price' => 2.00],
            ['category_id' => 5, 'name' => 'Pau Coklat', 'price' => 3.00, 'cost_price' => 1.20],
            
            // Spaghetti (category_id: 6) - Cost ~45% of price
            ['category_id' => 6, 'name' => 'Spaghetti Bolognese', 'price' => 5.50, 'cost_price' => 2.50],
            ['category_id' => 6, 'name' => 'Spaghetti Carbonara', 'price' => 5.50, 'cost_price' => 2.60],
            ['category_id' => 6, 'name' => 'Spaghetti Aglio Olio', 'price' => 5.50, 'cost_price' => 2.20],
            
            // Kuih-Muih (category_id: 7) - Cost ~40% of price
            ['category_id' => 7, 'name' => 'Donat Coklat', 'price' => 3.00, 'cost_price' => 1.20],
            ['category_id' => 7, 'name' => 'Donat Gula', 'price' => 3.00, 'cost_price' => 1.00],
            ['category_id' => 7, 'name' => 'Popia Sira', 'price' => 3.00, 'cost_price' => 1.20],
            ['category_id' => 7, 'name' => 'Popia Goreng', 'price' => 3.00, 'cost_price' => 1.10],
            ['category_id' => 7, 'name' => 'Kuih Talam', 'price' => 3.00, 'cost_price' => 1.20],
            ['category_id' => 7, 'name' => 'Apam Gula Melaka', 'price' => 3.00, 'cost_price' => 1.30],
            ['category_id' => 7, 'name' => 'Seri Muka', 'price' => 3.00, 'cost_price' => 1.40],
            ['category_id' => 7, 'name' => 'Seri Ayu', 'price' => 3.00, 'cost_price' => 1.30],
            ['category_id' => 7, 'name' => 'Pau Sambal', 'price' => 3.00, 'cost_price' => 1.40],
            ['category_id' => 7, 'name' => 'Karipap', 'price' => 3.00, 'cost_price' => 1.20],
            ['category_id' => 7, 'name' => 'Kuih Lapis', 'price' => 4.00, 'cost_price' => 1.80],
            
            // Goreng-goreng (category_id: 8) - Cost ~45% of price
            ['category_id' => 8, 'name' => 'Bebola Ketam', 'price' => 2.00, 'cost_price' => 0.90],
            ['category_id' => 8, 'name' => 'Bebola Ikan', 'price' => 2.00, 'cost_price' => 0.80],
            ['category_id' => 8, 'name' => 'Bebola Sotong', 'price' => 2.00, 'cost_price' => 0.95],
            ['category_id' => 8, 'name' => 'Sosej Original', 'price' => 2.50, 'cost_price' => 1.10],
            ['category_id' => 8, 'name' => 'Sosej Cheese', 'price' => 3.00, 'cost_price' => 1.40],
            ['category_id' => 8, 'name' => 'Tofu Cheese', 'price' => 2.50, 'cost_price' => 1.00],
            ['category_id' => 8, 'name' => 'Fish Roll', 'price' => 2.50, 'cost_price' => 1.10],
            ['category_id' => 8, 'name' => 'Nugget', 'price' => 2.00, 'cost_price' => 0.90],
            ['category_id' => 8, 'name' => 'Keropok Lekor', 'price' => 1.50, 'cost_price' => 0.60],
            
            // Lain-lain (category_id: 9) - Cost ~45-55% of price
            ['category_id' => 9, 'name' => 'Vietnam Roll', 'price' => 4.00, 'cost_price' => 1.80],
            ['category_id' => 9, 'name' => 'Ayam Korea', 'price' => 6.00, 'cost_price' => 3.20],
            ['category_id' => 9, 'name' => 'Ayam Goreng Crispy', 'price' => 6.00, 'cost_price' => 3.00],
            ['category_id' => 9, 'name' => 'Cucur Udang', 'price' => 3.00, 'cost_price' => 1.40],
            ['category_id' => 9, 'name' => 'Bubur Berlauk', 'price' => 5.00, 'cost_price' => 2.20],
            ['category_id' => 9, 'name' => 'Pulut Kuning Rendang', 'price' => 5.00, 'cost_price' => 2.50],
            ['category_id' => 9, 'name' => 'Pulut Kuning Sambal Bilis', 'price' => 5.00, 'cost_price' => 2.30],
            ['category_id' => 9, 'name' => 'Kek Butter', 'price' => 4.00, 'cost_price' => 1.80],
            ['category_id' => 9, 'name' => 'Kerepek', 'price' => 1.50, 'cost_price' => 0.60],
            
            // Air Cup (category_id: 10) - Cost ~35-45% of price (beverages have higher margin)
            ['category_id' => 10, 'name' => 'Latte', 'price' => 4.00, 'cost_price' => 1.50],
            ['category_id' => 10, 'name' => 'Nescafe O', 'price' => 3.00, 'cost_price' => 0.80],
            ['category_id' => 10, 'name' => 'Nescafe Susu', 'price' => 3.50, 'cost_price' => 1.00],
            ['category_id' => 10, 'name' => 'Chocolate', 'price' => 4.00, 'cost_price' => 1.40],
            ['category_id' => 10, 'name' => 'Teh O Ais', 'price' => 3.00, 'cost_price' => 0.70],
            ['category_id' => 10, 'name' => 'Teh Tarik', 'price' => 3.50, 'cost_price' => 0.90],
            ['category_id' => 10, 'name' => 'Teh Ais', 'price' => 3.50, 'cost_price' => 0.85],
            ['category_id' => 10, 'name' => 'Kopi', 'price' => 3.00, 'cost_price' => 0.80],
            ['category_id' => 10, 'name' => 'Ice Blended', 'price' => 5.00, 'cost_price' => 1.80],
        ];

        foreach ($products as $product) {
            DB::table('products')->insert([
                'category_id' => $product['category_id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'cost_price' => $product['cost_price'],
                'is_available' => true,
                'stock_quantity' => rand(10, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}