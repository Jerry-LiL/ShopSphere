<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Jobs\SendOrderWebhookJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $orders = Order::with(['items.store', 'customer'])
                ->latest('id')
                ->paginate(15);
        } elseif ($user->isVendor()) {
            $store = $user->store;
            if (!$store) {
                return response()->json(['data' => []]);
            }

            $orders = Order::whereHas('items', function ($query) use ($store) {
                $query->where('store_id', $store->id);
            })
            ->with(['items' => function ($query) use ($store) {
                $query->where('store_id', $store->id)->with('product');
            }, 'customer'])
            ->latest('id')
            ->paginate(15);
        } else {
            $orders = $user->orders()
                ->with(['items.product', 'items.store'])
                ->latest('id')
                ->paginate(15);
        }

        return response()->json($orders);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $order->loadMissing(['items.store', 'items.product', 'customer']);

        $this->authorize('view', $order);

        return response()->json(['order' => $order]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_postal_code' => ['required', 'string', 'max:20'],
            'shipping_phone' => ['required', 'string', 'max:30'],
            'payment_method' => ['required', 'string', Rule::in(['cash_on_delivery', 'card', 'bank_transfer'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();

        $customerName = $validated['customer_name'] ?? $user?->name;
        $customerEmail = $validated['customer_email'] ?? $user?->email;

        if (!$customerName || !$customerEmail) {
            return response()->json(['message' => 'Customer name and email are required.'], 422);
        }

        try {
            $order = DB::transaction(function () use ($validated, $user, $customerName, $customerEmail) {
                $totalAmount = 0.0;
                $itemsToCreate = [];

                foreach ($validated['items'] as $itemData) {
                    $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);

                    if (!$product->is_active) {
                        throw new \RuntimeException("Product '{$product->name}' is not currently available.");
                    }

                    if ($product->stock < $itemData['quantity']) {
                        throw new \RuntimeException("Insufficient stock for '{$product->name}'. Available: {$product->stock}.");
                    }

                    $subtotal = round(((float) $product->price) * $itemData['quantity'], 2);
                    $totalAmount += $subtotal;

                    $product->decrement('stock', $itemData['quantity']);

                    $itemsToCreate[] = [
                        'product_id' => $product->id,
                        'store_id' => $product->store_id,
                        'product_name' => $product->name,
                        'price' => $product->price,
                        'quantity' => $itemData['quantity'],
                        'subtotal' => $subtotal,
                        'status' => 'pending',
                    ];
                }

                $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'customer_id' => $user?->id,
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'shipping_address' => $validated['shipping_address'],
                    'shipping_city' => $validated['shipping_city'],
                    'shipping_postal_code' => $validated['shipping_postal_code'],
                    'shipping_phone' => $validated['shipping_phone'],
                    'total_amount' => $totalAmount,
                    'status' => Order::STATUS_PENDING,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => Order::PAYMENT_PENDING,
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($itemsToCreate as $item) {
                    $item['order_id'] = $order->id;
                    OrderItem::create($item);
                }

                return $order;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $order->load(['items.store', 'customer']);

        SendOrderWebhookJob::dispatch($order, 'order.created');

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully.',
            'order' => $order,
        ], 201);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $this->authorize('updateStatus', $order);

        $updateData = ['status' => $request->input('status')];
        if ($request->filled('payment_status')) {
            $updateData['payment_status'] = $request->input('payment_status');
        }

        $order->update($updateData);

        SendOrderWebhookJob::dispatch($order, 'order.updated');

        return response()->json([
            'success' => true,
            'message' => 'Order status updated.',
            'order' => $order->fresh(['items.store', 'customer']),
        ]);
    }
}
