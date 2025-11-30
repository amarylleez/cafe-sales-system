@extends('layouts.staff')

@section('page-title', 'Stock Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-boxes"></i> Stock Management
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
                    <p class="text-muted">View and update stock quantities for products.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">In Stock</h6>
                            <h3 class="mb-0">{{ $products->where('stock_quantity', '>', 10)->count() }}</h3>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Low Stock</h6>
                            <h3 class="mb-0">{{ $products->whereBetween('stock_quantity', [1, 10])->count() }}</h3>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Out of Stock</h6>
                            <h3 class="mb-0">{{ $products->where('stock_quantity', '<=', 0)->count() }}</h3>
                        </div>
                        <i class="bi bi-x-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Products</h6>
                            <h3 class="mb-0">{{ $products->count() }}</h3>
                        </div>
                        <i class="bi bi-box-seam fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Filter -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="btn-group flex-wrap" role="group">
                <button type="button" class="btn btn-outline-primary active" data-category="all">All Categories</button>
                @foreach($categories as $category)
                <button type="button" class="btn btn-outline-primary" data-category="{{ $category->id }}">
                    {{ $category->name }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Current Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="stockTableBody">
                                @foreach($products as $product)
                                <tr class="product-row" data-category="{{ $product->category_id }}">
                                    <td>#{{ $product->id }}</td>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                        @if($product->description)
                                        <br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $product->category->name }}</td>
                                    <td>RM {{ number_format($product->price, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $product->stock_quantity > 10 ? 'success' : ($product->stock_quantity > 0 ? 'warning' : 'danger') }} fs-6">
                                            {{ $product->stock_quantity }} units
                                        </span>
                                    </td>
                                    <td>
                                        @if($product->stock_quantity > 10)
                                            <span class="badge bg-success">In Stock</span>
                                        @elseif($product->stock_quantity > 0)
                                            <span class="badge bg-warning text-dark">Low Stock</span>
                                        @else
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary add-stock-btn" 
                                                data-product-id="{{ $product->id }}"
                                                data-product-name="{{ $product->name }}"
                                                data-current-stock="{{ $product->stock_quantity }}">
                                            <i class="bi bi-plus-circle"></i> Add Stock
                                        </button>
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

<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add Stock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addStockForm">
                    <input type="hidden" id="stockProductId" name="product_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control" id="stockProductName" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" class="form-control" id="stockCurrentQuantity" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity to Add <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="stockAddQuantity" name="quantity" min="1" required>
                        <div class="form-text">Enter the number of units to add to stock.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="stockNotes" name="notes" rows="2" placeholder="e.g., Restocked from supplier"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddStock">
                    <i class="bi bi-plus-circle"></i> Add Stock
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addStockModal = new bootstrap.Modal(document.getElementById('addStockModal'));
    
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.product-row').forEach(row => {
            const productName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const categoryName = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            
            if (productName.includes(searchTerm) || categoryName.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Category filter
    document.querySelectorAll('[data-category]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-category]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const category = this.dataset.category;
            
            document.querySelectorAll('.product-row').forEach(row => {
                if (category === 'all' || row.dataset.category === category) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    
    // Add stock button click
    document.querySelectorAll('.add-stock-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('stockProductId').value = this.dataset.productId;
            document.getElementById('stockProductName').value = this.dataset.productName;
            document.getElementById('stockCurrentQuantity').value = this.dataset.currentStock + ' units';
            document.getElementById('stockAddQuantity').value = '';
            document.getElementById('stockNotes').value = '';
            
            addStockModal.show();
        });
    });
    
    // Confirm add stock
    document.getElementById('confirmAddStock').addEventListener('click', function() {
        const productId = document.getElementById('stockProductId').value;
        const quantity = document.getElementById('stockAddQuantity').value;
        const notes = document.getElementById('stockNotes').value;
        
        if (!quantity || quantity < 1) {
            alert('Please enter a valid quantity');
            return;
        }
        
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
        
        fetch(`/staff/stock/${productId}/add`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ quantity: parseInt(quantity), notes: notes })
        })
        .then(response => {
            if (!response.ok) throw new Error('Server error');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                addStockModal.hide();
                // Reload page to show updated stock
                location.reload();
            } else {
                alert(data.message || 'Failed to add stock');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding stock');
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-plus-circle"></i> Add Stock';
        });
    });
});
</script>
@endpush
