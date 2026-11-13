<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark fw-bold">
                    <i class="fas fa-file-invoice text-primary me-2"></i> Generate e-Way Bill
                </h1>
                <p class="text-muted small mb-0">Create government e-Way Bill (Part-A / Part-B) directly via Zoho Books API</p>
            </div>
            <div class="col-sm-6 text-end">
                <a href="<?= site_url('eway_bills') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Console
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle me-1"></i> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('eway_bills/store') ?>" id="ewaybillForm">
            <?= csrf_field() ?>
            <input type="hidden" name="document_id" id="document_id" value="<?= esc($docId ?: old('document_id')) ?>">

            <div class="row">
                <!-- Left Column: Document & Supply Details -->
                <div class="col-lg-7">
                    <!-- 1. Document Selection Card -->
                    <div class="card card-outline card-primary shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title fw-bold mb-0 text-primary">
                                <i class="fas fa-file-alt me-2"></i> 1. Document & Transaction Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold">Document Type <span class="text-danger">*</span></label>
                                    <select name="document_type" id="document_type" class="form-select" onchange="resetDocumentLookup()">
                                        <option value="invoice" <?= ($docType === 'invoice') ? 'selected' : '' ?>>Tax Invoice (Outward)</option>
                                        <option value="credit_note" <?= ($docType === 'credit_note') ? 'selected' : '' ?>>Credit Note (Sales Return)</option>
                                        <option value="purchase_bill" <?= ($docType === 'purchase_bill') ? 'selected' : '' ?>>Purchase Bill (Inward)</option>
                                        <option value="delivery_challan" <?= ($docType === 'delivery_challan') ? 'selected' : '' ?>>Delivery Challan</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label fw-bold">Search Document Number / ID <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" id="doc_search_input" class="form-control" 
                                            placeholder="Type Invoice No (e.g. INV-001937)..." 
                                            value="<?= esc($document['invoice_number'] ?? ($document['return_number'] ?? '')) ?>">
                                        <button class="btn btn-outline-primary" type="button" onclick="performDocLookup()">
                                            <i class="fas fa-search me-1"></i> Find
                                        </button>
                                    </div>
                                    <div id="lookup_spinner" class="text-primary small mt-1 d-none">
                                        <i class="fas fa-spinner fa-spin me-1"></i> Looking up document details...
                                    </div>
                                </div>
                            </div>

                            <!-- Document Info Preview Box -->
                            <div class="p-3 bg-light rounded border mb-3" id="doc_info_box">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="text-muted small d-block">Document Number & Date</label>
                                        <span class="fw-bold" id="lbl_doc_no"><?= esc($document['invoice_number'] ?? ($document['return_number'] ?? 'Not Selected')) ?></span>
                                        <span class="text-muted ms-2" id="lbl_doc_date"><?= !empty($document['invoice_date'] ?? $document['return_date']) ? '(' . date('d M Y', strtotime($document['invoice_date'] ?? $document['return_date'])) . ')' : '' ?></span>
                                        <input type="hidden" name="document_number" id="hidden_doc_number" value="<?= esc($document['invoice_number'] ?? ($document['return_number'] ?? '')) ?>">
                                        <input type="hidden" name="document_date" id="hidden_doc_date" value="<?= esc($document['invoice_date'] ?? ($document['return_date'] ?? date('Y-m-d'))) ?>">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="text-muted small d-block">Customer / Party Name</label>
                                        <span class="fw-bold" id="lbl_party_name"><?= esc($party['name'] ?? ($party['customer_name'] ?? 'Not Selected')) ?></span>
                                        <div class="small text-muted" id="lbl_party_gstin">GSTIN: <?= esc($party['gstin'] ?? 'Unregistered') ?></div>
                                    </div>
                                </div>
                                <div class="row mt-2 pt-2 border-top">
                                    <div class="col-md-4">
                                        <label class="text-muted small d-block">Total Taxable + Tax</label>
                                        <span class="fs-5 fw-bold text-success" id="lbl_total_amount">₹<?= number_format((float) ($document['total_amount'] ?? 0), 2) ?></span>
                                        <input type="hidden" name="total_amount" id="hidden_total_amount" value="<?= esc($document['total_amount'] ?? 0) ?>">
                                    </div>
                                    <div class="col-md-8 text-end">
                                        <span class="badge bg-info text-white" id="lbl_existing_ewb">
                                            <?= !empty($document['ewaybill_number']) ? 'Existing EWB: #' . esc($document['ewaybill_number']) : 'No e-Way Bill Yet' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Supply Type</label>
                                    <select name="supply_type" class="form-select">
                                        <option value="Outward">Outward (Sales / Outflow)</option>
                                        <option value="Inward">Inward (Purchase / Inflow)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sub-Supply Type</label>
                                    <select name="sub_supply_type" class="form-select">
                                        <option value="Supply">Supply</option>
                                        <option value="Export">Export</option>
                                        <option value="Job Work">Job Work</option>
                                        <option value="SKD/CKD">SKD / CKD</option>
                                        <option value="Recipient Not Known">Recipient Not Known</option>
                                        <option value="For Own Use">For Own Use</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Address Details Card (Dispatch From, Bill From, Bill To, Ship To) -->
                    <div class="card card-outline card-secondary shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title fw-bold mb-0 text-secondary">
                                <i class="fas fa-map-marked-alt me-2"></i> 2. Address Details (Dispatch / Billing / Shipping)
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Row 1: Dispatch From & Bill From (Origin Details) -->
                            <div class="row g-3 pb-3 mb-3 border-bottom">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="fw-bold text-primary mb-0"><i class="fas fa-warehouse me-1"></i> Dispatch From</h6>
                                        <span class="badge bg-primary-subtle text-primary border">Place of Dispatch</span>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Address Line 1</label>
                                        <input type="text" name="from_address_1" class="form-control form-control-sm" value="C.P Kannaiah Street, Opp: Gopinath Engineering Works">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Address Line 2</label>
                                        <input type="text" name="from_address_2" class="form-control form-control-sm" value="Rasipuram (TK), Namakkal">
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <label class="form-label small">Dispatch City <span class="text-danger">*</span></label>
                                            <input type="text" name="from_place" class="form-control form-control-sm" value="Rasipuram" required>
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">State Code <span class="text-danger">*</span></label>
                                            <input type="text" name="from_state_code" class="form-control form-control-sm" value="33" required>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">Pincode <span class="text-danger">*</span></label>
                                            <input type="text" name="from_pincode" class="form-control form-control-sm" value="637408" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="fw-bold text-dark mb-0"><i class="fas fa-building me-1"></i> Bill From</h6>
                                        <span class="badge bg-light text-secondary border">Supplier Registered Place</span>
                                    </div>
                                    <div class="p-2 rounded bg-light border">
                                        <div class="small fw-semibold">Rasi Handloom Textiles</div>
                                        <div class="small text-muted">C.P Kannaiah Street, Opp: Gopinath Engineering Works,</div>
                                        <div class="small text-muted">Rasipuram (TK), Namakkal, Tamil Nadu, India - 637408</div>
                                        <div class="small text-primary mt-1"><strong>City:</strong> Rasipuram | <strong>State:</strong> 33 (TN) | <strong>PIN:</strong> 637408</div>
                                        <input type="hidden" name="bill_from_place" value="Rasipuram">
                                        <input type="hidden" name="bill_from_state_code" value="33">
                                        <input type="hidden" name="bill_from_pincode" value="637408">
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: Bill To & Ship To (Recipient Details) -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="fw-bold text-info mb-0"><i class="fas fa-file-invoice me-1"></i> Bill To</h6>
                                        <span class="badge bg-info-subtle text-info border">Buyer / Billing Address</span>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Billing Address Line 1</label>
                                        <input type="text" name="bill_to_address_1" id="bill_to_address_1" class="form-control form-control-sm" 
                                            value="<?= esc($billAddress['address_line1'] ?? '') ?>" placeholder="e.g. 56, DHARMARAJA KOIL STREET">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Billing Address Line 2</label>
                                        <input type="text" name="bill_to_address_2" id="bill_to_address_2" class="form-control form-control-sm" 
                                            value="<?= esc($billAddress['address_line2'] ?? '') ?>" placeholder="e.g. KOSAPPALAYAM,ARNI">
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <label class="form-label small">Billing City <span class="text-danger">*</span></label>
                                            <input type="text" name="bill_to_place" id="bill_to_place" class="form-control form-control-sm" 
                                                value="<?= esc($billAddress['city'] ?? '') ?>" placeholder="e.g. Arni" required>
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">State Code <span class="text-danger">*</span></label>
                                            <input type="text" name="bill_to_state_code" id="bill_to_state_code" class="form-control form-control-sm" 
                                                value="<?= esc($billAddress['gst_state_code'] ?? '33') ?>" required>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">Pincode <span class="text-danger">*</span></label>
                                            <input type="text" name="bill_to_pincode" id="bill_to_pincode" class="form-control form-control-sm" 
                                                value="<?= esc($billAddress['pincode'] ?? '') ?>" placeholder="632301" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="fw-bold text-success mb-0"><i class="fas fa-truck-moving me-1"></i> Ship To</h6>
                                        <span class="badge bg-success-subtle text-success border">Delivery / Consignee Address</span>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Shipping Address Line 1</label>
                                        <input type="text" name="to_address_1" id="to_address_1" class="form-control form-control-sm" 
                                            value="<?= esc($shipAddress['address_line1'] ?? '') ?>" placeholder="e.g. 500, Gandhi Road">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Shipping Address Line 2</label>
                                        <input type="text" name="to_address_2" id="to_address_2" class="form-control form-control-sm" 
                                            value="<?= esc($shipAddress['address_line2'] ?? '') ?>" placeholder="e.g. Near Bus Stand">
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <label class="form-label small">Delivery City <span class="text-danger">*</span></label>
                                            <input type="text" name="to_place" id="to_place" class="form-control form-control-sm" 
                                                value="<?= esc($shipAddress['city'] ?? '') ?>" placeholder="e.g. Kanchipuram" required>
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">State Code <span class="text-danger">*</span></label>
                                            <input type="text" name="to_state_code" id="to_state_code" class="form-control form-control-sm" 
                                                value="<?= esc($shipAddress['gst_state_code'] ?? '33') ?>" required>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">Pincode <span class="text-danger">*</span></label>
                                            <input type="text" name="to_pincode" id="to_pincode" class="form-control form-control-sm" 
                                                value="<?= esc($shipAddress['pincode'] ?? '') ?>" placeholder="631501" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Transportation & Generation Type -->
                <div class="col-lg-5">
                    <!-- Generation Mode (Full vs Part-A) -->
                    <div class="card card-outline card-success shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title fw-bold mb-0 text-success">
                                <i class="fas fa-truck-loading me-2"></i> 3. Generation Scope
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check p-3 border rounded mb-3 bg-light">
                                <input class="form-check-input" type="radio" name="action" id="action_full" value="save_generate" checked onchange="togglePartBRequirement()">
                                <label class="form-check-label fw-bold" for="action_full">
                                    <i class="fas fa-check-circle text-success me-1"></i> Full e-Way Bill (Part-A + Part-B)
                                </label>
                                <div class="text-muted small ps-4">
                                    Generates complete e-Way Bill ready for transit. Vehicle number & distance are mandatory.
                                </div>
                            </div>

                            <div class="form-check p-3 border rounded mb-3 bg-light">
                                <input class="form-check-input" type="radio" name="action" id="action_part_a" value="generate_part_a" onchange="togglePartBRequirement()">
                                <label class="form-check-label fw-bold" for="action_part_a">
                                    <i class="fas fa-clock text-warning me-1"></i> Part-A Slip Only (Deferred Vehicle)
                                </label>
                                <div class="text-muted small ps-4">
                                    Vehicle is not assigned yet or handed to a third-party transporter. Transporter ID is mandatory.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transport & Conveyance Form -->
                    <div class="card card-outline card-info shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title fw-bold mb-0 text-info">
                                <i class="fas fa-shipping-fast me-2"></i> 4. Transport & Vehicle Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Select Transporter (Optional)</label>
                                <select id="preset_transporter" class="form-select" onchange="applyPresetTransporter(this)">
                                    <option value="">-- Choose from Master List --</option>
                                    <?php foreach ($transports as $t): ?>
                                        <option value="<?= esc($t['id']) ?>" 
                                            data-name="<?= esc($t['transport_name']) ?>" 
                                            data-gst="<?= esc($t['branch_gst_number']) ?>">
                                            <?= esc($t['transport_name']) ?> (GST: <?= esc($t['branch_gst_number'] ?: 'N/A') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Transport Mode <span class="text-danger">*</span></label>
                                    <select name="transport_mode" class="form-select" required>
                                        <option value="1">1 - Road</option>
                                        <option value="2">2 - Rail</option>
                                        <option value="3">3 - Air</option>
                                        <option value="4">4 - Ship</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Approx Distance (Km)</label>
                                    <input type="number" name="distance_km" id="distance_km" class="form-control" value="0" min="0">
                                    <div class="form-text text-muted small">Keep 0 for Zoho / NIC auto-calculation by pincode.</div>
                                </div>
                            </div>

                            <div class="mb-3" id="group_vehicle_number">
                                <label class="form-label fw-bold">Vehicle Number <span class="text-danger" id="req_star_vehicle">*</span></label>
                                <input type="text" name="vehicle_number" id="vehicle_number" class="form-control text-uppercase" 
                                    placeholder="e.g. TN28AB1234" value="<?= esc($document['transport_name'] ?? '') ?>">
                                <div class="form-text small">Enter without space or hyphens.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vehicle Type</label>
                                <select name="vehicle_type" class="form-select">
                                    <option value="Regular">Regular</option>
                                    <option value="Over Dimensional Cargo">Over Dimensional Cargo</option>
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Transporter ID (GSTIN)</label>
                                    <input type="text" name="transporter_id" id="transporter_id" class="form-control text-uppercase" placeholder="15-digit GSTIN">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Transporter Name</label>
                                    <input type="text" name="transporter_name" id="transporter_name" class="form-control" placeholder="Agency Name">
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label">LR / Waybill Number</label>
                                    <input type="text" name="transporter_doc_number" class="form-control" 
                                        value="<?= esc($document['waybill_number'] ?? '') ?>" placeholder="LR Number">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">LR Date</label>
                                    <input type="date" name="transporter_doc_date" class="form-control" 
                                        value="<?= esc($document['waybill_date'] ?? date('Y-m-d')) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light p-3">
                            <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm fw-bold">
                                <i class="fas fa-paper-plane me-2"></i> Submit & Generate via Zoho Books API
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function togglePartBRequirement() {
    const isPartA = document.getElementById('action_part_a').checked;
    const reqStar = document.getElementById('req_star_vehicle');
    const vehInput = document.getElementById('vehicle_number');
    const transInput = document.getElementById('transporter_id');

    if (isPartA) {
        reqStar.classList.add('d-none');
        vehInput.removeAttribute('required');
        transInput.setAttribute('required', 'required');
    } else {
        reqStar.classList.remove('d-none');
        vehInput.setAttribute('required', 'required');
        transInput.removeAttribute('required');
    }
}

function applyPresetTransporter(select) {
    const opt = select.options[select.selectedIndex];
    if (opt && opt.value) {
        document.getElementById('transporter_name').value = opt.getAttribute('data-name') || '';
        document.getElementById('transporter_id').value = opt.getAttribute('data-gst') || '';
    }
}

function resetDocumentLookup() {
    document.getElementById('document_id').value = '';
    document.getElementById('doc_search_input').value = '';
    document.getElementById('lbl_doc_no').innerText = 'Not Selected';
    document.getElementById('lbl_party_name').innerText = 'Not Selected';
    document.getElementById('lbl_party_gstin').innerText = 'GSTIN: -';
    document.getElementById('lbl_total_amount').innerText = '₹0.00';
}

function performDocLookup() {
    const docType = document.getElementById('document_type').value;
    const q = document.getElementById('doc_search_input').value.trim();

    if (!q) {
        alert('Please enter a document number or ID to search.');
        return;
    }

    const spinner = document.getElementById('lookup_spinner');
    spinner.classList.remove('d-none');

    fetch('<?= site_url('eway_bills/lookup-document') ?>?doc_type=' + encodeURIComponent(docType) + '&q=' + encodeURIComponent(q))
        .then(res => res.json())
        .then(data => {
            spinner.classList.add('d-none');
            if (data.success && data.document) {
                const doc = data.document;
                document.getElementById('document_id').value = doc.id;
                document.getElementById('hidden_doc_number').value = doc.document_number;
                document.getElementById('hidden_doc_date').value = doc.document_date;
                document.getElementById('hidden_total_amount').value = doc.total_amount;

                document.getElementById('lbl_doc_no').innerText = '#' + doc.document_number;
                document.getElementById('lbl_doc_date').innerText = '(' + doc.document_date + ')';
                document.getElementById('lbl_party_name').innerText = doc.party_name || 'N/A';
                document.getElementById('lbl_party_gstin').innerText = 'GSTIN: ' + (doc.party_gstin || 'Unregistered');
                document.getElementById('lbl_total_amount').innerText = '₹' + parseFloat(doc.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2});

                // Populate Bill To
                if (doc.bill_to_address_1) {
                    document.getElementById('bill_to_address_1').value = doc.bill_to_address_1;
                }
                if (doc.bill_to_address_2) {
                    document.getElementById('bill_to_address_2').value = doc.bill_to_address_2;
                }
                if (doc.bill_to_place) {
                    document.getElementById('bill_to_place').value = doc.bill_to_place;
                }
                if (doc.bill_to_pincode) {
                    document.getElementById('bill_to_pincode').value = doc.bill_to_pincode;
                }
                if (doc.bill_to_state_code) {
                    document.getElementById('bill_to_state_code').value = doc.bill_to_state_code;
                }

                // Populate Ship To
                if (doc.to_address_1) {
                    document.getElementById('to_address_1').value = doc.to_address_1;
                }
                if (doc.to_address_2) {
                    document.getElementById('to_address_2').value = doc.to_address_2;
                }
                if (doc.to_place) {
                    document.getElementById('to_place').value = doc.to_place;
                }
                if (doc.to_pincode) {
                    document.getElementById('to_pincode').value = doc.to_pincode;
                }
                if (doc.to_state_code) {
                    document.getElementById('to_state_code').value = doc.to_state_code;
                }

                const ewbBadge = document.getElementById('lbl_existing_ewb');
                if (doc.existing_ewb) {
                    ewbBadge.className = 'badge bg-warning text-dark';
                    ewbBadge.innerText = 'Active EWB: #' + doc.existing_ewb;
                } else {
                    ewbBadge.className = 'badge bg-info text-white';
                    ewbBadge.innerText = 'No e-Way Bill Yet';
                }
            } else {
                alert(data.message || 'No matching document found.');
            }
        })
        .catch(err => {
            spinner.classList.add('d-none');
            alert('Lookup error occurred: ' + err.message);
        });
}

// Initial trigger on load
document.addEventListener('DOMContentLoaded', function() {
    togglePartBRequirement();
});
</script>
<?= $this->endSection() ?>

