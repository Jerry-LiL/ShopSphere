<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendOrderWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * Exponential backoff delays in seconds for retry attempts.
     *
     * @var array<int, int>
     */
    public array $backoff = [5, 15, 60];

    public Order $order;
    public string $event;

    public function __construct(Order $order, string $event = 'order.created')
    {
        $this->order = $order;
        $this->event = $event;
    }

    public function handle(): void
    {
        $this->order->loadMissing(['customer', 'items.store']);

        $endpoints = WebhookEndpoint::where('is_active', true)
            ->where(function ($query) {
                $query->where('event', $this->event)
                    ->orWhere('event', '*');
            })
            ->get();

        if ($endpoints->isEmpty()) {
            return;
        }

        $payload = [
            'event' => $this->event,
            'timestamp' => now()->toIso8601String(),
            'order' => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'status' => $this->order->status,
                'payment_method' => $this->order->payment_method,
                'payment_status' => $this->order->payment_status,
                'total_amount' => (float) $this->order->total_amount,
                'customer' => [
                    'id' => $this->order->customer_id,
                    'name' => $this->order->customer_name,
                    'email' => $this->order->customer_email,
                    'phone' => $this->order->shipping_phone,
                ],
                'shipping' => [
                    'address' => $this->order->shipping_address,
                    'city' => $this->order->shipping_city,
                    'postal_code' => $this->order->shipping_postal_code,
                ],
                'items' => $this->order->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'price' => (float) $item->price,
                        'quantity' => $item->quantity,
                        'subtotal' => (float) $item->subtotal,
                        'store_id' => $item->store_id,
                        'store_name' => $item->store ? $item->store->name : null,
                    ];
                })->all(),
            ],
        ];

        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $hasFailure = false;
        $lastErrorMessage = null;

        foreach ($endpoints as $endpoint) {
            $signature = hash_hmac('sha256', $payloadJson, $endpoint->secret);

            $delivery = WebhookDelivery::create([
                'webhook_endpoint_id' => $endpoint->id,
                'event' => $this->event,
                'payload' => $payload,
                'status' => 'pending',
                'attempts' => $this->attempts(),
            ]);

            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-ShopSphere-Signature' => $signature,
                        'X-ShopSphere-Event' => $this->event,
                        'User-Agent' => 'ShopSphere-Webhook/1.0',
                    ])
                    ->withBody($payloadJson, 'application/json')
                    ->post($endpoint->url);

                if ($response->successful()) {
                    $delivery->update([
                        'status' => 'success',
                        'response_status' => $response->status(),
                        'response_body' => substr($response->body(), 0, 1000),
                    ]);
                } else {
                    $hasFailure = true;
                    $lastErrorMessage = "HTTP {$response->status()} returned by endpoint";
                    $delivery->update([
                        'status' => 'failed',
                        'response_status' => $response->status(),
                        'response_body' => substr($response->body(), 0, 1000),
                        'error_message' => $lastErrorMessage,
                    ]);
                }
            } catch (\Throwable $e) {
                $hasFailure = true;
                $lastErrorMessage = $e->getMessage();
                $delivery->update([
                    'status' => 'failed',
                    'error_message' => substr($lastErrorMessage, 0, 1000),
                ]);
            }
        }

        if ($hasFailure) {
            throw new \RuntimeException("Webhook dispatch failed on attempt {$this->attempts()}: {$lastErrorMessage}");
        }
    }
}
