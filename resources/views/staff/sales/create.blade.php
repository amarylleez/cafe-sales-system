@extends('layouts.staff')

@section('page-title', 'Input Daily Sales')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cart-plus"></i> Record New Sale
                    </h5>
                </div>
                <div class="card-body">
                    <form id="salesForm">
                        @csrf
                        
                        <!-- Sale Date -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="sale_date" class="form-label">Sale Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="sale_date" name="sale_date" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="cash" selected>Cash</option>
                                    <option value="card">Card</option>
                                    <option value="e-wallet">E-Wallet</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div class="mb-3">
                            <label for="payment_details" class="form-label">Payment Details</label>
                            <input type="text" class="form-control" id="payment_details" name="payment_details" 
                                   placeholder="e.g., Reference number, card last 4 digits">
                        </div>

                        <!-- Items Section -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Sale Items <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-sm btn-primary" id="addItemBtn">
                                    <i class="bi bi-plus-circle"></i> Add Item
                                </button>
                            </div>
                            
                            <div id="itemsContainer">
                                <!-- Item rows will be added here -->
                            </div>
                            
                            <div class="alert alert-info mt-2" id="noItemsAlert">
                                <i class="bi bi-info-circle"></i> Click "Add Item" to start adding products to this sale.
                            </div>
                        </div>

                        <!-- Total Amount Display -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <strong>Total Items:</strong>
                                        <h4 id="totalItems">0</h4>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Subtotal:</strong>
                                        <h4 id="subtotalAmount">RM 0.00</h4>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Total Amount:</strong>
                                        <h4 class="text-primary" id="totalAmount">RM 0.00</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Any additional information about this sale"></textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Save Sale
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg" onclick="window.location.href='{{ route('staff.dashboard') }}'">
                                <i class="bi bi-x-circle"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Row Template -->
