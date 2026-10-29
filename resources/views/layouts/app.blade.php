<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ShopSphere') | Multi-Vendor E-Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
        }
        main {
            flex: 1;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .badge-role-admin { background-color: #dc3545; color: #fff; }
        .badge-role-vendor { background-color: #0d6efd; color: #fff; }
        .badge-role-customer { background-color: #198754; color: #fff; }
        .product-card {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08);
        }
        .toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 1090;
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $cartCount = app(\App\Services\CartService::class)->count();
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-shop me-1 text-primary"></i> ShopSphere
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('catalog.*') || request()->routeIs('home') ? 'active' : '' }}" href="{{ route('catalog.index') }}">
                            Catalog
                        </a>
                    </li>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-warning" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-shield-lock me-1"></i> Admin Panel
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.products.index') }}">All Products</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.orders.index') }}">All Orders</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.vendors.index') }}">Vendors</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.webhooks.index') }}">Webhooks</a></li>
                                </ul>
                            </li>
                        @elseif(auth()->user()->isVendor())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-info" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-briefcase me-1"></i> Vendor Panel
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('vendor.products.index') }}">My Products</a></li>
                                    <li><a class="dropdown-item" href="{{ route('vendor.products.create') }}">Add Product</a></li>
                                    <li><a class="dropdown-item" href="{{ route('vendor.orders.index') }}">Vendor Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('vendor.store.edit') }}">Store Settings</a></li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customer.orders.*') ? 'active' : '' }}" href="{{ route('customer.orders.index') }}">
                                    My Orders
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('cart.index') }}" class="btn btn-outline-light position-relative me-2">
                        <i class="bi bi-cart3"></i> Cart
                        <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ $cartCount > 0 ? '' : 'd-none' }}">
                            {{ $cartCount }}
                        </span>
                    </a>

                    @guest
                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-sm btn-primary">Register</a>
                    @else
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <span class="badge badge-role-{{ auth()->user()->role }} me-1">{{ ucfirst(auth()->user()->role) }}</span>
                                {{ auth()->user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if(auth()->user()->isCustomer())
                                    <li><a class="dropdown-item" href="{{ route('customer.orders.index') }}">My Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @elseif(auth()->user()->isVendor())
                                    <li><a class="dropdown-item" href="{{ route('vendor.dashboard') }}">Vendor Dashboard</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @elseif(auth()->user()->isAdmin())
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">Sign Out</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <main class="py-4">
        @yield('content')
    </main>

    <footer class="bg-white border-top py-4 mt-auto">
        <div class="container text-center text-muted small">
            <p class="mb-1">&copy; {{ date('Y') }} ShopSphere. Multi-vendor e-commerce platform.</p>
            <p class="mb-0">Built with PHP {{ phpversion() }}, Laravel, MySQL &amp; Bootstrap 5.</p>
        </div>
    </footer>

    <!-- Toast Notification Container for AJAX actions -->
    <div class="toast-container">
        <div id="ajax-toast" class="toast align-items-center text-white bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="ajax-toast-body">
                    Cart updated.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.ShopSphereCart = {
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),

            showToast: function(message, isError = false) {
                const toastEl = document.getElementById('ajax-toast');
                const toastBody = document.getElementById('ajax-toast-body');
                if (toastEl && toastBody) {
                    toastEl.classList.remove('bg-dark', 'bg-danger', 'bg-success');
                    toastEl.classList.add(isError ? 'bg-danger' : 'bg-success');
                    toastBody.textContent = message;
                    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
                    toast.show();
                }
            },

            updateBadge: function(count) {
                const badge = document.getElementById('cart-badge');
                if (badge) {
                    badge.textContent = count;
                    if (count > 0) {
                        badge.classList.remove('d-none');
                    } else {
                        badge.classList.add('d-none');
                    }
                }
            },

            add: function(productId, quantity = 1, buttonEl = null) {
                let originalText = '';
                if (buttonEl) {
                    originalText = buttonEl.innerHTML;
                    buttonEl.disabled = true;
                    buttonEl.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
                }

                return fetch('{{ route('cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({ product_id: productId, quantity: quantity })
                })
                .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data })))
                .then(({ ok, data }) => {
                    if (ok && data.success) {
                        this.updateBadge(data.cart_count);
                        this.showToast(data.message, false);
                    } else {
                        this.showToast(data.message || 'Could not add product to cart.', true);
                    }
                    return data;
                })
                .catch(err => {
                    this.showToast('Network error while updating cart.', true);
                })
                .finally(() => {
                    if (buttonEl) {
                        buttonEl.disabled = false;
                        buttonEl.innerHTML = originalText;
                    }
                });
            }
        };

        // Attach event listeners for any data-cart-add button
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-cart-add]').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.getAttribute('data-product-id');
                    const qtyInput = document.getElementById('product-qty-' + productId);
                    const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
                    ShopSphereCart.add(productId, qty, this);
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
