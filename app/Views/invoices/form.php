<?= $this->extend('layouts/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?= esc($title) ?></h1>
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
        padding: 5px 8px;
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
        min-height: 32px;
    }
    .gst-table th {
        background-color: #f8fafc !important;
        color: #475569;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        vertical-align: middle;
        padding: 8px 6px !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }
    .gst-table td {
        padding: 4px !important;
        vertical-align: middle;
    }
    .gst-table .form-control, .gst-table .form-select {
        border: 1px solid #e2e8f0;
        background: #fff;
    }
    .gst-table .form-control:focus, .gst-table .form-select:focus {
        border-color: #86b7fe;
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
        background-color: #f8fafc;
        color: #334155;
        font-size: 1.1rem;
        font-weight: bold;
    }
    .glass-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(2px);
        z-index: 10;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }
    .glass-overlay.active {
        opacity: 1;
        pointer-events: all;
    }
    .scan-feedback {
        transition: all 0.3s ease;
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
                            <div id="billingAddressDisplay" class="border p-2 bg-light rounded" style="min-height: 80px; white-space: pre-wrap; font-size: 0.8rem;"></div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label text-primary">Shipping Address</label>
                            <div id="shippingAddressDisplay" class="border p-2 bg-light rounded" style="min-height: 80px; white-space: pre-wrap; font-size: 0.8rem;"></div>
                        </div>
                    </div>
                    
                    <div class="row mt-2 bg-light border rounded p-2 m-0">
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
                        <input type="text" name="invoice_number" class="form-control fw-bold bg-light" value="<?= esc($invoice['invoice_number'] ?? $invoice_number) ?>" readonly>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Agent (Optional)</label>
                        <div class="input-group">
                            <select name="agent_id" id="agentSelect" class="form-select select2">
                                <option value="">-- None --</option>
                                <?php foreach ($agents as $agent): ?>
                                    <option value="<?= $agent['id'] ?>" data-commission="<?= $agent['commission_percentage'] ?>" <?= ($invoice && $invoice['agent_id'] == $agent['id']) ? 'selected' : '' ?>>
                                        <?= esc($agent['agent_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" step="0.01" min="0" max="100" name="agent_commission_percent" id="agentCommissionPercent"
                                class="form-control text-end" style="max-width: 80px;" placeholder="%" title="Commission %"
                                value="<?= esc($invoice['agent_commission_percent'] ?? 0) ?>">
                            <span class="input-group-text bg-light text-muted" style="font-size: 0.75rem;">%</span>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-sm-6">
                        <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" name="invoice_date" id="invoiceDate" class="form-control" value="<?= esc($invoice['invoice_date'] ?? date('Y-m-d')) ?>" required onchange="calculateDueDate(true)">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" id="dueDate" class="form-control text-danger fw-bold" value="<?= esc($invoice['due_date'] ?? date('Y-m-d', strtotime('+30 days'))) ?>" required>
                    </div>
                </div>
                
                <div class="row g-2">
                    <div class="col-sm-6">
                        <label class="form-label">P.O Number</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="Purchase Order Ref" value="<?= esc($invoice['reference_number'] ?? '') ?>">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">P.O Date</label>
                        <input type="date" name="po_date" class="form-control" value="<?= esc($invoice['po_date'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- DISPATCH & LOGISTICS BLOCK -->
        <div class="row g-2 border-bottom border-secondary-subtle bg-light p-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Transport Name <span class="text-danger">*</span></label>
                <select name="transport_name" id="transportSelect" class="form-select select2" required>
                    <option value="">-- Select Transport --</option>
                    <?php foreach ($transports as $transport): ?>
                        <option value="<?= esc($transport['transport_name']) ?>" <?= ($invoice && $invoice['transport_name'] == $transport['transport_name']) ? 'selected' : '' ?>>
                            <?= esc($transport['transport_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Waybill / LR No.</label>
                <input type="text" name="waybill_number" class="form-control" value="<?= esc($invoice['waybill_number'] ?? '') ?>" placeholder="LR No.">
            </div>
            <div class="col-md-2">
                <label class="form-label">Waybill Date</label>
                <input type="date" name="waybill_date" class="form-control" value="<?= esc($invoice['waybill_date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">E-Waybill No.</label>
                <input type="text" name="ewaybill_number" class="form-control" value="<?= esc($invoice['ewaybill_number'] ?? '') ?>" placeholder="E-Waybill No.">
            </div>
            <div class="col-md-2">
                <label class="form-label">Packages <span class="text-danger">*</span></label>
                <input type="number" required name="packages_count" class="form-control" min="1" step="1" value="<?= esc($invoice['packages_count'] ?? '1') ?>" placeholder="Qty">
            </div>
        </div>

        <!-- LINE ITEMS -->
        <div class="position-relative" id="lineItemsCardWrapper">
            <div class="d-flex justify-content-between align-items-center bg-light text-dark border-bottom border-secondary-subtle p-2">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="mb-0 text-uppercase fw-bold"><i class="fas fa-list me-1"></i> Item Details</h6>
                    <span id="barcodeStatusMsg" class="badge bg-success-subtle text-success border border-success-subtle scan-feedback" style="display: none;"></span>
                </div>
                <div class="d-flex gap-2">
                    <div class="input-group input-group-sm" style="width: 280px;">
                        <span class="input-group-text bg-white"><i class="fas fa-barcode"></i></span>
                        <input type="text" id="barcodeScanner" class="form-control" placeholder="Scan Barcode & Press Enter..." autocomplete="off">
                    </div>
                    <button type="button" class="btn btn-sm btn-success" onclick="addLineItem()">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
            </div>
            
            <div class="table-responsive position-relative p-0 m-0 border-bottom border-secondary-subtle">
                <!-- Blur Overlay -->
                <div class="glass-overlay <?= ($invoice && !empty($invoice['customer_id'])) ? '' : 'active' ?>" id="lineItemsOverlay">
                    <div class="bg-white p-3 border border-secondary-subtle text-center shadow rounded-3">
                        <i class="fas fa-lock fs-3 text-muted mb-2"></i>
                        <h6 class="fw-bold text-uppercase">Customer Required</h6>
                        <p class="small text-muted mb-0">Please select a customer above to enable item entry.</p>
                    </div>
                </div>

                <table class="table table-bordered border-secondary-subtle table-sm mb-0 gst-table" id="itemsTable">
                    <thead>
                        <tr>
                            <th width="3%" class="text-center">#</th>
                            <th width="30%">Product & Description</th>
                            <th width="12%">HSN/SAC</th>
                            <th width="10%" class="text-end">Qty</th>
                            <th width="12%" class="text-end">Rate (₹)</th>
                            <th width="14%" class="text-end">GST %</th>
                            <th width="14%" class="text-end">Amount (₹)</th>
                            <th width="5%" class="text-center">Del</th>
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
                                                <option value="<?= $product['id'] ?>" data-hsn="<?= esc($product['hsn_code']) ?>"
                                                    data-rate="<?= $product['selling_price'] ?>"
                                                    data-gst="<?= $product['tax_percentage'] ?>"
                                                    <?= ($item['product_id'] == $product['id']) ? 'selected' : '' ?>>
                                                    <?= esc($product['product_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="text" name="items[<?= $index ?>][description]" class="form-control form-control-sm mt-1 desc-input"
                                            placeholder="Item specifications / notes..." value="<?= esc($item['description'] ?? '') ?>">
                                    </td>
                                    <td><input type="text" name="items[<?= $index ?>][hsn_code]" class="form-control text-center hsn" value="<?= esc($item['hsn_code'] ?? '') ?>"></td>
                                    <td><input type="number" name="items[<?= $index ?>][quantity]" class="form-control text-end qty" value="<?= esc($item['quantity']) ?>" step="0.01" min="0.01" required oninput="calculateRow(this)" onchange="calculateRow(this)"></td>
                                    <td><input type="number" name="items[<?= $index ?>][rate]" class="form-control text-end rate" value="<?= esc($item['rate']) ?>" step="0.01" min="0" required oninput="calculateRow(this)" onchange="calculateRow(this)"></td>
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
                                    <td><input type="text" class="form-control text-end fw-bold amount bg-light" readonly value="<?= number_format($item['amount'], 2, '.', '') ?>"></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm text-danger p-0" onclick="removeRow(this)" title="Delete Row"><i class="fas fa-trash"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="item-row">
                                <td class="text-center fw-bold index-column text-muted">1</td>
                                <td>
                                    <select name="items[0][product_id]" class="form-select product-select select2">
                                        <option value="">-- Select Product --</option>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?= $product['id'] ?>" data-hsn="<?= esc($product['hsn_code']) ?>"
                                                data-rate="<?= $product['selling_price'] ?>"
                                                data-gst="<?= $product['tax_percentage'] ?>">
                                                <?= esc($product['product_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" name="items[0][description]" class="form-control form-control-sm mt-1 desc-input" placeholder="Item specifications / notes...">
                                </td>
                                <td><input type="text" name="items[0][hsn_code]" class="form-control text-center hsn"></td>
                                <td><input type="number" name="items[0][quantity]" class="form-control text-end qty" value="1" step="0.01" min="0.01" required oninput="calculateRow(this)" onchange="calculateRow(this)"></td>
                                <td><input type="number" name="items[0][rate]" class="form-control text-end rate" value="0.00" step="0.01" min="0" required oninput="calculateRow(this)" onchange="calculateRow(this)"></td>
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
                                <td><input type="text" class="form-control text-end fw-bold amount bg-light" readonly value="0.00"></td>
                                <td class="text-center"><button type="button" class="btn btn-sm text-danger p-0" onclick="removeRow(this)" title="Delete Row"><i class="fas fa-trash"></i></button></td>
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
                    <textarea name="notes" class="form-control bg-light" rows="3" placeholder="Add notes for the customer..."><?= esc($invoice['notes'] ?? '') ?></textarea>
                </div>
                <div class="mt-auto">
                    <label class="form-label">Terms & Conditions</label>
                    <textarea name="terms" class="form-control bg-light" rows="3" placeholder="Terms..."><?= esc($invoice['terms'] ?? "1. Goods once sold cannot be taken back or exchanged.\n2. Please verify the items at the time of delivery.") ?></textarea>
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
                                <input type="number" step="0.01" name="discount_amount" id="discount_amount" class="form-control text-end"
                                    value="<?= esc($invoice['discount_amount'] ?? 0) ?>" min="0" oninput="calculateTotals()" onchange="calculateTotals()">
                                <select name="discount_type" id="discount_type" class="form-select flex-shrink-0" style="width: 50px; padding: 2px;" onchange="calculateTotals()">
                                    <option value="Percentage" <?= (!isset($invoice) || empty($invoice['discount_type']) || $invoice['discount_type'] == 'Percentage') ? 'selected' : '' ?>>%</option>
                                    <option value="Fixed" <?= (isset($invoice) && $invoice['discount_type'] == 'Fixed') ? 'selected' : '' ?>>₹</option>
                                </select>
                                <input type="text" id="calculatedDiscount" class="form-control text-end text-danger bg-light flex-shrink-0" readonly value="0.00" style="width: 80px;">
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
                        <td><input type="number" name="shipping_charge" id="shipping_charge" class="form-control text-end"
                            value="<?= esc($invoice['shipping_charge'] ?? 0) ?>" min="0" step="0.01" oninput="calculateTotals()" onchange="calculateTotals()"></td>
                    </tr>
                    <tr>
                        <td class="gst-summary-label">Round Off</td>
                        <td><input type="text" name="roundoff_amount" id="roundoff_amount" class="form-control text-end bg-light"
                            value="<?= esc($invoice['roundoff_amount'] ?? '0.00') ?>" readonly></td>
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
    let rowIndex = <?= ($invoice && !empty($invoice['items'])) ? count($invoice['items']) : 1 ?>;
    let isInterState = <?= (isset($invoice) && !empty($invoice['is_inter_state'])) ? 'true' : 'false' ?>;
    let currentCreditDays = 0;
    let isInitialLoad = <?= $invoice ? 'true' : 'false' ?>;

    // Global caches for clean HTML templates
    let _productOptionsHtml = '';
    let _taxOptionsHtml = '';

    function calculateDueDate(force = false) {
        // If editing an existing invoice and not forced by user changing invoice date, preserve saved due date
        if (isInitialLoad && !force) return;

        const invoiceDateVal = document.getElementById('invoiceDate').value;
        if (!invoiceDateVal) return;

        // Parse YYYY-MM-DD safely in local time to avoid UTC day-offset issues
        const parts = invoiceDateVal.split('-').map(Number);
        if (parts.length !== 3) return;

        const date = new Date(parts[0], parts[1] - 1, parts[2]);
        date.setDate(date.getDate() + (parseInt(currentCreditDays) || 0));

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        document.getElementById('dueDate').value = `${year}-${month}-${day}`;
    }

    function addLineItem() {
        const customerId = $('#customerSelect').val();
        if (!customerId) {
            alert('Please select a customer first.');
            $('#customerSelect').select2('open');
            return;
        }

        const tbody = document.getElementById('itemsBody');
        const row = document.createElement('tr');
        row.className = 'item-row';

        const productOptions = _productOptionsHtml || document.querySelector('.product-select').innerHTML;
        const taxOptions = _taxOptionsHtml || document.querySelector('.gst').innerHTML;

        row.innerHTML = `
            <td class="text-center fw-bold index-column text-muted">${document.querySelectorAll('.item-row').length + 1}</td>
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select product-select select2">
                    ${productOptions}
                </select>
                <input type="text" name="items[${rowIndex}][description]" class="form-control form-control-sm mt-1 desc-input" placeholder="Item specifications / notes...">
            </td>
            <td><input type="text" name="items[${rowIndex}][hsn_code]" class="form-control text-center hsn"></td>
            <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control text-end qty" value="1" step="0.01" min="0.01" required oninput="calculateRow(this)" onchange="calculateRow(this)"></td>
            <td><input type="number" name="items[${rowIndex}][rate]" class="form-control text-end rate" value="0.00" step="0.01" min="0" required oninput="calculateRow(this)" onchange="calculateRow(this)"></td>
            <td>
                <select name="items[${rowIndex}][tax_percentage]" class="form-select text-end gst" onchange="calculateRow(this)">
                    ${taxOptions}
                </select>
                <input type="hidden" name="items[${rowIndex}][tax_type]" class="tax_type" value="${isInterState ? 'IGST' : 'CGST_SGST'}">
            </td>
            <td><input type="text" class="form-control text-end fw-bold amount bg-light" readonly value="0.00"></td>
            <td class="text-center"><button type="button" class="btn btn-sm text-danger p-0" onclick="removeRow(this)" title="Delete Row"><i class="fas fa-trash"></i></button></td>
        `;
        tbody.appendChild(row);

        const newSelect = row.querySelector('.product-select');
        initSelect2(newSelect);

        // Update labels for the new GST select based on current state
        updateRowTaxOptions(row.querySelector('.gst'), isInterState);

        rowIndex++;
        updateIndices();
    }

    function updateProductDetails(select) {
        const $select = $(select);
        const $option = $select.find(':selected');

        if (!$option.val()) {
            return;
        }

        const row = $select.closest('tr')[0];
        if (!row) return;

        const hsn = $option.attr('data-hsn') || '';
        const rate = parseFloat($option.attr('data-rate')) || 0;
        const gstAttr = $option.attr('data-gst');
        const prodName = $option.text().trim();

        row.querySelector('.hsn').value = hsn;
        row.querySelector('.rate').value = rate.toFixed(2);

        // Auto-fill description if currently empty or matching previous product
        const descInput = row.querySelector('.desc-input');
        if (descInput) {
            descInput.value = prodName;
        }

        // Match GST percentage
        if (gstAttr !== undefined && gstAttr !== null && gstAttr !== '') {
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
                gstSelect.value = "0";
            }
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
            alert('At least one item row is required.');
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
            const cappedPercent = Math.min(Math.max(discountInput, 0), 100);
            totalDiscount = (grossSubtotal * cappedPercent) / 100;
        } else {
            totalDiscount = Math.min(Math.max(discountInput, 0), grossSubtotal);
        }

        let taxBreakdown = {};
        let totalTax = 0;

        itemData.forEach(item => {
            const itemDiscount = (grossSubtotal > 0) ? (item.amount / grossSubtotal * totalDiscount) : 0;
            const taxableValue = Math.max(0, item.amount - itemDiscount);
            const taxAmount = (taxableValue * item.gstPercentage) / 100;

            if (item.gstPercentage > 0) {
                taxBreakdown[item.gstPercentage] = (taxBreakdown[item.gstPercentage] || 0) + taxAmount;
                totalTax += taxAmount;
            }
        });

        const taxBody = document.getElementById('taxBreakdownBody');
        taxBody.innerHTML = '';

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

        const shipping = Math.max(0, parseFloat(document.getElementById('shipping_charge').value) || 0);
        
        const preRoundoffTotal = (grossSubtotal - totalDiscount) + totalTax + shipping;
        const netTotal = Math.round(preRoundoffTotal);
        const roundoff = netTotal - preRoundoffTotal;

        document.getElementById('subtotal').value = grossSubtotal.toFixed(2);
        document.getElementById('calculatedDiscount').value = totalDiscount.toFixed(2);
        document.getElementById('taxAmount').value = totalTax.toFixed(2);
        document.getElementById('roundoff_amount').value = (roundoff >= 0 ? '+' : '') + roundoff.toFixed(2);
        document.getElementById('totalAmount').value = netTotal.toFixed(2);
    }

    function updateRowTaxOptions(select, interState) {
        if (!select) return;
        for (let i = 0; i < select.options.length; i++) {
            const opt = select.options[i];
            const pct = opt.value ? parseFloat(opt.value) : 0;
            if (pct === 0) {
                opt.text = '0%';
            } else if (interState) {
                opt.text = pct + '% (IGST)';
            } else {
                const half = (pct / 2);
                opt.text = pct + '% (CGST ' + half + '% + SGST ' + half + '%)';
            }
        }
    }

    function updateAllTaxOptions(interState) {
        document.querySelectorAll('.gst').forEach(select => {
            updateRowTaxOptions(select, interState);
        });
    }

    function enforceTaxType(interState) {
        document.querySelectorAll('.item-row').forEach((row, index) => {
            const taxTypeInput = row.querySelector('.tax_type');
            if (taxTypeInput) {
                taxTypeInput.value = interState ? 'IGST' : 'CGST_SGST';
            }
        });
    }

    function fetchCustomerDetails(customerId, isInitial = false) {
        const taxModeIndicator = $('#taxModeIndicator');
        const taxModeText = $('#taxModeText');

        if (!customerId) {
            isInterState = false;
            $('#isInterStateField').val(0);
            taxModeIndicator.hide();
            $('#customerAddressSection').slideUp();
            $('#lineItemsOverlay').addClass('active');
            $('#billingAddressDisplay').text('');
            $('#shippingAddressDisplay').text('');
            calculateTotals();
            return;
        }

        // Show address section & unlock line items
        $('#customerAddressSection').slideDown();
        $('#lineItemsOverlay').removeClass('active');

        fetch('<?= site_url('customers/details') ?>/' + customerId)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                isInterState = !!data.is_inter_state;
                $('#isInterStateField').val(isInterState ? 1 : 0);
                currentCreditDays = data.credit_period_days || 0;

                // Update addresses
                $('#billingAddressDisplay').text(data.billing_address || 'N/A');
                $('#shippingAddressDisplay').text(data.shipping_address || 'N/A');

                // Update contact info
                $('#customerPhone').text(data.phone || 'N/A');
                $('#customerWhatsapp').text(data.whatsapp_number || 'N/A');
                $('#customerGstType').text(data.gst_type || 'Unregistered');
                $('#customerGstin').text(data.gstin || 'N/A');

                // Update tax indicator badge
                taxModeIndicator.show();
                if (isInterState) {
                    taxModeIndicator.removeClass('bg-secondary bg-success').addClass('bg-info');
                    taxModeText.text('Inter-State (IGST)');
                } else {
                    taxModeIndicator.removeClass('bg-secondary bg-info').addClass('bg-success');
                    taxModeText.text('Intra-State (CGST + SGST)');
                }

                updateAllTaxOptions(isInterState);
                enforceTaxType(isInterState);

                // Auto select agent if customer has agent assigned and not already set
                if (data.agent_id && !$('#agentSelect').val()) {
                    $('#agentSelect').val(data.agent_id).trigger('change');
                    if (data.agent_commission) {
                        $('#agentCommissionPercent').val(data.agent_commission);
                    }
                }

                if (!isInitial) {
                    calculateDueDate(false);
                }

                calculateTotals();
                isInitialLoad = false;
            })
            .catch(error => {
                console.error('Error fetching customer details:', error);
                taxModeIndicator.show().removeClass('bg-info bg-success').addClass('bg-warning');
                taxModeText.text('Tax mode default');
            });
    }

    function initSelect2(element) {
        const config = {
            theme: 'bootstrap-5',
            placeholder: '-- Select --',
            allowClear: true,
            width: '100%'
        };

        if (element) {
            const select = $(element);
            if (select.data('select2')) {
                select.select2('destroy');
            }
            select.select2(config);
        } else {
            $('.select2').each(function () {
                initSelect2(this);
            });
        }
    }

    function showBarcodeFeedback(msg, isError = false) {
        const $badge = $('#barcodeStatusMsg');
        $badge.text(msg);
        if (isError) {
            $badge.removeClass('bg-success-subtle text-success border-success-subtle')
                  .addClass('bg-danger-subtle text-danger border-danger-subtle');
        } else {
            $badge.removeClass('bg-danger-subtle text-danger border-danger-subtle')
                  .addClass('bg-success-subtle text-success border-success-subtle');
        }
        $badge.stop(true, true).fadeIn().delay(2500).fadeOut();
    }

    function addProductByBarcodeData(product) {
        // 1. Check if product already exists in item rows
        let existingRow = null;
        document.querySelectorAll('.item-row').forEach(row => {
            const select = row.querySelector('.product-select');
            if (select && select.value == product.id) {
                existingRow = row;
            }
        });

        if (existingRow) {
            // Increment existing quantity
            const qtyInput = existingRow.querySelector('.qty');
            qtyInput.value = (parseFloat(qtyInput.value) || 0) + 1;
            calculateRow(qtyInput);
            showBarcodeFeedback(`Qty +1: ${product.product_name}`);
        } else {
            // Check if first row is untouched/empty
            const rows = document.querySelectorAll('.item-row');
            let targetRow = null;
            if (rows.length === 1) {
                const firstRowSelect = rows[0].querySelector('.product-select');
                if (!firstRowSelect.value) {
                    targetRow = rows[0];
                }
            }

            if (!targetRow) {
                addLineItem();
                const updatedRows = document.querySelectorAll('.item-row');
                targetRow = updatedRows[updatedRows.length - 1];
            }

            const select = targetRow.querySelector('.product-select');
            $(select).val(product.id).trigger('change');
            showBarcodeFeedback(`Added: ${product.product_name}`);
        }
    }

    $(document).ready(function () {
        // 1. Capture clean HTML templates before Select2 init
        const $productSelect = $('.product-select').first();
        const $gstSelect = $('.gst').first();

        if ($productSelect.length) {
            const $clone = $productSelect.clone();
            $clone.find('option').prop('selected', false).removeAttr('selected');
            _productOptionsHtml = $clone.html();
        }

        if ($gstSelect.length) {
            const $clone = $gstSelect.clone();
            $clone.find('option').prop('selected', false).removeAttr('selected');
            _taxOptionsHtml = $clone.html();
        }

        // 2. Initialize all Select2 elements
        initSelect2();

        // 3. Agent selection commission fill
        $('#agentSelect').on('change', function () {
            const selectedOption = $(this).find(':selected');
            const commission = selectedOption.data('commission');
            if (commission !== undefined && commission !== null && commission !== '') {
                $('#agentCommissionPercent').val(commission);
            } else if (!$(this).val()) {
                $('#agentCommissionPercent').val(0);
            }
        });

        // 4. Delegated handler for product selection
        $(document).on('change select2:select', '.product-select', function () {
            updateProductDetails(this);
        });

        // 5. Unified customer change handler
        $('#customerSelect').on('change select2:select', function () {
            const customerId = $(this).val();
            fetchCustomerDetails(customerId, false);
        });

        // 6. Initial customer load if editing existing invoice
        const initialCustomerId = $('#customerSelect').val();
        if (initialCustomerId) {
            fetchCustomerDetails(initialCustomerId, true);
        } else {
            calculateTotals();
        }

        // 7. Single clean Barcode Scanning Logic
        $('#barcodeScanner').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();

                const customerId = $('#customerSelect').val();
                if (!customerId) {
                    showBarcodeFeedback('Please select a customer first!', true);
                    $('#customerSelect').select2('open');
                    return;
                }

                const barcode = $(this).val().trim();
                if (!barcode) return;

                const $input = $(this);
                $input.prop('disabled', true);

                $.ajax({
                    url: '<?= site_url('master-data/product-by-barcode') ?>',
                    method: 'GET',
                    data: { barcode: barcode },
                    success: function (response) {
                        if (response.success && response.data) {
                            addProductByBarcodeData(response.data);
                            $input.val('');
                        } else {
                            showBarcodeFeedback('Product not found for barcode: ' + barcode, true);
                            $input.select();
                        }
                    },
                    error: function (xhr) {
                        showBarcodeFeedback('Product not found for barcode: ' + barcode, true);
                        $input.select();
                    },
                    complete: function () {
                        $input.prop('disabled', false).focus();
                    }
                });
            }
        });

        // 8. Form submit handler with validation and double-submit protection
        $('#invoiceForm').on('submit', function (e) {
            if (!this.checkValidity()) {
                return true;
            }

            // Check if there is at least one valid item with quantity > 0
            let hasValidItem = false;
            $('.item-row').each(function () {
                const prod = $(this).find('.product-select').val();
                const desc = $(this).find('.desc-input').val();
                const qty = parseFloat($(this).find('.qty').val()) || 0;
                if ((prod || (desc && desc.trim())) && qty > 0) {
                    hasValidItem = true;
                }
            });

            if (!hasValidItem) {
                e.preventDefault();
                alert('Please select at least one product with a quantity greater than zero.');
                return false;
            }

            const $btn = $('#submitBtn');
            $btn.prop('disabled', true);
            $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
            return true;
        });
    });
</script>
<?= $this->endSection() ?>