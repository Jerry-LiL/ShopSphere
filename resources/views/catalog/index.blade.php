@extends('layouts.app')

@section('title', 'Browse Catalog')

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h1 class="h3 fw-bold mb-1">Product Catalog</h1>
            <p class="text-muted mb-0">Browse items from verified independent vendors across all categories.</p>
        </div>
        <div class="col-md-4 text-md-end mt-2 mt-md-0">
            <span class="text-muted small">Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} items</span>
        </div>
    </div>

    <div class="row">
        <!-- Filter Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 5rem; z-index: 10;">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="bi bi-funnel me-1"></i> Filter Products
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('catalog.index') }}">
                        <!-- Search Keyword -->
                        <div class="mb-3">
                            <label for="q" class="form-label small fw-semibold text-uppercase text-muted">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Keywords, names, specs...">
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label for="category" class="form-label small fw-semibold text-uppercase text-muted">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->slug }}" {{ ($filters['category'] ?? '') === $category->slug ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Store / Vendor -->
                        <div class="mb-3">
                            <label for="store" class="form-label small fw-semibold text-uppercase text-muted">Vendor Store</label>
                            <select class="form-select" id="store" name="store">
                                <option value="">All Stores</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->slug }}" {{ ($filters['store'] ?? '') === $store->slug ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-uppercase text-muted">Price Range ($)</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" step="0.01" min="0" class="form-control" name="min_price" placeholder="Min" value="{{ $filters['min_price'] ?? '' }}">
                                </div>
                                <div class="col-6">
                                    <input type="number" step="0.01" min="0" class="form-control" name="max_price" placeholder="Max" value="{{ $filters['max_price'] ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <!-- In Stock Only -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="in_stock" id="in_stock" value="1" {{ !empty($filters['in_stock']) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="in_stock">
                                In stock only
                            </label>
                        </div>

                        <!-- Sort -->
                        <div class="mb-4">
                            <label for="sort" class="form-label small fw-semibold text-uppercase text-muted">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="latest" {{ ($filters['sort'] ?? '') === 'latest' ? 'selected' : '' }}>Newest arrivals</option>
                                <option value="price_asc" {{ ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_desc" {{ ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="name_asc" {{ ($filters['sort'] ?? '') === 'name_asc' ? 'selected' : '' }}>Name: A-Z</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-filter"></i> Apply Filters
                            </button>
                            <a href="{{ route('catalog.index') }}" class="btn btn-outline-secondary btn-sm">
                                Reset Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            @if($products->isEmpty())
                <div class="card shadow-sm border-0 py-5 text-center">
                    <div class="card-body">
                        <i class="bi bi-inbox text-muted display-4"></i>
                        <h4 class="mt-3 fw-bold">No Products Found</h4>
                        <p class="text-muted">No items matched your current filter selection. Try adjusting the search or price range.</p>
                        <a href="{{ route('catalog.index') }}" class="btn btn-primary mt-2">Clear All Filters</a>
                    </div>
                </div>
            @else
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                    @foreach($products as $product)
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 product-card d-flex flex-column">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-light text-dark border">
                                            {{ $product->category->name ?? 'Uncategorized' }}
                                        </span>
                                        @if($product->stock > 0)
                                            <span class="badge bg-success-subtle text-success small">In Stock ({{ $product->stock }})</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger small">Out of Stock</span>
                                        @endif
                                    </div>

                                    <h5 class="card-title fw-bold mb-1">
                                        <a href="{{ route('catalog.show', $product->slug) }}" class="text-dark text-decoration-none">
                                            {{ $product->name }}
                                        </a>
                                    </h5>

                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-shop me-1"></i> {{ $product->store->name ?? 'Unknown Store' }}
                                    </p>

                                    <p class="card-text text-muted small flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $product->description }}
                                    </p>

                                    <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                                        <div class="h5 fw-bold text-primary mb-0">
                                            ${{ number_format($product->price, 2) }}
                                        </div>

                                        @if($product->stock > 0)
                                            <button type="button" class="btn btn-sm btn-primary" data-cart-add data-product-id="{{ $product->id }}">
                                                <i class="bi bi-cart-plus"></i> Add
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-secondary" disabled>
                                                Sold Out
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-4 d-flex justify-content-center">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
