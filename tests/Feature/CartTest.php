<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CartTest extends TestCase
{
    protected Product $inStockProduct;
    protected Product $outOfStockProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $vendor = User::create([
            'name' => 'Tech Vendor',
            'email' => 'tech@vendor.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_VENDOR,
        ]);

        $store = Store::create([
            'user_id' => $vendor->id,
            'name' => 'Apex Tech Store',
            'slug' => 'apex-tech-store',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Peripherals',
            'slug' => 'peripherals',
        ]);

        $this->inStockProduct = Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Mechanical Keyboard',
            'slug' => 'mechanical-keyboard',
            'sku' => 'KB-001',
            'description' => 'Tactile switches',
            'price' => 99.50,
            'stock' => 15,
            'is_active' => true,
        ]);

        $this->outOfStockProduct = Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Limited Keycap Set',
            'slug' => 'limited-keycap-set',
            'sku' => 'KC-002',
            'description' => 'Rare keycap set',
            'price' => 45.00,
            'stock' => 0,
            'is_active' => true,
        ]);
    }

    public function test_can_add_in_stock_product_to_cart_via_ajax(): void
    {
        $response = $this->postJson('/cart/add', [
            'product_id' => $this->inStockProduct->id,
            'quantity' => 2,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'cart_count' => 2,
                'subtotal' => 199.00,
                'formatted_subtotal' => '$199.00',
            ]);

        $this->assertEquals(2, session('shopsphere_cart')[$this->inStockProduct->id]['quantity']);
    }

    public function test_adding_out_of_stock_product_fails(): void
    {
        $response = $this->postJson('/cart/add', [
            'product_id' => $this->outOfStockProduct->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Product is out of stock.',
            ]);
    }

    public function test_can_update_cart_quantity_via_ajax(): void
    {
        $this->postJson('/cart/add', [
            'product_id' => $this->inStockProduct->id,
            'quantity' => 1,
        ]);

        $response = $this->patchJson("/cart/update/{$this->inStockProduct->id}", [
            'quantity' => 4,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'cart_count' => 4,
                'subtotal' => 398.00,
            ]);
    }

    public function test_cart_quantity_caps_at_maximum_available_stock(): void
    {
        $this->postJson('/cart/add', [
            'product_id' => $this->inStockProduct->id,
            'quantity' => 1,
        ]);

        // Request 100 when only 15 exist in stock
        $response = $this->patchJson("/cart/update/{$this->inStockProduct->id}", [
            'quantity' => 100,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'cart_count' => 15,
                'subtotal' => 1492.50,
            ]);
    }

    public function test_can_remove_item_from_cart_via_ajax(): void
    {
        $this->postJson('/cart/add', [
            'product_id' => $this->inStockProduct->id,
            'quantity' => 2,
        ]);

        $response = $this->deleteJson("/cart/remove/{$this->inStockProduct->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'cart_count' => 0,
                'subtotal' => 0.0,
            ]);

        $this->assertEmpty(session('shopsphere_cart'));
    }

    public function test_can_clear_entire_cart(): void
    {
        $this->postJson('/cart/add', [
            'product_id' => $this->inStockProduct->id,
            'quantity' => 3,
        ]);

        $response = $this->postJson('/cart/clear');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'cart_count' => 0,
            ]);

        $this->assertNull(session('shopsphere_cart'));
    }

    public function test_cart_summary_endpoint(): void
    {
        $this->postJson('/cart/add', [
            'product_id' => $this->inStockProduct->id,
            'quantity' => 3,
        ]);

        $response = $this->getJson('/cart/summary');

        $response->assertOk()
            ->assertJson([
                'count' => 3,
                'subtotal' => 298.50,
                'formatted_subtotal' => '$298.50',
            ]);
    }

    public function test_cart_index_view_renders_cart_items(): void
    {
        $this->postJson('/cart/add', [
            'product_id' => $this->inStockProduct->id,
            'quantity' => 1,
        ]);

        $response = $this->get('/cart');

        $response->assertOk()
            ->assertSee('Mechanical Keyboard')
            ->assertSee('$99.50')
            ->assertSee('Apex Tech Store');
    }
}
