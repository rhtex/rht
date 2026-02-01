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
                <li class="breadcrumb-item"><a href="<?= base_url('invoices') ?>">Invoices</a></li>
                <li class="breadcrumb-item active"><?= $invoice ? 'Edit' : 'Create' ?></li>
            </ol>
        </div>
    </div>

    <form id="invoiceForm"
        action="<?= $invoice ? site_url('invoices/update/' . $invoice['id']) : site_url('invoices/store') ?>"
        method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="is_inter_state" id="isInterStateField"
            value="<?= $invoice['is_inter_state'] ?? 0 ?>">

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Invoice Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customerSelect" class="form-select select2" required>
                                <option value="">-- Select Customer --</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['id'] ?>" <?= ($invoice && $invoice['customer_id'] == $customer['id']) ? 'selected' : '' ?>>
                                        <?= esc($customer['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="mt-2">
                                <span id="taxModeIndicator" class="badge bg-secondary" style="display: none;">
                                    <i class="fas fa-info-circle"></i> <span id="taxModeText">Select customer to see tax
                                        mode</span>
                                </span>
                            </div>
                            <div id="customerAddressSection" class="row mt-3" style="display: none;">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small text-uppercase fw-bold mb-1 d-block"><i
                                            class="fas fa-file-invoice text-primary me-1"></i> Billing Address</label>
                                    <div id="billingAddressDisplay" class="text-dark small border p-2 rounded bg-light"
                                        style="white-space: pre-wrap; min-height: 80px;"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small text-uppercase fw-bold mb-1 d-block"><i
                                            class="fas fa-truck text-primary me-1"></i> Shipping Address</label>
                                    <div id="shippingAddressDisplay" class="text-dark small border p-2 rounded bg-light"
                                        style="white-space: pre-wrap; min-height: 80px;"></div>
                                </div>
                                <div class="col-md-12">
                                    <div id="contactInfoSection" class="row border-top pt-2 mt-1">
                                        <div class="col-md-3 mb-2">
                                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block"><i
                                                    class="fas fa-phone text-primary me-1"></i> Phone</label>
                                            <div id="customerPhone" class="small text-dark"></div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block"><i
                                                    class="fab fa-whatsapp text-success me-1"></i> WhatsApp</label>
                                            <div id="customerWhatsapp" class="small text-dark"></div>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block"><i
                                                    class="fas fa-id-card text-info me-1"></i> GST Details</label>
                                            <div class="small">
                                                <span id="customerGstType" class="badge bg-secondary me-1"></span>
                                                <span id="customerGstin" class="fw-bold text-dark"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" id="invoiceDate" class="form-control"
                                value="<?= $invoice['invoice_date'] ?? date('Y-m-d') ?>" required
                                onchange="calculateDueDate()">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label text-primary">Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" id="dueDate" class="form-control fw-bold border-primary"
                                value="<?= $invoice['due_date'] ?? date('Y-m-d', strtotime('+30 days')) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control fw-bold"
                                value="<?= $invoice['invoice_number'] ?? $invoice_number ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">P.O number</label>
                            <input type="text" name="reference_number" class="form-control"
                                value="<?= $invoice['reference_number'] ?? '' ?>" placeholder="e.g. PO number">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Agent (Optional)</label>
                            <select name="agent_id" id="agentSelect" class="form-select select2">
                                <option value="">-- Select Agent --</option>
                                <?php foreach ($agents as $agent): ?>
                                    <option value="<?= $agent['id'] ?>"
                                        data-commission="<?= $agent['commission_percentage'] ?>" <?= ($invoice && $invoice['agent_id'] == $agent['id']) ? 'selected' : '' ?>>
                                        <?= esc($agent['agent_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <!-- Hidden field to store commission percentage -->
                            <input type="hidden" name="agent_commission_percent" id="agentCommissionPercent"
                                value="<?= $invoice['agent_commission_percent'] ?? 0 ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Transport Name</label>
                            <select name="transport_name" class="form-select select2">
                                <option value="">-- Select Transport --</option>
                                <?php foreach ($transports as $transport): ?>
                                    <option value="<?= esc($transport['transport_name']) ?>" <?= ($invoice && $invoice['transport_name'] == $transport['transport_name']) ? 'selected' : '' ?>>
                                        <?= esc($transport['transport_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">E-Waybill No.</label>
                            <input type="text" name="ewaybill_number" class="form-control"
                                value="<?= $invoice['ewaybill_number'] ?? '' ?>" placeholder="E-Waybill No">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">No. of Packages</label>
                            <input type="number" name="packages_count" class="form-control"
                                value="<?= $invoice['packages_count'] ?? '' ?>" placeholder="Qty">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">Line Items</h3>
                <div class="card-tools d-flex gap-2">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                        <input type="text" id="barcodeScanner" class="form-control" placeholder="Scan Barcode here...">
                    </div>
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
                            <?php if ($invoice && !empty($invoice['items'])): ?>
                                <?php foreach ($invoice['items'] as $index => $item): ?>
                                    <tr class="item-row">
                                        <td class="text-center fw-bold index-column"><?= $index + 1 ?></td>
                                        <td>
                                            <select name="items[<?= $index ?>][product_id]"
                                                class="form-control product-select select2">
                                                <option value="">-- Select Product --</option>
                                                <?php foreach ($products as $product): ?>
                                                    <option value="<?= $product['id'] ?>" data-hsn="<?= $product['hsn_code'] ?>"
                                                        data-rate="<?= $product['selling_price'] ?>"
                                                        data-gst="<?= $product['tax_percentage'] ?>"
                                                        <?= ($item['product_id'] == $product['id']) ? 'selected' : '' ?>>
                                                        <?= esc($product['product_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="items[<?= $index ?>][description]"
                                                value="<?= esc($item['description']) ?>">
                                        </td>
                                        <td><input type="text" name="items[<?= $index ?>][hsn_code]" class="form-control hsn"
                                                value="<?= esc($item['hsn_code']) ?>"></td>
                                        <td><input type="number" name="items[<?= $index ?>][quantity]" class="form-control qty"
                                                value="<?= $item['quantity'] ?>" step="0.01" min="0.01" required
                                                onchange="calculateRow(this)"></td>
                                        <td><input type="number" name="items[<?= $index ?>][rate]" class="form-control rate"
                                                value="<?= $item['rate'] ?>" step="0.01" min="0" required
                                                onchange="calculateRow(this)"></td>
                                        <td>
                                            <select name="items[<?= $index ?>][tax_percentage]" class="form-control gst"
                                                onchange="calculateRow(this)">
                                                <option value="0">0%</option>
                                                <?php foreach ($taxes as $tax): ?>
                                                    <option value="<?= $tax['percentage'] ?>"
                                                        <?= ($item['tax_percentage'] == $tax['percentage']) ? 'selected' : '' ?>>
                                                        <?= $tax['percentage'] ?>%
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php $tax_type = (!empty($item['igst_rate']) && $item['igst_rate'] > 0) ? 'IGST' : 'CGST_SGST'; ?>
                                            <input type="hidden" name="items[<?= $index ?>][tax_type]" class="tax_type"
                                                value="<?= $tax_type ?>">
                                        </td>
                                        <td><input type="text" class="form-control amount" readonly
                                                value="<?= number_format($item['amount'], 2) ?>"></td>
                                        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i
                                                    class="fas fa-trash"></i></button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="item-row">
                                    <td class="text-center fw-bold index-column">1</td>
                                    <td>
                                        <select name="items[0][product_id]" class="form-control product-select select2">
                                            <option value="">-- Select Product --</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?= $product['id'] ?>" data-hsn="<?= $product['hsn_code'] ?>"
                                                    data-rate="<?= $product['selling_price'] ?>"
                                                    data-gst="<?= $product['tax_percentage'] ?>">
                                                    <?= esc($product['product_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="items[0][description]">
                                    </td>
                                    <td><input type="text" name="items[0][hsn_code]" class="form-control hsn"></td>
                                    <td><input type="number" name="items[0][quantity]" class="form-control qty" value="1"
                                            step="0.01" min="0.01" required onchange="calculateRow(this)"></td>
                                    <td><input type="number" name="items[0][rate]" class="form-control rate" value="0"
                                            step="0.01" min="0" required onchange="calculateRow(this)"></td>
                                    <td>
                                        <select name="items[0][tax_percentage]" class="form-control gst"
                                            onchange="calculateRow(this)">
                                            <option value="0">0%</option>
                                            <?php foreach ($taxes as $tax): ?>
                                                <option value="<?= $tax['percentage'] ?>">
                                                    <?= $tax['percentage'] ?>%
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="items[0][tax_type]" class="tax_type" value="CGST_SGST">
                                    </td>
                                    <td><input type="text" class="form-control amount" readonly value="0.00"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i
                                                class="fas fa-trash"></i></button></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <td colspan="6" class="text-end fw-bold">Subtotal:</td>
                                <td><input type="text" id="subtotal" class="form-control fw-bold" readonly value="0.00">
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end">
                                    <div class="input-group input-group-sm justify-content-end"
                                        style="width: 250px; float: right;">
                                        <span class="input-group-text">Discount</span>
                                        <select name="discount_type" id="discount_type"
                                            class="form-select form-select-sm" style="max-width: 80px;"
                                            onchange="calculateTotals()">
                                            <option value="Fixed" <?= ($invoice['discount_type'] ?? '') == 'Fixed' ? 'selected' : '' ?>>₹</option>
                                            <option value="Percentage" <?= ($invoice['discount_type'] ?? '') == 'Percentage' ? 'selected' : '' ?>>%</option>
                                        </select>
                                        <input type="number" name="discount_amount" id="discount_amount"
                                            class="form-control form-control-sm"
                                            value="<?= $invoice['discount_amount'] ?? 0 ?>" min="0" step="0.01"
                                            onchange="calculateTotals()">
                                    </div>
                                </td>
                                <td><input type="text" id="calculatedDiscount" class="form-control text-danger" readonly
                                        value="0.00"></td>
                                <td></td>
                            </tr>
                            <tbody id="taxBreakdownBody">
                                <!-- Dynamic tax rows -->
                            </tbody>
                            <tr>
                                <td colspan="6" class="text-end">Total Tax:</td>
                                <td><input type="text" id="taxAmount" class="form-control" readonly value="0.00"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end">Shipping Charges:</td>
                                <td><input type="number" name="shipping_charge" id="shipping_charge"
                                        class="form-control" value="<?= $invoice['shipping_charge'] ?? 0 ?>" min="0"
                                        step="0.01" onchange="calculateTotals()"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end">Roundoff:</td>
                                <td><input type="number" name="roundoff_amount" id="roundoff_amount"
                                        class="form-control" value="<?= $invoice['roundoff_amount'] ?? 0 ?>" step="0.01"
                                        onchange="calculateTotals()"></td>
                                <td></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="6" class="text-end fw-bold fs-5">Total:</td>
                                <td><input type="text" id="totalAmount" name="total_amount"
                                        class="form-control fw-bold fs-5" readonly value="0.00"></td>
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
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="Notes for the customer..."><?= $invoice['notes'] ?? '' ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea name="terms" class="form-control" rows="3"
                                placeholder="Terms for the customer..."><?= $invoice['terms'] ?? '1. Goods once sold cannot be taken back or exchanged.
2. Please verify the items at the time of delivery.' ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="d-flex justify-content-end gap-2 mb-3">
            <a href="<?= site_url('invoices') ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            <button type="submit" id="submitBtn" class="btn btn-primary"><i class="fas fa-save"></i> Save
                Invoice</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let rowIndex = <?= $invoice && !empty($invoice['items']) ? count($invoice['items']) : 1 ?>;
    let isInterState = <?= (isset($invoice) && $invoice['is_inter_state']) ? 'true' : 'false' ?>;
    console.log('Initial isInterState:', isInterState);
    let _prevIsInterState = isInterState;
    let currentCreditDays = 0;

    // Global caches for clean HTML templates
    let _productOptionsHtml = '';
    let _taxOptionsHtml = '';

    function calculateDueDate() {
        const invoiceDateVal = document.getElementById('invoiceDate').value;
        if (!invoiceDateVal) return;

        const date = new Date(invoiceDateVal);
        date.setDate(date.getDate() + parseInt(currentCreditDays));

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        document.getElementById('dueDate').value = `${year}-${month}-${day}`;
    }

    function addLineItem() {
        console.log('Adding new line item...');
        const tbody = document.getElementById('itemsBody');
        const row = document.createElement('tr');
        row.className = 'item-row';

        // Use clean cached options if available, otherwise fallback to DOM
        const productOptions = _productOptionsHtml || document.querySelector('.product-select').innerHTML;
        const taxOptions = _taxOptionsHtml || document.querySelector('.gst').innerHTML;

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
            <input type="hidden" name="items[${rowIndex}][tax_type]" class="tax_type" value="${isInterState ? 'IGST' : 'CGST_SGST'}">
        </td>
        <td><input type="text" class="form-control amount" readonly value="0.00"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
    `;
        tbody.appendChild(row);

        const newSelect = row.querySelector('.select2');
        initSelect2(newSelect);

        // Set tax type according to current mode
        const tt = row.querySelector('.tax_type');
        if (tt) tt.value = isInterState ? 'IGST' : 'CGST_SGST';

        rowIndex++;
        updateIndices();
        console.log('New row added successfully.');
    }

    function updateProductDetails(select) {
        console.log('updateProductDetails triggered for:', select);
        const $select = $(select);
        const $option = $select.find(':selected');

        if (!$option.val()) {
            console.log('No option selected, ignoring.');
            return;
        }

        const row = $select.closest('tr')[0];
        if (!row) {
            console.error('Could not find parent row for select element');
            return;
        }

        const hsn = $option.attr('data-hsn') || '';
        const rate = parseFloat($option.attr('data-rate')) || 0;
        const gstAttr = $option.attr('data-gst');

        console.log('Product Data:', { hsn, rate, gstAttr });

        row.querySelector('.hsn').value = hsn;
        row.querySelector('.rate').value = rate.toFixed(2);

        // Robustly select GST percentage
        if (gstAttr !== undefined && gstAttr !== null) {
            const gstVal = parseFloat(gstAttr);
            const gstSelect = row.querySelector('.gst');
            let matched = false;

            for (let i = 0; i < gstSelect.options.length; i++) {
                if (parseFloat(gstSelect.options[i].value) === gstVal) {
                    gstSelect.selectedIndex = i;
                    matched = true;
                    break;
                }
            }

            if (!matched) {
                gstSelect.value = "0"; // Fallback to 0 if no match
            }
        }

        // Set description to product name if empty
        const descInput = row.querySelector('input[name*="[description]"]');
        if (descInput && (!descInput.value || descInput.value === '')) {
            descInput.value = $option.text().trim();
        }

        calculateRow(select);
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) {
            btn.closest('tr').remove();
            updateIndices();
            calculateTotals();
        } else {
            alert('At least one item is required.');
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
        const amount = qty * rate;
        row.querySelector('.amount').value = amount.toFixed(2);
        calculateTotals();
    }

    function calculateTotals() {
        let grossSubtotal = 0;
        const itemRows = document.querySelectorAll('.item-row');
        const itemData = [];

        itemRows.forEach(row => {
            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            const rate = parseFloat(row.querySelector('.rate').value) || 0;
            const amount = qty * rate;
            const gstPercentage = parseFloat(row.querySelector('.gst').value) || 0;

            grossSubtotal += amount;
            itemData.push({ amount, gstPercentage });
        });

        const discountInput = parseFloat(document.getElementById('discount_amount').value) || 0;
        const discountType = document.getElementById('discount_type').value;
        let totalDiscount = 0;
        if (discountType === 'Percentage') {
            totalDiscount = (grossSubtotal * discountInput) / 100;
        } else {
            totalDiscount = discountInput;
        }

        let taxBreakdown = {};
        let totalTax = 0;

        itemData.forEach(item => {
            const itemDiscount = (grossSubtotal > 0) ? (item.amount / grossSubtotal * totalDiscount) : 0;
            const taxableValue = item.amount - itemDiscount;
            const taxAmount = (taxableValue * item.gstPercentage) / 100;

            if (item.gstPercentage > 0) {
                taxBreakdown[item.gstPercentage] = (taxBreakdown[item.gstPercentage] || 0) + taxAmount;
                totalTax += taxAmount;
            }
        });

        const taxBody = document.getElementById('taxBreakdownBody');
        taxBody.innerHTML = ''; // Always clear before recalculating

        console.log('Calculating Totals - InterState:', isInterState);

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

        const shipping = parseFloat(document.getElementById('shipping_charge').value) || 0;
        const roundoff = parseFloat(document.getElementById('roundoff_amount').value) || 0;

        const netTotal = (grossSubtotal - totalDiscount) + totalTax + shipping + roundoff;

        document.getElementById('subtotal').value = grossSubtotal.toFixed(2);
        document.getElementById('calculatedDiscount').value = totalDiscount.toFixed(2);
        document.getElementById('taxAmount').value = totalTax.toFixed(2);
        document.getElementById('totalAmount').value = netTotal.toFixed(2);
    }

    // Fetch customer state and info when customer is selected
    // Note: We use 'change' and 'select2:select' to ensure it works with/without Select2
    $(document).on('change select2:select', '#customerSelect', function () {
        const customerId = $(this).val();
        console.log('Customer selection detected - ID:', customerId);

        const taxModeIndicator = $('#taxModeIndicator');
        const taxModeText = $('#taxModeText');

        if (!customerId) {
            console.log('No customer selected, resetting tax mode.');
            isInterState = false;
            $('#isInterStateField').val(0);
            taxModeIndicator.hide();
            $('#customerAddressSection').hide();
            $('#billingAddressDisplay').text('');
            $('#shippingAddressDisplay').text('');
            calculateTotals();
            return;
        }

        console.log('Fetching state for customer:', customerId);

        fetch('<?= site_url('customers/details') ?>/' + customerId)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                console.log('Customer State Data Received:', data);
                isInterState = !!data.is_inter_state;

                $('#isInterStateField').val(isInterState ? 1 : 0);
                currentCreditDays = data.credit_period_days || 0;

                // Update addresses
                $('#customerAddressSection').show();
                $('#billingAddressDisplay').text(data.billing_address || 'N/A');
                $('#shippingAddressDisplay').text(data.shipping_address || 'N/A');

                // Update tax mode indicator
                taxModeIndicator.show();
                if (isInterState) {
                    console.log('Switching UI to Inter-State (IGST)');
                    taxModeIndicator.removeClass('bg-secondary bg-success').addClass('bg-info');
                    taxModeText.text('Inter-State Transaction (IGST)');
                } else {
                    console.log('Switching UI to Intra-State (CGST + SGST)');
                    taxModeIndicator.removeClass('bg-secondary bg-info').addClass('bg-success');
                    taxModeText.text('Intra-State Transaction (CGST + SGST)');
                }

                // Update contact info
                $('#customerPhone').text(data.phone || 'N/A');
                $('#customerWhatsapp').text(data.whatsapp_number || 'N/A');
                $('#customerGstType').text(data.gst_type || 'Unregistered');
                $('#customerGstin').text(data.gstin || 'N/A');

                // Update tax option labels and enforce types for all rows
                updateTaxOptions(isInterState);
                enforceTaxType(isInterState);

                _prevIsInterState = isInterState;
                calculateDueDate();
                calculateTotals();

                // Auto Select Agent if assigned to customer
                if (data.agent_id) {
                    $('#agentSelect').val(data.agent_id).trigger('change');
                    if (data.agent_commission) {
                        $('#agentCommissionPercent').val(data.agent_commission);
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching customer state:', error);
                // Fallback to intra-state if fetch fails to avoid breaking form
                taxModeIndicator.show().removeClass('bg-info bg-success').addClass('bg-warning');
                taxModeText.text('Tax mode unknown (Error fetching state)');
            });
    });

    // Update GST select option labels to indicate IGST vs CGST+SGST
    function updateTaxOptions(interState) {
        document.querySelectorAll('.gst').forEach(select => {
            for (let i = 0; i < select.options.length; i++) {
                const opt = select.options[i];
                const pct = opt.value ? parseFloat(opt.value) : 0;
                if (pct === 0) {
                    opt.text = '0%';
                } else if (interState) {
                    opt.text = pct + '% (IGST)';
                } else {
                    // Show split label for intra-state
                    const half = (pct / 2);
                    opt.text = pct + '% (CGST ' + half + '% + SGST ' + half + '%)';
                }
            }
        });
    }

    // Reset GST selections when tax mode changes — clears previously selected taxes that may be invalid
    function resetInvalidTaxSelections() {
        document.querySelectorAll('.gst').forEach(select => {
            // If current selected option is not present or is non-numeric, reset to 0
            const val = select.value;
            if (!val || isNaN(parseFloat(val))) {
                select.value = '0';
            }
            // Trigger recalculation
        });
    }

    // Enforce tax type hidden inputs per row (IGST vs CGST_SGST)
    function enforceTaxType(interState) {
        document.querySelectorAll('.item-row').forEach((row, index) => {
            const taxTypeInput = row.querySelector('.tax_type');
            if (taxTypeInput) {
                taxTypeInput.value = interState ? 'IGST' : 'CGST_SGST';
            } else {
                // If missing (unlikely), create one to be safe
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `items[${index}][tax_type]`;
                hidden.className = 'tax_type';
                hidden.value = interState ? 'IGST' : 'CGST_SGST';
                row.appendChild(hidden);
            }
        });
    }

    // This will be initialized in initSelect2

    // Barcode Scanner Logic
    document.getElementById('barcodeScanner').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const barcode = this.value.trim();
            if (barcode) {
                fetchProductByBarcode(barcode);
            }
            this.value = '';
        }
    });

    function fetchProductByBarcode(barcode) {
        fetch('<?= site_url('products/barcode') ?>/' + barcode)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    addProductToInvoice(data.product);
                } else {
                    alert(data.message || 'Product not found');
                }
            })
            .catch(error => {
                console.error('Error fetching product:', error);
                alert('Error connecting to server');
            });
    }

    function addProductToInvoice(product) {
        // 1. Check if product already exists in rows
        let existingRow = null;
        document.querySelectorAll('.item-row').forEach(row => {
            const select = row.querySelector('.product-select');
            if (select && select.value == product.id) {
                existingRow = row;
            }
        });

        if (existingRow) {
            // Increment quantity
            const qtyInput = existingRow.querySelector('.qty');
            qtyInput.value = parseFloat(qtyInput.value) + 1;
            calculateRow(qtyInput);
        } else {
            // Add new row
            const tbody = document.getElementById('itemsBody');
            // If the first row is empty/default, remove it or fill it
            const rows = document.querySelectorAll('.item-row');
            if (rows.length === 1) {
                const firstRowSelect = rows[0].querySelector('.product-select');
                if (!firstRowSelect.value) {
                    fillRow(rows[0], product);
                    return;
                }
            }

            // Create new row
            const row = document.createElement('tr');
            row.className = 'item-row';

            // Use clean cached options if available, otherwise fallback to DOM
            const productOptions = _productOptionsHtml || document.querySelector('.product-select').innerHTML;
            const taxOptions = _taxOptionsHtml || document.querySelector('.gst').innerHTML;

            row.innerHTML = `
            <td class="text-center fw-bold index-column">${document.querySelectorAll('.item-row').length + 1}</td>
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-control product-select select2">
                    ${productOptions}
                </select>
                <input type="hidden" name="items[${rowIndex}][description]" value="${product.product_name}">
            </td>
            <td><input type="text" name="items[${rowIndex}][hsn_code]" class="form-control hsn" value="${product.hsn_code || ''}"></td>
            <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control qty" value="1" step="0.01" min="0.01" required onchange="calculateRow(this)"></td>
            <td><input type="number" name="items[${rowIndex}][rate]" class="form-control rate" value="${product.selling_price || 0}" step="0.01" min="0" required onchange="calculateRow(this)"></td>
            <td>
                <select name="items[${rowIndex}][tax_percentage]" class="form-control gst" onchange="calculateRow(this)">
                    ${taxOptions}
                </select>
                <input type="hidden" name="items[${rowIndex}][tax_type]" class="tax_type" value="${isInterState ? 'IGST' : 'CGST_SGST'}">
            </td>
            <td><input type="text" class="form-control amount" readonly value="0.00"></td>
            <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
        `;
            tbody.appendChild(row);
            const select = row.querySelector('.select2');
            initSelect2(select);
            $(select).val(product.id).trigger('change');

            // Manual tax setting since trigger('change') might not set it from data attrs for dynamic row
            row.querySelector('.gst').value = product.tax_percentage || 0;
            // Set tax type based on current mode
            const taxTypeInput = row.querySelector('.tax_type');
            if (taxTypeInput) taxTypeInput.value = isInterState ? 'IGST' : 'CGST_SGST';

            calculateRow(select[0]);
            updateIndices();
            rowIndex++;
        }
    }

    function fillRow(row, product) {
        const select = row.querySelector('.product-select');
        $(select).val(product.id).trigger('change');

        // Ensure detail population
        row.querySelector('.hsn').value = product.hsn_code || '';
        row.querySelector('.rate').value = product.selling_price || 0;
        row.querySelector('.gst').value = product.tax_percentage || 0;
        row.querySelector('input[name*="[description]"]').value = product.product_name;
        const taxTypeInput = row.querySelector('.tax_type');
        if (taxTypeInput) taxTypeInput.value = isInterState ? 'IGST' : 'CGST_SGST';

        calculateRow(select);
    }

    // Initializations moved to consolidated $(document).ready block below

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
            // Destroy if already initialized to avoid issues
            if (select.data('select2')) {
                select.select2('destroy');
            }

            select.select2(config);

            // Specifically for agent selection
            if (select.attr('id') === 'agentSelect') {
                select.on('change', function () {
                    const selectedOption = $(this).find(':selected');
                    const commission = selectedOption.data('commission') || 0;
                    $('#agentCommissionPercent').val(commission);
                });
            }
        } else {
            // Initialize all select2 elements on the page
            $('.select2').each(function () {
                initSelect2(this);
            });
        }
    }
</script>
<script>
    $(document).ready(function () {
        // 1. Capture clean HTML templates before Select2 init
        const $productSelect = $('.product-select').first();
        const $gstSelect = $('.gst').first();

        if ($productSelect.length) {
            const $clone = $productSelect.clone();
            $clone.find('option').prop('selected', false).removeAttr('selected');
            _productOptionsHtml = $clone.html();
            console.log('Captured clean product options template.');
        }

        if ($gstSelect.length) {
            const $clone = $gstSelect.clone();
            $clone.find('option').prop('selected', false).removeAttr('selected');
            _taxOptionsHtml = $clone.html();
            console.log('Captured clean tax options template.');
        }

        // 2. Initialize all Select2 elements once
        initSelect2();

        // 3. Delegated event handler for product selection (handles dynamic rows automatically)
        $(document).on('change select2:select', '.product-select', function () {
            console.log('Delegated selection event fired.');
            updateProductDetails(this);
        });

        // 4. Initial UI update for established isInterState
        const taxModeIndicator = $('#taxModeIndicator');
        const taxModeText = $('#taxModeText');

        if (isInterState) {
            taxModeIndicator.show();
            taxModeIndicator.removeClass('bg-secondary bg-success').addClass('bg-info');
            taxModeText.text('Inter-State Transaction (IGST)');
            updateTaxOptions(true); // Ensure labels are correct
        } else if ($('#customerSelect').val()) {
            taxModeIndicator.show();
            taxModeIndicator.removeClass('bg-secondary bg-info').addClass('bg-success');
            taxModeText.text('Intra-State Transaction (CGST + SGST)');
            updateTaxOptions(false);
        } else {
            taxModeIndicator.hide();
        }

        // 3. Trigger change logic if a customer is already selected (e.g., on Edit page)
        const initialCustomer = $('#customerSelect').val();
        if (initialCustomer) {
            console.log('Page Load - triggering customer change logic for:', initialCustomer);
            $('#customerSelect').trigger('change');
        } else {
            calculateTotals(); // Just in case
        }

        // Prevent double submission
        $('#invoiceForm').on('submit', function () {
            const $btn = $('#submitBtn');
            $btn.prop('disabled', true);
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            return true;
        });
    });
</script>
<?= $this->endSection() ?>
```