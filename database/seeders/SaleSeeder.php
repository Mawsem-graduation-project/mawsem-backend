<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $shop = Shop::first();
        $products = Product::where('shop_id', $shop->id)->get();

        $startDate = Carbon::now()->subYears(2);
        $endDate = Carbon::now();

        foreach ($products as $product) {
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                $qty = $this->generateQuantity($product, $currentDate);

                if ($qty > 0){
                    Sale::create([
                        'shop_id' => $shop->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'sale_date' => $currentDate->copy()->setTime(rand(9, 21), rand(0, 59)),
                    ]);
                }

                $currentDate = $currentDate->addDay();
            }
        }
    }

    private function generateQuantity($product, $date)
    {
        $baseQuantity = rand(5, 10);

        if ($date->isWeekend()) {
            $baseQuantity += rand(5, 12);
        }

        if (str_contains($product->name, 'فيمتو شراب التوت 710 مل')) {

            $isVimtoSeason = false;

            if ($date->year == 2024 && $date->between('2024-03-01', '2024-04-09')) {
                $isVimtoSeason = true;
            }

            if ($date->year == 2025 && $date->between('2025-02-15', '2025-03-29')) {
                $isVimtoSeason = true;
            }

            if ($date->year == 2026 && $date->between('2026-02-05', '2026-03-10')) {
                $isVimtoSeason = true;
            }

            if ($isVimtoSeason) {
                return rand(40, 100);
            }

            return rand(0, 3);
        }

        if (str_contains($product->name, 'حليب')) {
            if (in_array($date->month, [11, 12, 1])) {
                $baseQuantity += rand(2, 5);
            }
        }

        return $baseQuantity;
    }
}
