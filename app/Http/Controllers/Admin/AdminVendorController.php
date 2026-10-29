<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminVendorController extends Controller
{
    public function index(Request $request): View
    {
        $stores = Store::with(['user'])
            ->withCount('products')
            ->latest('id')
            ->paginate(15);

        return view('admin.vendors.index', [
            'stores' => $stores,
        ]);
    }

    public function toggleStatus(Store $store): RedirectResponse
    {
        $store->update([
            'is_active' => !$store->is_active,
        ]);

        $status = $store->is_active ? 'activated' : 'suspended';
        return back()->with('success', "Store '{$store->name}' has been {$status}.");
    }
}
