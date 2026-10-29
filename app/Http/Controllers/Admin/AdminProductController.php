<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['store', 'category']);

        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%");
            });
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', (int) $request->input('store_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        $products = $query->latest('id')->paginate(15)->withQueryString();

        $stores = Store::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', [
            'products' => $products,
            'stores' => $stores,
            'categories' => $categories,
        ]);
    }

    public function toggleActive(Product $product): RedirectResponse
    {
        $product->update([
            'is_active' => !$product->is_active,
        ]);

        $statusText = $product->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Product '{$product->name}' {$statusText}.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->delete();

        return back()->with('success', "Product '{$name}' deleted.");
    }
}
