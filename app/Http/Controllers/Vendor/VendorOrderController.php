<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorOrderController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;
        if (!$store) {
            abort(404, 'Store not found');
        }

        // Get orders that have items for this vendor
        $orders = Order::whereHas('items', function ($query) use ($store) {
            $query->where('store_id', $store->id);
        })
        ->with(['items' => function ($query) use ($store) {
            $query->where('store_id', $store->id)->with('product');
        }, 'customer'])
        ->latest('id')
        ->paginate(15);

        return view('vendor.orders.index', [
            'store' => $store,
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $store = $request->user()->store;
        if (!$store) {
            abort(404, 'Store not found');
        }

        $this->authorize('view', $order);

        $vendorItems = $order->items()
            ->where('store_id', $store->id)
            ->with('product')
            ->get();

        if ($vendorItems->isEmpty()) {
            abort(403, 'No items belonging to your store in this order.');
        }

        return view('vendor.orders.show', [
            'store' => $store,
            'order' => $order,
            'vendorItems' => $vendorItems,
        ]);
    }

    public function updateItemStatus(Request $request, OrderItem $item): RedirectResponse
    {
        $store = $request->user()->store;
        if (!$store || $item->store_id !== $store->id) {
            abort(403, 'Unauthorized item update.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,processing,shipped,delivered,cancelled'],
        ]);

        $item->update(['status' => $validated['status']]);

        return back()->with('success', "Item fulfillment status updated to '{$validated['status']}'.");
    }
}
