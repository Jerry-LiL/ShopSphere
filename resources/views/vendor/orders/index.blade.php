@extends('layouts.app')

@section('title', 'Vendor Orders')

@section('content')
<div class="container">
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Customer Orders</h1>
        <p class="text-muted mb-0">Orders containing items purchased from {{ $store->name }}.</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if($orders->isEmpty())
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-inbox display-4"></i>
                    <h5 class="mt-3 fw-bold">No Orders Placed Yet</h5>
                    <p class="mb-0">When customers place orders with your products, they will appear here.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th>Order Ref</th>
                                <th>Customer</th>
                                <th>Date Placed</th>
                                <th>Your Items</th>
                                <th>Store Subtotal</th>
                                <th>Overall Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                @php
                                    $storeTotal = $order->items->sum('subtotal');
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('vendor.orders.show', $order) }}" class="fw-semibold text-primary font-monospace text-decoration-none">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $order->customer_name }}</div>
                                        <span class="text-muted small">{{ $order->shipping_city }}</span>
                                    </td>
                                    <td class="text-muted small">{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>{{ $order->items->count() }} item(s)</td>
                                    <td class="fw-bold text-primary">${{ number_format($storeTotal, 2) }}</td>
                                    <td>
                                        <span class="badge bg-secondary text-uppercase small">{{ $order->status }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('vendor.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                            Manage Fulfillment
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if($orders->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
