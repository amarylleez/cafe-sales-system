<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Waffle (category_id: 1)
            ['category_id' => 1, 'name' => 'Waffle Peanut', 'price' => 4.00],
            ['category_id' => 1, 'name' => 'Waffle Coklat', 'price' => 4.00],
            ['category_id' => 1, 'name' => 'Waffle Strawberry', 'price' => 4.00],
            ['category_id' => 1, 'name' => 'Waffle Kaya', 'price' => 4.00],
            ['category_id' => 1, 'name' => 'Waffle Butter', 'price' => 4.00],
            
            // Aneka Nasi (category_id: 2)
            ['category_id' => 2, 'name' => 'Nasi Lemak Bungkus', 'price' => 4.00],
            ['category_id' => 2, 'name' => 'Nasi Lemak Telur Mata', 'price' => 5.00],
            ['category_id' => 2, 'name' => 'Nasi Lemak Paru', 'price' => 8.00],
            ['category_id' => 2, 'name' => 'Nasi Lemak Ayam', 'price' => 8.00],
            ['category_id' => 2, 'name' => 'Nasi Daging', 'price' => 10.00],
            ['category_id' => 2, 'name' => 'Nasi Beriyani', 'price' => 10.00],
            ['category_id' => 2, 'name' => 'Nasi Kak Wok', 'price' => 8.00],
            ['category_id' => 2, 'name' => 'Nasi Khao Mok', 'price' => 10.00],
            ['category_id' => 2, 'name' => 'Nasi Goreng Ayam', 'price' => 8.00],
            ['category_id' => 2, 'name' => 'Nasi Ayam', 'price' => 8.00],
            ['category_id' => 2, 'name' => 'Nasi Tomato', 'price' => 8.00],
            ['category_id' => 2, 'name' => 'Nasi Kerabu', 'price' => 8.00],
            
            // Aneka Mee/Bihun (category_id: 3)
            ['category_id' => 3, 'name' => 'Mee Goreng', 'price' => 5.00],
            ['category_id' => 3, 'name' => 'Bihun Goreng', 'price' => 5.00],
            ['category_id' => 3, 'name' => 'Maggi Goreng', 'price' => 5.00],
            ['category_id' => 3, 'name' => 'Mee Sizzling', 'price' => 6.00],
            ['category_id' => 3, 'name' => 'Mee Kari', 'price' => 5.00],
            ['category_id' => 3, 'name' => 'Laksa', 'price' => 5.00],
            ['category_id' => 3, 'name' => 'Bihun Sup', 'price' => 5.00],
            ['category_id' => 3, 'name' => 'Kerabu Maggi', 'price' => 5.00],
            
            // Aneka Roti (category_id: 4)
            ['category_id' => 4, 'name' => 'Sandwich', 'price' => 3.00],
            ['category_id' => 4, 'name' => 'Tortilla', 'price' => 4.00],
            ['category_id' => 4, 'name' => 'Roti Gulung Sardine', 'price' => 3.50],
            ['category_id' => 4, 'name' => 'Roti Gulung Sosej', 'price' => 3.50],
            ['category_id' => 4, 'name' => 'Burger Ayam Crispy', 'price' => 5.50],
            ['category_id' => 4, 'name' => 'Roti Bakar Mushroom Soup', 'price' => 4.00],
            ['category_id' => 4, 'name' => 'Roti Bakar Kaya', 'price' => 3.00],
            ['category_id' => 4, 'name' => 'Kaya Ball', 'price' => 1.50],
            ['category_id' => 4, 'name' => 'Wanpaku', 'price' => 5.00],
            
            // Pau Kukus (category_id: 5)
            ['category_id' => 5, 'name' => 'Pau Kacang Merah', 'price' => 3.00],
            ['category_id' => 5, 'name' => 'Pau Kari Ayam', 'price' => 4.00],
            ['category_id' => 5, 'name' => 'Pau Kari Daging', 'price' => 4.00],
            ['category_id' => 5, 'name' => 'Pau Coklat', 'price' => 3.00],
            
            // Spaghetti (category_id: 6)
            ['category_id' => 6, 'name' => 'Spaghetti Bolognese', 'price' => 5.50],
            ['category_id' => 6, 'name' => 'Spaghetti Carbonara', 'price' => 5.50],
            ['category_id' => 6, 'name' => 'Spaghetti Aglio Olio', 'price' => 5.50],
            
            // Kuih-Muih (category_id: 7)
            ['category_id' => 7, 'name' => 'Donat Coklat', 'price' => 3.00],
            ['category_id' => 7, 'name' => 'Donat Gula', 'price' => 3.00],
            ['category_id' => 7, 'name' => 'Popia Sira', 'price' => 3.00],
            ['category_id' => 7, 'name' => 'Popia Goreng', 'price' => 3.00],
            ['category_id' => 7, 'name' => 'Kuih Talam', 'price' => 3.00],
            ['category_id' => 7, 'name' => 'Apam Gula Melaka', 'price' => 3.00],
            ['category_id' => 7, 'name' => 'Seri Muka', 'price' => 3.00],
            ['category_id' => 7, 'name' => 'Seri Ayu', 'price' => 3.00],
            ['category_id' => 7, 'name' => 'Pau Sambal', 'price' => 3.00],
            ['category_id' => 7, 'name' => 'Karipap', 'price' => 3.00],
            ['category_id' => 7, 'name' => 'Kuih Lapis', 'price' => 4.00],
            
            // Goreng-goreng (category_id: 8)
            ['category_id' => 8, 'name' => 'Bebola Ketam', 'price' => 2.00],
            ['category_id' => 8, 'name' => 'Bebola Ikan', 'price' => 2.00],
            ['category_id' => 8, 'name' => 'Bebola Sotong', 'price' => 2.00],
            ['category_id' => 8, 'name' => 'Sosej Original', 'price' => 2.50],
            ['category_id' => 8, 'name' => 'Sosej Cheese', 'price' => 3.00],
            ['category_id' => 8, 'name' => 'Tofu Cheese', 'price' => 2.50],
            ['category_id' => 8, 'name' => 'Fish Roll', 'price' => 2.50],
            ['category_id' => 8, 'name' => 'Nugget', 'price' => 2.00],
            ['category_id' => 8, 'name' => 'Keropok Lekor', 'price' => 1.50],
            
            // Lain-lain (category_id: 9)
            ['category_id' => 9, 'name' => 'Vietnam Roll', 'price' => 4.00],
            ['category_id' => 9, 'name' => 'Ayam Korea', 'price' => 6.00],
            ['category_id' => 9, 'name' => 'Ayam Goreng Crispy', 'price' => 6.00],
            ['category_id' => 9, 'name' => 'Cucur Udang', 'price' => 3.00],
            ['category_id' => 9, 'name' => 'Bubur Berlauk', 'price' => 5.00],
            ['category_id' => 9, 'name' => 'Pulut Kuning Rendang', 'price' => 5.00],
            ['category_id' => 9, 'name' => 'Pulut Kuning Sambal Bilis', 'price' => 5.00],
            ['category_id' => 9, 'name' => 'Kek Butter', 'price' => 4.00],
            ['category_id' => 9, 'name' => 'Kerepek', 'price' => 1.50],
            
            // Air Cup (category_id: 10)
            ['category_id' => 10, 'name' => 'Latte', 'price' => 4.00],
            ['category_id' => 10, 'name' => 'Nescafe O', 'price' => 3.00],
            ['category_id' => 10, 'name' => 'Nescafe Susu', 'price' => 3.50],
            ['category_id' => 10, 'name' => 'Chocolate', 'price' => 4.00],
            ['category_id' => 10, 'name' => 'Teh O Ais', 'price' => 3.00],
            ['category_id' => 10, 'name' => 'Teh Tarik', 'price' => 3.50],
            ['category_id' => 10, 'name' => 'Teh Ais', 'price' => 3.50],
            ['category_id' => 10, 'name' => 'Kopi', 'price' => 3.00],
            ['category_id' => 10, 'name' => 'Ice Blended', 'price' => 5.00],
        ];

        foreach ($products as $product) {
            DB::table('products')->insert([
                'category_id' => $product['category_id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}