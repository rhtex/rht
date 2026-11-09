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
                <li class="breadcrumb-item"><a href="<?= base_url('quotations') ?>">Quotations</a></li>
                <li class="breadcrumb-item active"><?= $quotation ? 'Edit' : 'Create' ?></li>
            </ol>
        </div>
    </div>

    <form id="quotationForm"
        action="<?= $quotation ? site_url('quotations/update/' . $quotation['id']) : site_url('quotations/store') ?>"
        method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="is_inter_state" id="isInterStateField"
            value="<?= $quotation['is_inter_state'] ?? 0 ?>">

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
                    <h6 class="fw-bold mb-0 text-uppercase"><i class="fas fa-user-tie me-1"></i> Quotation For (Customer)</h6>
                    <span id="taxModeIndicator" class="badge bg-secondary" style="display: none;">
                        <span id="taxModeText">Tax Mode</span>
                    </span>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customerSelect" class="form-select select2" required>
                        <option value="">-- Search Customer --</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>" <?= ($quotation && $quotation['customer_id'] == $customer['id']) ? 'selected' : '' ?>>
                                <?= esc($customer['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="customerAddressSection" style="display: none;">
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <label class="form-label text-primary">Billing Address</label>
                            <div id="billingAddressDisplay" class="border p-2 bg-light rounded" style="min-height: 75px; white-space: pre-wrap; font-size: 0.8rem;"></div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label class="form-label text-primary">Shipping Address</label>
                            <div id="shippingAddressDisplay" class="border p-2 bg-light rounded" style="min-height: 75px; white-space: pre-wrap; font-size: 0.8rem;"></div>
                        </div>
                    </div>

                    <div class="row mt-1">
                        <div class="col-sm-6">
                            <span class="text-muted"><i class="fas fa-phone me-1"></i> Phone:</span>
                            <span id="customerPhone" class="fw-semibold"></span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted"><i class="fab fa-whatsapp text-success me-1"></i> WA:</span>
                            <span id="customerWhatsapp" class="fw-semibold"></span>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-sm-12">
                            <span class="text-muted"><i class="fas fa-id-card me-1"></i> GSTIN:</span>
                            <span id="customerGstin" class="fw-semibold badge bg-light text-dark border"></span>
                            <span id="customerGstType" class="badge bg-secondary ms-1"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Voucher Details -->
            <div class="col-md-6 p-3 bg-light bg-opacity-25">
                <div class="border-bottom pb-2 mb-3">
                    <h6 class="fw-bold mb-0 text-uppercase"><i class="fas fa-file-invoice me-1"></i> Estimate / Quotation Details</h6>
                </div>
                
                <div class="row g-2">
                    <div class="col-sm-6">
                        <label class="form-label">Quotation Number <span class="text-danger">*</span></label>
                        <input type="text" name="quotation_number" class="form-control fw-bold bg-light"
                            value="<?= esc($quotation['quotation_number'] ?? $quotation_number) ?>" readonly>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Quotation Date <span class="text-danger">*</span></label>
                        <input type="date" name="quotation_date" class="form-control"
                            value="<?= $quotation['quotation_date'] ?? date('Y-m-d') ?>" required>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Expiry / Valid Until</label>
                        <input type="date" name="expiry_date" class="form-control"
                            value="<?= $quotation['expiry_date'] ?? date('Y-m-d', strtotime('+15 days')) ?>">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Customer Ref / P.O Number</label>
                        <input type="text" name="reference_number" class="form-control"
                            value="<?= esc($quotation['reference_number'] ?? '') ?>" placeholder="Customer P.O. / Enquiry Ref">
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Commission Agent</label>
                        <select name="agent_id" id="agentSelect" class="form-select select2">
                            <option value="">-- No Agent --</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent['id'] ?>" 
                                    data-commission="<?= $agent['commission_percentage'] ?? 0 ?>"
                                    <?= ($quotation && $quotation['agent_id'] == $agent['id']) ? 'selected' : '' ?>>
                                    <?= esc($agent['agent_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Agent Comm. %</label>
                        <input type="number" step="0.01" name="agent_commission_percent" id="agentCommPercent" class="form-control"
                            value="<?= esc($quotation['agent_commission_percent'] ?? 0) ?>" placeholder="0.00">
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Preferred Transport</label>
                        <select name="transport_name" class="form-select select2">
                            <option value="">-- Select Transport --</option>
                            <?php foreach ($transports as $t): ?>
                                <option value="<?= esc($t['transport_name']) ?>" <?= ($quotation && $quotation['transport_name'] == $t['transport_name']) ? 'selected' : '' ?>>
                                    <?= esc($t['transport_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Waybill / LR No</label>
                        <input type="text" name="waybill_number" class="form-control"
                            value="<?= esc($quotation['waybill_number'] ?? '') ?>" placeholder="LR / Reference">
                    </div>
                </div>
            </div>
        </div>

        <!-- BARCODE & QUICK SCANNER -->
        <div class="bg-light p-2 border-bottom d-flex align-items-center gap-2 position-relative">
            <span class="badge bg-dark px-2 py-1"><i class="fas fa-barcode me-1"></i> Barcode Scanner</span>
            <input type="text" id="barcodeScannerInput" class="form-control form-control-sm" style="max-width: 320px;" placeholder="Scan barcode or type & press enter...">
            <span id="scanFeedback" class="small text-muted"></span>
            
            <div class="ms-auto d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="addEmptyRowBtn">
                    <i class="fas fa-plus me-1"></i> Add Item Line
                </button>
            </div>
        </div>

        <!-- ITEMS TABLE CONTAINER WITH GLASS OVERLAY -->
        <div class="position-relative">
            <div id="tableGlassOverlay" class="glass-overlay">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <h6 class="fw-bold text-dark">Adding Product via Barcode...</h6>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered gst-table mb-0 align-middle" id="itemsTable">
                    <thead>
                        <tr>
                            <th width="3%" class="text-center">#</th>
                            <th width="28%">Product / Item Description</th>
                            <th width="12%">HSN / SAC</th>
                            <th width="10%" class="text-center">Quantity</th>
                            <th width="12%" class="text-end">Rate (₹)</th>
                            <th width="12%">GST Rate</th>
                            <th width="15%" class="text-end">Amount (₹)</th>
                            <th width="4%" class="text-center"><i class="fas fa-trash"></i></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <?php if (!empty($quotation['items'])): ?>
                            <?php foreach ($quotation['items'] as $index => $item): ?>
                                <tr class="item-row" data-index="<?= $index ?>">
                                    <td class="text-center row-number fw-bold text-muted"><?= $index + 1 ?></td>
                                    <td>
                                        <select name="items[<?= $index ?>][product_id]" class="form-select form-select-sm product-select select2 mb-1">
                                            <option value="">-- Custom Item / Select Product --</option>
                                            <?php foreach ($products as $prod): ?>
                                                <option value="<?= $prod['id'] ?>" 
                                                    data-rate="<?= $prod['selling_price'] ?? $prod['rate'] ?? 0 ?>" 
                                                    data-hsn="<?= $prod['hsn_code'] ?? '' ?>" 
                                                    data-tax="<?= $prod['tax_percentage'] ?? 0 ?>"
                                                    <?= ($item['product_id'] == $prod['id']) ? 'selected' : '' ?>>
                                                    <?= esc($prod['product_name']) ?> (<?= esc($prod['product_code'] ?? $prod['sku'] ?? '') ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="text" name="items[<?= $index ?>][description]" class="form-control form-control-sm item-desc" placeholder="Item description" value="<?= esc($item['description']) ?>" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[<?= $index ?>][hsn_code]" class="form-control form-control-sm item-hsn text-center" value="<?= esc($item['hsn_code']) ?>">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[<?= $index ?>][quantity]" class="form-control form-control-sm item-qty text-center fw-bold" value="<?= $item['quantity'] + 0 ?>" min="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[<?= $index ?>][rate]" class="form-control form-control-sm item-rate text-end fw-bold" value="<?= number_format($item['rate'], 2, '.', '') ?>" min="0" required>
                                    </td>
                                    <td>
                                        <select name="items[<?= $index ?>][tax_percentage]" class="form-select form-select-sm item-tax">
                                            <option value="0" <?= ((float)$item['tax_percentage'] == 0) ? 'selected' : '' ?>>0% (Exempt)</option>
                                            <?php foreach ($taxes as $tax): ?>
                                                <option value="<?= $tax['percentage'] ?>" <?= ((float)$item['tax_percentage'] == (float)$tax['percentage']) ? 'selected' : '' ?>>
                                                    <?= esc($tax['name']) ?> (<?= $tax['percentage'] + 0 ?>%)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="text-end">
                                        <span class="item-amount-display fw-bold">0.00</span>
                                        <input type="hidden" name="items[<?= $index ?>][amount]" class="item-amount" value="<?= $item['amount'] ?? 0 ?>">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-link text-danger remove-row-btn p-0" title="Delete Row"><i class="fas fa-times-circle fs-6"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Initial Empty Row -->
                            <tr class="item-row" data-index="0">
                                <td class="text-center row-number fw-bold text-muted">1</td>
                                <td>
                                    <select name="items[0][product_id]" class="form-select form-select-sm product-select select2 mb-1">
                                        <option value="">-- Custom Item / Select Product --</option>
                                        <?php foreach ($products as $prod): ?>
                                            <option value="<?= $prod['id'] ?>" 
                                                data-rate="<?= $prod['selling_price'] ?? $prod['rate'] ?? 0 ?>" 
                                                data-hsn="<?= $prod['hsn_code'] ?? '' ?>" 
                                                data-tax="<?= $prod['tax_percentage'] ?? 0 ?>">
                                                <?= esc($prod['product_name']) ?> (<?= esc($prod['product_code'] ?? $prod['sku'] ?? '') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" name="items[0][description]" class="form-control form-control-sm item-desc" placeholder="Item description" required>
                                </td>
                                <td>
                                    <input type="text" name="items[0][hsn_code]" class="form-control form-control-sm item-hsn text-center">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][quantity]" class="form-control form-control-sm item-qty text-center fw-bold" value="1" min="0.01" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][rate]" class="form-control form-control-sm item-rate text-end fw-bold" value="0.00" min="0" required>
                                </td>
                                <td>
                                    <select name="items[0][tax_percentage]" class="form-select form-select-sm item-tax">
                                        <option value="0">0% (Exempt)</option>
                                        <?php foreach ($taxes as $tax): ?>
                                            <option value="<?= $tax['percentage'] ?>" <?= ((float)$tax['percentage'] == 5) ? 'selected' : '' ?>>
                                                <?= esc($tax['name']) ?> (<?= $tax['percentage'] + 0 ?>%)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="text-end">
                                    <span class="item-amount-display fw-bold">0.00</span>
                                    <input type="hidden" name="items[0][amount]" class="item-amount" value="0">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-link text-danger remove-row-btn p-0" title="Delete Row"><i class="fas fa-times-circle fs-6"></i></button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BOTTOM ROW: Notes/Terms & Summary Totals -->
        <div class="row g-0 border-top">
            <!-- Left: Notes & Terms -->
            <div class="col-md-7 p-3 border-end">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-sticky-note me-1"></i> Customer Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Notes will be displayed on the quotation document..."><?= esc($quotation['notes'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="form-label"><i class="fas fa-gavel me-1"></i> Terms & Conditions</label>
                    <textarea name="terms" class="form-control" rows="3" placeholder="Payment terms, delivery schedules, validity terms..."><?= esc($quotation['terms'] ?? "1. Prices are valid until the expiry date mentioned above.\n2. Goods once sold will not be taken back.\n3. Subject to local jurisdiction.") ?></textarea>
                </div>
            </div>

            <!-- Right: Calculation Summary Card -->
            <div class="col-md-5 p-0 bg-light">
                <table class="table gst-summary-table mb-0 w-100">
                    <tr>
                        <td class="gst-summary-label">Sub Total:</td>
                        <td class="text-end fw-bold">₹<span id="subTotalDisplay">0.00</span></td>
                    </tr>
                    <tr>
                        <td class="gst-summary-label">
                            <div class="d-flex align-items-center gap-1">
                                <span>Discount:</span>
                                <select name="discount_type" id="discountType" class="form-select form-select-sm p-0 px-1" style="width: auto; height: 24px; font-size: 0.75rem;">
                                    <option value="Fixed" <?= (($quotation['discount_type'] ?? '') == 'Fixed') ? 'selected' : '' ?>>₹ Fixed</option>
                                    <option value="Percentage" <?= (($quotation['discount_type'] ?? '') == 'Percentage') ? 'selected' : '' ?>>% Pct</option>
                                </select>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="input-group input-group-sm justify-content-end">
                                <input type="number" step="0.01" name="discount_amount" id="discountAmountInput" class="form-control form-control-sm text-end" style="max-width: 110px;" value="<?= $quotation['discount_amount'] ?? 0 ?>">
                            </div>
                            <span id="discountCalculatedDisplay" class="text-danger small" style="display: none;">(-₹0.00)</span>
                        </td>
                    </tr>

                    <!-- DYNAMIC TAX ROWS (CGST/SGST OR IGST) -->
                    <tr id="cgstRow">
                        <td class="gst-summary-label ps-3 text-muted">CGST Total:</td>
                        <td class="text-end text-muted">₹<span id="cgstDisplay">0.00</span></td>
                    </tr>
                    <tr id="sgstRow">
                        <td class="gst-summary-label ps-3 text-muted">SGST Total:</td>
                        <td class="text-end text-muted">₹<span id="sgstDisplay">0.00</span></td>
                    </tr>
                    <tr id="igstRow" style="display: none;">
                        <td class="gst-summary-label ps-3 text-muted">IGST Total:</td>
                        <td class="text-end text-muted">₹<span id="igstDisplay">0.00</span></td>
                    </tr>

                    <tr>
                        <td class="gst-summary-label">Shipping / Freight:</td>
                        <td class="text-end">
                            <input type="number" step="0.01" name="shipping_charge" id="shippingChargeInput" class="form-control form-control-sm text-end float-end" style="max-width: 110px;" value="<?= $quotation['shipping_charge'] ?? 0 ?>">
                        </td>
                    </tr>

                    <tr>
                        <td class="gst-summary-label">Round Off:</td>
                        <td class="text-end">
                            <input type="number" step="0.01" name="roundoff_amount" id="roundoffInput" class="form-control form-control-sm text-end float-end" style="max-width: 110px;" value="<?= $quotation['roundoff_amount'] ?? 0 ?>">
                        </td>
                    </tr>

                    <tr class="grand-total-row">
                        <td class="text-uppercase py-3">Total Amount (₹):</td>
                        <td class="text-end py-3 text-primary fw-bolder fs-5">₹<span id="grandTotalDisplay">0.00</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- FORM ACTION BUTTONS -->
        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center">
            <a href="<?= site_url('quotations') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Quotations
            </a>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                    <i class="fas fa-check-circle me-1"></i> <?= $quotation ? 'Update Quotation / Estimate' : 'Save Quotation / Estimate' ?>
                </button>
            </div>
        </div>

    </div>
</div>
</form>
</div>

<!-- TEMPLATE ROW FOR ADDING PRODUCTS DYNAMICALLY -->
<template id="rowTemplate">
    <tr class="item-row">
        <td class="text-center row-number fw-bold text-muted"></td>
        <td>
            <select class="form-select form-select-sm product-select select2-dynamic mb-1">
                <option value="">-- Custom Item / Select Product --</option>
                <?php foreach ($products as $prod): ?>
                    <option value="<?= $prod['id'] ?>" 
                        data-rate="<?= $prod['selling_price'] ?? $prod['rate'] ?? 0 ?>" 
                        data-hsn="<?= $prod['hsn_code'] ?? '' ?>" 
                        data-tax="<?= $prod['tax_percentage'] ?? 0 ?>">
                        <?= esc($prod['product_name']) ?> (<?= esc($prod['product_code'] ?? $prod['sku'] ?? '') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" class="form-control form-control-sm item-desc" placeholder="Item description" required>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm item-hsn text-center">
        </td>
        <td>
            <input type="number" step="0.01" class="form-control form-control-sm item-qty text-center fw-bold" value="1" min="0.01" required>
        </td>
        <td>
            <input type="number" step="0.01" class="form-control form-control-sm item-rate text-end fw-bold" value="0.00" min="0" required>
        </td>
        <td>
            <select class="form-select form-select-sm item-tax">
                <option value="0">0% (Exempt)</option>
                <?php foreach ($taxes as $tax): ?>
                    <option value="<?= $tax['percentage'] ?>" <?= ((float)$tax['percentage'] == 5) ? 'selected' : '' ?>>
                        <?= esc($tax['name']) ?> (<?= $tax['percentage'] + 0 ?>%)
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td class="text-end">
            <span class="item-amount-display fw-bold">0.00</span>
            <input type="hidden" class="item-amount" value="0">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger remove-row-btn p-0" title="Delete Row"><i class="fas fa-times-circle fs-6"></i></button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let isInterState = <?= !empty($quotation['is_inter_state']) ? 'true' : 'false' ?>;

    // Initialize Select2 elements
    function initSelect2(element) {
        if (typeof $.fn.select2 !== 'undefined') {
            $(element).select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        }
    }

    $('.select2').each(function() {
        initSelect2(this);
    });

    // Handle Customer Selection
    $('#customerSelect').on('change', function() {
        const customerId = $(this).val();
        if (!customerId) {
            $('#customerAddressSection').slideUp();
            $('#taxModeIndicator').hide();
            return;
        }

        fetch(`<?= site_url('customers/details/') ?>${customerId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    $('#billingAddressDisplay').text(data.billing_address || 'N/A');
                    $('#shippingAddressDisplay').text(data.shipping_address || 'Same as billing address');
                    $('#customerPhone').text(data.phone || 'N/A');
                    $('#customerWhatsapp').text(data.whatsapp_number || 'N/A');
                    $('#customerGstin').text(data.gstin || 'URP / Not Registered');
                    $('#customerGstType').text(data.gst_type || 'Unregistered');
                    $('#customerAddressSection').slideDown();

                    // Tax mode determination
                    isInterState = data.is_inter_state;
                    $('#isInterStateField').val(isInterState ? 1 : 0);

                    if (isInterState) {
                        $('#taxModeIndicator').show().removeClass('bg-secondary bg-primary').addClass('bg-info').find('#taxModeText').text('Inter-State (IGST)');
                        $('#cgstRow, #sgstRow').hide();
                        $('#igstRow').show();
                    } else {
                        $('#taxModeIndicator').show().removeClass('bg-secondary bg-info').addClass('bg-primary').find('#taxModeText').text('Intra-State (CGST + SGST)');
                        $('#cgstRow, #sgstRow').show();
                        $('#igstRow').hide();
                    }

                    recalculateAll();
                }
            })
            .catch(err => {
                console.error("Error fetching customer info:", err);
            });
    });

    // Trigger customer info load if editing
    if ($('#customerSelect').val()) {
        $('#customerSelect').trigger('change');
    }

    // Barcode Scanner Listener
    const barcodeInput = document.getElementById('barcodeScannerInput');
    const glassOverlay = document.getElementById('tableGlassOverlay');
    const scanFeedback = document.getElementById('scanFeedback');

    barcodeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const code = this.value.trim();
            if (!code) return;

            glassOverlay.classList.add('active');
            scanFeedback.innerHTML = `<span class="text-primary"><i class="fas fa-spinner fa-spin"></i> Searching ${code}...</span>`;

            fetch(`<?= site_url('master-data/product-by-barcode') ?>?barcode=${encodeURIComponent(code)}`)
                .then(res => res.json())
                .then(data => {
                    glassOverlay.classList.remove('active');
                    if (data && data.status === 'success' && data.product) {
                        addProductRow(data.product);
                        scanFeedback.innerHTML = `<span class="text-success"><i class="fas fa-check"></i> Added: ${data.product.product_name}</span>`;
                        barcodeInput.value = '';
                        barcodeInput.focus();
                    } else {
                        scanFeedback.innerHTML = `<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Not found: ${code}</span>`;
                        barcodeInput.select();
                    }
                })
                .catch(err => {
                    glassOverlay.classList.remove('active');
                    scanFeedback.innerHTML = `<span class="text-danger">Error scanning barcode</span>`;
                });
        }
    });

    // Add Empty Row
    $('#addEmptyRowBtn').on('click', function() {
        addNewRow();
    });

    function addNewRow(productData = null) {
        const tbody = document.getElementById('itemsBody');
        const template = document.getElementById('rowTemplate');
        const clone = template.content.cloneNode(true);
        const tr = clone.querySelector('tr');
        
        tbody.appendChild(tr);
        reindexRows();

        const newSelect = $(tr).find('.product-select');
        initSelect2(newSelect);

        if (productData) {
            newSelect.val(productData.id).trigger('change');
            $(tr).find('.item-desc').val(productData.product_name || productData.name);
            $(tr).find('.item-hsn').val(productData.hsn_code || '');
            $(tr).find('.item-rate').val(parseFloat(productData.selling_price || productData.rate || 0).toFixed(2));
            if (productData.tax_percentage !== undefined) {
                $(tr).find('.item-tax').val(productData.tax_percentage);
            }
        }

        recalculateAll();
        return tr;
    }

    function addProductRow(product) {
        // Check if product is already in the table
        let existingRow = null;
        $('#itemsBody .item-row').each(function() {
            const pid = $(this).find('.product-select').val();
            if (pid == product.id) {
                existingRow = this;
                return false;
            }
        });

        if (existingRow) {
            const qtyInput = $(existingRow).find('.item-qty');
            qtyInput.val(parseFloat(qtyInput.val() || 0) + 1);
            recalculateRow($(existingRow));
            recalculateAll();
        } else {
            // Check if first row is completely empty, use it
            const firstRow = $('#itemsBody .item-row').first();
            if (firstRow.length && !firstRow.find('.product-select').val() && !firstRow.find('.item-desc').val()) {
                firstRow.find('.product-select').val(product.id).trigger('change');
                firstRow.find('.item-desc').val(product.product_name || product.name);
                firstRow.find('.item-hsn').val(product.hsn_code || '');
                firstRow.find('.item-rate').val(parseFloat(product.selling_price || product.rate || 0).toFixed(2));
                if (product.tax_percentage !== undefined) {
                    firstRow.find('.item-tax').val(product.tax_percentage);
                }
                recalculateRow(firstRow);
                recalculateAll();
            } else {
                addNewRow(product);
            }
        }
    }

    // Reindex all rows for proper POST structure
    function reindexRows() {
        $('#itemsBody .item-row').each(function(index) {
            $(this).attr('data-index', index);
            $(this).find('.row-number').text(index + 1);
            $(this).find('.product-select').attr('name', `items[${index}][product_id]`);
            $(this).find('.item-desc').attr('name', `items[${index}][description]`);
            $(this).find('.item-hsn').attr('name', `items[${index}][hsn_code]`);
            $(this).find('.item-qty').attr('name', `items[${index}][quantity]`);
            $(this).find('.item-rate').attr('name', `items[${index}][rate]`);
            $(this).find('.item-tax').attr('name', `items[${index}][tax_percentage]`);
            $(this).find('.item-amount').attr('name', `items[${index}][amount]`);
        });
    }

    // Product selection change inside row
    $(document).on('change', '.product-select', function() {
        const tr = $(this).closest('tr');
        const selected = $(this).find(':selected');
        const pid = $(this).val();

        if (pid) {
            const rate = selected.data('rate') || 0;
            const hsn = selected.data('hsn') || '';
            const tax = selected.data('tax') || 0;
            const text = selected.text().trim();

            if (!tr.find('.item-desc').val()) {
                tr.find('.item-desc').val(text.split('(')[0].trim());
            }
            tr.find('.item-hsn').val(hsn);
            tr.find('.item-rate').val(parseFloat(rate).toFixed(2));
            tr.find('.item-tax').val(tax);
        }
        recalculateRow(tr);
        recalculateAll();
    });

    // Remove row
    $(document).on('click', '.remove-row-btn', function() {
        if ($('#itemsBody .item-row').length > 1) {
            $(this).closest('tr').remove();
            reindexRows();
            recalculateAll();
        } else {
            // Just clear the single row
            const tr = $(this).closest('tr');
            tr.find('.product-select').val('').trigger('change');
            tr.find('.item-desc').val('');
            tr.find('.item-hsn').val('');
            tr.find('.item-qty').val('1');
            tr.find('.item-rate').val('0.00');
            tr.find('.item-tax').val('0');
            recalculateRow(tr);
            recalculateAll();
        }
    });

    // Dynamic inputs calculation
    $(document).on('input change', '.item-qty, .item-rate, .item-tax', function() {
        recalculateRow($(this).closest('tr'));
        recalculateAll();
    });

    $('#discountType, #discountAmountInput, #shippingChargeInput, #roundoffInput').on('input change', function() {
        recalculateAll();
    });

    // Calculate individual row
    function recalculateRow(tr) {
        const qty = parseFloat(tr.find('.item-qty').val()) || 0;
        const rate = parseFloat(tr.find('.item-rate').val()) || 0;
        const amount = qty * rate;

        tr.find('.item-amount-display').text(amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        tr.find('.item-amount').val(amount.toFixed(2));
    }

    // Recalculate Grand Totals & Taxes
    function recalculateAll() {
        let subtotal = 0;
        let taxGroups = {};

        $('#itemsBody .item-row').each(function() {
            const qty = parseFloat($(this).find('.item-qty').val()) || 0;
            const rate = parseFloat($(this).find('.item-rate').val()) || 0;
            const taxPct = parseFloat($(this).find('.item-tax').val()) || 0;
            const lineAmount = qty * rate;

            subtotal += lineAmount;

            if (lineAmount > 0 && taxPct > 0) {
                if (!taxGroups[taxPct]) taxGroups[taxPct] = 0;
                taxGroups[taxPct] += lineAmount;
            }
        });

        // Discount calculation
        const discType = $('#discountType').val();
        const discInput = parseFloat($('#discountAmountInput').val()) || 0;
        let totalDiscount = 0;

        if (discType === 'Percentage') {
            totalDiscount = (subtotal * discInput) / 100;
            $('#discountCalculatedDisplay').text(`(-₹${totalDiscount.toFixed(2)})`).show();
        } else {
            totalDiscount = discInput;
            $('#discountCalculatedDisplay').hide();
        }

        if (totalDiscount > subtotal) totalDiscount = subtotal;

        // Taxes with proportional discount deduction
        let totalTax = 0;
        let totalCGST = 0;
        let totalSGST = 0;
        let totalIGST = 0;

        for (let slab in taxGroups) {
            const slabAmount = taxGroups[slab];
            const slabProportionalDiscount = subtotal > 0 ? (slabAmount / subtotal) * totalDiscount : 0;
            const taxableValue = Math.max(0, slabAmount - slabProportionalDiscount);
            const slabTax = (taxableValue * parseFloat(slab)) / 100;

            totalTax += slabTax;
            if (isInterState) {
                totalIGST += slabTax;
            } else {
                totalCGST += slabTax / 2;
                totalSGST += slabTax / 2;
            }
        }

        const shipping = parseFloat($('#shippingChargeInput').val()) || 0;
        let roundoff = parseFloat($('#roundoffInput').val()) || 0;

        const baseGrandTotal = (subtotal - totalDiscount) + totalTax + shipping;
        
        // Auto calculate round off if not manually set or zero
        if ($('#roundoffInput').data('auto') !== false) {
            const roundedTotal = Math.round(baseGrandTotal);
            roundoff = +(roundedTotal - baseGrandTotal).toFixed(2);
            $('#roundoffInput').val(roundoff);
        }

        const grandTotal = Math.max(0, baseGrandTotal + roundoff);

        // Update displays
        $('#subTotalDisplay').text(subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#cgstDisplay').text(totalCGST.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#sgstDisplay').text(totalSGST.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#igstDisplay').text(totalIGST.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#grandTotalDisplay').text(grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    $('#roundoffInput').on('change', function() {
        $(this).data('auto', false);
    });

    // Agent commission pre-fill
    $('#agentSelect').on('change', function() {
        const comm = $(this).find(':selected').data('commission') || 0;
        if (comm > 0) {
            $('#agentCommPercent').val(comm);
        }
    });

    // Initialize calculation on load
    $('#itemsBody .item-row').each(function() {
        recalculateRow($(this));
    });
    recalculateAll();
});
</script>
<?= $this->endSection() ?>
