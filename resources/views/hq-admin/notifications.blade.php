@extends('layouts.hq-admin')

@section('page-title', 'Alerts & Notifications')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1"><i class="bi bi-broadcast"></i> Notification Center</h4>
                            <p class="mb-0 opacity-75">Send announcements to branches and view system alerts</p>
                        </div>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#broadcastModal">
                            <i class="bi bi-megaphone-fill"></i> New Broadcast
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Total Broadcasts Sent</small>
                            <h3 class="mb-0 mt-2">{{ $totalBroadcasts }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-broadcast" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">This Month</small>
                            <h3 class="mb-0 mt-2">{{ $thisMonthBroadcasts }}</h3>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">System Alerts</small>
                            <h3 class="mb-0 mt-2">{{ $systemAlerts->count() }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Active Branches</small>
                            <h3 class="mb-0 mt-2">{{ $activeBranches }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-building-check" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- System Alerts Column -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-warning bg-opacity-25">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle text-warning"></i> System Alerts</h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @if($systemAlerts->count() > 0)
                        @foreach($systemAlerts as $alert)
                        <div class="alert alert-{{ $alert['type'] === 'danger' ? 'danger' : ($alert['type'] === 'warning' ? 'warning' : 'info') }} mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>
                                        <i class="bi bi-{{ $alert['icon'] }}"></i> {{ $alert['title'] }}
                                    </strong>
                                    <p class="mb-1 small">{{ $alert['message'] }}</p>
                                    <small class="text-muted">{{ $alert['time'] }}</small>
                                </div>
                                @if(isset($alert['action_url']))
                                <a href="{{ $alert['action_url'] }}" class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-check-circle" style="font-size: 3rem;"></i>
                            <p class="mt-3">No system alerts at the moment.<br>All branches are performing well!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Broadcast History Column -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Broadcast History</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                        <button type="button" class="btn btn-outline-primary" data-filter="announcement">Announcements</button>
                        <button type="button" class="btn btn-outline-primary" data-filter="urgent">Urgent</button>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                    @if($broadcasts->count() > 0)
                    <div class="list-group list-group-flush" id="broadcastList">
                        @foreach($broadcasts as $broadcast)
                        <div class="list-group-item list-group-item-action broadcast-item" data-type="{{ $broadcast->priority }}" data-broadcast="true">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-1">
                                        @if($broadcast->priority === 'urgent')
                                        <span class="badge bg-danger me-2"><i class="bi bi-exclamation-circle"></i> URGENT</span>
                                        @elseif($broadcast->priority === 'high')
                                        <span class="badge bg-warning text-dark me-2"><i class="bi bi-exclamation-triangle"></i> HIGH</span>
                                        @else
                                        <span class="badge bg-info me-2"><i class="bi bi-info-circle"></i> NORMAL</span>
                                        @endif
                                        
                                        @if($broadcast->data['target_branch_id'] ?? null)
                                        <span class="badge bg-secondary me-2">{{ $broadcast->branch->name ?? 'Specific Branch' }}</span>
                                        @else
                                        <span class="badge bg-primary me-2"><i class="bi bi-globe"></i> All Branches</span>
                                        @endif
                                    </div>
                                    <h6 class="mb-1">{{ $broadcast->title }}</h6>
                                    <p class="mb-1 text-muted small">{{ Str::limit($broadcast->message, 100) }}</p>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> {{ $broadcast->created_at->diffForHumans() }}
                                        &middot;
                                        <i class="bi bi-person"></i> {{ $broadcast->sender->name ?? 'System' }}
                                    </small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item view-broadcast" href="#" data-id="{{ $broadcast->id }}"><i class="bi bi-eye me-2"></i>View Details</a></li>
                                        <li><a class="dropdown-item resend-broadcast" href="#" data-id="{{ $broadcast->id }}"><i class="bi bi-arrow-repeat me-2"></i>Resend</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger delete-broadcast" href="#" data-id="{{ $broadcast->id }}"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-megaphone" style="font-size: 3rem;"></i>
                        <p class="mt-3">No broadcasts sent yet.<br>Click "New Broadcast" to send your first announcement!</p>
                    </div>
                    @endif
                </div>
                @if($broadcasts->hasPages())
                <div class="card-footer bg-white">
                    {{ $broadcasts->links('vendor.pagination.bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Broadcast Modal -->
<div class="modal fade" id="broadcastModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #423A8E 0%, #00CCCD 100%);">
                <h5 class="modal-title text-white"><i class="bi bi-megaphone-fill"></i> Send Broadcast</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hq-admin.notifications.broadcast') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" placeholder="Enter announcement title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" name="priority" required>
                                <option value="medium">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="message" rows="4" placeholder="Enter your announcement message..." required></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Target Recipients</label>
                            <select class="form-select" name="target" id="targetSelect">
                                <option value="all">All Branches & Staff</option>
                                <option value="managers">Branch Managers Only</option>
                                <option value="staff">Staff Only</option>
                                <option value="branch">Specific Branch</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="branchSelectWrapper" style="display: none;">
                            <label class="form-label">Select Branch</label>
                            <select class="form-select" name="branch_id">
                                <option value="">Choose branch...</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Action URL (Optional)</label>
                        <input type="url" class="form-control" name="action_url" placeholder="https://example.com/page">
                        <small class="text-muted">Add a link if you want recipients to take action</small>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Note:</strong> This broadcast will be sent immediately to all selected recipients. They will see it in their notification center.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Send Broadcast
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Broadcast Modal -->
<div class="modal fade" id="viewBroadcastModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Broadcast Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="broadcastDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Target select toggle
    const targetSelect = document.getElementById('targetSelect');
    const branchSelectWrapper = document.getElementById('branchSelectWrapper');
    
    targetSelect.addEventListener('change', function() {
        if (this.value === 'branch') {
            branchSelectWrapper.style.display = 'block';
        } else {
            branchSelectWrapper.style.display = 'none';
        }
    });

    // Filter broadcasts
    document.querySelectorAll('[data-filter]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            document.querySelectorAll('.broadcast-item').forEach(item => {
                if (filter === 'all') {
                    item.style.display = '';
                } else if (filter === 'announcement') {
                    // All broadcasts are announcements, so show all
                    item.style.display = '';
                } else if (filter === 'urgent') {
                    item.style.display = (item.dataset.type === 'urgent' || item.dataset.type === 'high') ? '' : 'none';
                } else {
                    item.style.display = item.dataset.type === filter ? '' : 'none';
                }
            });
        });
    });

    // View broadcast details
    const viewBroadcastModal = new bootstrap.Modal(document.getElementById('viewBroadcastModal'));
    document.querySelectorAll('.view-broadcast').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const broadcastId = this.dataset.id;
            
            document.getElementById('broadcastDetailsContent').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            viewBroadcastModal.show();
            
            fetch(`/hq-admin/notifications/${broadcastId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const broadcast = data.broadcast;
                    document.getElementById('broadcastDetailsContent').innerHTML = `
                        <div class="mb-3">
                            <span class="badge bg-${broadcast.priority === 'urgent' ? 'danger' : (broadcast.priority === 'high' ? 'warning' : 'info')}">${broadcast.priority.toUpperCase()}</span>
                        </div>
                        <h5>${broadcast.title}</h5>
                        <p class="text-muted">${broadcast.message}</p>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Sent by</small>
                                <p class="mb-0">${broadcast.sender?.name || 'System'}</p>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Sent at</small>
                                <p class="mb-0">${new Date(broadcast.created_at).toLocaleString()}</p>
                            </div>
                        </div>
                        ${broadcast.action_url ? `<hr><a href="${broadcast.action_url}" class="btn btn-primary btn-sm" target="_blank"><i class="bi bi-link-45deg"></i> View Link</a>` : ''}
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('broadcastDetailsContent').innerHTML = `
                    <div class="alert alert-danger">Failed to load broadcast details</div>
                `;
            });
        });
    });

    // Delete broadcast
    document.querySelectorAll('.delete-broadcast').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this broadcast?')) {
                const broadcastId = this.dataset.id;
                
                fetch(`/hq-admin/notifications/${broadcastId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete broadcast');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        });
    });

    // Resend broadcast
    document.querySelectorAll('.resend-broadcast').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const broadcastId = this.dataset.id;
            
            // Fetch broadcast details first
            fetch(`/hq-admin/notifications/${broadcastId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const broadcast = data.broadcast;
                    // Pre-fill the broadcast modal with existing data
                    document.querySelector('#broadcastModal input[name="title"]').value = broadcast.title;
                    document.querySelector('#broadcastModal textarea[name="message"]').value = broadcast.message;
                    document.querySelector('#broadcastModal select[name="priority"]').value = broadcast.priority;
                    if (broadcast.action_url) {
                        document.querySelector('#broadcastModal input[name="action_url"]').value = broadcast.action_url;
                    }
                    // Open the modal
                    new bootstrap.Modal(document.getElementById('broadcastModal')).show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load broadcast details');
            });
        });
    });
});
</script>
@endpush
@endsection
