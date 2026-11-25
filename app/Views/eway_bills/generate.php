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
                <p class="text-muted small mb-0">Create government e-Way Bill (Part-A / Part-B) directly via API Sathi (NIC Gateway)</p>
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

        <?php
        $hasExistingEwb = !empty($existingEwb) && in_array($existingEwb['status'] ?? '', ['generated', 'part_a_generated'], true);
        $existingEwbUrl = $hasExistingEwb
            ? (!empty($existingEwb['id']) ? site_url('eway_bills/view/' . $existingEwb['id']) : site_url('invoices/view/' . ($docId ?: ($existingEwb['document_id'] ?? 0))))
            : '#';
        ?>
        <div id="existingEwbAlert" class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2 shadow-sm <?= $hasExistingEwb ? '' : 'd-none' ?>" role="alert">
            <div>
                <i class="fas fa-info-circle me-1"></i>
                <strong>Already Generated:</strong> e-Way Bill
                <strong id="existingEwbNo"><?= $hasExistingEwb && !empty($existingEwb['ewaybill_number']) ? '#' . esc($existingEwb['ewaybill_number']) : '' ?></strong>
                (<span id="existingEwbStatus"><?= $hasExistingEwb ? esc(strtoupper(str_replace('_', ' ', $existingEwb['status']))) : '' ?></span>) already exists for this document.
            </div>
            <a id="existingEwbLink" href="<?= $existingEwbUrl ?>" class="btn btn-sm btn-dark fw-semibold">
                <i class="fas fa-eye me-1"></i> View Existing e-Way Bill
            </a>
        </div>

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
                                            value="<?= esc(!empty($document) ? ($document['invoice_number'] ?? ($document['return_number'] ?? '')) : '') ?>">
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
                                        <span class="fw-bold" id="lbl_doc_no"><?= esc(!empty($document) ? ($document['invoice_number'] ?? ($document['return_number'] ?? 'Not Selected')) : 'Not Selected') ?></span>
                                        <span class="text-muted ms-2" id="lbl_doc_date">
                                            <?php 
                                            $docDate = !empty($document) ? ($document['invoice_date'] ?? ($document['return_date'] ?? null)) : null;
                                            echo $docDate ? '(' . date('d M Y', strtotime($docDate)) . ')' : '';
                                            ?>
                                        </span>
                                        <input type="hidden" name="document_number" id="hidden_doc_number" value="<?= esc(!empty($document) ? ($document['invoice_number'] ?? ($document['return_number'] ?? '')) : '') ?>">
                                        <input type="hidden" name="document_date" id="hidden_doc_date" value="<?= esc(!empty($document) ? ($document['invoice_date'] ?? ($document['return_date'] ?? date('Y-m-d'))) : date('Y-m-d')) ?>">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="text-muted small d-block">Customer / Party Name</label>
                                        <span class="fw-bold" id="lbl_party_name"><?= esc(!empty($party) ? ($party['name'] ?? ($party['customer_name'] ?? 'Not Selected')) : 'Not Selected') ?></span>
                                        <div class="small text-muted" id="lbl_party_gstin">GSTIN: <?= esc(!empty($party['gstin']) ? $party['gstin'] : 'Unregistered') ?></div>
                                    </div>
                                </div>
                                <div class="row mt-2 pt-2 border-top">
                                    <div class="col-md-4">
                                        <label class="text-muted small d-block">Total Taxable + Tax</label>
                                        <span class="fs-5 fw-bold text-success" id="lbl_total_amount">₹<?= number_format((float) (!empty($document['total_amount']) ? $document['total_amount'] : 0), 2) ?></span>
                                        <input type="hidden" name="total_amount" id="hidden_total_amount" value="<?= esc(!empty($document['total_amount']) ? $document['total_amount'] : 0) ?>">
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
                                    <div class="p-2 rounded bg-light border">
                                        <div class="small fw-semibold">Rasi Handloom Textiles</div>
                                        <div class="small text-muted" id="disp_from_address_1">C.P Kannaiah Street, Opp: Gopinath Engineering Works</div>
                                        <div class="small text-muted" id="disp_from_address_2">Rasipuram (TK), Namakkal</div>
                                        <div class="small text-primary mt-1">
                                            <strong>City:</strong> <span id="disp_from_place">Rasipuram</span> | 
                                            <strong>State:</strong> <span id="disp_from_state_code">33 (TN)</span> | 
                                            <strong>PIN:</strong> <span id="disp_from_pincode">637408</span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="from_address_1" id="from_address_1" value="C.P Kannaiah Street, Opp: Gopinath Engineering Works">
                                    <input type="hidden" name="from_address_2" id="from_address_2" value="Rasipuram (TK), Namakkal">
                                    <input type="hidden" name="from_place" id="from_place" value="Rasipuram">
                                    <input type="hidden" name="from_state_code" id="from_state_code" value="33">
                                    <input type="hidden" name="from_pincode" id="from_pincode" value="637408">
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
                                    </div>
                                    <input type="hidden" name="bill_from_place" value="Rasipuram">
                                    <input type="hidden" name="bill_from_state_code" value="33">
                                    <input type="hidden" name="bill_from_pincode" value="637408">
                                </div>
                            </div>

                            <!-- Row 2: Bill To & Ship To (Recipient Details) -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="fw-bold text-info mb-0"><i class="fas fa-file-invoice me-1"></i> Bill To</h6>
                                        <span class="badge bg-info-subtle text-info border">Buyer / Billing Address</span>
                                    </div>
                                    <div class="p-2 rounded bg-light border">
                                        <div class="small fw-semibold" id="disp_bill_party_name"><?= esc(!empty($party) ? ($party['name'] ?? ($party['customer_name'] ?? 'Not Selected')) : 'Not Selected') ?></div>
                                        <div class="small text-muted" id="disp_bill_to_address_1"><?= esc($billAddress['address_line1'] ?? '-') ?></div>
                                        <div class="small text-muted" id="disp_bill_to_address_2"><?= esc($billAddress['address_line2'] ?? '') ?></div>
                                        <div class="small text-info mt-1">
                                            <strong>City:</strong> <span id="disp_bill_to_place"><?= esc($billAddress['city'] ?? '-') ?></span> | 
                                            <strong>State:</strong> <span id="disp_bill_to_state_code"><?= esc($billAddress['gst_state_code'] ?? '33') ?></span> | 
                                            <strong>PIN:</strong> <span id="disp_bill_to_pincode"><?= esc($billAddress['pincode'] ?? '-') ?></span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="bill_to_address_1" id="bill_to_address_1" value="<?= esc($billAddress['address_line1'] ?? '') ?>">
                                    <input type="hidden" name="bill_to_address_2" id="bill_to_address_2" value="<?= esc($billAddress['address_line2'] ?? '') ?>">
                                    <input type="hidden" name="bill_to_place" id="bill_to_place" value="<?= esc($billAddress['city'] ?? '') ?>">
                                    <input type="hidden" name="bill_to_state_code" id="bill_to_state_code" value="<?= esc($billAddress['gst_state_code'] ?? '33') ?>">
                                    <input type="hidden" name="bill_to_pincode" id="bill_to_pincode" value="<?= esc($billAddress['pincode'] ?? '') ?>">
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="fw-bold text-success mb-0"><i class="fas fa-truck-moving me-1"></i> Ship To</h6>
                                        <span class="badge bg-success-subtle text-success border">Delivery / Consignee Address</span>
                                    </div>
                                    <div class="p-2 rounded bg-light border">
                                        <div class="small fw-semibold" id="disp_ship_party_name"><?= esc(!empty($party) ? ($party['name'] ?? ($party['customer_name'] ?? 'Not Selected')) : 'Not Selected') ?></div>
                                        <div class="small text-muted" id="disp_to_address_1"><?= esc($shipAddress['address_line1'] ?? '-') ?></div>
                                        <div class="small text-muted" id="disp_to_address_2"><?= esc($shipAddress['address_line2'] ?? '') ?></div>
                                        <div class="small text-success mt-1">
                                            <strong>City:</strong> <span id="disp_to_place"><?= esc($shipAddress['city'] ?? '-') ?></span> | 
                                            <strong>State:</strong> <span id="disp_to_state_code"><?= esc($shipAddress['gst_state_code'] ?? '33') ?></span> | 
                                            <strong>PIN:</strong> <span id="disp_to_pincode"><?= esc($shipAddress['pincode'] ?? '-') ?></span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="to_address_1" id="to_address_1" value="<?= esc($shipAddress['address_line1'] ?? '') ?>">
                                    <input type="hidden" name="to_address_2" id="to_address_2" value="<?= esc($shipAddress['address_line2'] ?? '') ?>">
                                    <input type="hidden" name="to_place" id="to_place" value="<?= esc($shipAddress['city'] ?? '') ?>">
                                    <input type="hidden" name="to_state_code" id="to_state_code" value="<?= esc($shipAddress['gst_state_code'] ?? '33') ?>">
                                    <input type="hidden" name="to_pincode" id="to_pincode" value="<?= esc($shipAddress['pincode'] ?? '') ?>">
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
                            <div class="border rounded p-3 mb-3 bg-light">
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="action" id="action_full" value="save_generate" checked onchange="togglePartBRequirement()">
                                    <label class="form-check-label fw-bold text-dark cursor-pointer" for="action_full">
                                        <i class="fas fa-check-circle text-success me-1"></i> Full e-Way Bill (Part-A + Part-B)
                                    </label>
                                </div>
                                <div class="text-muted small ps-4">
                                    Generates complete e-Way Bill ready for transit. Vehicle number & distance are mandatory.
                                </div>
                            </div>

                            <div class="border rounded p-3 mb-0 bg-light">
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="action" id="action_part_a" value="generate_part_a" onchange="togglePartBRequirement()">
                                    <label class="form-check-label fw-bold text-dark cursor-pointer" for="action_part_a">
                                        <i class="fas fa-clock text-warning me-1"></i> Part-A Slip Only (Deferred Vehicle)
                                    </label>
                                </div>
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
                                            data-gst="<?= esc($t['branch_gst_number']) ?>"
                                            data-zoho-id="<?= esc($t['zoho_transporter_id'] ?? '') ?>">
                                            <?= esc($t['transport_name']) ?> <?= !empty($t['zoho_transporter_id']) ? '(Zoho ID: ' . esc($t['zoho_transporter_id']) . ')' : (!empty($t['branch_gst_number']) ? '(GST: ' . esc($t['branch_gst_number']) . ')' : '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Transport Mode <span class="text-danger">*</span></label>
                                    <select name="transport_mode" class="form-select" required>
                                        <option value="road">1 - Road</option>
                                        <option value="rail">2 - Rail</option>
                                        <option value="air">3 - Air</option>
                                        <option value="ship">4 - Ship</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Approx Distance (Km)</label>
                                    <input type="number" name="distance_km" id="distance_km" class="form-control" value="0" min="0">
                                    <div class="form-text text-muted small">Keep 0 for NIC auto-calculation by pincode.</div>
                                </div>
                            </div>

                            <div class="mb-3" id="group_vehicle_number">
                                <label class="form-label fw-bold">Vehicle Number <span class="text-danger" id="req_star_vehicle">*</span></label>
                                <select id="vehicle_picker" class="form-select select2-vehicle" onchange="onVehicleSelect(this)">
                                    <option value="">-- Select Registered Vehicle or Others --</option>
                                    <?php 
                                        $initialDocVeh = !empty($document['transport_name']) ? strtoupper(str_replace(' ', '', $document['transport_name'])) : '';
                                        $isMatched = false;
                                    ?>
                                    <?php if (!empty($vehicles)): ?>
                                        <?php foreach ($vehicles as $v): ?>
                                            <?php 
                                                $vNum = strtoupper(str_replace(' ', '', $v['vehicle_number']));
                                                $selected = (!empty($initialDocVeh) && $initialDocVeh === $vNum);
                                                if ($selected) { $isMatched = true; }
                                            ?>
                                            <option value="<?= esc($vNum) ?>" 
                                                data-type="<?= esc($v['vehicle_type']) ?>"
                                                <?= $selected ? 'selected' : '' ?>>
                                                <?= esc($v['vehicle_number']) ?> <?= !empty($v['vehicle_name']) ? ' - ' . esc($v['vehicle_name']) : '' ?> (<?= esc($v['vehicle_type']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <option value="OTHERS" <?= (!empty($initialDocVeh) && !$isMatched) ? 'selected' : '' ?>>
                                        ➕ Others / Third-party Vehicle (Enter Manually)
                                    </option>
                                </select>

                                <!-- Manual Vehicle Input (Shown if Others is selected) -->
                                <div id="group_manual_vehicle" class="mt-2 <?= (!empty($initialDocVeh) && !$isMatched) ? '' : 'd-none' ?>">
                                    <label class="form-label text-primary small fw-bold mb-1"><i class="fas fa-pencil-alt me-1"></i> Enter Vehicle Number Manually:</label>
                                    <input type="text" name="manual_vehicle_number" id="manual_vehicle_number" class="form-control text-uppercase"
                                        placeholder="e.g. TN28BY9140"
                                        value="<?= (!empty($initialDocVeh) && !$isMatched) ? esc($initialDocVeh) : '' ?>"
                                        oninput="syncVehicleNumber()">
                                </div>

                                <!-- Hidden input submitted with the form -->
                                <input type="hidden" name="vehicle_number" id="vehicle_number" 
                                    value="<?= (!empty($initialDocVeh)) ? esc($initialDocVeh) : '' ?>">
                                <div class="form-text small">Select from Operations &gt; Vehicles master list or choose 'Others' to type manually.</div>
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
                                    <label class="form-label">Transporter ID (Optional)</label>
                                    <input type="text" name="transporter_id" id="transporter_id" class="form-control bg-light" placeholder="Auto-populated if available" readonly>
                                    <div class="form-text small text-muted">Optional transporter reference ID.</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Transporter GSTIN / TRANSIN</label>
                                    <input type="text" name="transporter_gstin" id="transporter_gstin" class="form-control text-uppercase bg-light" placeholder="15-digit GSTIN / TRANSIN" readonly>
                                    <div class="form-text small text-muted">Auto-populated from transporter (uneditable).</div>
                                </div>
                                <input type="hidden" name="transporter_name" id="transporter_name" value="">
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label">LR / Waybill Number</label>
                                    <input type="text" name="transporter_doc_number" class="form-control" 
                                        value="<?= esc(!empty($document['waybill_number']) ? $document['waybill_number'] : '') ?>" placeholder="LR Number">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">LR Date</label>
                                    <input type="date" name="transporter_doc_date" class="form-control" 
                                        value="<?= esc(!empty($document['waybill_date']) ? $document['waybill_date'] : date('Y-m-d')) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light p-3">
                            <button type="submit" id="btnGenerateEwb" class="btn btn-success btn-lg w-100 shadow-sm fw-bold" <?= $hasExistingEwb ? 'disabled' : '' ?>>
                                <i class="fas fa-paper-plane me-2"></i> Submit & Generate via API Sathi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?= $this->endSection() ?>

<?= $this->section('scripts') ?>
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
        document.getElementById('transporter_id').value = opt.getAttribute('data-zoho-id') || '';
        document.getElementById('transporter_gstin').value = opt.getAttribute('data-gst') || '';
    } else {
        document.getElementById('transporter_name').value = '';
        document.getElementById('transporter_id').value = '';
        document.getElementById('transporter_gstin').value = '';
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
                document.getElementById('disp_bill_party_name').innerText = doc.party_name || '-';
                document.getElementById('disp_bill_to_address_1').innerText = doc.bill_to_address_1 || '-';
                document.getElementById('disp_bill_to_address_2').innerText = doc.bill_to_address_2 || '';
                document.getElementById('disp_bill_to_place').innerText = doc.bill_to_place || '-';
                document.getElementById('disp_bill_to_state_code').innerText = doc.bill_to_state_code || '33';
                document.getElementById('disp_bill_to_pincode').innerText = doc.bill_to_pincode || '-';

                document.getElementById('bill_to_address_1').value = doc.bill_to_address_1 || '';
                document.getElementById('bill_to_address_2').value = doc.bill_to_address_2 || '';
                document.getElementById('bill_to_place').value = doc.bill_to_place || '';
                document.getElementById('bill_to_pincode').value = doc.bill_to_pincode || '';
                document.getElementById('bill_to_state_code').value = doc.bill_to_state_code || '33';

                // Populate Ship To
                document.getElementById('disp_ship_party_name').innerText = doc.party_name || '-';
                document.getElementById('disp_to_address_1').innerText = doc.to_address_1 || '-';
                document.getElementById('disp_to_address_2').innerText = doc.to_address_2 || '';
                document.getElementById('disp_to_place').innerText = doc.to_place || '-';
                document.getElementById('disp_to_state_code').innerText = doc.to_state_code || '33';
                document.getElementById('disp_to_pincode').innerText = doc.to_pincode || '-';

                document.getElementById('to_address_1').value = doc.to_address_1 || '';
                document.getElementById('to_address_2').value = doc.to_address_2 || '';
                document.getElementById('to_place').value = doc.to_place || '';
                document.getElementById('to_pincode').value = doc.to_pincode || '';
                document.getElementById('to_state_code').value = doc.to_state_code || '33';

                // Populate Vehicle Number if present in doc
                if (doc.vehicle_number) {
                    const $veh = $('#vehicle_picker');
                    if ($veh.find("option[value='" + doc.vehicle_number + "']").length) {
                        $veh.val(doc.vehicle_number).trigger('change');
                    } else {
                        $veh.val('OTHERS').trigger('change');
                        $('#manual_vehicle_number').val(doc.vehicle_number);
                        $('#vehicle_number').val(doc.vehicle_number);
                    }
                }

                const ewbBadge = document.getElementById('lbl_existing_ewb');
                const ewbAlert = document.getElementById('existingEwbAlert');
                const btnSubmit = document.getElementById('btnGenerateEwb');
                if (doc.existing_ewb) {
                    const viewUrl = doc.existing_ewb_url || (doc.existing_ewb_id ? ('<?= site_url('eway_bills/view') ?>/' + doc.existing_ewb_id) : ('<?= site_url('invoices/view') ?>/' + doc.id));
                    ewbBadge.className = 'badge bg-warning text-dark';
                    ewbBadge.innerHTML = '<a href="' + viewUrl + '" class="text-dark text-decoration-underline">Active EWB: #' + doc.existing_ewb + ' <i class="fas fa-external-link-alt ms-1"></i></a>';
                    document.getElementById('existingEwbNo').innerText = '#' + doc.existing_ewb;
                    document.getElementById('existingEwbStatus').innerText = (doc.existing_ewb_status || 'GENERATED').toUpperCase().replace(/_/g, ' ');
                    document.getElementById('existingEwbLink').href = viewUrl;
                    ewbAlert.classList.remove('d-none');
                    if (btnSubmit) btnSubmit.disabled = true;
                } else {
                    ewbBadge.className = 'badge bg-info text-white';
                    ewbBadge.innerText = 'No e-Way Bill Yet';
                    ewbAlert.classList.add('d-none');
                    if (btnSubmit) btnSubmit.disabled = false;
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

function onVehicleSelect(select) {
    const val = select.value;
    const groupManual = document.getElementById('group_manual_vehicle');
    const manualInput = document.getElementById('manual_vehicle_number');
    const hiddenVehInput = document.getElementById('vehicle_number');

    if (val === 'OTHERS') {
        groupManual.classList.remove('d-none');
        manualInput.focus();
        hiddenVehInput.value = manualInput.value.trim().toUpperCase().replace(/\s+/g, '');
    } else {
        groupManual.classList.add('d-none');
        hiddenVehInput.value = val ? val.trim().toUpperCase().replace(/\s+/g, '') : '';
    }

    const opt = select.options ? select.options[select.selectedIndex] : null;
    if (opt && opt.getAttribute('data-type')) {
        const vTypeSelect = document.querySelector('select[name="vehicle_type"]');
        if (vTypeSelect) {
            vTypeSelect.value = opt.getAttribute('data-type');
        }
    }
}

function syncVehicleNumber() {
    const manualInput = document.getElementById('manual_vehicle_number');
    const hiddenVehInput = document.getElementById('vehicle_number');
    hiddenVehInput.value = manualInput.value.trim().toUpperCase().replace(/\s+/g, '');
}

// Initial trigger on load
$(document).ready(function() {
    togglePartBRequirement();

    $('.select2-vehicle').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Select Registered Vehicle or Others --',
        allowClear: true,
        width: '100%'
    }).on('select2:select select2:clear', function() {
        onVehicleSelect(this);
    });

    // Run initial sync
    const vPicker = document.getElementById('vehicle_picker');
    if (vPicker) {
        onVehicleSelect(vPicker);
    }

    // Ensure vehicle_number is always synced & validated before form submit
    const ewbForm = document.getElementById('ewaybillForm');
    if (ewbForm) {
        ewbForm.addEventListener('submit', function(e) {
            if (vPicker) onVehicleSelect(vPicker);
            const isFull = document.getElementById('action_full')?.checked;
            const vehVal = (document.getElementById('vehicle_number')?.value || '').trim();
            if (isFull && !vehVal) {
                e.preventDefault();
                alert('Vehicle Number is required for Full e-Way Bill (Part-A + Part-B). Select "Part-A Slip Only" if vehicle is not assigned yet.');
            }
        });
    }
});
</script>
<?= $this->endSection() ?>

