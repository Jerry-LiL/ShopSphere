<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    protected User $vendor1;
    protected User $vendor2;
    protected User $customer;
    protected User $admin;
    protected Store $store1;
    protected Store $store2;
    protected Category $category;
    protected Product $product1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendor1 = User::create(['name' => 'Vendor 1', 'email' => 'v1@api.test', 'password' => Hash::make('password'), 'role' => User::ROLE_VENDOR]);
        $this->vendor2 = User::create(['name' => 'Vendor 2', 'email' => 'v2@api.test', 'password' => Hash::make('password'), 'role' => User::ROLE_VENDOR]);
        $this->customer = User::create(['name' => 'Customer', 'email' => 'c@api.test', 'password' => Hash::make('password'), 'role' => User::ROLE_CUSTOMER]);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@api.test', 'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN]);

        $this->store1 = Store::create(['user_id' => $this->vendor1->id, 'name' => 'Store 1', 'slug' => 'store-1', 'is_active' => true]);
        $this->store2 = Store::create(['user_id' => $this->vendor2->id, 'name' => 'Store 2', 'slug' => 'store-2', 'is_active' => true]);

        $this->category = Category::create(['name' => 'Gadgets', 'slug' => 'gadgets']);

        $this->product1 = Product::create([
            'store_id' => $this->store1->id,
            'category_id' => $this->category->id,
            'name' => 'Smart Watch Pro',
            'slug' => 'smart-watch-pro',
            'sku' => 'SW-900',
            'description' => 'Fitness and heart tracking',
            'price' => 199.99,
            'stock' => 20,
            'is_active' => true,
        ]);
    }

    public function test_public_can_list_products_via_api(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price', 'slug', 'sku', 'store', 'category']
                ],
                'current_page',
                'total',
            ]);
    }

    public function test_public_can_get_single_product_by_slug_or_id(): void
    {
        $response = $this->getJson("/api/v1/products/{$this->product1->slug}");

        $response->assertOk()
            ->assertJsonPath('product.name', 'Smart Watch Pro')
            ->assertJsonPath('product.sku', 'SW-900');
    }

    public function test_unauthenticated_user_cannot_create_product(): void
    {
        $response = $this->postJson('/api/v1/products', [
            'name' => 'New Product',
            'sku' => 'NP-001',
            'price' => 50,
            'stock' => 10,
            'category_id' => $this->category->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_customer_cannot_create_product(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Customer Product Attempt',
            'sku' => 'CP-001',
            'price' => 50,
            'stock' => 10,
            'category_id' => $this->category->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_vendor_can_create_product_for_their_store(): void
    {
        Sanctum::actingAs($this->vendor1);

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Bluetooth Earbuds',
            'sku' => 'BE-100',
            'price' => 79.00,
            'stock' => 30,
            'category_id' => $this->category->id,
            'description' => 'Compact wireless earbuds with charging case',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('product.name', 'Bluetooth Earbuds')
            ->assertJsonPath('product.store_id', $this->store1->id);

        $this->assertDatabaseHas('products', [
            'sku' => 'BE-100',
            'store_id' => $this->store1->id,
        ]);
    }

    public function test_vendor_can_update_their_own_product(): void
    {
        Sanctum::actingAs($this->vendor1);

        $response = $this->putJson("/api/v1/products/{$this->product1->id}", [
            'name' => 'Smart Watch Pro Max',
            'price' => 249.99,
        ]);

        $response->assertOk()
            ->assertJsonPath('product.name', 'Smart Watch Pro Max')
            ->assertJsonPath('product.price', '249.99');

        $this->assertEquals(249.99, (float) $this->product1->fresh()->price);
    }

    public function test_vendor_cannot_update_another_vendors_product(): void
    {
        Sanctum::actingAs($this->vendor2);

        $response = $this->putJson("/api/v1/products/{$this->product1->id}", [
            'name' => 'Unauthorized Hijack',
            'price' => 1.00,
        ]);

        $response->assertStatus(403);
    }

    public function test_vendor_can_delete_their_own_product(): void
    {
        Sanctum::actingAs($this->vendor1);

        $response = $this->deleteJson("/api/v1/products/{$this->product1->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('products', ['id' => $this->product1->id]);
    }

    public function test_admin_can_update_any_product(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->patchJson("/api/v1/products/{$this->product1->id}", [
            'price' => 179.99,
            'is_active' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('product.price', '179.99')
            ->assertJsonPath('product.is_active', false);
    }
}
