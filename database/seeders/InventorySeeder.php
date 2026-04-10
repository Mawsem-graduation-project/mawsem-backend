<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Take the first shop on database
        $shop = Shop::first();

        if (!$shop) {
            $this->command->error("Shop not found");
            return;
        }

        // Take all products from database and link it with the shop on inventory table
        $products = Product::all();

        foreach ($products as $product) {
            Inventory::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'shop_id' => $shop->id,
                    'current_stock' => rand(50, 250),
                    'minimum_stock' => 10,
                ]
            );
        }


    }
}
