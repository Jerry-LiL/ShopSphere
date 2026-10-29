<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
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

        $perPage = min((int) $request->input('per_page', 15), 50);

        $products = Product::active()
            ->with(['category', 'store'])
            ->filter($filters)
            ->paginate($perPage);

        return response()->json($products);
    }

    public function show(string $idOrSlug): JsonResponse
    {
        $product = Product::with(['category', 'store'])
            ->where(function ($query) use ($idOrSlug) {
                if (is_numeric($idOrSlug)) {
                    $query->where('id', (int) $idOrSlug);
                } else {
                    $query->where('slug', $idOrSlug);
                }
            })
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        if (!$product->is_active) {
            $user = auth('sanctum')->user();
            if (!$user || (!$user->isAdmin() && (!$user->isVendor() || $user->store?->id !== $product->store_id))) {
                return response()->json(['message' => 'Product not found.'], 404);
            }
        }

        return response()->json(['product' => $product]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $storeId = $request->input('store_id');
        } else {
            $store = $user->store;
            if (!$store) {
                return response()->json(['message' => 'Vendor store not found.'], 422);
            }
            $storeId = $store->id;
        }

        $name = $request->input('name');
        $slug = Str::slug($name . '-' . Str::random(5));

        $product = Product::create([
            'store_id' => $storeId,
            'category_id' => $request->input('category_id'),
            'name' => $name,
            'slug' => $slug,
            'sku' => $request->input('sku'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'stock' => $request->input('stock'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $product->load(['category', 'store']);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'product' => $product,
        ], 201);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $data = $request->only(['name', 'category_id', 'sku', 'description', 'price', 'stock', 'is_active']);

        if ($request->filled('name') && $request->input('name') !== $product->name) {
            $data['slug'] = Str::slug($request->input('name') . '-' . Str::random(4));
        }

        if ($request->user()->isAdmin() && $request->filled('store_id')) {
            $data['store_id'] = $request->input('store_id');
        }

        $product->update($data);
        $product->load(['category', 'store']);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'product' => $product,
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }
}
