@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Platform Administration</h1>
            <p class="text-muted mb-0">ShopSphere Multi-Vendor Marketplace Overview</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">Manage Orders</a>
            <a href="{{ route('admin.webhooks.index') }}" class="btn btn-primary btn-sm">Webhooks</a>
        </div>
    </div>

    <!-- Platform KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-bold text-uppercase">Platform Revenue</span>
                    <h3 class="fw-bold text-success my-2">${{ number_format($totalRevenue, 2) }}</h3>
                    <span class="text-muted small">Paid orders aggregate</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-bold text-uppercase">Total Orders</span>
                    <h3 class="fw-bold text-primary my-2">{{ $totalOrders }}</h3>
                    <span class="text-muted small">All-time order volume</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-bold text-uppercase">Products</span>
                    <h3 class="fw-bold text-dark my-2">{{ $totalProducts }}</h3>
                    <span class="text-muted small">Across all vendor stores</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-bold text-uppercase">Vendors</span>
                    <h3 class="fw-bold text-info my-2">{{ $totalVendors }}</h3>
                    <span class="text-muted small">Independent merchant stores</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-bold text-uppercase">Shoppers</span>
                    <h3 class="fw-bold text-dark my-2">{{ $totalCustomers }}</h3>
                    <span class="text-muted small">Registered customers</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold">Recent Platform Orders</h5>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase text-muted">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold text-primary font-monospace text-decoration-none">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $order->customer_name }}</div>
                                            <span class="text-muted small">{{ $order->customer_email }}</span>
                                        </td>
                                        <td class="fw-bold">${{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-secondary text-uppercase small">{{ $order->status }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">
                                                Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Webhook Deliveries -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold">Outgoing Webhooks</h5>
                    <a href="{{ route('admin.webhooks.index') }}" class="btn btn-sm btn-outline-primary">Settings</a>
                </div>
                <div class="card-body p-0">
                    @if($recentDeliveries->isEmpty())
                        <div class="p-4 text-center text-muted small">
                            No webhook deliveries logged yet. Configure an endpoint in the Webhooks tab.
                        </div>
                    @else
                        <ul class="list-group list-group-flush small">
                            @foreach($recentDeliveries as $del)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <div>
                                        <div class="fw-semibold font-monospace">{{ $del->event }}</div>
                                        <span class="text-muted small">{{ Str::limit($del->endpoint->url ?? 'Unknown', 30) }}</span>
                                    </div>
                                    <div class="text-end">
                                        @if($del->status === 'success')
                                            <span class="badge bg-success">HTTP {{ $del->response_status ?? 200 }}</span>
                                        @elseif($del->status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                        <div class="text-muted" style="font-size: 11px;">{{ $del->created_at->diffForHumans() }}</div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
