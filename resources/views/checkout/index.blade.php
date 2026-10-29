@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 fw-bold mb-1">Checkout</h1>
            <p class="text-muted mb-0">Provide your shipping details and select payment method to complete your order.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('checkout.store') }}">
        @csrf
        <div class="row g-4">
            <!-- Checkout Form -->
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="bi bi-geo-alt me-1 text-primary"></i> 1. Shipping Address
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="customer_name" class="form-label small fw-semibold">Full Name *</label>
                                <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                                       id="customer_name" name="customer_name"
                                       value="{{ old('customer_name', $user?->name) }}" required>
                                @error('customer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="customer_email" class="form-label small fw-semibold">Email Address *</label>
                                <input type="email" class="form-control @error('customer_email') is-invalid @enderror"
                                       id="customer_email" name="customer_email"
                                       value="{{ old('customer_email', $user?->email) }}" required>
                                @error('customer_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="shipping_address" class="form-label small fw-semibold">Street Address *</label>
                                <input type="text" class="form-control @error('shipping_address') is-invalid @enderror"
                                       id="shipping_address" name="shipping_address"
                                       value="{{ old('shipping_address') }}" placeholder="Apartment, suite, unit, street" required>
                                @error('shipping_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="shipping_city" class="form-label small fw-semibold">City *</label>
                                <input type="text" class="form-control @error('shipping_city') is-invalid @enderror"
                                       id="shipping_city" name="shipping_city"
                                       value="{{ old('shipping_city') }}" required>
                                @error('shipping_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="shipping_postal_code" class="form-label small fw-semibold">Postal / ZIP Code *</label>
                                <input type="text" class="form-control @error('shipping_postal_code') is-invalid @enderror"
                                       id="shipping_postal_code" name="shipping_postal_code"
                                       value="{{ old('shipping_postal_code') }}" required>
                                @error('shipping_postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="shipping_phone" class="form-label small fw-semibold">Phone Number *</label>
                                <input type="text" class="form-control @error('shipping_phone') is-invalid @enderror"
                                       id="shipping_phone" name="shipping_phone"
                                       value="{{ old('shipping_phone', $user?->phone) }}" placeholder="+1 555-0123" required>
                                @error('shipping_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="notes" class="form-label small fw-semibold">Delivery Instructions (Optional)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="2"
                                          placeholder="Notes for courier or special drop-off instructions...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="bi bi-credit-card me-1 text-primary"></i> 2. Payment Method
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="pay_cod" value="cash_on_delivery" {{ old('payment_method', 'cash_on_delivery') === 'cash_on_delivery' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="pay_cod">
                                Cash on Delivery (COD)
                                <div class="text-muted small fw-normal">Pay directly to the delivery courier upon receiving package.</div>
                            </label>
                        </div>

                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="pay_card" value="card" {{ old('payment_method') === 'card' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="pay_card">
                                Credit / Debit Card (Simulated Sandbox)
                                <div class="text-muted small fw-normal">Instant authorization for multi-vendor checkout sandbox.</div>
                            </label>
                        </div>

                        <div class="form-check p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="pay_bank" value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="pay_bank">
                                Direct Bank Wire Transfer
                                <div class="text-muted small fw-normal">Wire reference matches your order number.</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Review Sidebar -->
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 sticky-top" style="top: 5rem;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-semibold">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive mb-3">
                            <table class="table table-sm align-middle mb-0">
                                <tbody class="small">
                                    @foreach($items as $item)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $item['name'] }}</div>
                                                <span class="text-muted small">Qty: {{ $item['quantity'] }} &times; ${{ number_format($item['price'], 2) }}</span>
                                            </td>
                                            <td class="text-end fw-semibold">
                                                {{ $item['formatted_subtotal'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal:</span>
                                <span class="fw-semibold">{{ $formattedSubtotal }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Shipping:</span>
                                <span class="text-success fw-semibold">FREE</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="h5 fw-bold mb-0">Total Due:</span>
                                <span class="h5 fw-bold text-primary mb-0">{{ $formattedSubtotal }}</span>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                Place Order <i class="bi bi-lock-fill ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
