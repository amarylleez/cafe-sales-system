@extends('layouts.hq-admin')

@section('page-title', 'Manage Staff')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="bi bi-people"></i> Staff Management
                        </h5>
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                            <i class="bi bi-plus-circle"></i> Add New Staff
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Register, update, and manage staff members across all branches.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white text-center">
                    <h3>{{ $totalStaff }}</h3>
                    <small>Total Staff</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white text-center">
                    <h3>{{ $branchManagers }}</h3>
                    <small>Branch Managers</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white text-center">
                    <h3>{{ $staffMembers }}</h3>
                    <small>Staff Members</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="card-body text-white text-center">
                    <h3>{{ $hqAdmins }}</h3>
                    <small>HQ Admins</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="searchStaff" placeholder="Search by name or email...">
            </div>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterBranch">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterRole">
                <option value="">All Roles</option>
                <option value="hq_admin">HQ Admin</option>
                <option value="branch_manager">Branch Manager</option>
                <option value="staff">Staff</option>
            </select>
        </div>
    </div>

    <!-- Staff Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-table"></i> All Staff Members</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Branch</th>
                                    <th>Joined Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="staffTableBody">
                                @foreach($allStaff as $staff)
                                <tr data-branch="{{ $staff->branch_id }}" data-role="{{ $staff->role }}">
                                    <td>{{ $staff->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 35px; height: 35px; font-size: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items-center; justify-content: center; color: white; font-weight: 600;">
                                                {{ substr($staff->name, 0, 1) }}
                                            </div>
                                            <span>{{ $staff->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $staff->email }}</td>
                                    <td>
                                        <span class="badge bg-{{ $staff->role === 'hq_admin' ? 'danger' : ($staff->role === 'branch_manager' ? 'primary' : 'info') }}">
                                            {{ ucfirst(str_replace('_', ' ', $staff->role)) }}
                                        </span>
                                    </td>
                                    <td>{{ $staff->branch->name ?? 'N/A' }}</td>
                                    <td>{{ $staff->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editStaff({{ $staff->id }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteStaff({{ $staff->id }}, '{{ $staff->name }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addStaffForm" method="POST" action="{{ route('hq-admin.manage.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" id="addRole" required>
                            <option value="">Select Role</option>
                            <option value="hq_admin">HQ Admin</option>
                            <option value="branch_manager">Branch Manager</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="mb-3" id="addBranchField">
                        <label class="form-label">Branch</label>
                        <select class="form-select" name="branch_id" id="addBranch">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Add Staff
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editStaffForm">
                @csrf
                @method('PATCH')
                <input type="hidden" id="editStaffId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" id="editRole" name="role" required>
                            <option value="hq_admin">HQ Admin</option>
                            <option value="branch_manager">Branch Manager</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="mb-3" id="editBranchField">
                        <label class="form-label">Branch</label>
                        <select class="form-select" id="editBranch" name="branch_id">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const editModal = new bootstrap.Modal(document.getElementById('editStaffModal'));

// Search functionality
document.getElementById('searchStaff').addEventListener('input', filterTable);
document.getElementById('filterBranch').addEventListener('change', filterTable);
document.getElementById('filterRole').addEventListener('change', filterTable);

function filterTable() {
    const search = document.getElementById('searchStaff').value.toLowerCase();
    const branch = document.getElementById('filterBranch').value;
    const role = document.getElementById('filterRole').value;
    
    document.querySelectorAll('#staffTableBody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        const rowBranch = row.dataset.branch;
        const rowRole = row.dataset.role;
        
        const matchSearch = text.includes(search);
        const matchBranch = !branch || rowBranch === branch;
        const matchRole = !role || rowRole === role;
        
        row.style.display = (matchSearch && matchBranch && matchRole) ? '' : 'none';
    });
}

// Role change handler for add form
document.getElementById('addRole').addEventListener('change', function() {
    const branchField = document.getElementById('addBranchField');
    if (this.value === 'hq_admin') {
        branchField.style.display = 'none';
        document.getElementById('addBranch').required = false;
    } else {
        branchField.style.display = 'block';
        document.getElementById('addBranch').required = true;
    }
});

// Edit staff
function editStaff(staffId) {
    fetch(`/hq-admin/manage/${staffId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editStaffId').value = data.staff.id;
                document.getElementById('editName').value = data.staff.name;
                document.getElementById('editEmail').value = data.staff.email;
                document.getElementById('editRole').value = data.staff.role;
                document.getElementById('editBranch').value = data.staff.branch_id || '';
                
                if (data.staff.role === 'hq_admin') {
                    document.getElementById('editBranchField').style.display = 'none';
                } else {
                    document.getElementById('editBranchField').style.display = 'block';
                }
                
                editModal.show();
            }
        });
}

// Update staff
document.getElementById('editStaffForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const staffId = document.getElementById('editStaffId').value;
    const formData = new FormData(this);
    
    fetch(`/hq-admin/manage/${staffId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Staff updated successfully!');
            location.reload();
        }
    });
});

// Delete staff
function deleteStaff(staffId, name) {
    if (confirm(`Are you sure you want to delete ${name}?`)) {
        fetch(`/hq-admin/manage/${staffId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Staff deleted successfully!');
                location.reload();
            }
        });
    }
}
</script>
@endpush
@endsection