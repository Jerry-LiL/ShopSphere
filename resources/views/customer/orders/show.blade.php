@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Order #<span class="font-monospace text-primary">{{ $order->order_number }}</span></h1>
            <p class="text-muted mb-0">Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
        </div>
        <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Orders
        </a>
    </div>

    <!-- Status Tracking Timeline -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-semibold">Shipment Tracker</h5>
        </div>
        <div class="card-body py-4">
            @if($order->status === 'cancelled')
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-x-octagon-fill me-2"></i> This order was cancelled. Items have been restocked.
                </div>
            @else
                @php
                    $steps = ['pending' => 'Pending Confirmation', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
                    $keys = array_keys($steps);
                    $currentIndex = array_search($order->status, $keys);
                    if ($currentIndex === false) $currentIndex = 0;
                    $percent = ($currentIndex / (count($keys) - 1)) * 100;
                @endphp
                <div class="position-relative m-4">
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%;"></div>
                    </div>
                    <div class="d-flex justify-content-between position-absolute top-0 start-0 w-100 translate-middle-y">
                        @foreach($keys as $idx => $stepKey)
                            @php
                                $isCompleted = $idx <= $currentIndex;
                                $isCurrent = $idx === $currentIndex;
                            @endphp
                            <div class="text-center" style="width: 100px; margin-left: -50px; margin-right: -50px;">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $isCompleted ? 'bg-success text-white' : 'bg-light text-muted border' }}"
                                     style="width: 32px; height: 32px; font-size: 14px; font-weight: bold;">
                                    @if($isCompleted)
                                        <i class="bi bi-check-lg"></i>
                                    @else
                                        {{ $idx + 1 }}
                                    @endif
                                </div>
                                <div class="small mt-2 {{ $isCurrent ? 'fw-bold text-dark' : 'text-muted' }}">
                                    {{ $steps[$stepKey] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">Purchased Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th>Product</th>
                                    <th>Vendor</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">
                                                @if($item->product)
                                                    <a href="{{ route('catalog.show', $item->product->slug) }}" class="text-dark text-decoration-none">
                                                        {{ $item->product_name }}
                                                    </a>
                                                @else
                                                    {{ $item->product_name }}
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $item->store->name ?? 'Platform' }}</span>
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">${{ number_format($item->price, 2) }}</td>
                                        <td class="text-end fw-semibold">${{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold text-primary fs-6">${{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">Shipping Address</h5>
                </div>
                <div class="card-body">
                    <p class="fw-semibold mb-1">{{ $order->customer_name }}</p>
                    <p class="text-muted small mb-1">{{ $order->shipping_address }}</p>
                    <p class="text-muted small mb-1">{{ $order->shipping_city }}, {{ $order->shipping_postal_code }}</p>
                    <p class="text-muted small mb-2"><i class="bi bi-telephone me-1"></i>{{ $order->shipping_phone }}</p>
                    <p class="text-muted small mb-0"><i class="bi bi-envelope me-1"></i>{{ $order->customer_email }}</p>

                    @if($order->notes)
                        <hr>
                        <p class="small text-muted mb-0"><strong>Note:</strong> {{ $order->notes }}</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">Payment Details</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1 small">
                        Method: <strong class="text-capitalize">{{ str_replace('_', ' ', $order->payment_method) }}</strong>
                    </p>
                    <p class="mb-0 small">
                        Payment Status: <span class="badge bg-secondary text-capitalize">{{ $order->payment_status }}</span>
                    </p>
                </div>
            </div>

            @if($order->canCancel())
                <div class="card shadow-sm border-danger border-opacity-25 mb-4">
                    <div class="card-body text-center">
                        <p class="small text-muted mb-2">Order still pending processing? You can cancel and restock items.</p>
                        <form action="{{ route('customer.orders.cancel', $order->order_number) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle me-1"></i> Cancel Order
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
