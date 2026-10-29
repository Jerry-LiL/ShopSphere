@extends('layouts.app')

@section('title', 'Manage Products')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">My Products</h1>
            <p class="text-muted mb-0">Inventory and catalog management for {{ $store->name }}.</p>
        </div>
        <a href="{{ route('vendor.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add New Product
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('vendor.products.index') }}" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Search by name or SKU..." value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                    @if(request()->hasAny(['q', 'status']))
                        <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if($products->isEmpty())
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-box-seam display-4"></i>
                    <h5 class="mt-3 fw-bold">No Products Found</h5>
                    <p class="mb-3">Start by listing your first product.</p>
                    <a href="{{ route('vendor.products.create') }}" class="btn btn-primary btn-sm">Add Product</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            <a href="{{ route('catalog.show', $product->slug) }}" class="text-dark text-decoration-none">
                                                {{ $product->name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $product->category->name ?? 'None' }}</span>
                                    </td>
                                    <td class="font-monospace small text-muted">{{ $product->sku }}</td>
                                    <td class="fw-bold">${{ number_format($product->price, 2) }}</td>
                                    <td>
                                        @if($product->stock > 0)
                                            <span class="badge bg-success-subtle text-success">{{ $product->stock }} units</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Out of stock</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('vendor.products.edit', $product) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('vendor.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product permanently?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if($products->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
