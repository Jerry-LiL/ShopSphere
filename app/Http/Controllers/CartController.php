<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService)
    {
    }

    public function index(): View
    {
        return view('cart.index', [
            'items' => $this->cartService->items(),
            'subtotal' => $this->cartService->subtotal(),
            'formattedSubtotal' => $this->cartService->formattedSubtotal(),
            'count' => $this->cartService->count(),
            'stockErrors' => $this->cartService->validateStock(),
        ]);
    }

    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::active()->findOrFail($validated['product_id']);
        $quantity = (int) ($validated['quantity'] ?? 1);

        if ($product->stock < 1) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is out of stock.',
                ], 422);
            }
            return back()->with('error', 'Product is out of stock.');
        }

        $summary = $this->cartService->add($product, $quantity);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Added '{$product->name}' to cart.",
                'cart_count' => $summary['count'],
                'subtotal' => $summary['subtotal'],
                'formatted_subtotal' => $summary['formatted_subtotal'],
                'items' => $summary['items'],
            ]);
        }

        return back()->with('success', "Added '{$product->name}' to your cart.");
    }

    public function update(Request $request, int $productId): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $summary = $this->cartService->update($productId, (int) $validated['quantity']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart updated.',
                'cart_count' => $summary['count'],
                'subtotal' => $summary['subtotal'],
                'formatted_subtotal' => $summary['formatted_subtotal'],
                'items' => $summary['items'],
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Cart updated successfully.');
    }

    public function remove(Request $request, int $productId): JsonResponse|RedirectResponse
    {
        $summary = $this->cartService->remove($productId);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart.',
                'cart_count' => $summary['count'],
                'subtotal' => $summary['subtotal'],
                'formatted_subtotal' => $summary['formatted_subtotal'],
                'items' => $summary['items'],
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        $this->cartService->clear();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared.',
                'cart_count' => 0,
                'subtotal' => 0.0,
                'formatted_subtotal' => '$0.00',
                'items' => [],
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Cart cleared.');
    }

    public function summary(): JsonResponse
    {
        return response()->json($this->cartService->summary());
    }
}
