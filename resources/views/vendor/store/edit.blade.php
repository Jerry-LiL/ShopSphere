@extends('layouts.app')

@section('title', 'Store Settings')

@section('content')
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 fw-bold mb-0">Store Settings</h1>
                <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('vendor.store.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label small fw-semibold">Store / Brand Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $store->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="slug" class="form-label small fw-semibold">Store Slug (Permanent Identifier)</label>
                                <input type="text" class="form-control bg-light font-monospace" id="slug" value="{{ $store->slug }}" disabled>
                                <div class="form-text small">Catalog URL: <code>{{ url('/catalog?store=' . $store->slug) }}</code></div>
                            </div>

                            <div class="col-12">
                                <label for="contact_email" class="form-label small fw-semibold">Contact Email</label>
                                <input type="email" class="form-control @error('contact_email') is-invalid @enderror"
                                       id="contact_email" name="contact_email" value="{{ old('contact_email', $store->contact_email) }}">
                                @error('contact_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label small fw-semibold">Store Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4">{{ old('description', $store->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4 pt-2 border-top d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary px-4">Save Store Settings</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
