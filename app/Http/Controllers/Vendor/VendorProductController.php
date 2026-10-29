<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VendorProductController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;
        if (!$store) {
            abort(404, 'Store not found');
        }

        $query = Product::where('store_id', $store->id)->with('category');

        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $products = $query->latest('id')->paginate(15)->withQueryString();

        return view('vendor.products.index', [
            'store' => $store,
            'products' => $products,
        ]);
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('vendor.products.create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $store = $request->user()->store;
        $name = $request->input('name');
        $slug = Str::slug($name . '-' . Str::random(5));

        Product::create([
            'store_id' => $store->id,
            'category_id' => $request->input('category_id'),
            'name' => $name,
            'slug' => $slug,
            'sku' => $request->input('sku'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'stock' => $request->input('stock'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $categories = Category::orderBy('name')->get();

        return view('vendor.products.edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = [
            'category_id' => $request->input('category_id', $product->category_id),
            'name' => $request->input('name', $product->name),
            'sku' => $request->input('sku', $product->sku),
            'description' => $request->input('description', $product->description),
            'price' => $request->input('price', $product->price),
            'stock' => $request->input('stock', $product->stock),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $product->is_active,
        ];

        if ($request->filled('name') && $request->input('name') !== $product->name) {
            $data['slug'] = Str::slug($request->input('name') . '-' . Str::random(4));
        }

        $product->update($data);

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()->route('vendor.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
