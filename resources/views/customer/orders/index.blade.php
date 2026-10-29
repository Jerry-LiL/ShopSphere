@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 fw-bold mb-1">My Orders</h1>
            <p class="text-muted mb-0">Track shipments, review past invoices, and monitor delivery progress.</p>
        </div>
    </div>

    @if($orders->isEmpty())
        <div class="card shadow-sm border-0 py-5 text-center">
            <div class="card-body">
                <i class="bi bi-bag-x text-muted display-4"></i>
                <h4 class="mt-3 fw-bold">No Orders Yet</h4>
                <p class="text-muted">You haven't placed any orders with ShopSphere vendors.</p>
                <a href="{{ route('catalog.index') }}" class="btn btn-primary mt-2">
                    <i class="bi bi-cart me-1"></i> Browse Products
                </a>
            </div>
        </div>
    @else
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th>Order Number</th>
                                <th>Date Placed</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Order Status</th>
                                <th>Payment</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('customer.orders.show', $order->order_number) }}" class="fw-semibold text-primary font-monospace text-decoration-none">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $order->created_at->format('M d, Y') }}
                                    </td>
                                    <td>{{ $order->items->count() }} item(s)</td>
                                    <td class="fw-bold">${{ number_format($order->total_amount, 2) }}</td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'shipped' => 'primary',
                                                'delivered' => 'success',
                                                'cancelled' => 'danger',
                                            ];
                                            $color = $statusColors[$order->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }} text-uppercase small">{{ $order->status }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border small text-capitalize">{{ $order->payment_status }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('customer.orders.show', $order->order_number) }}" class="btn btn-sm btn-outline-primary">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if($orders->hasPages())
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
