@extends('layouts.app')

@section('title', 'Admin - Vendors & Stores')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Registered Vendors</h1>
            <p class="text-muted mb-0">Merchant store directories, verification status, and catalog counts.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th>Store Name</th>
                            <th>Merchant Owner</th>
                            <th>Slug</th>
                            <th>Products Listed</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stores as $store)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $store->name }}</div>
                                    <span class="text-muted small">{{ $store->contact_email ?? $store->user->email }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $store->user->name ?? 'N/A' }}</div>
                                    <span class="text-muted small">{{ $store->user->phone ?? 'No phone' }}</span>
                                </td>
                                <td><code class="text-primary">{{ $store->slug }}</code></td>
                                <td><span class="badge bg-light text-dark border">{{ $store->products_count }} items</span></td>
                                <td>
                                    @if($store->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Suspended</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('admin.vendors.toggle', $store) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $store->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                            {{ $store->is_active ? 'Suspend Store' : 'Activate Store' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-muted">No vendors registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($stores->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                {{ $stores->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
