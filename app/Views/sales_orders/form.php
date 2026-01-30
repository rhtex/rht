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
                <li class="breadcrumb-item"><a href="<?= base_url('sales_orders') ?>">Sales Orders</a></li>
                <li class="breadcrumb-item active"><?= $order ? 'Edit' : 'Create' ?></li>
            </ol>
        </div>
    </div>

    <form action="<?= $order ? site_url('sales_orders/update/' . $order['id']) : site_url('sales_orders/store') ?>" method="post">
        <?= csrf_field() ?>
        
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Order Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select select2" required>
                                <option value="">-- Select Customer --</option>
                                <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>" <?= ($order && $order['customer_id'] == $customer['id']) ? 'selected' : '' ?>>
                                    <?= esc($customer['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Order Date <span class="text-danger">*</span></label>
                            <input type="date" name="order_date" class="form-control" value="<?= $order['order_date'] ?? date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Expected Shipment</label>
                            <input type="date" name="shipment_date" class="form-control" value="<?= $order['shipment_date'] ?? date('Y-m-d', strtotime('+3 days')) ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Order Number</label>
                            <input type="text" name="sales_order_number" class="form-control fw-bold" value="<?= $order['sales_order_number'] ?? $order_number ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">P.O number</label>
                            <input type="text" name="reference_number" class="form-control" value="<?= $order['reference_number'] ?? '' ?>" placeholder="e.g. Purchase Order #">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Agent (Optional)</label>
                            <select name="agent_id" id="agentSelect" class="form-select select2">
                                <option value="">-- Select Agent --</option>
                                <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent['id'] ?>" data-commission="<?= $agent['commission_percentage'] ?>" <?= ($order && $order['agent_id'] == $agent['id']) ? 'selected' : '' ?>>
                                    <?= esc($agent['agent_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Transport Name</label>
                            <select name="transport_name" class="form-select select2">
                                <option value="">-- Select Transport --</option>
                                <?php foreach ($transports as $transport): ?>
                                <option value="<?= esc($transport['transport_name']) ?>" <?= ($order && $order['transport_name'] == $transport['transport_name']) ? 'selected' : '' ?>>
                                    <?= esc($transport['transport_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Waybill / LR Number</label>
                            <input type="text" name="waybill_number" class="form-control" value="<?= $order['waybill_number'] ?? '' ?>" placeholder="e.g. 123456789">
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
                                <th width="5%">S.No</th>
                                <th width="35%">Description</th>
                                <th width="10%">HSN</th>
                                <th width="10%">Qty</th>
                                <th width="12%">Rate (₹)</th>
                                <th width="10%">GST %</th>
                                <th width="15%">Amount (₹)</th>
                                <th width="8%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <?php if ($order && !empty($order['items'])): ?>
                                <?php foreach ($order['items'] as $index => $item): ?>
                                    <tr class="item-row">
                                        <td class="text-center fw-bold index-column"><?= $index + 1 ?></td>
                                        <td>
                                            <select name="items[<?= $index ?>][product_id]" class="form-control product-select select2">
                                                <option value="">-- Select Product --</option>
                                                <?php foreach($products as $product): ?>
                                                    <option value="<?= $product['id'] ?>" data-hsn="<?= $product['hsn_code'] ?>" data-rate="<?= $product['selling_price'] ?>" data-gst="<?= $product['tax_percentage'] ?>" <?= ($item['product_id'] == $product['id']) ? 'selected' : '' ?>>
                                                        <?= esc($product['product_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="items[<?= $index ?>][description]" value="<?= esc($item['description']) ?>">
                                        </td>
                                        <td><input type="text" name="items[<?= $index ?>][hsn_code]" class="form-control hsn" value="<?= esc($item['hsn_code']) ?>"></td>
                                        <td><input type="number" name="items[<?= $index ?>][quantity]" class="form-control qty" value="<?= $item['quantity'] ?>" step="0.01" min="0.01" required onchange="calculateRow(this)"></td>
                                        <td><input type="number" name="items[<?= $index ?>][rate]" class="form-control rate" value="<?= $item['rate'] ?>" step="0.01" min="0" required onchange="calculateRow(this)"></td>
                                        <td>
                                            <select name="items[<?= $index ?>][tax_percentage]" class="form-control gst" onchange="calculateRow(this)">
                                                <option value="0">0%</option>
                                                <?php foreach($taxes as $tax): ?>
                                                    <option value="<?= $tax['percentage'] ?>" <?= ($item['tax_percentage'] == $tax['percentage']) ? 'selected' : '' ?>>
                                                        <?= $tax['percentage'] ?>%
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control amount" readonly value="<?= number_format($item['amount'], 2) ?>"></td>
                                        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="item-row">
                                    <td class="text-center fw-bold index-column">1</td>
                                    <td>
                                        <select name="items[0][product_id]" class="form-control product-select select2">
                                            <option value="">-- Select Product --</option>
                                            <?php foreach($products as $product): ?>
                                                <option value="<?= $product['id'] ?>" data-hsn="<?= $product['hsn_code'] ?>" data-rate="<?= $product['selling_price'] ?>" data-gst="<?= $product['tax_percentage'] ?>">
                                                    <?= esc($product['product_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="items[0][description]">
                                    </td>
                                    <td><input type="text" name="items[0][hsn_code]" class="form-control hsn"></td>
                                    <td><input type="number" name="items[0][quantity]" class="form-control qty" value="1" step="0.01" min="0.01" required onchange="calculateRow(this)"></td>
                                    <td><input type="number" name="items[0][rate]" class="form-control rate" value="0" step="0.01" min="0" required onchange="calculateRow(this)"></td>
                                    <td>
                                        <select name="items[0][tax_percentage]" class="form-control gst" onchange="calculateRow(this)">
                                            <option value="0">0%</option>
                                            <?php foreach($taxes as $tax): ?>
                                                <option value="<?= $tax['percentage'] ?>">
                                                    <?= $tax['percentage'] ?>%
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
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
                                            <option value="Fixed" <?= ($order['discount_type'] ?? '') == 'Fixed' ? 'selected' : '' ?>>₹</option>
                                            <option value="Percentage" <?= ($order['discount_type'] ?? '') == 'Percentage' ? 'selected' : '' ?>>%</option>
                                        </select>
                                        <input type="number" name="discount_amount" id="discount_amount" class="form-control form-control-sm" value="<?= $order['discount_amount'] ?? 0 ?>" min="0" step="0.01" onchange="calculateTotals()">
                                    </div>
                                </td>
                                <td><input type="text" id="calculatedDiscount" class="form-control text-danger" readonly value="0.00"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">Total Tax:</td>
                                <td><input type="text" id="taxAmount" class="form-control" readonly value="0.00"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">Shipping:</td>
                                <td><input type="number" name="shipping_charge" id="shipping_charge" class="form-control" value="<?= $order['shipping_charge'] ?? 0 ?>" step="0.01" onchange="calculateTotals()"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">Roundoff:</td>
                                <td><input type="number" name="roundoff_amount" id="roundoff_amount" class="form-control" value="<?= $order['roundoff_amount'] ?? 0 ?>" step="0.01" onchange="calculateTotals()"></td>
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

        <div class="card card-outline card-info">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"><?= $order['notes'] ?? '' ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Terms & Conditions</label>
                        <textarea name="terms" class="form-control" rows="3"><?= $order['terms'] ?? '1. Standard sales terms apply.' ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-3">
            <a href="<?= site_url('sales_orders') ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Order</button>
        </div>
    </form>
</div>

<script>
let rowIndex = <?= $order && !empty($order['items']) ? count($order['items']) : 1 ?>;

function addLineItem() {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.className = 'item-row';
    const productOptions = document.querySelector('.product-select').innerHTML;
    const taxOptions = document.querySelector('.gst').innerHTML;
    
    row.innerHTML = `
        <td class="text-center fw-bold index-column">${document.querySelectorAll('.item-row').length + 1}</td>
        <td>
            <select name="items[${rowIndex}][product_id]" class="form-control product-select select2">
                ${productOptions}
            </select>
            <input type="hidden" name="items[${rowIndex}][description]">
        </td>
        <td><input type="text" name="items[${rowIndex}][hsn_code]" class="form-control hsn"></td>
        <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control qty" value="1" step="0.01" min="0.01" required onchange="calculateRow(this)"></td>
        <td><input type="number" name="items[${rowIndex}][rate]" class="form-control rate" value="0" step="0.01" min="0" required onchange="calculateRow(this)"></td>
        <td>
            <select name="items[${rowIndex}][tax_percentage]" class="form-control gst" onchange="calculateRow(this)">
                ${taxOptions}
            </select>
        </td>
        <td><input type="text" class="form-control amount" readonly value="0.00"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
    initSelect2(row.querySelector('.select2'));
    rowIndex++;
}

function updateProductDetails(select) {
    const option = select.options[select.selectedIndex];
    const row = select.closest('tr');
    if (option.value) {
        row.querySelector('.hsn').value = option.dataset.hsn || '';
        row.querySelector('.rate').value = option.dataset.rate || 0;
        row.querySelector('.gst').value = option.dataset.gst || 0;
        row.querySelector('input[name*="[description]"]').value = option.text;
    }
    calculateRow(select);
}

function removeRow(btn) {
    if (document.querySelectorAll('.item-row').length > 1) {
        btn.closest('tr').remove();
        updateIndices();
        calculateTotals();
    }
}

function updateIndices() {
    document.querySelectorAll('.item-row').forEach((row, index) => {
        row.querySelector('.index-column').textContent = index + 1;
    });
}

function calculateRow(input) {
    const row = input.closest('tr');
    const qty = parseFloat(row.querySelector('.qty').value) || 0;
    const rate = parseFloat(row.querySelector('.rate').value) || 0;
    row.querySelector('.amount').value = (qty * rate).toFixed(2);
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    const items = [];
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const rate = parseFloat(row.querySelector('.rate').value) || 0;
        const gst = parseFloat(row.querySelector('.gst').value) || 0;
        const amt = qty * rate;
        subtotal += amt;
        items.push({ amt, gst });
    });

    const disc = parseFloat(document.getElementById('discount_amount').value) || 0;
    const type = document.getElementById('discount_type').value;
    const totalDisc = (type === 'Percentage') ? (subtotal * disc / 100) : disc;

    let totalTax = 0;
    items.forEach(item => {
        const itemDisc = (subtotal > 0) ? (item.amt / subtotal * totalDisc) : 0;
        totalTax += (item.amt - itemDisc) * (item.gst / 100);
    });

    const shipping = parseFloat(document.getElementById('shipping_charge').value) || 0;
    const roundoff = parseFloat(document.getElementById('roundoff_amount').value) || 0;

    document.getElementById('subtotal').value = subtotal.toFixed(2);
    document.getElementById('calculatedDiscount').value = totalDisc.toFixed(2);
    document.getElementById('taxAmount').value = totalTax.toFixed(2);
    document.getElementById('totalAmount').value = (subtotal - totalDisc + totalTax + shipping + roundoff).toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
    
    // Initialize Select2
    $('.select2').each(function() {
        $(this).select2({
            theme: 'bootstrap-5',
            placeholder: $(this).data('placeholder') || '-- Select --',
            allowClear: true,
            width: '100%'
        });
    });
});

// Re-initialize Select2 for dynamic rows
function initSelect2(element) {
    const config = {
        theme: 'bootstrap-5',
        placeholder: '-- Select --',
        allowClear: true,
        width: '100%'
    };
    
    if (element) {
        const select = $(element);
        select.select2(config);
        if (select.hasClass('product-select')) {
            select.on('select2:select', function(e) {
                updateProductDetails(this);
            });
        }
    } else {
        const selects = $('.select2');
        selects.select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
        $('.product-select').on('select2:select', function(e) {
            updateProductDetails(this);
        });
    }
}
</script>
<?= $this->endSection() ?>
