<?= $this->extend('layouts/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?= $title ?></h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('bills') ?>">Bills</a></li>
                <li class="breadcrumb-item active"><?= $bill ? 'Edit' : 'Create' ?></li>
            </ol>
        </div>
    </div>

    <form action="<?= $bill ? site_url('bills/update/' . $bill['id']) : site_url('bills/store') ?>" method="post">
        <?= csrf_field() ?>
        
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Bill Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Vendor <span class="text-danger">*</span></label>
                            <select name="vendor_id" id="vendorSelect" class="form-select select2" required>
                                <option value="">-- Select Vendor --</option>
                                <?php foreach ($vendors as $vendor): ?>
                                <option value="<?= $vendor['id'] ?>" <?= ($bill && $bill['vendor_id'] == $vendor['id']) ? 'selected' : '' ?>>
                                    <?= esc($vendor['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Bill Date <span class="text-danger">*</span></label>
                            <input type="date" name="bill_date" class="form-control" value="<?= $bill['bill_date'] ?? date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control" value="<?= $bill['due_date'] ?? date('Y-m-d', strtotime('+30 days')) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Reference Number (Vendor Invoice #)</label>
                            <input type="text" name="reference_number" class="form-control" value="<?= $bill['reference_number'] ?? '' ?>" placeholder="Vendor's invoice number">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">Line Items</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" onclick="addLineItem()">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="25%">Description</th>
                                <th width="12%">HSN Code</th>
                                <th width="10%">Quantity</th>
                                <th width="12%">Rate (₹)</th>
                                <th width="10%">GST %</th>
                                <th width="15%">Amount (₹)</th>
                                <th width="8%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <?php if ($bill && !empty($bill['items'])): ?>
                                <?php foreach ($bill['items'] as $index => $item): ?>
                                    <tr class="item-row">
                                        <td><input type="text" name="items[<?= $index ?>][description]" class="form-control" value="<?= esc($item['description']) ?>" required></td>
                                        <td><input type="text" name="items[<?= $index ?>][hsn_code]" class="form-control" value="<?= esc($item['hsn_code']) ?>"></td>
                                        <td><input type="number" name="items[<?= $index ?>][quantity]" class="form-control qty" value="<?= $item['quantity'] ?>" step="0.01" min="0.01" required onchange="calculateRow(this)"></td>
                                        <td><input type="number" name="items[<?= $index ?>][rate]" class="form-control rate" value="<?= $item['rate'] ?>" step="0.01" min="0" required onchange="calculateRow(this)"></td>
                                        <td>
                                            <select name="items[<?= $index ?>][tax_percentage]" class="form-control gst" onchange="updateTaxId(this); calculateRow(this)">
                                                <option value="0" data-id="">0%</option>
                                                <?php foreach($taxes as $tax): ?>
                                                    <option value="<?= $tax['percentage'] ?>" data-id="<?= $tax['id'] ?>" <?= ($item['tax_percentage'] == $tax['percentage']) ? 'selected' : '' ?>>
                                                        <?= esc($tax['name']) ?> (<?= $tax['percentage'] ?>%)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="items[<?= $index ?>][tax_id]" class="tax-id" value="<?= $item['tax_id'] ?? '' ?>">
                                        </td>
                                        <td><input type="text" class="form-control amount" readonly value="<?= number_format($item['amount'], 2) ?>"></td>
                                        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="item-row">
                                    <td><input type="text" name="items[0][description]" class="form-control" required></td>
                                    <td><input type="text" name="items[0][hsn_code]" class="form-control"></td>
                                    <td><input type="number" name="items[0][quantity]" class="form-control qty" value="1" step="0.01" min="0.01" required onchange="calculateRow(this)"></td>
                                    <td><input type="number" name="items[0][rate]" class="form-control rate" value="0" step="0.01" min="0" required onchange="calculateRow(this)"></td>
                                    <td>
                                        <select name="items[0][tax_percentage]" class="form-control gst" onchange="updateTaxId(this); calculateRow(this)">
                                            <option value="0" data-id="">0%</option>
                                            <?php foreach($taxes as $tax): ?>
                                                <option value="<?= $tax['percentage'] ?>" data-id="<?= $tax['id'] ?>">
                                                    <?= esc($tax['name']) ?> (<?= $tax['percentage'] ?>%)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="items[0][tax_id]" class="tax-id">
                                    </td>
                                    <td><input type="text" class="form-control amount" readonly value="0.00"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                                <td><input type="text" id="subtotal" class="form-control fw-bold" readonly value="0.00"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">
                                    <div class="input-group input-group-sm justify-content-end" style="width: 250px; float: right;">
                                        <span class="input-group-text">Discount</span>
                                        <select name="discount_type" id="discount_type" class="form-select form-select-sm" style="max-width: 80px;" onchange="calculateTotals()">
                                            <option value="Amount" <?= ($bill['discount_type'] ?? '') == 'Amount' ? 'selected' : '' ?>>₹</option>
                                            <option value="Percentage" <?= ($bill['discount_type'] ?? '') == 'Percentage' ? 'selected' : '' ?>>%</option>
                                        </select>
                                        <input type="number" name="discount_amount" id="discount_amount" class="form-control form-control-sm" value="<?= $bill['discount_amount'] ?? 0 ?>" min="0" step="0.01" onchange="calculateTotals()">
                                    </div>
                                </td>
                                <td><input type="text" id="calculatedDiscount" class="form-control text-danger" readonly value="0.00"></td>
                                <td></td>
                            </tr>
                            <tbody id="taxBreakdownBody">
                                <!-- Dynamic tax rows will be inserted here -->
                            </tbody>
                            <tr>
                                <td colspan="5" class="text-end">Total Tax:</td>
                                <td><input type="text" id="taxAmount" class="form-control" readonly value="0.00"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">Shipping Charges:</td>
                                <td><input type="number" name="shipping_charge" id="shipping_charge" class="form-control" value="<?= $bill['shipping_charge'] ?? 0 ?>" min="0" step="0.01" onchange="calculateTotals()"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">Roundoff:</td>
                                <td><input type="number" name="roundoff_amount" id="roundoff_amount" class="form-control" value="<?= $bill['roundoff_amount'] ?? 0 ?>" step="0.01" onchange="calculateTotals()"></td>
                                <td></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="5" class="text-end fw-bold fs-5">Total:</td>
                                <td><input type="text" id="totalAmount" name="total_amount" class="form-control fw-bold fs-5" readonly value="0.00"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Notes and Terms -->
        <div class="card card-outline card-info">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"><?= $bill['notes'] ?? '' ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Payment Terms</label>
                            <textarea name="terms" class="form-control" rows="3"><?= $bill['terms'] ?? 'Payment due within 30 days' ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="d-flex justify-content-end gap-2 mb-3">
            <a href="<?= site_url('bills') ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Bill</button>
        </div>
    </form>
</div>

<script>
let rowIndex = <?= $bill && !empty($bill['items']) ? count($bill['items']) : 1 ?>;
let isInterState = false; // Will be set when vendor is selected

function addLineItem() {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.className = 'item-row';
    const taxOptions = document.querySelector('.gst').innerHTML;
    
    row.innerHTML = `
        <td><input type="text" name="items[${rowIndex}][description]" class="form-control" required></td>
        <td><input type="text" name="items[${rowIndex}][hsn_code]" class="form-control"></td>
        <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control qty" value="1" step="0.01" min="0.01" required onchange="calculateRow(this)"></td>
        <td><input type="number" name="items[${rowIndex}][rate]" class="form-control rate" value="0" step="0.01" min="0" required onchange="calculateRow(this)"></td>
        <td>
            <select name="items[${rowIndex}][tax_percentage]" class="form-control gst" onchange="updateTaxId(this); calculateRow(this)">
                ${taxOptions}
            </select>
            <input type="hidden" name="items[${rowIndex}][tax_id]" class="tax-id">
        </td>
        <td><input type="text" class="form-control amount" readonly value="0.00"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
    updateTaxId(row.querySelector('.gst'));
    rowIndex++;
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) {
        btn.closest('tr').remove();
        calculateTotals();
    } else {
        alert('At least one item is required.');
    }
}

function updateTaxId(select) {
    const option = select.options[select.selectedIndex];
    const taxId = option.getAttribute('data-id');
    const row = select.closest('tr');
    row.querySelector('.tax-id').value = taxId || '';
}

function calculateRow(input) {
    const row = input.closest('tr');
    const qty = parseFloat(row.querySelector('.qty').value) || 0;
    const rate = parseFloat(row.querySelector('.rate').value) || 0;
    const amount = qty * rate;
    row.querySelector('.amount').value = amount.toFixed(2);
    calculateTotals();
}

function calculateTotals() {
    let grossSubtotal = 0;
    const itemRows = document.querySelectorAll('.item-row');
    const itemData = [];

    // Pass 1: Calculate Gross Subtotal
    itemRows.forEach(row => {
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const rate = parseFloat(row.querySelector('.rate').value) || 0;
        const amount = qty * rate;
        const gstPercentage = parseFloat(row.querySelector('.gst').value) || 0;
        
        grossSubtotal += amount;
        itemData.push({ amount, gstPercentage });
    });

    // Calculate Discount
    const discountInput = parseFloat(document.getElementById('discount_amount').value) || 0;
    const discountType = document.getElementById('discount_type').value;
    let totalDiscount = 0;
    if (discountType === 'Percentage') {
        totalDiscount = (grossSubtotal * discountInput) / 100;
    } else {
        totalDiscount = discountInput;
    }

    // Pass 2: Calculate Taxes on Discounted Value
    let taxBreakdown = {};
    let totalTax = 0;
    
    itemData.forEach(item => {
        // Apportion discount proportional to item value
        const itemDiscount = (grossSubtotal > 0) ? (item.amount / grossSubtotal * totalDiscount) : 0;
        const taxableValue = item.amount - itemDiscount;
        const taxAmount = (taxableValue * item.gstPercentage) / 100;

        if (item.gstPercentage > 0) {
            taxBreakdown[item.gstPercentage] = (taxBreakdown[item.gstPercentage] || 0) + taxAmount;
            totalTax += taxAmount;
        }
    });

    // Generate dynamic tax rows
    const taxBody = document.getElementById('taxBreakdownBody');
    taxBody.innerHTML = '';
    
    Object.keys(taxBreakdown).sort((a, b) => a - b).forEach(percentage => {
        const amount = taxBreakdown[percentage];
        const rate = parseFloat(percentage);
        
        if (isInterState) {
            taxBody.innerHTML += `
                <tr>
                    <td colspan="5" class="text-end">IGST (${rate}%):</td>
                    <td><input type="text" class="form-control" readonly value="${amount.toFixed(2)}"></td>
                    <td></td>
                </tr>
            `;
        } else {
            const halfAmount = amount / 2;
            const halfRate = rate / 2;
            taxBody.innerHTML += `
                <tr>
                    <td colspan="5" class="text-end">CGST (${halfRate}%):</td>
                    <td><input type="text" class="form-control" readonly value="${halfAmount.toFixed(2)}"></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end">SGST (${halfRate}%):</td>
                    <td><input type="text" class="form-control" readonly value="${halfAmount.toFixed(2)}"></td>
                    <td></td>
                </tr>
            `;
        }
    });
    
    // Additional charges
    const shipping = parseFloat(document.getElementById('shipping_charge').value) || 0;
    const roundoff = parseFloat(document.getElementById('roundoff_amount').value) || 0;

    const netTotal = (grossSubtotal - totalDiscount) + totalTax + shipping + roundoff;
    
    document.getElementById('subtotal').value = grossSubtotal.toFixed(2);
    document.getElementById('calculatedDiscount').value = totalDiscount.toFixed(2);
    document.getElementById('taxAmount').value = totalTax.toFixed(2);
    document.getElementById('totalAmount').value = netTotal.toFixed(2);
}

// Fetch vendor state when vendor is selected
document.getElementById('vendorSelect').addEventListener('change', function() {
    const vendorId = this.value;
    if (!vendorId) {
        isInterState = false;
        calculateTotals();
        return;
    }
    
    fetch('<?= site_url('bills/vendor-state') ?>/' + vendorId)
        .then(response => response.json())
        .then(data => {
            isInterState = data.is_inter_state;
            console.log('Vendor state:', data.vendor_state_id, 'Company state:', data.company_state_id, 'Inter-state:', isInterState);
            calculateTotals();
        })
        .catch(error => {
            console.error('Error fetching vendor state:', error);
        });
});

// Calculate on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tax IDs for existing rows
    document.querySelectorAll('.gst').forEach(select => updateTaxId(select));
    
    // If editing, fetch vendor state
    const vendorSelect = document.getElementById('vendorSelect');
    if (vendorSelect.value) {
        vendorSelect.dispatchEvent(new Event('change'));
    } else {
        calculateTotals();
    }
});
</script>
<?= $this->endSection() ?>
