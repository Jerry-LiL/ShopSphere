<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Jobs\SendOrderWebhookJob;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with(['customer', 'items.store']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', "%{$term}%")
                  ->orWhere('customer_name', 'like', "%{$term}%")
                  ->orWhere('customer_email', 'like', "%{$term}%");
            });
        }

        $orders = $query->latest('id')->paginate(15)->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'items.store', 'items.product']);

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $statusChanged = $request->input('status') !== $order->status;

        $updateData = [
            'status' => $request->input('status'),
        ];

        if ($request->filled('payment_status')) {
            $updateData['payment_status'] = $request->input('payment_status');
        }

        $order->update($updateData);

        if ($statusChanged) {
            SendOrderWebhookJob::dispatch($order, 'order.updated');
        }

        return back()->with('success', "Order #{$order->order_number} status updated to '{$order->status}'.");
    }
}
