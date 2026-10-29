@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 fw-bold mb-1">Your Shopping Cart</h1>
            <p class="text-muted mb-0">Review items from your selected vendors before checkout.</p>
        </div>
    </div>

    @if(!empty($stockErrors))
        <div class="alert alert-warning mb-4">
            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle me-1"></i> Stock Notice</h6>
            <ul class="mb-0 small">
                @foreach($stockErrors as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="cart-container">
        @if(empty($items))
            <div class="card shadow-sm border-0 py-5 text-center">
                <div class="card-body">
                    <i class="bi bi-cart-x text-muted display-4"></i>
                    <h4 class="mt-3 fw-bold">Your Cart is Empty</h4>
                    <p class="text-muted">Looks like you haven't added any products yet.</p>
                    <a href="{{ route('catalog.index') }}" class="btn btn-primary mt-2">
                        <i class="bi bi-arrow-left me-1"></i> Start Shopping
                    </a>
                </div>
            </div>
        @else
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light small text-uppercase text-muted">
                                        <tr>
                                            <th scope="col" style="min-width: 250px;">Product</th>
                                            <th scope="col">Price</th>
                                            <th scope="col" style="width: 140px;">Quantity</th>
                                            <th scope="col">Subtotal</th>
                                            <th scope="col" class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cart-items-body">
                                        @foreach($items as $item)
                                            <tr id="cart-row-{{ $item['product_id'] }}">
                                                <td>
                                                    <div class="fw-semibold">
                                                        <a href="{{ route('catalog.show', $item['slug']) }}" class="text-dark text-decoration-none">
                                                            {{ $item['name'] }}
                                                        </a>
                                                    </div>
                                                    <span class="badge bg-light text-muted border small">
                                                        <i class="bi bi-shop me-1"></i>{{ $item['store_name'] }}
                                                    </span>
                                                </td>
                                                <td>${{ number_format($item['price'], 2) }}</td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control text-center cart-qty-input"
                                                               id="cart-qty-{{ $item['product_id'] }}"
                                                               value="{{ $item['quantity'] }}" min="1" max="{{ $item['stock'] }}"
                                                               data-product-id="{{ $item['product_id'] }}">
                                                        <button class="btn btn-outline-secondary btn-update-cart" type="button"
                                                                data-product-id="{{ $item['product_id'] }}" title="Update quantity">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="fw-bold" id="item-subtotal-{{ $item['product_id'] }}">
                                                    {{ $item['formatted_subtotal'] }}
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-cart"
                                                            data-product-id="{{ $item['product_id'] }}" title="Remove item">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                            <a href="{{ route('catalog.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                            </a>
                            <form action="{{ route('cart.clear') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash3 me-1"></i> Clear Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 sticky-top" style="top: 5rem;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 fw-semibold">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Items (<span id="cart-total-count">{{ $count }}</span>):</span>
                                <span class="fw-semibold" id="cart-subtotal">{{ $formattedSubtotal }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Standard Shipping:</span>
                                <span class="text-success fw-semibold">FREE</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="h5 fw-bold mb-0">Total:</span>
                                <span class="h5 fw-bold text-primary mb-0" id="cart-total-amount">{{ $formattedSubtotal }}</span>
                            </div>

                            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg w-100">
                                Proceed to Checkout <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Handle AJAX Quantity Updates
    document.querySelectorAll('.btn-update-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const qtyInput = document.getElementById('cart-qty-' + productId);
            const quantity = parseInt(qtyInput.value) || 1;

            button.disabled = true;

            fetch(`/cart/update/${productId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ quantity: quantity })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    ShopSphereCart.updateBadge(data.cart_count);
                    ShopSphereCart.showToast(data.message, false);

                    // Update subtotal for this item
                    const item = data.items.find(i => i.product_id == productId);
                    if (item) {
                        const subtotalEl = document.getElementById('item-subtotal-' + productId);
                        if (subtotalEl) subtotalEl.textContent = item.formatted_subtotal;
                    }

                    // Update totals
                    const totalCountEl = document.getElementById('cart-total-count');
                    const subtotalEl = document.getElementById('cart-subtotal');
                    const totalAmountEl = document.getElementById('cart-total-amount');

                    if (totalCountEl) totalCountEl.textContent = data.cart_count;
                    if (subtotalEl) subtotalEl.textContent = data.formatted_subtotal;
                    if (totalAmountEl) totalAmountEl.textContent = data.formatted_subtotal;
                }
            })
            .catch(() => ShopSphereCart.showToast('Failed to update cart item.', true))
            .finally(() => { button.disabled = false; });
        });
    });

    // Handle AJAX Item Removal
    document.querySelectorAll('.btn-remove-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            button.disabled = true;

            fetch(`/cart/remove/${productId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    ShopSphereCart.updateBadge(data.cart_count);
                    ShopSphereCart.showToast(data.message, false);

                    const row = document.getElementById('cart-row-' + productId);
                    if (row) row.remove();

                    if (data.cart_count === 0) {
                        window.location.reload();
                    } else {
                        const totalCountEl = document.getElementById('cart-total-count');
                        const subtotalEl = document.getElementById('cart-subtotal');
                        const totalAmountEl = document.getElementById('cart-total-amount');

                        if (totalCountEl) totalCountEl.textContent = data.cart_count;
                        if (subtotalEl) subtotalEl.textContent = data.formatted_subtotal;
                        if (totalAmountEl) totalAmountEl.textContent = data.formatted_subtotal;
                    }
                }
            })
            .catch(() => ShopSphereCart.showToast('Failed to remove item.', true))
            .finally(() => { button.disabled = false; });
        });
    });
});
</script>
@endpush
