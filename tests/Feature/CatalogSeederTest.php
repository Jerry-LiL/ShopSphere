<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\LargeCatalogSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CatalogSeederTest extends TestCase
{
    public function test_large_catalog_seeder_creates_ten_thousand_products(): void
    {
        $vendor = User::create([
            'name' => 'Mass Vendor',
            'email' => 'mass@vendor.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_VENDOR,
        ]);

        Store::create([
            'user_id' => $vendor->id,
            'name' => 'Mass Store 1',
            'slug' => 'mass-store-1',
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Hardware',
            'slug' => 'hardware',
        ]);

        $seeder = new LargeCatalogSeeder();
        $startTime = microtime(true);
        $seeder->run(10000, 500);
        $duration = microtime(true) - $startTime;

        $this->assertEquals(10000, Product::count());
        $this->assertLessThan(10.0, $duration, "Expected 10k insertion to complete well under 10 seconds, took {$duration}s");

        // Verify index performance on filtered query with eager loading
        $queryStart = microtime(true);
        $results = Product::active()
            ->filter(['in_stock' => 1, 'min_price' => 50, 'max_price' => 500, 'sort' => 'price_asc'])
            ->with(['category', 'store'])
            ->paginate(12);
        $queryDuration = (microtime(true) - $queryStart) * 1000;

        $this->assertNotEmpty($results->items());
        $this->assertLessThan(250.0, $queryDuration, "Expected indexed query to complete in under 250ms, took {$queryDuration}ms");
    }

    public function test_artisan_command_shop_seed_catalog(): void
    {
        $vendor = User::create([
            'name' => 'Artisan Vendor',
            'email' => 'artisan@vendor.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_VENDOR,
        ]);

        Store::create([
            'user_id' => $vendor->id,
            'name' => 'Artisan Store',
            'slug' => 'artisan-store',
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Artisan Category',
            'slug' => 'artisan-category',
        ]);

        $this->artisan('shop:seed-catalog', ['--count' => 1000, '--chunk' => 200])
            ->assertSuccessful();

        $this->assertEquals(1000, Product::count());
    }
}
