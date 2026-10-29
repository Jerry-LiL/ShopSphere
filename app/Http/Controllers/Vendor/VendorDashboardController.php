<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;

        if (!$store) {
            abort(404, 'Vendor store profile not found.');
        }

        $totalProducts = Product::where('store_id', $store->id)->count();
        $activeProducts = Product::where('store_id', $store->id)->where('is_active', true)->count();

        $orderItems = OrderItem::where('store_id', $store->id)->get();
        $totalSales = (float) $orderItems->sum('subtotal');
        $totalOrdersCount = OrderItem::where('store_id', $store->id)
            ->distinct('order_id')
            ->count('order_id');

        $recentItems = OrderItem::where('store_id', $store->id)
            ->with(['order', 'product'])
            ->latest('id')
            ->take(8)
            ->get();

        return view('vendor.dashboard', [
            'store' => $store,
            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'totalSales' => $totalSales,
            'totalOrdersCount' => $totalOrdersCount,
            'recentItems' => $recentItems,
        ]);
    }
}
