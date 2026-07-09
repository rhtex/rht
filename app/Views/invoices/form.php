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

        
        
<style>
    .gst-container {
        font-size: 0.85rem;
    }
    .gst-container .form-label {
        font-weight: 600;
        margin-bottom: 2px;
        color: #444;
        font-size: 0.75rem;
        text-transform: uppercase;
    }
    .gst-container .form-control, .gst-container .form-select {
        font-size: 0.85rem;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        height: auto;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        transition: all 0.2s ease;
    }
    .gst-container .form-control:focus, .gst-container .form-select:focus {
        border-color: #94a3b8;
        box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.15);
    }
    .gst-container .select2-container--bootstrap-5 .select2-selection {
        font-size: 0.85rem;
        padding: 2px 4px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        min-height: 28px;
    }
    .gst-table th {
        background-color: #f8fafc !important;
        color: #475569;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        vertical-align: middle;
        padding: 10px 6px !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }
    .gst-table td {
        padding: 4px !important;
        vertical-align: middle;
    }
    .gst-table .form-control {
        border: 1px solid transparent;
        background: transparent;
    }
    .gst-table .form-control:focus {
        border: 1px solid #86b7fe;
        background: #fff;
    }
    .gst-summary-table td {
        padding: 6px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #dee2e6;
    }
    .gst-summary-label {
        font-weight: 600;
        color: #333;
    }
    .grand-total-row {
        background-color: #f8fafc; color: #334155;
        font-size: 1.1rem;
        font-weight: bold;
    }
    .glass-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(2px);
        z-index: 10;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
    }
    .glass-overlay.active {
        opacity: 1;
        pointer-events: all;
    }
</style>

