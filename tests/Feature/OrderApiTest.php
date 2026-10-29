<?php

namespace Tests\Feature;

use App\Jobs\SendOrderWebhookJob;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    protected User $customer1;
    protected User $customer2;
    protected User $vendor;
    protected User $admin;
    protected Store $store;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer1 = User::create(['name' => 'Cust 1', 'email' => 'c1@orderapi.test', 'password' => Hash::make('password'), 'role' => User::ROLE_CUSTOMER]);
        $this->customer2 = User::create(['name' => 'Cust 2', 'email' => 'c2@orderapi.test', 'password' => Hash::make('password'), 'role' => User::ROLE_CUSTOMER]);
        $this->vendor = User::create(['name' => 'Ven', 'email' => 'v@orderapi.test', 'password' => Hash::make('password'), 'role' => User::ROLE_VENDOR]);
        $this->admin = User::create(['name' => 'Adm', 'email' => 'a@orderapi.test', 'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN]);

        $this->store = Store::create(['user_id' => $this->vendor->id, 'name' => 'Audio Store', 'slug' => 'audio-store', 'is_active' => true]);
        $category = Category::create(['name' => 'Audio', 'slug' => 'audio']);

        $this->product = Product::create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
            'name' => 'Studio Monitor Speaker',
            'slug' => 'studio-monitor-speaker',
            'sku' => 'AUD-SPK-01',
            'price' => 150.00,
            'stock' => 12,
            'is_active' => true,
        ]);
    }

    public function test_customer_can_place_order_via_api(): void
    {
        Queue::fake();
        Sanctum::actingAs($this->customer1);

        $response = $this->postJson('/api/v1/orders', [
            'shipping_address' => '500 Market St',
            'shipping_city' => 'San Francisco',
            'shipping_postal_code' => '94105',
            'shipping_phone' => '+1-555-8888',
            'payment_method' => 'card',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.customer_id', $this->customer1->id)
            ->assertJsonPath('order.total_amount', '300.00');

        $this->assertEquals(10, $this->product->fresh()->stock);

        Queue::assertPushed(SendOrderWebhookJob::class);
    }

    public function test_customer_can_view_own_orders_via_api(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-API-001',
            'customer_id' => $this->customer1->id,
            'customer_name' => $this->customer1->name,
            'customer_email' => $this->customer1->email,
            'shipping_address' => '500 Market St',
            'shipping_city' => 'San Francisco',
            'shipping_postal_code' => '94105',
            'shipping_phone' => '+1-555-8888',
            'total_amount' => 150.00,
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'card',
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        Sanctum::actingAs($this->customer1);

        $response = $this->getJson('/api/v1/orders');

        $response->assertOk()
            ->assertJsonPath('data.0.order_number', 'ORD-API-001');
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-API-SECRET',
            'customer_id' => $this->customer1->id,
            'customer_name' => $this->customer1->name,
            'customer_email' => $this->customer1->email,
            'shipping_address' => '500 Market St',
            'shipping_city' => 'San Francisco',
            'shipping_postal_code' => '94105',
            'shipping_phone' => '+1-555-8888',
            'total_amount' => 150.00,
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'card',
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        Sanctum::actingAs($this->customer2);

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_vendor_can_update_order_status_for_order_with_their_items(): void
    {
        Queue::fake();

        $order = Order::create([
            'order_number' => 'ORD-API-VENDOR',
            'customer_id' => $this->customer1->id,
            'customer_name' => $this->customer1->name,
            'customer_email' => $this->customer1->email,
            'shipping_address' => '500 Market St',
            'shipping_city' => 'San Francisco',
            'shipping_postal_code' => '94105',
            'shipping_phone' => '+1-555-8888',
            'total_amount' => 150.00,
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'card',
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'store_id' => $this->store->id,
            'product_name' => $this->product->name,
            'price' => 150.00,
            'quantity' => 1,
            'subtotal' => 150.00,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->vendor);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/status", [
            'status' => Order::STATUS_PROCESSING,
        ]);

        $response->assertOk()
            ->assertJsonPath('order.status', Order::STATUS_PROCESSING);

        $this->assertEquals(Order::STATUS_PROCESSING, $order->fresh()->status);

        Queue::assertPushed(SendOrderWebhookJob::class, function ($job) use ($order) {
            return $job->order->id === $order->id && $job->event === 'order.updated';
        });
    }

    public function test_customer_cannot_update_order_status(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-API-CUST-LOCK',
            'customer_id' => $this->customer1->id,
            'customer_name' => $this->customer1->name,
            'customer_email' => $this->customer1->email,
            'shipping_address' => '500 Market St',
            'shipping_city' => 'San Francisco',
            'shipping_postal_code' => '94105',
            'shipping_phone' => '+1-555-8888',
            'total_amount' => 150.00,
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'card',
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        Sanctum::actingAs($this->customer1);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/status", [
            'status' => Order::STATUS_DELIVERED,
        ]);

        $response->assertStatus(403);
    }
}
