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
                            <label for="productCostPrice" class="form-label">Cost Price (RM)</label>
                            <input type="number" class="form-control" id="productCostPrice" name="cost_price" step="0.01" min="0" placeholder="Auto-calculate if empty">
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
                                
                                <h6>Current Stock</h6>
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0 text-${stats.stock_quantity > 10 ? 'success' : (stats.stock_quantity > 0 ? 'warning' : 'danger')}">${stats.stock_quantity} units</h3>
                                        <small class="text-muted">${stats.stock_quantity > 10 ? 'Well Stocked' : (stats.stock_quantity > 0 ? 'Low Stock' : 'Out of Stock')}</small>
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
});
</script>
@endpush
@endsection


