@extends('layouts.staff')

@section('page-title', 'Inventory Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-box-seam"></i> Update Sales Item Records
                        </h5>
                        <div class="input-group" style="max-width: 400px;">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search products...">
                            <button class="btn btn-light" type="button">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted">Manage product availability and update stock levels.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Filter -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary active" data-category="all">All Categories</button>
                @foreach($categories as $category)
                <button type="button" class="btn btn-outline-primary" data-category="{{ $category->id }}">
                    {{ $category->name }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row g-3" id="productsContainer">
        @foreach($products as $product)
        <div class="col-md-6 col-lg-4 col-xl-3 product-item" data-category="{{ $product->category_id }}">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="card-title mb-1">{{ $product->name }}</h6>
                            <small class="text-muted">{{ $product->category->name }}</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input availability-toggle" 
                                   type="checkbox" 
                                   id="availability{{ $product->id }}"
                                   data-product-id="{{ $product->id }}"
                                   {{ $product->is_available ? 'checked' : '' }}>
                            <label class="form-check-label" for="availability{{ $product->id }}">
                                <small>{{ $product->is_available ? 'Available' : 'Unavailable' }}</small>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Price:</span>
                            <strong class="text-primary">RM {{ number_format($product->price, 2) }}</strong>
                        </div>
                    </div>

                    @if($product->description)
                    <p class="card-text small text-muted mb-3">{{ Str::limit($product->description, 80) }}</p>
                    @endif

                    <div class="d-grid gap-2">
                        <button class="btn btn-sm btn-outline-primary view-details-btn" data-product-id="{{ $product->id }}">
                            <i class="bi bi-eye"></i> View Details
                        </button>
                        <button class="btn btn-sm btn-outline-success mark-sold-btn" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}">
                            <i class="bi bi-check-circle"></i> Mark as Sold
                        </button>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-{{ $product->is_available ? 'check-circle text-success' : 'x-circle text-danger' }}"></i>
                            {{ $product->is_available ? 'In Stock' : 'Out of Stock' }}
                        </small>
                        <small class="text-muted">ID: #{{ $product->id }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="row mt-4">
        <div class="col-12">
            {{ $products->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Product Details Modal -->
<div class="modal fade" id="productDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="productDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark as Sold Modal -->
<div class="modal fade" id="markSoldModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Items as Sold</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="markSoldForm">
                    <input type="hidden" id="soldProductId">
                    <div class="mb-3">
                        <label class="form-label">Product:</label>
                        <h6 id="soldProductName"></h6>
                    </div>
                    <div class="mb-3">
                        <label for="soldQuantity" class="form-label">Quantity Sold <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="soldQuantity" min="1" value="1" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmMarkSold">
                    <i class="bi bi-check-circle"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productDetailsModal = new bootstrap.Modal(document.getElementById('productDetailsModal'));
    const markSoldModal = new bootstrap.Modal(document.getElementById('markSoldModal'));

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.product-item').forEach(item => {
            const productName = item.querySelector('.card-title').textContent.toLowerCase();
            if (productName.includes(query)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Category filter
    document.querySelectorAll('[data-category]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-category]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const category = this.dataset.category;
            document.querySelectorAll('.product-item').forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Toggle availability
    document.querySelectorAll('.availability-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const isAvailable = this.checked;
            
            fetch(`/staff/inventory/${productId}/availability`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ is_available: isAvailable })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const label = this.nextElementSibling.querySelector('small');
                    label.textContent = isAvailable ? 'Available' : 'Unavailable';
                    
                    const card = this.closest('.card');
                    const statusIcon = card.querySelector('.card-footer i');
                    const statusText = card.querySelector('.card-footer small');
                    
                    if (isAvailable) {
                        statusIcon.className = 'bi bi-check-circle text-success';
                        statusText.textContent = 'In Stock';
                    } else {
                        statusIcon.className = 'bi bi-x-circle text-danger';
                        statusText.textContent = 'Out of Stock';
                    }
                } else {
                    this.checked = !isAvailable;
                    alert('Failed to update availability');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !isAvailable;
                alert('An error occurred');
            });
        });
    });

    // View product details
    document.querySelectorAll('.view-details-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            
            document.getElementById('productDetailsContent').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            productDetailsModal.show();
            
            fetch(`/staff/inventory/${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const product = data.product;
                    const stats = data.statistics;
                    
                    document.getElementById('productDetailsContent').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Product Information</h6>
                                <table class="table">
                                    <tr><th>Name:</th><td>${product.name}</td></tr>
                                    <tr><th>Category:</th><td>${product.category.name}</td></tr>
                                    <tr><th>Price:</th><td>RM ${parseFloat(product.price).toFixed(2)}</td></tr>
                                    <tr><th>Status:</th><td><span class="badge bg-${product.is_available ? 'success' : 'danger'}">${product.is_available ? 'Available' : 'Unavailable'}</span></td></tr>
                                </table>
                                ${product.description ? `<p class="text-muted">${product.description}</p>` : ''}
                            </div>
                            <div class="col-md-6">
                                <h6>Sales Statistics</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <small class="text-muted">Total Sold</small>
                                            <h4>${stats.total_sold} units</h4>
                                        </div>
                                        <div>
                                            <small class="text-muted">Total Revenue</small>
                                            <h4 class="text-success">RM ${parseFloat(stats.total_revenue).toFixed(2)}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('productDetailsContent').innerHTML = `
                    <div class="alert alert-danger">Failed to load product details</div>
                `;
            });
        });
    });

    // Mark as sold
    document.querySelectorAll('.mark-sold-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            
            document.getElementById('soldProductId').value = productId;
            document.getElementById('soldProductName').textContent = productName;
            document.getElementById('soldQuantity').value = 1;
            
            markSoldModal.show();
        });
    });

    document.getElementById('confirmMarkSold').addEventListener('click', function() {
        const productId = document.getElementById('soldProductId').value;
        const quantity = document.getElementById('soldQuantity').value;
        
        if (quantity < 1) {
            alert('Please enter a valid quantity');
            return;
        }
        
        fetch(`/staff/inventory/${productId}/mark-sold`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ quantity: quantity })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                markSoldModal.hide();
            } else {
                alert('Failed to mark items as sold');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    });
});
</script>
@endpush
@endsection