<div class="gst-container mb-4">
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
        
        <!-- TOP ROW: Party & Voucher Details -->
        <div class="row g-0 border-bottom border-secondary-subtle">
            
            <!-- LEFT: Party Details -->
            <div class="col-md-6 border-end border-secondary-subtle p-3">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <h6 class="fw-bold mb-0 text-uppercase"><i class="fas fa-user-tie me-1"></i> Billed To</h6>
                    <span id="taxModeIndicator" class="badge bg-secondary" style="display: none;">
                        <span id="taxModeText">Tax Mode</span>
                    </span>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customerSelect" class="form-select select2" required>
                        <option value="">-- Search Customer --</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>" <?= ($invoice && $invoice['customer_id'] == $customer['id']) ? 'selected' : '' ?>>
                                <?= esc($customer['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="customerAddressSection" style="display: none;">
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <label class="form-label text-primary">Billing Address</label>
                            <div id="billingAddressDisplay" class="border p-2 bg-light" style="min-height: 80px; white-space: pre-wrap;"></div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label text-primary">Shipping Address</label>
                            <div id="shippingAddressDisplay" class="border p-2 bg-light" style="min-height: 80px; white-space: pre-wrap;"></div>
                        </div>
                    </div>
                    
                    <div class="row mt-2 bg-light border p-2 m-0">
                        <div class="col-6">
                            <label class="form-label">Phone:</label> <span id="customerPhone" class="fw-bold">-</span><br>
                            <label class="form-label">WhatsApp:</label> <span id="customerWhatsapp" class="fw-bold text-success">-</span>
                        </div>
                        <div class="col-6">
                            <label class="form-label">GST Type:</label> <span id="customerGstType" class="badge bg-dark">-</span><br>
                            <label class="form-label">GSTIN:</label> <span id="customerGstin" class="fw-bold text-primary">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Voucher Details -->
            <div class="col-md-6 p-3">
                <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3"><i class="fas fa-file-invoice me-1"></i> Invoice Details</h6>
                
                <div class="row g-2 mb-2">
                    <div class="col-sm-6">
                        <label class="form-label">Invoice No.</label>
                        <input type="text" name="invoice_number" class="form-control fw-bold bg-light" value="<?= $invoice['invoice_number'] ?? $invoice_number ?>" readonly>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Agent (Optional)</label>
                        <select name="agent_id" id="agentSelect" class="form-select select2">
                            <option value="">-- Select Agent --</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent['id'] ?>" data-commission="<?= $agent['commission_percentage'] ?>" <?= ($invoice && $invoice['agent_id'] == $agent['id']) ? 'selected' : '' ?>>
                                    <?= esc($agent['agent_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="agent_commission_percent" id="agentCommissionPercent" value="<?= $invoice['agent_commission_percent'] ?? 0 ?>">
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-sm-6">
                        <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" name="invoice_date" id="invoiceDate" class="form-control" value="<?= $invoice['invoice_date'] ?? date('Y-m-d') ?>" required onchange="calculateDueDate()">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" id="dueDate" class="form-control text-danger fw-bold" value="<?= $invoice['due_date'] ?? date('Y-m-d', strtotime('+30 days')) ?>" required>
                    </div>
                </div>
                
                <div class="row g-2">
                    <div class="col-sm-6">
                        <label class="form-label">P.O Number</label>
                        <input type="text" name="reference_number" class="form-control" value="<?= $invoice['reference_number'] ?? '' ?>">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">P.O Date</label>
                        <input type="date" name="po_date" class="form-control" value="<?= $invoice['po_date'] ?? '' ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- DISPATCH BLOCK -->
        <div class="row g-0 border-bottom border-secondary-subtle bg-light p-2 align-items-end">
            <div class="col-md-4 px-2">
                <label class="form-label">Transport Name <span class="text-danger">*</span></label>
                <select name="transport_name" class="form-select select2" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($transports as $transport): ?>
                        <option value="<?= esc($transport['transport_name']) ?>" <?= ($invoice && $invoice['transport_name'] == $transport['transport_name']) ? 'selected' : '' ?>>
                            <?= esc($transport['transport_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 px-2">
                <label class="form-label">E-Waybill No.</label>
                <input type="text" name="ewaybill_number" class="form-control" value="<?= $invoice['ewaybill_number'] ?? '' ?>" placeholder="E-Waybill">
            </div>
            <div class="col-md-4 px-2">
                <label class="form-label">Packages <span class="text-danger">*</span></label>
                <input type="number" required name="packages_count" class="form-control" value="<?= $invoice['packages_count'] ?? '' ?>" placeholder="Qty">
            </div>
        </div>

        <!-- LINE ITEMS -->
        <div class="position-relative" id="lineItemsCardWrapper">
            <div class="d-flex justify-content-between align-items-center bg-light text-dark border-bottom border-secondary-subtle p-2">
                <h6 class="mb-0 text-uppercase fw-bold"><i class="fas fa-list me-1"></i> Item Details</h6>
                <div class="d-flex gap-2">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-light"><i class="fas fa-barcode"></i></span>
                        <input type="text" id="barcodeScanner" class="form-control" placeholder="Scan Barcode...">
                    </div>
                    <button type="button" class="btn btn-sm btn-success" onclick="addLineItem()">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>
            
            <div class="table-responsive position-relative p-0 m-0 border-bottom border-secondary-subtle">
                <!-- Blur Overlay -->
                <div class="glass-overlay <?= ($invoice && !empty($invoice['customer_id'])) ? '' : 'active' ?>" id="lineItemsOverlay">
                    <div class="bg-white p-3 border border-secondary-subtle text-center shadow">
                        <i class="fas fa-lock fs-3 text-muted mb-2"></i>
                        <h6 class="fw-bold text-uppercase">Customer Required</h6>
                        <p class="small text-muted mb-0">Select Billed To party first</p>
                    </div>
                </div>

                <table class="table table-bordered border-secondary-subtle table-sm mb-0 gst-table" id="itemsTable">
                    <thead>
                        <tr>
                            <th width="4%" class="text-center">S.No</th>
                            <th width="28%">Description of Goods</th>
                            <th width="12%">HSN/SAC</th>
                            <th width="10%" class="text-end">Qty</th>
                            <th width="12%" class="text-end">Rate (₹)</th>
                            <th width="12%" class="text-end">GST %</th>
                            <th width="16%" class="text-end">Amount (₹)</th>
                            <th width="6%" class="text-center">Del</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <?php if ($invoice && !empty($invoice['items'])): ?>
                            <?php foreach ($invoice['items'] as $index => $item): ?>
                                <tr class="item-row">
                                    <td class="text-center fw-bold index-column text-muted"><?= $index + 1 ?></td>
                                    <td>
                                        <select name="items[<?= $index ?>][product_id]" class="form-select product-select select2">
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
                                        <input type="hidden" name="items[<?= $index ?>][description]" value="<?= esc($item['description']) ?>">
                                    </td>
                                    <td><input type="text" name="items[<?= $index ?>][hsn_code]" class="form-control text-center hsn" value="<?= esc($item['hsn_code']) ?>"></td>
                                    <td><input type="number" name="items[<?= $index ?>][quantity]" class="form-control text-end qty" value="<?= $item['quantity'] ?>" step="0.01" min="0.01" required onchange="calculateRow(this)"></td>
                                    <td><input type="number" name="items[<?= $index ?>][rate]" class="form-control text-end rate" value="<?= $item['rate'] ?>" step="0.01" min="0" required onchange="calculateRow(this)"></td>
                                    <td>
                                        <select name="items[<?= $index ?>][tax_percentage]" class="form-select text-end gst" onchange="calculateRow(this)">
                                            <option value="0">0%</option>
                                            <?php foreach ($taxes as $tax): ?>
                                                <option value="<?= $tax['percentage'] ?>" <?= ($item['tax_percentage'] == $tax['percentage']) ? 'selected' : '' ?>>
                                                    <?= $tax['percentage'] ?>%
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php $tax_type = (!empty($item['igst_rate']) && $item['igst_rate'] > 0) ? 'IGST' : 'CGST_SGST'; ?>
                                        <input type="hidden" name="items[<?= $index ?>][tax_type]" class="tax_type" value="<?= $tax_type ?>">
                                    </td>
                                    <td><input type="text" class="form-control text-end fw-bold amount" readonly value="<?= number_format($item['amount'], 2, '.', '') ?>"></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm text-danger p-0" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="item-row">
                                <td class="text-center fw-bold index-column text-muted">1</td>
                                <td>
                                    <select name="items[0][product_id]" class="form-select product-select select2">
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
                                <td><input type="text" name="items[0][hsn_code]" class="form-control text-center hsn"></td>
                                <td><input type="number" name="items[0][quantity]" class="form-control text-end qty" value="1" step="0.01" min="0.01" required onchange="calculateRow(this)"></td>
                                <td><input type="number" name="items[0][rate]" class="form-control text-end rate" value="0" step="0.01" min="0" required onchange="calculateRow(this)"></td>
                                <td>
                                    <select name="items[0][tax_percentage]" class="form-select text-end gst" onchange="calculateRow(this)">
                                        <option value="0">0%</option>
                                        <?php foreach ($taxes as $tax): ?>
                                            <option value="<?= $tax['percentage'] ?>">
                                                <?= $tax['percentage'] ?>%
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="items[0][tax_type]" class="tax_type" value="CGST_SGST">
                                </td>
                                <td><input type="text" class="form-control text-end fw-bold amount" readonly value="0.00"></td>
                                <td class="text-center"><button type="button" class="btn btn-sm text-danger p-0" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FOOTER: Notes & Summary -->
        <div class="row g-0">
            <!-- LEFT: Notes -->
            <div class="col-md-7 border-end border-secondary-subtle p-3 d-flex flex-column">
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control bg-light" rows="3" placeholder="Add notes..."><?= $invoice['notes'] ?? '' ?></textarea>
                </div>
                <div class="mt-auto">
                    <label class="form-label">Terms & Conditions</label>
                    <textarea name="terms" class="form-control bg-light" rows="3" placeholder="Terms..."><?= $invoice['terms'] ?? '1. Goods once sold cannot be taken back or exchanged.
2. Please verify the items at the time of delivery.' ?></textarea>
                </div>
            </div>
            
            <!-- RIGHT: Summary -->
            <div class="col-md-5">
                <table class="table table-borderless gst-summary-table mb-0 w-100">
                    <tr>
                        <td width="50%" class="gst-summary-label">Taxable Subtotal</td>
                        <td width="50%"><input type="text" id="subtotal" class="form-control text-end fw-bold bg-light" readonly value="0.00"></td>
                    </tr>
                    <tr>
                        <td class="gst-summary-label align-middle">Less: Discount</td>
                        <td>
                            <div class="d-flex gap-1">
                                <input type="number" step="1" name="discount_amount" id="discount_amount" class="form-control text-end" value="<?= $invoice['discount_amount'] ?? 0 ?>" min="0" onchange="calculateTotals()">
                                <select name="discount_type" id="discount_type" class="form-select flex-shrink-0" style="width: 48px; padding: 2px;" onchange="calculateTotals()">
                                    <option value="Percentage" <?= (!isset($invoice) || empty($invoice['discount_type']) || $invoice['discount_type'] == 'Percentage') ? 'selected' : '' ?>>%</option>
                                    <option value="Fixed" <?= (isset($invoice) && $invoice['discount_type'] == 'Fixed') ? 'selected' : '' ?>>₹</option>
                                </select>
                                <input type="text" id="calculatedDiscount" class="form-control text-end text-danger bg-light flex-shrink-0" readonly value="0.00" style="width: 70px;">
                            </div>
                        </td>
                    </tr>
                    
                    <tbody id="taxBreakdownBody">
                        <!-- JS injected tax rows -->
                    </tbody>

                    <tr>
                        <td class="gst-summary-label border-top border-secondary-subtle">Total Tax</td>
                        <td class="border-top border-secondary-subtle"><input type="text" id="taxAmount" class="form-control text-end fw-bold bg-light" readonly value="0.00"></td>
                    </tr>
                    <tr>
                        <td class="gst-summary-label">Add: Shipping</td>
                        <td><input type="number" name="shipping_charge" id="shipping_charge" class="form-control text-end" value="<?= $invoice['shipping_charge'] ?? 0 ?>" min="0" step="0.01" onchange="calculateTotals()"></td>
                    </tr>
                    <tr>
                        <td class="gst-summary-label">Round Off</td>
                        <td><input type="number" name="roundoff_amount" id="roundoff_amount" class="form-control text-end bg-light" value="<?= $invoice['roundoff_amount'] ?? 0 ?>" step="0.01" readonly></td>
                    </tr>
                    <tr class="grand-total-row border-top border-secondary-subtle">
                        <td class="py-3">Grand Total (₹)</td>
                        <td class="py-3"><input type="text" id="totalAmount" name="total_amount" class="form-control text-end fw-bold bg-transparent border-0 fs-5" readonly value="0.00"></td>
                    </tr>
                </table>
                <div class="p-3 text-end bg-light border-top border-secondary-subtle">
                    <a href="<?= site_url('invoices') ?>" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" id="submitBtn" class="btn btn-dark fw-bold"><i class="fas fa-save me-1"></i> Save Invoice</button>
                </div>
            </div>
        </div>
    </div>
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
        // Validation: Check if customer is selected
        const customerId = $('#customerSelect').val();
        if (!customerId) {
            alert('Please select a customer first.');
            $('#customerSelect').select2('open');
            return;
        }

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
                    <td class="gst-summary-label text-primary">Add: IGST (${rate}%)</td>
                    <td><input type="text" class="form-control text-end text-primary fw-bold bg-light" readonly value="${amount.toFixed(2)}"></td>
                </tr>
            `;
            } else {
                const halfAmount = amount / 2;
                const halfRate = rate / 2;
                taxBody.innerHTML += `
                <tr>
                    <td class="gst-summary-label text-primary">Add: CGST (${halfRate}%)</td>
                    <td><input type="text" class="form-control text-end text-primary fw-bold bg-light" readonly value="${halfAmount.toFixed(2)}"></td>
                </tr>
                <tr>
                    <td class="gst-summary-label text-primary">Add: SGST (${halfRate}%)</td>
                    <td><input type="text" class="form-control text-end text-primary fw-bold bg-light" readonly value="${halfAmount.toFixed(2)}"></td>
                </tr>
            `;
            }
        });

        const shipping = parseFloat(document.getElementById('shipping_charge').value) || 0;
        
        const preRoundoffTotal = (grossSubtotal - totalDiscount) + totalTax + shipping;
        const netTotal = Math.round(preRoundoffTotal);
        const roundoff = netTotal - preRoundoffTotal;
        
        document.getElementById('roundoff_amount').value = roundoff.toFixed(2);

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

        // 4. Unified Customer Change Handler
        function handleCustomerChange() {
            const customerId = $('select[name="customer_id"]').val();

            // 1. Visibility Logic (Instant)
            if (customerId) {
                $('#lineItemsOverlay').removeClass('active');
                $('#customerAddressSection').slideDown();
                // If specific invoice logic for tax/billing/shipping is needed here, add it
                loadCustomerDetails(customerId);
            } else {
                $('#lineItemsOverlay').addClass('active');
                $('#taxModeIndicator').hide();
                $('#customerAddressSection').slideUp();
                $('#billingAddressDisplay').text('');
                $('#shippingAddressDisplay').text('');
            }
        }

        // Bind to change event
        $('select[name="customer_id"]').on('change', handleCustomerChange);

        // Initial check on page load
        handleCustomerChange();

        // Prevent double submission
        $('#invoiceForm').on('submit', function () {
            const $btn = $('#submitBtn');
            $btn.prop('disabled', true);
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            return true;
        });
    });

    function loadCustomerDetails(customerId) {
        if (!customerId) return;
        // Function logic specific to customer details (currently none shown in snippet but keeping structure)
    }
    // Barcode Scanning Logic
    $('#barcodeScanner').on('keypress', function (e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();

            // Validation: Check if customer is selected
            const customerId = $('#customerSelect').val();
            if (!customerId) {
                alert('Please select a customer first.');
                $('#customerSelect').select2('open');
                return;
            }

            const barcode = $(this).val().trim();
            if (!barcode) return;

            // Show loading state
            const $input = $(this);
            $input.prop('disabled', true);

            $.ajax({
                url: '<?= site_url('master-data/product-by-barcode') ?>',
                method: 'GET',
                data: { barcode: barcode },
                success: function (response) {
                    if (response.success && response.data) {
                        addProductByBarcode(response.data);
                        $input.val('').focus(); // Clear and keep focus for next scan
                        alert('Item added: ' + response.data.product_name);
                    } else {
                        alert('Product not found for barcode: ' + barcode);
                        $input.select();
                    }
                },
                error: function (xhr) {
                    // Check for 404
                    if (xhr.status === 404) {
                        alert('Product not found for barcode: ' + barcode);
                    } else {
                        alert('Error searching for barcode');
                    }
                    $input.select();
                },
                complete: function () {
                    $input.prop('disabled', false).focus();
                }
            });
        }
    });

    function addProductByBarcode(product) {
        // Add new row
        addLineItem();

        // Get the last added row (which is the new one)
        const $rows = $('.item-row');
        const $lastRow = $rows.last();

        // 1. Set Product in Select2
        const $select = $lastRow.find('.product-select');

        // Check if option exists
        if ($select.find(`option[value="${product.id}"]`).length > 0) {
            $select.val(product.id).trigger('change');
        } else {
            alert('Product found but not in list. Please check active status.');
        }
    }
</script>
<?= $this->endSection() ?>
```