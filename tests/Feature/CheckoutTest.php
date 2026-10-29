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
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    protected User $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name' => 'Alice Customer',
            'email' => 'alice@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $vendor = User::create([
            'name' => 'Bob Vendor',
            'email' => 'bob@vendor.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_VENDOR,
        ]);

        $store = Store::create([
            'user_id' => $vendor->id,
            'name' => 'Bob Goods',
            'slug' => 'bob-goods',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Home',
            'slug' => 'home',
        ]);

        $this->product = Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Ceramic Mug',
            'slug' => 'ceramic-mug',
            'sku' => 'MUG-001',
            'price' => 18.00,
            'stock' => 10,
            'is_active' => true,
        ]);
    }

    public function test_checkout_redirects_if_cart_is_empty(): void
    {
        $response = $this->actingAs($this->customer)->get('/checkout');

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    public function test_checkout_page_renders_with_cart_items(): void
    {
        $this->postJson('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->customer)->get('/checkout');

        $response->assertOk()
            ->assertSee('Ceramic Mug')
            ->assertSee('$36.00');
    }

    public function test_placing_order_creates_order_and_items_and_decrements_stock(): void
    {
        Queue::fake();

        $this->postJson('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->customer)->post('/checkout', [
            'customer_name' => 'Alice Customer',
            'customer_email' => 'alice@test.com',
            'shipping_address' => '123 Pine St',
            'shipping_city' => 'Portland',
            'shipping_postal_code' => '97201',
            'shipping_phone' => '+1-555-0144',
            'payment_method' => 'cash_on_delivery',
            'notes' => 'Leave near porch',
        ]);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customer->id,
            'customer_email' => 'alice@test.com',
            'shipping_city' => 'Portland',
            'total_amount' => 36.00,
            'status' => Order::STATUS_PENDING,
        ]);

        $order = Order::where('customer_email', 'alice@test.com')->first();
        $this->assertNotNull($order);

        $response->assertRedirect(route('checkout.confirmation', $order->order_number));

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 18.00,
            'subtotal' => 36.00,
        ]);

        // Verify stock decremented: 10 - 2 = 8
        $this->assertEquals(8, $this->product->fresh()->stock);

        // Verify cart is cleared
        $this->assertEmpty(session('shopsphere_cart'));

        // Verify outgoing order webhook queued job dispatched
        Queue::assertPushed(SendOrderWebhookJob::class, function ($job) use ($order) {
            return $job->order->id === $order->id && $job->event === 'order.created';
        });
    }

    public function test_customer_can_cancel_pending_order_and_restores_stock(): void
    {
        $this->product->update(['stock' => 8]);

        $order = Order::create([
            'order_number' => 'ORD-20261029-TEST1',
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'shipping_address' => '123 Pine St',
            'shipping_city' => 'Portland',
            'shipping_postal_code' => '97201',
            'shipping_phone' => '+1-555-0144',
            'total_amount' => 36.00,
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => Order::PAYMENT_PENDING,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'store_id' => $this->product->store_id,
            'product_name' => $this->product->name,
            'price' => 18.00,
            'quantity' => 2,
            'subtotal' => 36.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->customer)
            ->post("/account/orders/{$order->order_number}/cancel");

        $response->assertSessionHas('success');

        $this->assertEquals(Order::STATUS_CANCELLED, $order->fresh()->status);
        // Restored 8 + 2 = 10
        $this->assertEquals(10, $this->product->fresh()->stock);
    }
}
