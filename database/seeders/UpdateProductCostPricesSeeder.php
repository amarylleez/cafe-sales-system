<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class UpdateProductCostPricesSeeder extends Seeder
{
    public function run(): void
    {
        $costs = [
            // Waffle
            'Waffle Peanut' => 1.80,
            'Waffle Coklat' => 1.90,
            'Waffle Strawberry' => 2.00,
            'Waffle Kaya' => 1.70,
            'Waffle Butter' => 1.60,
            
            // Aneka Nasi
            'Nasi Lemak Bungkus' => 2.00,
            'Nasi Lemak Telur Mata' => 2.50,
            'Nasi Lemak Paru' => 4.00,
            'Nasi Lemak Ayam' => 4.20,
            'Nasi Daging' => 5.50,
            'Nasi Beriyani' => 5.00,
            'Nasi Kak Wok' => 4.00,
            'Nasi Khao Mok' => 5.20,
            'Nasi Goreng Ayam' => 3.80,
            'Nasi Ayam' => 4.00,
            'Nasi Tomato' => 3.80,
            'Nasi Kerabu' => 4.20,
            
            // Aneka Mee/Bihun
            'Mee Goreng' => 2.20,
            'Bihun Goreng' => 2.00,
            'Maggi Goreng' => 2.30,
            'Mee Sizzling' => 2.80,
            'Mee Kari' => 2.50,
            'Laksa' => 2.60,
            'Bihun Sup' => 2.20,
            'Kerabu Maggi' => 2.30,
            
            // Aneka Roti
            'Sandwich' => 1.30,
            'Tortilla' => 1.80,
            'Roti Gulung Sardine' => 1.60,
            'Roti Gulung Sosej' => 1.50,
            'Burger Ayam Crispy' => 2.80,
            'Roti Bakar Mushroom Soup' => 1.90,
            'Roti Bakar Kaya' => 1.20,
            'Kaya Ball' => 0.60,
            'Wanpaku' => 2.50,
            
            // Pau Kukus
            'Pau Kacang Merah' => 1.30,
            'Pau Kari Ayam' => 1.80,
            'Pau Kari Daging' => 2.00,
            'Pau Coklat' => 1.20,
            
            // Spaghetti
            'Spaghetti Bolognese' => 2.50,
            'Spaghetti Carbonara' => 2.60,
            'Spaghetti Aglio Olio' => 2.20,
            
            // Kuih-Muih
            'Donat Coklat' => 1.20,
            'Donat Gula' => 1.00,
            'Popia Sira' => 1.20,
            'Popia Goreng' => 1.10,
            'Kuih Talam' => 1.20,
            'Apam Gula Melaka' => 1.30,
            'Seri Muka' => 1.40,
            'Seri Ayu' => 1.30,
            'Pau Sambal' => 1.40,
            'Karipap' => 1.20,
            'Kuih Lapis' => 1.80,
            
            // Goreng-goreng
            'Bebola Ketam' => 0.90,
            'Bebola Ikan' => 0.80,
            'Bebola Sotong' => 0.95,
            'Sosej Original' => 1.10,
            'Sosej Cheese' => 1.40,
            'Tofu Cheese' => 1.00,
            'Fish Roll' => 1.10,
            'Nugget' => 0.90,
            'Keropok Lekor' => 0.60,
            
            // Lain-lain
            'Vietnam Roll' => 1.80,
            'Ayam Korea' => 3.20,
            'Ayam Goreng Crispy' => 3.00,
            'Cucur Udang' => 1.40,
            'Bubur Berlauk' => 2.20,
            'Pulut Kuning Rendang' => 2.50,
            'Pulut Kuning Sambal Bilis' => 2.30,
            'Kek Butter' => 1.80,
            'Kerepek' => 0.60,
            
            // Air Cup
            'Latte' => 1.50,
            'Nescafe O' => 0.80,
            'Nescafe Susu' => 1.00,
            'Chocolate' => 1.40,
            'Teh O Ais' => 0.70,
            'Teh Tarik' => 0.90,
            'Teh Ais' => 0.85,
            'Kopi' => 0.80,
            'Ice Blended' => 1.80,
        ];

        $updated = 0;
        foreach ($costs as $name => $costPrice) {
            $count = Product::where('name', $name)->update(['cost_price' => $costPrice]);
            $updated += $count;
        }

        $this->command->info("Updated {$updated} products with cost prices.");
        
        // For any products without cost_price, set a default of 50% of selling price
        $remaining = Product::where('cost_price', 0)->orWhereNull('cost_price')->get();
        foreach ($remaining as $product) {
            $product->cost_price = $product->price * 0.5;
            $product->save();
        }
        
        if ($remaining->count() > 0) {
            $this->command->info("Set default cost prices for {$remaining->count()} remaining products.");
        }
    }
}
