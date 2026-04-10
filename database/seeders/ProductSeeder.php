<?php

namespace Database\Seeders;

use App\Models\Product;
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
        $products = [
            [
                'name' => 'حليب المراعي طويل الأجل 1 لتر',
                'sku' => '6281007032261',
                'unit' => 'piece',
                'category_id' => 1,
            ],
            [
                'name' => 'فيمتو شراب التوت 710 مل',
                'sku' => '6281034001063',
                'unit' => 'bottle',
                'category_id' => 2,
            ],
            [
                'name' => 'شاي ربيع فرط 400 جرام',
                'sku' => '6281013163003',
                'unit' => 'bottle',
                'category_id' => 2,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate($product);
        }
    }
}
