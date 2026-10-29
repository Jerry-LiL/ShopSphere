<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected const SESSION_KEY = 'shopsphere_cart';

    public function getCart(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function add(Product $product, int $quantity = 1): array
    {
        if ($quantity <= 0) {
            $quantity = 1;
        }

        $cart = $this->getCart();
        $id = (string) $product->id;

        $currentQty = isset($cart[$id]) ? $cart[$id]['quantity'] : 0;
        $newQty = $currentQty + $quantity;

        // Cap at available stock
        if ($newQty > $product->stock) {
            $newQty = $product->stock;
        }

        $cart[$id] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'price' => (float) $product->price,
            'quantity' => $newQty,
            'store_name' => $product->store ? $product->store->name : 'Platform',
            'stock' => $product->stock,
        ];

        Session::put(self::SESSION_KEY, $cart);

        return $this->summary();
    }

    public function update(int $productId, int $quantity): array
    {
        $cart = $this->getCart();
        $id = (string) $productId;

        if (!isset($cart[$id])) {
            return $this->summary();
        }

        if ($quantity <= 0) {
            return $this->remove($productId);
        }

        $product = Product::find($productId);
        if ($product && $quantity > $product->stock) {
            $quantity = $product->stock;
        }

        $cart[$id]['quantity'] = $quantity;
        if ($product) {
            $cart[$id]['stock'] = $product->stock;
            $cart[$id]['price'] = (float) $product->price;
        }

        Session::put(self::SESSION_KEY, $cart);

        return $this->summary();
    }

    public function remove(int $productId): array
    {
        $cart = $this->getCart();
        $id = (string) $productId;

        unset($cart[$id]);
        Session::put(self::SESSION_KEY, $cart);

        return $this->summary();
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function items(): array
    {
        $cart = $this->getCart();
        $items = [];

        foreach ($cart as $item) {
            $subtotal = round($item['price'] * $item['quantity'], 2);
            $items[] = array_merge($item, [
                'subtotal' => $subtotal,
                'formatted_price' => '$' . number_format($item['price'], 2),
                'formatted_subtotal' => '$' . number_format($subtotal, 2),
            ]);
        }

        return $items;
    }

    public function count(): int
    {
        $cart = $this->getCart();
        $total = 0;
        foreach ($cart as $item) {
            $total += (int) $item['quantity'];
        }
        return $total;
    }

    public function subtotal(): float
    {
        $cart = $this->getCart();
        $total = 0.0;
        foreach ($cart as $item) {
            $total += ((float) $item['price']) * ((int) $item['quantity']);
        }
        return round($total, 2);
    }

    public function formattedSubtotal(): string
    {
        return '$' . number_format($this->subtotal(), 2);
    }

    public function summary(): array
    {
        return [
            'count' => $this->count(),
            'subtotal' => $this->subtotal(),
            'formatted_subtotal' => $this->formattedSubtotal(),
            'items' => $this->items(),
        ];
    }

    public function validateStock(): array
    {
        $cart = $this->getCart();
        $errors = [];

        foreach ($cart as $id => $item) {
            $product = Product::find($item['product_id']);
            if (!$product || !$product->is_active) {
                $errors[] = "Product '{$item['name']}' is no longer available.";
            } elseif ($product->stock < $item['quantity']) {
                $errors[] = "Insufficient stock for '{$item['name']}' (available: {$product->stock}).";
            }
        }

        return $errors;
    }
}
