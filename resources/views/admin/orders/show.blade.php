@extends('layouts.app')

@section('title', 'Admin - Order #' . $order->order_number)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Order #<span class="font-monospace text-primary">{{ $order->order_number }}</span></h1>
            <p class="text-muted mb-0">Platform order inspection and status control &bull; Placed {{ $order->created_at->format('M d, Y h:i A') }}</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Orders
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">Order Items Across Vendors</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Vendor Store</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                    <th>Item Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item->product_name }}</div>
                                            @if($item->product)
                                                <span class="text-muted small">SKU: {{ $item->product->sku }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $item->store->name ?? 'Platform' }}</span>
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>${{ number_format($item->price, 2) }}</td>
                                        <td class="fw-bold">${{ number_format($item->subtotal, 2) }}</td>
                                        <td>
                                            <span class="badge bg-secondary text-uppercase small">{{ $item->status }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                    <td colspan="2" class="fw-bold text-primary fs-6">${{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Customer & Shipping Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">Shipping &amp; Recipient Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-muted fw-bold text-uppercase">Recipient Name</label>
                            <p class="fw-semibold mb-2">{{ $order->customer_name }}</p>

                            <label class="small text-muted fw-bold text-uppercase">Contact Email</label>
                            <p class="mb-2">{{ $order->customer_email }}</p>

                            <label class="small text-muted fw-bold text-uppercase">Phone</label>
                            <p class="mb-0">{{ $order->shipping_phone }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted fw-bold text-uppercase">Delivery Address</label>
                            <p class="mb-1">{{ $order->shipping_address }}</p>
                            <p class="mb-2">{{ $order->shipping_city }}, {{ $order->shipping_postal_code }}</p>

                            @if($order->notes)
                                <label class="small text-muted fw-bold text-uppercase">Customer Notes</label>
                                <p class="mb-0 small text-muted bg-light p-2 rounded">{{ $order->notes }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Control & Workflow Actions -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">Status &amp; Workflow Controls</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.update_status', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="status" class="form-label small fw-semibold text-uppercase text-muted">Order Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="payment_status" class="form-label small fw-semibold text-uppercase text-muted">Payment Status</label>
                            <select name="payment_status" id="payment_status" class="form-select">
                                <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Update Status &amp; Dispatch Webhook
                            </button>
                        </div>
                        <div class="form-text small mt-2 text-muted">
                            Updating order status automatically queues the outgoing <code>order.updated</code> webhook job with retry backoff.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
