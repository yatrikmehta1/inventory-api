<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $electronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'is_active' => true]);
        $phones = Category::create(['name' => 'Phones', 'slug' => 'phones', 'parent_id' => $electronics->id, 'is_active' => true]);
        $accessories = Category::create(['name' => 'Accessories', 'slug' => 'accessories', 'parent_id' => $electronics->id, 'is_active' => true]);
        $legacy = Category::create(['name' => 'Legacy Devices', 'slug' => 'legacy-devices', 'parent_id' => $electronics->id, 'is_active' => false]);
        Category::create(['name' => 'Legacy Cases', 'slug' => 'legacy-cases', 'parent_id' => $legacy->id, 'is_active' => true]);
        Category::create(['name' => 'Unassigned Category', 'slug' => 'unassigned-category', 'is_active' => true]);

        $warehouses = collect([
            Warehouse::create(['name' => 'Ahmedabad Central', 'city' => 'Ahmedabad', 'is_active' => true]),
            Warehouse::create(['name' => 'Surat South', 'city' => 'Surat', 'is_active' => true]),
            Warehouse::create(['name' => 'Vadodara West', 'city' => 'Vadodara', 'is_active' => true]),
        ]);
        $categories = [$phones->id, $accessories->id, $electronics->id];
        $products = collect();
        for ($i = 1; $i <= 50; $i++) {
            $products->push(Product::create([
                'category_id' => $categories[($i - 1) % count($categories)],
                'name' => sprintf('Inventory Product %02d', $i), 'sku' => sprintf('SKU-%05d', $i),
                'description' => 'Seeded demo product '.$i, 'base_price' => number_format(99 + $i * 17.35, 2, '.', ''),
                'is_active' => true,
            ]));
        }
        foreach ($products as $index => $product) {
            foreach ($warehouses as $wi => $warehouse) {
                // Some products intentionally have zero stock in all warehouses; others are distributed.
                $quantity = ($index % 10 === 0) ? 0 : (($index + $wi * 7) % 60);
                $reserved = $quantity > 0 ? min((int)floor($quantity / 5), $quantity) : 0;
                Stock::create(['product_id' => $product->id, 'warehouse_id' => $warehouse->id,
                    'quantity' => $quantity, 'reserved_quantity' => $reserved]);
            }
        }
        $types = ['stock_in', 'stock_out', 'reservation', 'reservation_release'];
        for ($i = 1; $i <= 220; $i++) {
            StockMovement::create([
                'product_id' => $products[($i - 1) % $products->count()]->id,
                'warehouse_id' => $warehouses[($i - 1) % $warehouses->count()]->id,
                'movement_type' => $types[($i - 1) % count($types)], 'quantity' => ($i % 8) + 1,
                'reference_id' => null, 'reference_type' => null, 'note' => 'Seed movement '.$i,
                'moved_at' => now()->subDays($i % 60)->subMinutes($i),
            ]);
        }
    }
}
