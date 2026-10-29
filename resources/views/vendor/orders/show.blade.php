@extends('layouts.app')

@section('title', 'Manage Order #' . $order->order_number)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Order #<span class="font-monospace text-primary">{{ $order->order_number }}</span></h1>
            <p class="text-muted mb-0">Order items fulfillment for {{ $store->name }} &bull; Placed {{ $order->created_at->format('M d, Y h:i A') }}</p>
        </div>
        <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Orders
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">Your Store Items in this Order</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                    <th>Fulfillment Status</th>
                                    <th class="text-end">Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vendorItems as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item->product_name }}</div>
                                            @if($item->product)
                                                <span class="text-muted small">SKU: {{ $item->product->sku }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>${{ number_format($item->price, 2) }}</td>
                                        <td class="fw-bold">${{ number_format($item->subtotal, 2) }}</td>
                                        <td>
                                            <span class="badge bg-secondary text-uppercase">{{ $item->status }}</span>
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('vendor.orders.update_item', $item) }}" method="POST" class="d-inline-flex gap-1">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" class="form-select form-select-sm" style="width: 130px;">
                                                    <option value="pending" {{ $item->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="processing" {{ $item->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                                    <option value="shipped" {{ $item->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                                    <option value="delivered" {{ $item->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                    <option value="cancelled" {{ $item->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">Shipping Destination</h5>
                </div>
                <div class="card-body">
                    <p class="fw-semibold mb-1">{{ $order->customer_name }}</p>
                    <p class="text-muted small mb-1">{{ $order->shipping_address }}</p>
                    <p class="text-muted small mb-1">{{ $order->shipping_city }}, {{ $order->shipping_postal_code }}</p>
                    <p class="text-muted small mb-2"><i class="bi bi-telephone me-1"></i>{{ $order->shipping_phone }}</p>
                    <p class="text-muted small mb-0"><i class="bi bi-envelope me-1"></i>{{ $order->customer_email }}</p>

                    @if($order->notes)
                        <hr>
                        <p class="small text-muted mb-0"><strong>Customer Notes:</strong> {{ $order->notes }}</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">Order Summary</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1 small">Platform Order Status: <span class="badge bg-primary text-uppercase">{{ $order->status }}</span></p>
                    <p class="mb-1 small">Payment Method: <strong class="text-capitalize">{{ str_replace('_', ' ', $order->payment_method) }}</strong></p>
                    <p class="mb-0 small">Payment Status: <span class="badge bg-secondary text-capitalize">{{ $order->payment_status }}</span></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
