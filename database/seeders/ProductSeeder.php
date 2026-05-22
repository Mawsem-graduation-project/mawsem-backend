<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $shop = Shop::first();

        $products = [
            [
                'name' => 'حليب المراعي طويل الأجل 1 لتر',
                'sku' => '6281007032261',
                'unit' => 'piece',
                'category_name' => 'الألبان والأجبان',
                'shop_id' => $shop->id,
            ],
            [
                'name' => 'فيمتو شراب التوت 710 مل',
                'sku' => '6281034001063',
                'unit' => 'bottle',
                'category_name' => 'رمضانيات ومشروبات',
                'shop_id' => $shop->id,
            ],
            [
                'name' => 'شاي ربيع فرط 400 جرام',
                'sku' => '6281013163003',
                'unit' => 'bottle',
                'category_name' => 'رمضانيات ومشروبات',
                'shop_id' => $shop->id,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['shop_id' => $product['shop_id'], 'sku' => $product['sku']],
                $product
            );
        }
    }
}
