@extends('layouts.staff')

@section('page-title', 'Alerts & Notifications')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-bell-fill"></i> Important Notices & Alerts
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">Stay updated with important notifications from branch managers and system alerts.</p>
                </div>
            </div>
        </div>
    </div>

    @if($alerts->count() > 0)
        @foreach($alerts as $alert)
        <div class="row mb-3">
            <div class="col-12">
                <div class="card shadow-sm {{ $alert->is_read ? '' : 'border-warning' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    @if($alert->type === 'kpi_target_not_met')
                                    <span class="badge bg-danger me-2">
                                        <i class="bi bi-exclamation-triangle-fill"></i> KPI Alert
                                    </span>
                                    @elseif($alert->type === 'low_stock_alert')
                                    <span class="badge bg-warning me-2">
                                        <i class="bi bi-box-seam"></i> Stock Alert
                                    </span>
                                    @else
                                    <span class="badge bg-info me-2">
                                        <i class="bi bi-info-circle"></i> Notice
                                    </span>
                                    @endif

                                    @if($alert->priority === 'urgent')
                                    <span class="badge bg-danger me-2">URGENT</span>
                                    @elseif($alert->priority === 'high')
                                    <span class="badge bg-warning me-2">HIGH</span>
                                    @endif

                                    @if(!$alert->is_read)
                                    <span class="badge bg-primary">NEW</span>
                                    @endif
                                </div>

                                <h5 class="card-title mb-2">{{ $alert->title }}</h5>
                                <p class="card-text">{{ $alert->message }}</p>

                                @if($alert->data)
                                <div class="alert alert-light mt-3">
                                    <strong>Details:</strong>
                                    <ul class="mb-0 mt-2">
                                        @if(isset($alert->data['target_value']))
                                        <li>Target Value: {{ number_format($alert->data['target_value'], 2) }}</li>
                                        @endif
                                        @if(isset($alert->data['current_value']))
                                        <li>Current Value: {{ number_format($alert->data['current_value'], 2) }}</li>
                                        @endif
                                        @if(isset($alert->data['loss']))
                                        <li class="text-danger">Loss: RM {{ number_format($alert->data['loss'], 2) }}</li>
                                        @endif
                                        @if(isset($alert->data['date']))
                                        <li>Date: {{ $alert->data['date'] }}</li>
                                        @endif
                                    </ul>
                                </div>
                                @endif

                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> {{ $alert->created_at->diffForHumans() }}
                                </small>
                            </div>

                            <div class="ms-3">
                                @if(!$alert->is_read)
                                <button class="btn btn-sm btn-primary mark-read-btn mb-2" data-id="{{ $alert->id }}">
                                    <i class="bi bi-check"></i> Mark as Read
                                </button>
                                @endif
                                @if($alert->action_url)
                                <a href="{{ $alert->action_url }}" class="btn btn-sm btn-outline-primary d-block">
                                    <i class="bi bi-arrow-right"></i> View Details
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Pagination -->
        <div class="row mt-4">
            <div class="col-12">
                {{ $alerts->links() }}
            </div>
        </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> No alerts at the moment. Keep up the good work!
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const alertId = this.dataset.id;
            
            fetch(`/staff/notifications/${alertId}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});
</script>
@endpush
@endsection