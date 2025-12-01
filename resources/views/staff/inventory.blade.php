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
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-muted">Stock:</span>
                            <span class="badge bg-{{ $product->stock_quantity > 10 ? 'info' : ($product->stock_quantity > 0 ? 'warning' : 'danger') }}">
                                {{ $product->stock_quantity }} units
                            </span>
                        </div>
                    </div>

                    @if($product->description)
                    <p class="card-text small text-muted mb-3">{{ Str::limit($product->description, 80) }}</p>
                    @endif

                    <div class="d-grid gap-2">
                        <button class="btn btn-sm btn-outline-primary view-details-btn" data-product-id="{{ $product->id }}">
                            <i class="bi bi-eye"></i> View Details
                        </button>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-{{ $product->stock_quantity > 0 ? 'check-circle text-success' : 'x-circle text-danger' }}"></i>
                            {{ $product->stock_quantity > 0 ? 'In Stock' : 'Out of Stock' }}
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



@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productDetailsModal = new bootstrap.Modal(document.getElementById('productDetailsModal'));

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
            const toggleElement = this;
            
            fetch(`/staff/inventory/${productId}/availability`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ is_available: isAvailable })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const label = toggleElement.nextElementSibling.querySelector('small');
                    label.textContent = isAvailable ? 'Available' : 'Unavailable';
                    
                    const card = toggleElement.closest('.card');
                    const footer = card.querySelector('.card-footer');
                    const statusIcon = footer.querySelector('i');
                    const statusSmall = footer.querySelector('small');
                    
                    if (isAvailable) {
                        statusIcon.className = 'bi bi-check-circle text-success';
                        statusSmall.innerHTML = '<i class="bi bi-check-circle text-success"></i> In Stock';
                    } else {
                        statusIcon.className = 'bi bi-x-circle text-danger';
                        statusSmall.innerHTML = '<i class="bi bi-x-circle text-danger"></i> Out of Stock';
                    }
                } else {
                    toggleElement.checked = !isAvailable;
                    alert('Failed to update availability');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toggleElement.checked = !isAvailable;
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
                                    <tr><th>Added to Stock:</th><td>${stats.added_date}</td></tr>
                                </table>
                                ${product.description ? `<p class="text-muted">${product.description}</p>` : ''}
                            </div>
                            <div class="col-md-6">
                                <h6>Sales Statistics</h6>
                                <div class="card bg-light mb-3">
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
                                
                                <h6>Stock Management</h6>
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <small class="text-muted">Current Stock</small>
                                            <h3 id="currentStockDisplay" class="mb-0">${stats.stock_quantity} units</h3>
                                        </div>
                                        <div class="d-flex justify-content-center align-items-center gap-2">
                                            <button type="button" class="btn btn-danger btn-lg" id="decreaseStockBtn" data-product-id="${product.id}" ${stats.stock_quantity <= 0 ? 'disabled' : ''}>
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                            <input type="number" id="stockAdjustAmount" class="form-control text-center" value="1" min="1" max="100" style="width: 80px;">
                                            <button type="button" class="btn btn-success btn-lg" id="increaseStockBtn" data-product-id="${product.id}">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                        <div class="text-center mt-2">
                                            <small class="text-muted">Click - to remove or + to add stock</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Attach stock adjustment handlers
                    attachStockHandlers(product.id, stats.stock_quantity);
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
    
    // Stock adjustment handlers
    function attachStockHandlers(productId, currentStock) {
        let stockCount = currentStock;
        
        document.getElementById('increaseStockBtn').addEventListener('click', function() {
            const amount = parseInt(document.getElementById('stockAdjustAmount').value) || 1;
            adjustStock(productId, amount, 'add');
        });
        
        document.getElementById('decreaseStockBtn').addEventListener('click', function() {
            const amount = parseInt(document.getElementById('stockAdjustAmount').value) || 1;
            if (stockCount >= amount) {
                adjustStock(productId, amount, 'remove');
            } else {
                alert('Cannot remove more than current stock');
            }
        });
    }
    
    function adjustStock(productId, amount, type) {
        const btn = type === 'add' ? document.getElementById('increaseStockBtn') : document.getElementById('decreaseStockBtn');
        btn.disabled = true;
        
        fetch(`/staff/stock/${productId}/adjust`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ quantity: amount, type: type })
        })
        .then(response => {
            if (!response.ok) throw new Error('Server error');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('currentStockDisplay').textContent = data.new_quantity + ' units';
                
                // Update decrease button state
                const decreaseBtn = document.getElementById('decreaseStockBtn');
                decreaseBtn.disabled = data.new_quantity <= 0;
                
                // Update the card on the main page
                const card = document.querySelector(`[data-product-id="${productId}"]`).closest('.card');
                if (card) {
                    const stockBadge = card.querySelector('.badge.bg-info, .badge.bg-warning, .badge.bg-danger');
                    if (stockBadge && stockBadge.textContent.includes('units')) {
                        stockBadge.textContent = data.new_quantity + ' units';
                        stockBadge.className = 'badge bg-' + (data.new_quantity > 10 ? 'info' : (data.new_quantity > 0 ? 'warning' : 'danger'));
                    }
                }
            } else {
                alert(data.message || 'Failed to adjust stock');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adjusting stock');
        })
        .finally(() => {
            btn.disabled = false;
        });
    }
});
</script>
@endpush
@endsection
