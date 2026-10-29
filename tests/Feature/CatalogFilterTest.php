<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CatalogFilterTest extends TestCase
{
    protected Store $store1;
    protected Store $store2;
    protected Category $catElectronics;
    protected Category $catHome;

    protected function setUp(): void
    {
        parent::setUp();

        $v1 = User::create(['name' => 'V1', 'email' => 'v1@test.com', 'password' => Hash::make('password'), 'role' => User::ROLE_VENDOR]);
        $v2 = User::create(['name' => 'V2', 'email' => 'v2@test.com', 'password' => Hash::make('password'), 'role' => User::ROLE_VENDOR]);

        $this->store1 = Store::create(['user_id' => $v1->id, 'name' => 'Alpha Store', 'slug' => 'alpha-store', 'is_active' => true]);
        $this->store2 = Store::create(['user_id' => $v2->id, 'name' => 'Beta Store', 'slug' => 'beta-store', 'is_active' => true]);

        $this->catElectronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        $this->catHome = Category::create(['name' => 'Home Living', 'slug' => 'home-living']);

        // Seed varied test products
        Product::create([
            'store_id' => $this->store1->id,
            'category_id' => $this->catElectronics->id,
            'name' => 'Wireless Keyboard',
            'slug' => 'wireless-keyboard',
            'sku' => 'KB-101',
            'description' => 'Low latency mechanical switches with RGB backlight',
            'price' => 120.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        Product::create([
            'store_id' => $this->store1->id,
            'category_id' => $this->catElectronics->id,
            'name' => 'Gaming Mouse',
            'slug' => 'gaming-mouse',
            'sku' => 'MS-102',
            'description' => 'High precision optical sensor and lightweight cord',
            'price' => 45.00,
            'stock' => 5,
            'is_active' => true,
        ]);

        Product::create([
            'store_id' => $this->store2->id,
            'category_id' => $this->catHome->id,
            'name' => 'Oak Table Lamp',
            'slug' => 'oak-table-lamp',
            'sku' => 'LP-201',
            'description' => 'Warm Scandinavian style illumination for nightstands',
            'price' => 60.00,
            'stock' => 8,
            'is_active' => true,
        ]);

        Product::create([
            'store_id' => $this->store2->id,
            'category_id' => $this->catHome->id,
            'name' => 'Ceramic Planter Pot',
            'slug' => 'ceramic-planter-pot',
            'sku' => 'PT-202',
            'description' => 'Matte white glazed terracotta planter with saucer',
            'price' => 25.00,
            'stock' => 0, // out of stock
            'is_active' => true,
        ]);

        Product::create([
            'store_id' => $this->store1->id,
            'category_id' => $this->catElectronics->id,
            'name' => 'Archived Headset',
            'slug' => 'archived-headset',
            'sku' => 'HD-103',
            'description' => 'Discontinued audio hardware',
            'price' => 80.00,
            'stock' => 3,
            'is_active' => false, // inactive
        ]);
    }

    public function test_catalog_displays_only_active_products(): void
    {
        $response = $this->get('/catalog');

        $response->assertOk()
            ->assertSee('Wireless Keyboard')
            ->assertSee('Gaming Mouse')
            ->assertSee('Oak Table Lamp')
            ->assertSee('Ceramic Planter Pot')
            ->assertDontSee('Archived Headset');
    }

    public function test_filter_by_search_query(): void
    {
        // Search by keyword in title
        $response = $this->get('/catalog?q=Keyboard');
        $response->assertOk()
            ->assertSee('Wireless Keyboard')
            ->assertDontSee('Gaming Mouse')
            ->assertDontSee('Oak Table Lamp');

        // Search by keyword in description
        $response2 = $this->get('/catalog?q=Scandinavian');
        $response2->assertOk()
            ->assertSee('Oak Table Lamp')
            ->assertDontSee('Wireless Keyboard');
    }

    public function test_filter_by_category(): void
    {
        $response = $this->get('/catalog?category=electronics');

        $response->assertOk()
            ->assertSee('Wireless Keyboard')
            ->assertSee('Gaming Mouse')
            ->assertDontSee('Oak Table Lamp');
    }

    public function test_filter_by_store(): void
    {
        $response = $this->get('/catalog?store=beta-store');

        $response->assertOk()
            ->assertSee('Oak Table Lamp')
            ->assertSee('Ceramic Planter Pot')
            ->assertDontSee('Wireless Keyboard')
            ->assertDontSee('Gaming Mouse');
    }

    public function test_filter_by_price_range(): void
    {
        $response = $this->get('/catalog?min_price=40&max_price=70');

        $response->assertOk()
            ->assertSee('Gaming Mouse') // 45.00
            ->assertSee('Oak Table Lamp') // 60.00
            ->assertDontSee('Wireless Keyboard') // 120.00
            ->assertDontSee('Ceramic Planter Pot'); // 25.00
    }

    public function test_filter_by_in_stock_only(): void
    {
        $response = $this->get('/catalog?in_stock=1');

        $response->assertOk()
            ->assertSee('Wireless Keyboard')
            ->assertSee('Gaming Mouse')
            ->assertSee('Oak Table Lamp')
            ->assertDontSee('Ceramic Planter Pot'); // stock is 0
    }

    public function test_sort_by_price_asc_and_desc(): void
    {
        $responseAsc = $this->get('/catalog?sort=price_asc');
        $responseAsc->assertOk();

        $productsAsc = $responseAsc->viewData('products')->items();
        $this->assertEquals('Ceramic Planter Pot', $productsAsc[0]->name); // $25
        $this->assertEquals('Wireless Keyboard', end($productsAsc)->name); // $120

        $responseDesc = $this->get('/catalog?sort=price_desc');
        $responseDesc->assertOk();

        $productsDesc = $responseDesc->viewData('products')->items();
        $this->assertEquals('Wireless Keyboard', $productsDesc[0]->name); // $120
        $this->assertEquals('Ceramic Planter Pot', end($productsDesc)->name); // $25
    }

    public function test_eager_loading_prevents_n_plus_one_queries(): void
    {
        $response = $this->get('/catalog');
        $products = $response->viewData('products');

        foreach ($products as $prod) {
            $this->assertTrue($prod->relationLoaded('category'));
            $this->assertTrue($prod->relationLoaded('store'));
        }
    }
}
