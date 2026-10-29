<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->with(['items'])
            ->latest('id')
            ->paginate(10);

        return view('customer.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, string $orderNumber): View
    {
        $order = Order::with(['items.store', 'items.product'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $this->authorize('view', $order);

        return view('customer.orders.show', [
            'order' => $order,
        ]);
    }

    public function cancel(Request $request, string $orderNumber): RedirectResponse
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();

        $this->authorize('cancel', $order);

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => Order::STATUS_CANCELLED,
            ]);

            // Restore product stock
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                }
            }
        });

        return back()->with('success', 'Order has been cancelled and items returned to stock.');
    }
}
