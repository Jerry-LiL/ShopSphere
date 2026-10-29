<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Webhook\StoreWebhookEndpointRequest;
use App\Jobs\SendOrderWebhookJob;
use App\Models\Order;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminWebhookController extends Controller
{
    public function index(Request $request): View
    {
        $endpoints = WebhookEndpoint::withCount('deliveries')->latest('id')->get();

        $deliveries = WebhookDelivery::with('endpoint')
            ->latest('id')
            ->paginate(15);

        return view('admin.webhooks.index', [
            'endpoints' => $endpoints,
            'deliveries' => $deliveries,
        ]);
    }

    public function store(StoreWebhookEndpointRequest $request): RedirectResponse
    {
        WebhookEndpoint::create([
            'url' => $request->input('url'),
            'secret' => $request->input('secret'),
            'event' => $request->input('event', 'order.created'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Webhook endpoint registered successfully.');
    }

    public function destroy(WebhookEndpoint $endpoint): RedirectResponse
    {
        $endpoint->delete();

        return back()->with('success', 'Webhook endpoint removed.');
    }

    public function testDispatch(Request $request, WebhookEndpoint $endpoint): RedirectResponse
    {
        $order = Order::with('items')->latest('id')->first();

        if (!$order) {
            return back()->with('error', 'Cannot test webhook: no orders exist in the database yet.');
        }

        SendOrderWebhookJob::dispatch($order, $endpoint->event === '*' ? 'order.created' : $endpoint->event);

        return back()->with('success', "Queued test webhook dispatch for order #{$order->order_number}.");
    }
}