<template id="itemRowTemplate">
    <div class="card mb-2 item-row">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Product <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm product-select" name="items[INDEX][product_id]" required>
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" 
                                data-price="{{ $product->price }}"
                                data-stock="{{ $product->stock_quantity }}">
                            {{ $product->name }} ({{ $product->category->name }}) - RM {{ number_format($product->price, 2) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 text-center">
                    <label class="form-label small mb-1">Stock</label>
                    <div class="stock-info">
                        <span class="badge bg-secondary">-</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Qty <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm quantity-input" name="items[INDEX][quantity]" 
                           min="1" value="1" required>
                    <small class="text-danger stock-warning" style="display: none; font-size: 10px;">Exceeds stock!</small>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Unit Price <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm price-input" name="items[INDEX][unit_price]" 
                           step="0.01" min="0" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label small mb-1">Discount</label>
                    <input type="number" class="form-control form-control-sm discount-input" name="items[INDEX][discount]" 
                           step="0.01" min="0" value="0">
                </div>
                <div class="col-md-1 text-center">
                    <label class="form-label small mb-1">Total</label>
                    <div class="item-total fw-bold text-primary">RM 0.00</div>
                </div>
                <div class="col-md-1 text-center">
                    <label class="form-label small mb-1">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm remove-item-btn">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
let itemIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    const addItemBtn = document.getElementById('addItemBtn');
    const itemsContainer = document.getElementById('itemsContainer');
    const noItemsAlert = document.getElementById('noItemsAlert');
    const salesForm = document.getElementById('salesForm');
    const template = document.getElementById('itemRowTemplate');

    // Add first item automatically
    addItem();

    // Add Item Button
    addItemBtn.addEventListener('click', addItem);

    function addItem() {
        const clone = template.content.cloneNode(true);
        const itemRow = clone.querySelector('.item-row');
        
        // Replace INDEX with actual index
        itemRow.innerHTML = itemRow.innerHTML.replace(/INDEX/g, itemIndex);
        
        // Add event listeners
        const productSelect = itemRow.querySelector('.product-select');
        const quantityInput = itemRow.querySelector('.quantity-input');
        const priceInput = itemRow.querySelector('.price-input');
        const discountInput = itemRow.querySelector('.discount-input');
        const removeBtn = itemRow.querySelector('.remove-item-btn');

        productSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.dataset.price;
            const stock = selectedOption.dataset.stock;
            priceInput.value = price || 0;
            
            // Show stock info in separate column
            const stockInfo = itemRow.querySelector('.stock-info');
            if (stock !== undefined && this.value) {
                stockInfo.innerHTML = `<span class="badge bg-${stock > 10 ? 'success' : (stock > 0 ? 'warning' : 'danger')}">${stock}</span>`;
                quantityInput.max = stock;
            } else {
                stockInfo.innerHTML = '<span class="badge bg-secondary">-</span>';
                quantityInput.removeAttribute('max');
            }
            
            calculateItemTotal(itemRow);
            checkStockWarning(itemRow);
        });

        quantityInput.addEventListener('input', () => {
            calculateItemTotal(itemRow);
            checkStockWarning(itemRow);
        });
        priceInput.addEventListener('input', () => calculateItemTotal(itemRow));
        discountInput.addEventListener('input', () => calculateItemTotal(itemRow));

        removeBtn.addEventListener('click', function() {
            itemRow.remove();
            calculateGrandTotal();
            
            if (itemsContainer.children.length === 0) {
                noItemsAlert.style.display = 'block';
            }
        });

        itemsContainer.appendChild(itemRow);
        noItemsAlert.style.display = 'none';
        itemIndex++;
    }

    function calculateItemTotal(itemRow) {
        const quantity = parseFloat(itemRow.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(itemRow.querySelector('.price-input').value) || 0;
        const discount = parseFloat(itemRow.querySelector('.discount-input').value) || 0;
        
        const subtotal = quantity * price;
        const total = subtotal - discount;
        
        itemRow.querySelector('.item-total').textContent = 'RM ' + total.toFixed(2);
        calculateGrandTotal();
    }
    
    function checkStockWarning(itemRow) {
        const productSelect = itemRow.querySelector('.product-select');
        const quantityInput = itemRow.querySelector('.quantity-input');
        const stockWarning = itemRow.querySelector('.stock-warning');
        
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const stock = parseInt(selectedOption.dataset.stock) || 0;
        const quantity = parseInt(quantityInput.value) || 0;
        
        if (productSelect.value && quantity > stock) {
            stockWarning.style.display = 'block';
            quantityInput.classList.add('is-invalid');
        } else {
            stockWarning.style.display = 'none';
            quantityInput.classList.remove('is-invalid');
        }
    }

    function calculateGrandTotal() {
        let totalItems = 0;
        let subtotal = 0;
        let totalDiscount = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
            
            totalItems += quantity;
            subtotal += (quantity * price);
            totalDiscount += discount;
        });

        const grandTotal = subtotal - totalDiscount;

        document.getElementById('totalItems').textContent = totalItems;
        document.getElementById('subtotalAmount').textContent = 'RM ' + subtotal.toFixed(2);
        document.getElementById('totalAmount').textContent = 'RM ' + grandTotal.toFixed(2);
    }

    // Form Submission
    salesForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (itemsContainer.children.length === 0) {
            alert('Please add at least one item to the sale.');
            return;
        }
        
        // Check for stock warnings
        let hasStockError = false;
        document.querySelectorAll('.item-row').forEach((row) => {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const stock = parseInt(selectedOption.dataset.stock) || 0;
            const quantity = parseInt(quantityInput.value) || 0;
            
            if (productSelect.value && quantity > stock) {
                hasStockError = true;
            }
        });
        
        if (hasStockError) {
            alert('Some items exceed available stock. Please adjust quantities.');
            return;
        }

        const formData = new FormData(this);
        const items = [];

        document.querySelectorAll('.item-row').forEach((row, idx) => {
            items.push({
                product_id: row.querySelector('.product-select').value,
                quantity: row.querySelector('.quantity-input').value,
                unit_price: row.querySelector('.price-input').value,
                discount: row.querySelector('.discount-input').value || 0
            });
        });

        const data = {
            sale_date: formData.get('sale_date'),
            payment_method: formData.get('payment_method'),
            payment_details: formData.get('payment_details'),
            notes: formData.get('notes'),
            items: items
        };

        // Show loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        // Submit via AJAX
        fetch('{{ route("staff.sales.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content || formData.get('_token')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Sale recorded successfully! Transaction ID: ' + data.transaction_id);
                window.location.href = '{{ route("staff.dashboard") }}';
            } else {
                alert('Error: ' + data.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the sale.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});
</script>
@endpush
@endsection