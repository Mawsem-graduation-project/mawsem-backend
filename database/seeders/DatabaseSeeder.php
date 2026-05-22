<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $shop = Shop::firstOrCreate(
            ['name' => 'متاجر السعودية'],
            ['city' => 'مكة المكرمة']
        );

        $this->call([
           ProductSeeder::class,
           SaleSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'مالك',
            'email' => 'mtjr@example.com',
            'password' => Hash::make('123'),
            'shop_id' => $shop->id,
        ]);
    }
}
