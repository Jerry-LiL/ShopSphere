@extends('layouts.app')

@section('title', 'Vendor Dashboard')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ $store->name }}</h1>
            <p class="text-muted mb-0">Vendor Merchant Center &bull; Store slug: <code class="text-primary">{{ $store->slug }}</code></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vendor.products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Product
            </a>
            <a href="{{ route('vendor.store.edit') }}" class="btn btn-outline-secondary">
                <i class="bi bi-gear me-1"></i> Settings
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-bold text-uppercase">Total Sales</span>
                    <h3 class="fw-bold text-primary my-2">${{ number_format($totalSales, 2) }}</h3>
                    <span class="text-muted small">Gross revenue across items</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-bold text-uppercase">Orders Received</span>
                    <h3 class="fw-bold text-dark my-2">{{ $totalOrdersCount }}</h3>
                    <span class="text-muted small">Orders containing your products</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-bold text-uppercase">Total Catalog</span>
                    <h3 class="fw-bold text-dark my-2">{{ $totalProducts }}</h3>
                    <span class="text-muted small">Products in your inventory</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-bold text-uppercase">Active Listings</span>
                    <h3 class="fw-bold text-success my-2">{{ $activeProducts }}</h3>
                    <span class="text-muted small">Live and purchasable</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Order Items -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-semibold">Recent Store Items Sold</h5>
            <a href="{{ route('vendor.orders.index') }}" class="btn btn-sm btn-outline-primary">View All Orders</a>
        </div>
        <div class="card-body p-0">
            @if($recentItems->isEmpty())
                <div class="p-4 text-center text-muted">
                    No orders have been placed for your products yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th>Order Ref</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                                <th>Fulfillment Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentItems as $item)
                                <tr>
                                    <td>
                                        <a href="{{ route('vendor.orders.show', $item->order_id) }}" class="fw-semibold text-primary font-monospace text-decoration-none">
                                            {{ $item->order->order_number ?? '#' . $item->order_id }}
                                        </a>
                                    </td>
                                    <td class="fw-semibold">{{ $item->product_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->price, 2) }}</td>
                                    <td class="fw-bold">${{ number_format($item->subtotal, 2) }}</td>
                                    <td>
                                        <span class="badge bg-secondary text-uppercase small">{{ $item->status }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('vendor.orders.show', $item->order_id) }}" class="btn btn-sm btn-outline-secondary">
                                            Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
