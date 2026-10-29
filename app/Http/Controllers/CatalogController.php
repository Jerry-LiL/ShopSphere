<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only([
            'q',
            'category',
            'store',
            'min_price',
            'max_price',
            'in_stock',
            'sort',
        ]);

        $products = Product::active()
            ->with(['category', 'store'])
            ->filter($filters)
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();
        $stores = Store::where('is_active', true)->orderBy('name')->get();

        return view('catalog.index', [
            'products' => $products,
            'categories' => $categories,
            'stores' => $stores,
            'filters' => $filters,
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::with(['category', 'store.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Allow preview if owner vendor or admin, otherwise require active
        if (!$product->is_active) {
            $user = auth()->user();
            if (!$user || (!$user->isAdmin() && (!$user->isVendor() || $user->store?->id !== $product->store_id))) {
                abort(404);
            }
        }

        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category', 'store'])
            ->take(4)
            ->get();

        return view('catalog.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
