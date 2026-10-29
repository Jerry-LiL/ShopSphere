<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\WebhookDelivery;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalRevenue = (float) Order::where('payment_status', Order::PAYMENT_PAID)->sum('total_amount');
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalVendors = Store::count();
        $totalCustomers = User::where('role', User::ROLE_CUSTOMER)->count();

        $recentOrders = Order::with('customer')
            ->latest('id')
            ->take(8)
            ->get();

        $recentDeliveries = WebhookDelivery::with('endpoint')
            ->latest('id')
            ->take(6)
            ->get();

        return view('admin.dashboard', [
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'totalProducts' => $totalProducts,
            'totalVendors' => $totalVendors,
            'totalCustomers' => $totalCustomers,
            'recentOrders' => $recentOrders,
            'recentDeliveries' => $recentDeliveries,
        ]);
    }
}
