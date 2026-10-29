@extends('layouts.app')

@section('title', 'Create Account')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 text-center">
                    <h4 class="card-title fw-bold mb-0">Create an Account</h4>
                    <p class="text-muted small mb-0">Join ShopSphere as a shopper or open your vendor storefront</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Role Selector -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-uppercase text-muted">Account Type</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="role" id="role_customer" value="customer" {{ old('role', 'customer') === 'customer' ? 'checked' : '' }} onchange="toggleVendorFields()">
                                    <label class="btn btn-outline-success w-100 py-2" for="role_customer">
                                        <i class="bi bi-person me-1"></i> Customer
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="role" id="role_vendor" value="vendor" {{ old('role') === 'vendor' ? 'checked' : '' }} onchange="toggleVendorFields()">
                                    <label class="btn btn-outline-primary w-100 py-2" for="role_vendor">
                                        <i class="bi bi-shop me-1"></i> Vendor
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label small fw-semibold">Full Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label small fw-semibold">Email Address *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label small fw-semibold">Password *</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label small fw-semibold">Confirm Password *</label>
                                <input type="password" class="form-control"
                                       id="password_confirmation" name="password_confirmation" required>
                            </div>

                            <div class="col-12">
                                <label for="phone" class="form-label small fw-semibold">Phone Number (Optional)</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Vendor Specific Fields -->
                            <div id="vendor-fields" class="{{ old('role') === 'vendor' ? '' : 'd-none' }}">
                                <div class="p-3 bg-light rounded border mt-2">
                                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-shop me-1"></i> Vendor Store Setup</h6>
                                    <div class="mb-3">
                                        <label for="store_name" class="form-label small fw-semibold">Store / Brand Name *</label>
                                        <input type="text" class="form-control @error('store_name') is-invalid @enderror"
                                               id="store_name" name="store_name" value="{{ old('store_name') }}">
                                        @error('store_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-0">
                                        <label for="store_description" class="form-label small fw-semibold">Store Description (Optional)</label>
                                        <textarea class="form-control @error('store_description') is-invalid @enderror"
                                                  id="store_description" name="store_description" rows="2">{{ old('store_description') }}</textarea>
                                        @error('store_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                    Complete Registration
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light border-top py-3 text-center small">
                    Already have an account? <a href="{{ route('login') }}" class="fw-semibold">Sign in here</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleVendorFields() {
    const isVendor = document.getElementById('role_vendor').checked;
    const fields = document.getElementById('vendor-fields');
    if (isVendor) {
        fields.classList.remove('d-none');
    } else {
        fields.classList.add('d-none');
    }
}
</script>
@endpush
