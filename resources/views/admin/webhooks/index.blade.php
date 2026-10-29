@extends('layouts.app')

@section('title', 'Admin - Outgoing Webhooks')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Outgoing Order Webhooks</h1>
            <p class="text-muted mb-0">Dispatched via queued jobs with automated retry and exponential backoff [5s, 15s, 60s].</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- New Endpoint Form -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="bi bi-plus-circle me-1 text-primary"></i> Register Webhook Endpoint
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.webhooks.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="url" class="form-label small fw-semibold">Target Endpoint URL *</label>
                            <input type="url" class="form-control @error('url') is-invalid @enderror"
                                   id="url" name="url" placeholder="https://example.com/api/webhooks" value="{{ old('url') }}" required>
                            @error('url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="secret" class="form-label small fw-semibold">Signature Secret (HMAC-SHA256) *</label>
                            <input type="text" class="form-control font-monospace @error('secret') is-invalid @enderror"
                                   id="secret" name="secret" value="{{ old('secret', 'whsec_' . Str::random(24)) }}" required>
                            <div class="form-text small">Used to compute <code>X-ShopSphere-Signature</code> header.</div>
                            @error('secret')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="event" class="form-label small fw-semibold">Subscribed Event *</label>
                            <select name="event" id="event" class="form-select">
                                <option value="order.created">order.created (When order is placed)</option>
                                <option value="order.updated">order.updated (When status changes)</option>
                                <option value="*">* (All order lifecycle events)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Register Endpoint
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Endpoints List -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-semibold">Configured Endpoints</h5>
                </div>
                <div class="card-body p-0">
                    @if($endpoints->isEmpty())
                        <div class="p-4 text-center text-muted small">
                            No webhook endpoints configured yet. Register one on the left.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light small text-uppercase text-muted">
                                    <tr>
                                        <th>Target URL</th>
                                        <th>Event</th>
                                        <th>Deliveries</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($endpoints as $ep)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-break">{{ $ep->url }}</div>
                                                <span class="text-muted small font-monospace">Secret: {{ Str::mask($ep->secret, '*', 4, 16) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border font-monospace">{{ $ep->event }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $ep->deliveries_count }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <form action="{{ route('admin.webhooks.test', $ep) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-primary" title="Dispatch test job">
                                                            Test
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.webhooks.destroy', $ep) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this webhook endpoint?');">
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
            </div>
        </div>
    </div>

    <!-- Deliveries Log Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-semibold">Recent Webhook Deliveries</h5>
        </div>
        <div class="card-body p-0">
            @if($deliveries->isEmpty())
                <div class="p-4 text-center text-muted small">
                    No delivery attempts recorded yet. Deliveries trigger automatically when orders are placed or updated.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase text-muted">
                            <tr>
                                <th>Timestamp</th>
                                <th>Event</th>
                                <th>Endpoint</th>
                                <th>Status</th>
                                <th>HTTP Code</th>
                                <th>Attempts</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deliveries as $del)
                                <tr>
                                    <td class="text-muted small">{{ $del->created_at->format('M d, H:i:s') }}</td>
                                    <td><code class="text-primary">{{ $del->event }}</code></td>
                                    <td><span class="small text-muted">{{ Str::limit($del->endpoint->url ?? 'Deleted endpoint', 35) }}</span></td>
                                    <td>
                                        @if($del->status === 'success')
                                            <span class="badge bg-success">Success</span>
                                        @elseif($del->status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($del->response_status)
                                            <span class="badge bg-light text-dark border">{{ $del->response_status }}</span>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>
                                    <td>{{ $del->attempts }}</td>
                                    <td>
                                        @if($del->error_message)
                                            <span class="small text-danger text-truncate d-inline-block" style="max-width: 200px;" title="{{ $del->error_message }}">
                                                {{ $del->error_message }}
                                            </span>
                                        @else
                                            <span class="small text-muted">Delivered cleanly</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if($deliveries->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                {{ $deliveries->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
