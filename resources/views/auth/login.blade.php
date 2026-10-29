@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 text-center">
                    <h4 class="card-title fw-bold mb-0">Sign In to ShopSphere</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label small fw-semibold">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label small" for="remember">
                                Keep me signed in
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            Sign In
                        </button>
                    </form>

                    <div class="mt-4 pt-3 border-top">
                        <p class="small text-muted mb-2 fw-semibold text-uppercase">Demo Credentials (Password: <code>password</code>)</p>
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="fillLogin('admin@shopsphere.test')">Admin</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillLogin('vendor1@shopsphere.test')">Vendor</button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="fillLogin('customer@shopsphere.test')">Customer</button>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-top py-3 text-center small">
                    Don't have an account? <a href="{{ route('register') }}" class="fw-semibold">Register here</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function fillLogin(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'password';
}
</script>
@endpush
