<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Jobs\SendOrderWebhookJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(protected CartService $cartService)
    {
    }

    public function index(): View|RedirectResponse
    {
        $items = $this->cartService->items();

        if (empty($items)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $stockErrors = $this->cartService->validateStock();
        if (!empty($stockErrors)) {
            return redirect()->route('cart.index')->with('error', implode(' ', $stockErrors));
        }

        $user = auth()->user();

        return view('checkout.index', [
            'items' => $items,
            'subtotal' => $this->cartService->subtotal(),
            'formattedSubtotal' => $this->cartService->formattedSubtotal(),
            'user' => $user,
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $items = $this->cartService->items();

        if (empty($items)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $order = DB::transaction(function () use ($request, $items) {
            $user = $request->user();
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $user?->id,
                'customer_name' => $request->input('customer_name'),
                'customer_email' => $request->input('customer_email'),
                'shipping_address' => $request->input('shipping_address'),
                'shipping_city' => $request->input('shipping_city'),
                'shipping_postal_code' => $request->input('shipping_postal_code'),
                'shipping_phone' => $request->input('shipping_phone'),
                'total_amount' => $this->cartService->subtotal(),
                'status' => Order::STATUS_PENDING,
                'payment_method' => $request->input('payment_method'),
                'payment_status' => Order::PAYMENT_PENDING,
                'notes' => $request->input('notes'),
            ]);

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \RuntimeException("Insufficient stock for {$product->name}. Only {$product->stock} available.");
                }

                $product->decrement('stock', $item['quantity']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'store_id' => $product->store_id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => round($product->price * $item['quantity'], 2),
                    'status' => 'pending',
                ]);
            }

            return $order;
        });

        $this->cartService->clear();

        // Dispatch outgoing order webhook queued job
        SendOrderWebhookJob::dispatch($order, 'order.created');

        return redirect()->route('checkout.confirmation', $order->order_number)
            ->with('success', 'Your order has been placed successfully!');
    }

    public function confirmation(string $orderNumber): View
    {
        $order = Order::with(['items.store', 'customer'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('checkout.confirmation', [
            'order' => $order,
        ]);
    }
}
