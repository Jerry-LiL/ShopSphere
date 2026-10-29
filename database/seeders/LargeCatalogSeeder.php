<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LargeCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds for a large catalog (~10k products).
     */
    public function run(int $targetCount = 10000, int $chunkSize = 500): void
    {
        $stores = Store::pluck('id')->all();
        $categories = Category::pluck('id')->all();

        if (empty($stores) || empty($categories)) {
            $this->command?->warn('Stores and categories must exist before running LargeCatalogSeeder. Run DatabaseSeeder first.');
            return;
        }

        $adjectives = [
            'Pro', 'Ultra', 'Essential', 'Wireless', 'Ergonomic', 'Smart', 'Eco', 'Compact',
            'Heavy-Duty', 'Classic', 'Modern', 'Premium', 'Minimalist', 'Deluxe', 'Portable',
            'Tactical', 'Slim', 'Silent', 'Dynamic', 'Thermal', 'Organic', 'Hybrid', 'Active',
        ];

        $nouns = [
            'Keyboard', 'Mouse', 'Monitor', 'Desk Lamp', 'Headphones', 'Backpack', 'Water Bottle',
            'Coffee Mug', 'Chair Cushion', 'Cable Organizer', 'Desk Mat', 'Notebook', 'Pen Set',
            'Phone Stand', 'Microphone', 'Webcam', 'Charger', 'Power Bank', 'Backpack', 'Jacket',
            'Running Shoes', 'Yoga Mat', 'Fitness Band', 'Bluetooth Speaker', 'Travel Bag',
        ];

        $now = now();
        $totalInserted = 0;
        $batch = [];

        $storeCount = count($stores);
        $categoryCount = count($categories);
        $adjCount = count($adjectives);
        $nounCount = count($nouns);

        $this->command?->info("Seeding {$targetCount} products in batches of {$chunkSize}...");
        $startTime = microtime(true);

        for ($i = 1; $i <= $targetCount; $i++) {
            $adj = $adjectives[$i % $adjCount];
            $noun = $nouns[($i * 3) % $nounCount];
            $name = "{$adj} {$noun} Edition #{$i}";
            $slug = Str::slug("{$name}-{$i}-" . Str::random(4));
            $sku = sprintf('SP-%05d-%s', $i, strtoupper(Str::random(3)));
            $price = round((float) (10 + (($i * 7) % 990) + (($i % 100) / 100)), 2);
            $stock = ($i % 15 === 0) ? 0 : (($i * 11) % 250 + 5);

            $batch[] = [
                'store_id' => $stores[$i % $storeCount],
                'category_id' => $categories[$i % $categoryCount],
                'name' => $name,
                'slug' => $slug,
                'sku' => $sku,
                'description' => "Detailed specification and high-grade materials for {$name}. Designed for multi-vendor catalog performance testing.",
                'price' => $price,
                'stock' => $stock,
                'is_active' => ($i % 30 !== 0), // 96.7% active
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= $chunkSize) {
                Product::insert($batch);
                $totalInserted += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            Product::insert($batch);
            $totalInserted += count($batch);
        }

        $elapsed = round(microtime(true) - $startTime, 2);
        $this->command?->info("Successfully inserted {$totalInserted} products in {$elapsed} seconds.");
    }
}
