<?php

namespace Tests\Feature;

use App\Jobs\SendOrderWebhookJob;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookJobTest extends TestCase
{
    protected Order $order;
    protected WebhookEndpoint $endpoint;

    protected function setUp(): void
    {
        parent::setUp();

        $customer = User::create(['name' => 'John Webhook', 'email' => 'john@wh.test', 'password' => Hash::make('password'), 'role' => User::ROLE_CUSTOMER]);
        $vendor = User::create(['name' => 'V Webhook', 'email' => 'v@wh.test', 'password' => Hash::make('password'), 'role' => User::ROLE_VENDOR]);

        $store = Store::create(['user_id' => $vendor->id, 'name' => 'Wh Store', 'slug' => 'wh-store', 'is_active' => true]);
        $category = Category::create(['name' => 'Misc', 'slug' => 'misc']);

        $product = Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Webhook Test Item',
            'slug' => 'webhook-test-item',
            'sku' => 'WH-001',
            'price' => 50.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->order = Order::create([
            'order_number' => 'ORD-WH-1001',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'shipping_address' => '404 Webhook Way',
            'shipping_city' => 'Austin',
            'shipping_postal_code' => '78701',
            'shipping_phone' => '+1-555-4321',
            'total_amount' => 50.00,
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'card',
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'store_id' => $store->id,
            'product_name' => $product->name,
            'price' => 50.00,
            'quantity' => 1,
            'subtotal' => 50.00,
            'status' => 'pending',
        ]);

        $this->endpoint = WebhookEndpoint::create([
            'url' => 'https://external-service.test/webhook',
            'secret' => 'super_secret_webhook_key_123',
            'event' => 'order.created',
            'is_active' => true,
        ]);
    }

    public function test_job_has_configured_tries_and_exponential_backoff(): void
    {
        $job = new SendOrderWebhookJob($this->order, 'order.created');

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([5, 15, 60], $job->backoff);
    }

    public function test_webhook_delivers_signed_payload_and_records_success(): void
    {
        Http::fake([
            'https://external-service.test/webhook' => Http::response(['received' => true], 200),
        ]);

        $job = new SendOrderWebhookJob($this->order, 'order.created');
        $job->handle();

        Http::assertSent(function ($request) {
            $hasHeader = $request->hasHeader('X-ShopSphere-Signature');
            $hasEvent = $request->header('X-ShopSphere-Event')[0] === 'order.created';

            $expectedSignature = hash_hmac('sha256', $request->body(), 'super_secret_webhook_key_123');
            $signatureMatches = $request->header('X-ShopSphere-Signature')[0] === $expectedSignature;

            $data = json_decode($request->body(), true);
            $hasOrderNumber = ($data['order']['order_number'] ?? '') === 'ORD-WH-1001';

            return $hasHeader && $hasEvent && $signatureMatches && $hasOrderNumber;
        });

        $this->assertDatabaseHas('webhook_deliveries', [
            'webhook_endpoint_id' => $this->endpoint->id,
            'event' => 'order.created',
            'status' => 'success',
            'response_status' => 200,
        ]);
    }

    public function test_webhook_records_failed_delivery_and_throws_for_queue_retry(): void
    {
        Http::fake([
            'https://external-service.test/webhook' => Http::response('Internal Server Error', 500),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Webhook dispatch failed/i');

        try {
            $job = new SendOrderWebhookJob($this->order, 'order.created');
            $job->handle();
        } finally {
            $this->assertDatabaseHas('webhook_deliveries', [
                'webhook_endpoint_id' => $this->endpoint->id,
                'status' => 'failed',
                'response_status' => 500,
            ]);
        }
    }

    public function test_inactive_endpoint_is_not_called(): void
    {
        $this->endpoint->update(['is_active' => false]);

        Http::fake();

        $job = new SendOrderWebhookJob($this->order, 'order.created');
        $job->handle();

        Http::assertNothingSent();
        $this->assertEquals(0, WebhookDelivery::count());
    }
}
