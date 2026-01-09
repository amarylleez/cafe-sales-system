@extends('layouts.branch-manager')

@section('page-title', 'Inventory Management')

@push('styles')
<style>
    .category-scroll-container {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE/Edge */
    }
    .category-scroll-container::-webkit-scrollbar {
        display: none; /* Chrome/Safari */
    }
    .product-card:hover .remove-product-btn {
        opacity: 1;
    }
    .remove-product-btn {
        opacity: 0;
        transition: opacity 0.2s;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-box-seam"></i> Inventory Management
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                <i class="bi bi-plus-circle"></i> Add New Product
                            </button>
                            <form action="{{ route('branch-manager.inventory') }}" method="GET" style="max-width: 300px;">
                                @if($selectedCategory && $selectedCategory !== 'all')
                                <input type="hidden" name="category" value="{{ $selectedCategory }}">
                                @endif
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" name="search" placeholder="Search products..." value="{{ request('search') }}">
                                    <button class="btn btn-light" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Manage products, availability, and stock levels for your branch.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-3 fw-semibold" style="white-space: nowrap;">
                            <i class="bi bi-filter"></i> Filter:
                        </span>
                        <div class="category-scroll-container" style="overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch;">
                            <div class="d-inline-flex gap-2 pb-1">
                                <a href="{{ route('branch-manager.inventory', array_merge(['category' => 'all'], request('search') ? ['search' => request('search')] : [])) }}" 
                                   class="btn btn-sm {{ !$selectedCategory || $selectedCategory === 'all' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                                    <i class="bi bi-grid-3x3-gap"></i> All
                                </a>
                                @foreach($categories as $category)
                                <a href="{{ route('branch-manager.inventory', array_merge(['category' => $category->id], request('search') ? ['search' => request('search')] : [])) }}" 
                                   class="btn btn-sm {{ $selectedCategory == $category->id ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                                    {{ $category->name }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row g-3" id="productsContainer">
        @foreach($paginatedProducts as $product)
        <div class="col-md-6 col-lg-4 col-xl-3 product-item" data-category="{{ $product->category_id }}" data-product-id="{{ $product->id }}">
            <div class="card shadow-sm h-100 product-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="card-title mb-1">{{ $product->name }}</h6>
                            <small class="text-muted">{{ $product->category->name }}</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-danger remove-product-btn" 
                                    data-product-id="{{ $product->id }}" 
                                    data-product-name="{{ $product->name }}"
                                    title="Remove Product">
                                <i class="bi bi-trash"></i>
                            </button>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input availability-toggle" 
                                       type="checkbox" 
                                       id="availability{{ $product->id }}"
                                       data-product-id="{{ $product->id }}"
                                       {{ $product->is_available ? 'checked' : '' }}>
                            </div>
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
                        @if($product->expiry_status)
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-muted">Expiry:</span>
                            @if($product->expiry_status === 'expired')
                                <span class="badge bg-danger">
                                    <i class="bi bi-exclamation-triangle"></i> Expired
                                </span>
                            @elseif($product->expiry_status === 'expiring_soon')
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock"></i> Expiring Soon
                                </span>
                            @else
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Fresh
                                </span>
                            @endif
                        </div>
                        @if($product->expiry_date)
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-muted small">Expires at:</span>
                            <span class="small {{ $product->expiry_status === 'expired' ? 'text-danger' : 'text-muted' }}">
                                {{ \Carbon\Carbon::parse($product->expiry_date)->format('d M, h:i A') }}
                            </span>
                        </div>
                        @endif
                        @endif
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
                            <i class="bi bi-{{ $product->is_available ? 'check-circle text-success' : 'x-circle text-danger' }}"></i>
                            {{ $product->is_available ? 'Available' : 'Unavailable' }}
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
            {{ $paginatedProducts->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Product Details Modal -->
<div class="modal fade" id="productDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-box-seam"></i> <span id="modalProductName">Product Details</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addProductForm">
                    <div class="mb-3">
                        <label for="productName" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="productName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="productCategory" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="productCategory" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productPrice" class="form-label">Selling Price (RM) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productCostPrice" class="form-label">Cost Price (RM) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="productCostPrice" name="cost_price" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="productInitialStock" class="form-label">Initial Stock Quantity</label>
                        <input type="number" class="form-control" id="productInitialStock" name="initial_stock" min="0" value="0">
                    </div>
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescription" name="description" rows="3" placeholder="Optional product description"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddProduct">
                    <i class="bi bi-plus-circle"></i> Add Product
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Product Confirmation Modal -->
<div class="modal fade" id="removeProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Remove Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove <strong id="removeProductName"></strong> from the inventory?</p>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-info-circle"></i> 
                    <small>If this product has sales history, it will only be removed from your branch. Otherwise, it will be permanently deleted.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveProduct">
                    <i class="bi bi-trash"></i> Remove Product
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="productSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <div class="success-checkmark mx-auto mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h3 class="text-success mb-3">Product Added Successfully!</h3>
                    <div class="alert alert-light border">
                        <strong>Product Name:</strong>
                        <div class="mt-2">
                            <span id="addedProductName" class="fs-5 text-primary fw-bold"></span>
                        </div>
                    </div>
                    <p class="text-muted mb-0">The product has been added to your inventory.</p>
                </div>
                <button type="button" class="btn btn-primary btn-lg px-5" id="productSuccessModalOk">
                    <i class="bi bi-check-circle"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Product Success Modal -->
<div class="modal fade" id="removeProductSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <div class="success-checkmark mx-auto mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h3 class="text-success mb-3">Product Removed Successfully!</h3>
                    <div class="alert alert-light border">
                        <strong>Product Name:</strong>
                        <div class="mt-2">
                            <span id="removedProductName" class="fs-5 text-primary fw-bold"></span>
                        </div>
                    </div>
                    <p class="text-muted mb-0" id="removeSuccessMessage">The product has been removed from your inventory.</p>
                </div>
                <button type="button" class="btn btn-primary btn-lg px-5" id="removeProductSuccessOk">
                    <i class="bi bi-check-circle"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productDetailsModal = new bootstrap.Modal(document.getElementById('productDetailsModal'));
    const addProductModal = new bootstrap.Modal(document.getElementById('addProductModal'));
    const removeProductModal = new bootstrap.Modal(document.getElementById('removeProductModal'));
    let productToRemove = null;

    // Toggle availability
    document.querySelectorAll('.availability-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const isAvailable = this.checked;
            
            fetch(`/branch-manager/inventory/${productId}/availability`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ is_available: isAvailable })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = this.closest('.card');
                    const statusIcon = card.querySelector('.card-footer i');
                    const statusText = card.querySelector('.card-footer small');
                    
                    if (isAvailable) {
                        statusIcon.className = 'bi bi-check-circle text-success';
                        statusText.innerHTML = '<i class="bi bi-check-circle text-success"></i> Available';
                    } else {
                        statusIcon.className = 'bi bi-x-circle text-danger';
                        statusText.innerHTML = '<i class="bi bi-x-circle text-danger"></i> Unavailable';
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

    // Add Product
    document.getElementById('confirmAddProduct').addEventListener('click', function() {
        const form = document.getElementById('addProductForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Validate required fields
        if (!data.name || !data.category_id || !data.price) {
            alert('Please fill in all required fields');
            return;
        }

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';

        fetch('/branch-manager/inventory/add-product', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                addProductModal.hide();
                form.reset();
                
                // Show success modal
                document.getElementById('addedProductName').textContent = data.product_name || 'New Product';
                const successModal = new bootstrap.Modal(document.getElementById('productSuccessModal'));
                successModal.show();
                
                // Reload when OK is clicked
                document.getElementById('productSuccessModalOk').addEventListener('click', function() {
                    location.reload();
                });
            } else {
                alert(data.message || 'Failed to add product');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the product');
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-plus-circle"></i> Add Product';
        });
    });

    // Remove Product - Show confirmation modal
    document.querySelectorAll('.remove-product-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            productToRemove = this.dataset.productId;
            document.getElementById('removeProductName').textContent = this.dataset.productName;
            removeProductModal.show();
        });
    });

    // Confirm Remove Product
    document.getElementById('confirmRemoveProduct').addEventListener('click', function() {
        if (!productToRemove) return;

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Removing...';

        const productName = document.getElementById('removeProductName').textContent;

        fetch(`/branch-manager/inventory/${productToRemove}/remove`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                removeProductModal.hide();
                
                // Remove the product card from DOM
                const productCard = document.querySelector(`.product-item[data-product-id="${productToRemove}"]`);
                if (productCard) {
                    productCard.remove();
                }
                
                // Show success modal
                document.getElementById('removedProductName').textContent = productName;
                document.getElementById('removeSuccessMessage').textContent = data.message;
                const removeSuccessModal = new bootstrap.Modal(document.getElementById('removeProductSuccessModal'));
                removeSuccessModal.show();
                
                // Reload when OK is clicked
                document.getElementById('removeProductSuccessOk').addEventListener('click', function() {
                    location.reload();
                });
            } else {
                alert(data.message || 'Failed to remove product');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing the product');
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-trash"></i> Remove Product';
            productToRemove = null;
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
            document.getElementById('modalProductName').textContent = 'Product Details';
            
            productDetailsModal.show();
            
            fetch(`/staff/inventory/${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const product = data.product;
                    const stats = data.statistics;
                    
                    document.getElementById('modalProductName').textContent = product.name;
                    
                    document.getElementById('productDetailsContent').innerHTML = `
                        <div class="row">
                            <!-- Left Column: Product Info & Edit Form -->
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Product Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <form id="editProductForm">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Name</label>
                                                <input type="text" class="form-control bg-light" value="${product.name}" readonly>
                                                <small class="text-muted">Product name cannot be changed</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Category</label>
                                                <input type="text" class="form-control bg-light" value="${product.category.name}" readonly>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Selling Price (RM) <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" id="editPrice" value="${parseFloat(product.price).toFixed(2)}" step="0.01" min="0" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Cost Price (RM)</label>
                                                    <input type="number" class="form-control" id="editCostPrice" value="${product.cost_price ? parseFloat(product.cost_price).toFixed(2) : ''}" step="0.01" min="0" placeholder="Optional">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Description / Notes</label>
                                                <textarea class="form-control" id="editDescription" rows="3" placeholder="Add notes or description...">${product.description || ''}</textarea>
                                            </div>
                                            <div class="d-grid">
                                                <button type="button" class="btn btn-primary" id="saveProductChanges" data-product-id="${product.id}">
                                                    <i class="bi bi-check-circle"></i> Save Changes
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column: Stats & Stock Management -->
                            <div class="col-md-6">
                                <!-- Sales Statistics -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="bi bi-graph-up"></i> Sales Statistics</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <small class="text-muted d-block">Total Sold</small>
                                                <h4 class="mb-0">${stats.total_sold} units</h4>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Total Revenue</small>
                                                <h4 class="mb-0 text-success">RM ${parseFloat(stats.total_revenue).toFixed(2)}</h4>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="text-center">
                                            <small class="text-muted d-block">Added to Stock</small>
                                            <span>${stats.added_date}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Stock Management -->
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bi bi-boxes"></i> Stock Management</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <small class="text-muted d-block">Current Stock</small>
                                            <h2 id="currentStockDisplay" class="mb-0 text-${stats.stock_quantity > 10 ? 'success' : (stats.stock_quantity > 0 ? 'warning' : 'danger')}">${stats.stock_quantity} units</h2>
                                            <small class="text-muted">${stats.stock_quantity > 10 ? 'Well Stocked' : (stats.stock_quantity > 0 ? 'Low Stock' : 'Out of Stock')}</small>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                                            <button type="button" class="btn btn-danger btn-lg" id="decreaseStockBtn" data-product-id="${product.id}" ${stats.stock_quantity <= 0 ? 'disabled' : ''}>
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                            <input type="number" id="stockAdjustAmount" class="form-control text-center" value="1" min="1" max="999" style="width: 100px; font-size: 1.2rem;">
                                            <button type="button" class="btn btn-success btn-lg" id="increaseStockBtn" data-product-id="${product.id}">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                        <div class="text-center">
                                            <small class="text-muted">Click <span class="text-danger">-</span> to remove or <span class="text-success">+</span> to add stock</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Attach handlers
                    attachProductEditHandlers(product.id);
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
    
    // Product edit save handler
    function attachProductEditHandlers(productId) {
        document.getElementById('saveProductChanges').addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
            
            const data = {
                price: parseFloat(document.getElementById('editPrice').value) || 0,
                cost_price: document.getElementById('editCostPrice').value ? parseFloat(document.getElementById('editCostPrice').value) : null,
                description: document.getElementById('editDescription').value
            };
            
            fetch(`/branch-manager/inventory/${productId}/update`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    btn.innerHTML = '<i class="bi bi-check-circle"></i> Saved!';
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-success');
                    
                    // Update the card on the main page
                    const productCard = document.querySelector(`[data-product-id="${productId}"]`);
                    if (productCard) {
                        const priceElement = productCard.querySelector('.text-primary strong, strong.text-primary');
                        if (priceElement) {
                            priceElement.textContent = 'RM ' + parseFloat(data.price).toFixed(2);
                        }
                    }
                    
                    setTimeout(() => {
                        btn.innerHTML = '<i class="bi bi-check-circle"></i> Save Changes';
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-primary');
                        btn.disabled = false;
                    }, 2000);
                } else {
                    alert(result.message || 'Failed to save changes');
                    btn.innerHTML = '<i class="bi bi-check-circle"></i> Save Changes';
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving');
                btn.innerHTML = '<i class="bi bi-check-circle"></i> Save Changes';
                btn.disabled = false;
            });
        });
    }
    
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
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        fetch(`/branch-manager/stock/${productId}/adjust`, {
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
                const stockDisplay = document.getElementById('currentStockDisplay');
                stockDisplay.textContent = data.new_quantity + ' units';
                
                // Update color based on stock level
                stockDisplay.className = 'mb-0 text-' + (data.new_quantity > 10 ? 'success' : (data.new_quantity > 0 ? 'warning' : 'danger'));
                
                // Update decrease button state
                const decreaseBtn = document.getElementById('decreaseStockBtn');
                decreaseBtn.disabled = data.new_quantity <= 0;
                
                // Update the card on the main page
                const productCard = document.querySelector(`[data-product-id="${productId}"]`);
                if (productCard) {
                    const stockBadge = productCard.querySelector('.badge.bg-info, .badge.bg-warning, .badge.bg-danger');
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
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
});
</script>
@endpush
@endsection


