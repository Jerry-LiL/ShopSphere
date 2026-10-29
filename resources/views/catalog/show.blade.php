@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('catalog.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4 text-center bg-white rounded-3">
                <div class="py-5 bg-light rounded-3 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                    <div class="text-muted">
                        <i class="bi bi-box-seam display-1"></i>
                        <div class="mt-2 text-uppercase fw-semibold small text-secondary">Product Preview</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
                    <span class="text-muted small">SKU: <strong class="text-dark">{{ $product->sku }}</strong></span>
                </div>

                <h1 class="h2 fw-bold mb-2">{{ $product->name }}</h1>

                <div class="mb-3 text-muted">
                    Sold by: <a href="{{ route('catalog.index', ['store' => $product->store->slug]) }}" class="text-decoration-none fw-semibold text-primary">
                        <i class="bi bi-shop me-1"></i>{{ $product->store->name }}
                    </a>
                </div>

                <div class="h2 fw-bold text-primary mb-3">
                    ${{ number_format($product->price, 2) }}
                </div>

                <div class="mb-3">
                    @if($product->stock > 0)
                        <span class="badge bg-success-subtle text-success fs-6 px-3 py-2">
                            <i class="bi bi-check-circle me-1"></i> In Stock ({{ $product->stock }} units available)
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger fs-6 px-3 py-2">
                            <i class="bi bi-x-circle me-1"></i> Currently Out of Stock
                        </span>
                    @endif
                </div>

                <div class="mb-4">
                    <h5 class="fw-semibold mb-2">Description</h5>
                    <p class="text-muted lh-base">{{ $product->description }}</p>
                </div>

                @if($product->stock > 0)
                    <div class="border-top pt-4">
                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <label for="product-qty-{{ $product->id }}" class="col-form-label small fw-semibold">Qty:</label>
                            </div>
                            <div class="col-auto" style="width: 100px;">
                                <input type="number" id="product-qty-{{ $product->id }}" class="form-control" value="1" min="1" max="{{ $product->stock }}">
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-primary btn-lg w-100" data-cart-add data-product-id="{{ $product->id }}">
                                    <i class="bi bi-cart-plus me-1"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($relatedProducts->isNotEmpty())
        <div class="mt-5">
            <h3 class="h4 fw-bold mb-4">Related Products in {{ $product->category->name }}</h3>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4">
                @foreach($relatedProducts as $related)
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0 product-card">
                            <div class="card-body d-flex flex-column">
                                <span class="badge bg-light text-dark border mb-2 align-self-start">{{ $related->store->name }}</span>
                                <h6 class="card-title fw-bold mb-1">
                                    <a href="{{ route('catalog.show', $related->slug) }}" class="text-dark text-decoration-none">
                                        {{ $related->name }}
                                    </a>
                                </h6>
                                <div class="mt-auto pt-3 d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-primary">${{ number_format($related->price, 2) }}</span>
                                    <a href="{{ route('catalog.show', $related->slug) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
