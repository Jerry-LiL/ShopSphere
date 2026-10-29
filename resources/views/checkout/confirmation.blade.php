@extends('layouts.app')

@section('title', 'Order Confirmation #' . $order->order_number)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 text-center p-4 mb-4">
                <div class="text-success mb-3">
                    <i class="bi bi-check-circle-fill display-3"></i>
                </div>
                <h1 class="h3 fw-bold mb-1">Thank You For Your Order!</h1>
                <p class="text-muted">Your order has been received and queued for dispatch with our vendor partners.</p>
                <div class="d-inline-block bg-light border rounded px-3 py-2 my-2">
                    Order Reference: <strong class="text-primary font-monospace fs-5">{{ $order->order_number }}</strong>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold">Order Details</h5>
                    <span class="badge bg-primary text-uppercase">{{ $order->status }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-4 mb-4">
                        <div class="col-sm-6">
                            <h6 class="text-uppercase text-muted small fw-bold">Customer &amp; Shipping</h6>
                            <p class="mb-1 fw-semibold">{{ $order->customer_name }}</p>
                            <p class="mb-1 text-muted small">{{ $order->customer_email }}</p>
                            <p class="mb-1 text-muted small">{{ $order->shipping_phone }}</p>
                            <p class="mb-0 text-muted small">
                                {{ $order->shipping_address }}, {{ $order->shipping_city }} {{ $order->shipping_postal_code }}
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <h6 class="text-uppercase text-muted small fw-bold">Payment &amp; Status</h6>
                            <p class="mb-1">Method: <strong class="text-capitalize">{{ str_replace('_', ' ', $order->payment_method) }}</strong></p>
                            <p class="mb-1">Payment Status: <span class="badge bg-secondary text-capitalize">{{ $order->payment_status }}</span></p>
                            <p class="mb-0 text-muted small">Placed: {{ $order->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>

                    <h6 class="text-uppercase text-muted small fw-bold mb-3">Items Purchased</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>Item</th>
                                    <th>Vendor Store</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="fw-semibold">{{ $item->product_name }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $item->store->name ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">${{ number_format($item->price, 2) }}</td>
                                        <td class="text-end fw-semibold">${{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total Amount:</td>
                                    <td class="text-end fw-bold text-primary fs-6">${{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-3 text-center">
                    @auth
                        @if(auth()->user()->isCustomer())
                            <a href="{{ route('customer.orders.show', $order->order_number) }}" class="btn btn-primary me-2">
                                <i class="bi bi-clock-history me-1"></i> Track Order in Account
                            </a>
                        @endif
                    @endauth
                    <a href="{{ route('catalog.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
