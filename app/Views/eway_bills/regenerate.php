<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-2">
            <div class="col-sm-7">
                <h1 class="m-0 text-dark fw-bold">
                    <i class="fas fa-redo text-warning me-2"></i> Regenerate e-Way Bill
                </h1>
                <p class="text-muted small mb-0">
                    Re-issuing e-Way Bill for <strong><?= esc($ewayBill['document_number']) ?></strong> 
                    (Original Status: <span class="badge bg-secondary"><?= strtoupper(esc($ewayBill['status'])) ?></span>)
                </p>
            </div>
            <div class="col-sm-5 text-end">
                <div class="d-inline-flex gap-2">
                    <?php if (in_array($ewayBill['status'], ['generated', 'part_a_generated']) && can('eway_bill.delete')): ?>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalCancelDirect">
                            <i class="fas fa-ban me-1"></i> Cancel this e-Way Bill
                        </button>
                    <?php endif; ?>
                    <a href="<?= site_url('eway_bills') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Console
                    </a>
                </div>
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

        <?php if (!empty($ewayBill['error_message'])): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <div class="fw-bold"><i class="fas fa-info-circle me-1"></i> Last Error Encountered:</div>
                <div class="small mt-1"><?= esc($ewayBill['error_message']) ?></div>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('eway_bills/regenerate/' . $ewayBill['id']) ?>" id="regenerateForm">
            <?= csrf_field() ?>
            <input type="hidden" name="document_id" id="document_id" value="<?= esc($docId ?: $ewayBill['document_id']) ?>">

            <div class="row">
                <!-- Left Column: Document & Supply Details -->
                <div class="col-lg-7">
                    <!-- 1. Document Details Card -->
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
                                    <select name="document_type" id="document_type" class="form-select">
                                        <option value="invoice" <?= ($docType === 'invoice') ? 'selected' : '' ?>>Tax Invoice (Outward)</option>
                                        <option value="credit_note" <?= ($docType === 'credit_note') ? 'selected' : '' ?>>Credit Note (Sales Return)</option>
                                        <option value="purchase_bill" <?= ($docType === 'purchase_bill') ? 'selected' : '' ?>>Purchase Bill (Inward)</option>
                                        <option value="delivery_challan" <?= ($docType === 'delivery_challan') ? 'selected' : '' ?>>Delivery Challan</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label fw-bold">Document Number</label>
                                    <input type="text" class="form-control bg-light" readonly
                                        value="<?= esc($ewayBill['document_number'] ?? (!empty($document) ? ($document['invoice_number'] ?? ($document['return_number'] ?? '')) : '')) ?>">
                                </div>
                            </div>

                            <!-- Document Info Preview Box -->
                            <div class="p-3 bg-light rounded border mb-3" id="doc_info_box">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="text-muted small d-block">Document Number & Date</label>
                                        <span class="fw-bold" id="lbl_doc_no">#<?= esc($ewayBill['document_number']) ?></span>
                                        <span class="text-muted ms-2" id="lbl_doc_date">
                                            (<?= date('d M Y', strtotime($ewayBill['document_date'])) ?>)
                                        </span>
                                        <input type="hidden" name="document_number" id="hidden_doc_number" value="<?= esc($ewayBill['document_number']) ?>">
                                        <input type="hidden" name="document_date" id="hidden_doc_date" value="<?= esc($ewayBill['document_date']) ?>">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="text-muted small d-block">Customer / Party Name</label>
                                        <span class="fw-bold" id="lbl_party_name"><?= esc(!empty($party) ? ($party['name'] ?? ($party['customer_name'] ?? 'Not Selected')) : 'Not Selected') ?></span>
                                        <div class="small text-muted" id="lbl_party_gstin">GSTIN: <?= esc(!empty($party['gstin']) ? $party['gstin'] : 'Unregistered') ?></div>
                                    </div>
                                </div>
                                <div class="row mt-2 pt-2 border-top">
                                    <div class="col-md-6">
                                        <label class="text-muted small d-block">Total Invoice Amount (Taxable + Tax)</label>
                                        <span class="fs-5 fw-bold text-success" id="lbl_total_amount">₹<?= number_format((float) $ewayBill['total_amount'], 2) ?></span>
                                        <input type="hidden" name="total_amount" id="hidden_total_amount" value="<?= esc($ewayBill['total_amount']) ?>">
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <span class="badge bg-secondary" id="lbl_existing_ewb">
                                            <?= !empty($ewayBill['ewaybill_number']) ? 'Original EWB: #' . esc($ewayBill['ewaybill_number']) : 'No e-Way Bill Issued Yet' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Supply Type</label>
                                    <select name="supply_type" class="form-select">
                                        <option value="Outward" <?= (($ewayBill['supply_type'] ?? 'Outward') === 'Outward') ? 'selected' : '' ?>>Outward (Sales / Outflow)</option>
                                        <option value="Inward" <?= (($ewayBill['supply_type'] ?? '') === 'Inward') ? 'selected' : '' ?>>Inward (Purchase / Inflow)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sub-Supply Type</label>
                                    <select name="sub_supply_type" class="form-select">
                                        <option value="Supply" <?= (($ewayBill['sub_supply_type'] ?? 'Supply') === 'Supply') ? 'selected' : '' ?>>Supply</option>
                                        <option value="Export" <?= (($ewayBill['sub_supply_type'] ?? '') === 'Export') ? 'selected' : '' ?>>Export</option>
                                        <option value="Job Work" <?= (($ewayBill['sub_supply_type'] ?? '') === 'Job Work') ? 'selected' : '' ?>>Job Work</option>
                                        <option value="SKD/CKD" <?= (($ewayBill['sub_supply_type'] ?? '') === 'SKD/CKD') ? 'selected' : '' ?>>SKD / CKD</option>
                                        <option value="Recipient Not Known" <?= (($ewayBill['sub_supply_type'] ?? '') === 'Recipient Not Known') ? 'selected' : '' ?>>Recipient Not Known</option>
                                        <option value="For Own Use" <?= (($ewayBill['sub_supply_type'] ?? '') === 'For Own Use') ? 'selected' : '' ?>>For Own Use</option>
                                        <option value="Others" <?= (($ewayBill['sub_supply_type'] ?? '') === 'Others') ? 'selected' : '' ?>>Others</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Address Details Card -->
                    <div class="card card-outline card-secondary shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title fw-bold mb-0 text-secondary">
                                <i class="fas fa-map-marked-alt me-2"></i> 2. Address Details (Dispatch / Billing / Shipping)
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Row 1: Dispatch From & Bill From -->
                            <div class="row g-3 pb-3 mb-3 border-bottom">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="fw-bold text-primary mb-0"><i class="fas fa-warehouse me-1"></i> Dispatch From</h6>
                                        <span class="badge bg-primary-subtle text-primary border">Place of Dispatch</span>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Address Line 1</label>
                                        <input type="text" name="from_address_1" class="form-control form-control-sm" 
                                            value="<?= esc($ewayBill['from_address_1'] ?? 'C.P Kannaiah Street, Opp: Gopinath Engineering Works') ?>">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Address Line 2</label>
                                        <input type="text" name="from_address_2" class="form-control form-control-sm" 
                                            value="<?= esc($ewayBill['from_address_2'] ?? 'Rasipuram (TK), Namakkal') ?>">
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <label class="form-label small">Dispatch City <span class="text-danger">*</span></label>
                                            <input type="text" name="from_place" class="form-control form-control-sm" 
                                                value="<?= esc($ewayBill['from_place'] ?? 'Rasipuram') ?>" required>
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">State Code <span class="text-danger">*</span></label>
                                            <input type="text" name="from_state_code" class="form-control form-control-sm" 
                                                value="<?= esc($ewayBill['from_state_code'] ?? '33') ?>" required>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">Pincode <span class="text-danger">*</span></label>
                                            <input type="text" name="from_pincode" class="form-control form-control-sm" 
                                                value="<?= esc($ewayBill['from_pincode'] ?? '637408') ?>" required>
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
                                        <input type="hidden" name="bill_from_place" value="<?= esc($ewayBill['bill_from_place'] ?? 'Rasipuram') ?>">
                                        <input type="hidden" name="bill_from_state_code" value="<?= esc($ewayBill['bill_from_state_code'] ?? '33') ?>">
                                        <input type="hidden" name="bill_from_pincode" value="<?= esc($ewayBill['bill_from_pincode'] ?? '637408') ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: Bill To & Ship To -->
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
                                                value="<?= esc(!empty($ewayBill['bill_to_place']) ? $ewayBill['bill_to_place'] : ($billAddress['city'] ?? '')) ?>" required>
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">State Code <span class="text-danger">*</span></label>
                                            <input type="text" name="bill_to_state_code" id="bill_to_state_code" class="form-control form-control-sm" 
                                                value="<?= esc(!empty($ewayBill['bill_to_state_code']) ? $ewayBill['bill_to_state_code'] : ($billAddress['gst_state_code'] ?? '33')) ?>" required>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">Pincode <span class="text-danger">*</span></label>
                                            <input type="text" name="bill_to_pincode" id="bill_to_pincode" class="form-control form-control-sm" 
                                                value="<?= esc(!empty($ewayBill['bill_to_pincode']) ? $ewayBill['bill_to_pincode'] : ($billAddress['pincode'] ?? '')) ?>" required>
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
                                            value="<?= esc(!empty($ewayBill['to_address_1']) ? $ewayBill['to_address_1'] : ($shipAddress['address_line1'] ?? '')) ?>">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Shipping Address Line 2</label>
                                        <input type="text" name="to_address_2" id="to_address_2" class="form-control form-control-sm" 
                                            value="<?= esc(!empty($ewayBill['to_address_2']) ? $ewayBill['to_address_2'] : ($shipAddress['address_line2'] ?? '')) ?>">
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-5">
                                            <label class="form-label small">Delivery City <span class="text-danger">*</span></label>
                                            <input type="text" name="to_place" id="to_place" class="form-control form-control-sm" 
                                                value="<?= esc(!empty($ewayBill['to_place']) ? $ewayBill['to_place'] : ($shipAddress['city'] ?? '')) ?>" required>
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">State Code <span class="text-danger">*</span></label>
                                            <input type="text" name="to_state_code" id="to_state_code" class="form-control form-control-sm" 
                                                value="<?= esc(!empty($ewayBill['to_state_code']) ? $ewayBill['to_state_code'] : ($shipAddress['gst_state_code'] ?? '33')) ?>" required>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">Pincode <span class="text-danger">*</span></label>
                                            <input type="text" name="to_pincode" id="to_pincode" class="form-control form-control-sm" 
                                                value="<?= esc(!empty($ewayBill['to_pincode']) ? $ewayBill['to_pincode'] : ($shipAddress['pincode'] ?? '')) ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Transportation & Generation Scope -->
                <div class="col-lg-5">
                    <!-- Scope Selection -->
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
                                        <?php 
                                            $isSelected = (!empty($ewayBill['transporter_name']) && strtolower($t['transport_name']) === strtolower($ewayBill['transporter_name']))
                                                || (!empty($ewayBill['transporter_id']) && ($t['zoho_transporter_id'] === $ewayBill['transporter_id'] || $t['branch_gst_number'] === $ewayBill['transporter_id']));
                                        ?>
                                        <option value="<?= esc($t['id']) ?>" 
                                            data-name="<?= esc($t['transport_name']) ?>" 
                                            data-gst="<?= esc($t['branch_gst_number']) ?>"
                                            data-zoho-id="<?= esc($t['zoho_transporter_id'] ?? '') ?>"
                                            <?= $isSelected ? 'selected' : '' ?>>
                                            <?= esc($t['transport_name']) ?> <?= !empty($t['zoho_transporter_id']) ? '(Zoho ID: ' . esc($t['zoho_transporter_id']) . ')' : (!empty($t['branch_gst_number']) ? '(GST: ' . esc($t['branch_gst_number']) . ')' : '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Transport Mode <span class="text-danger">*</span></label>
                                    <select name="transport_mode" class="form-select" required>
                                        <option value="road" <?= (in_array(($ewayBill['transport_mode'] ?? 'road'), [1, '1', 'road'])) ? 'selected' : '' ?>>1 - Road</option>
                                        <option value="rail" <?= (in_array(($ewayBill['transport_mode'] ?? ''), [2, '2', 'rail'])) ? 'selected' : '' ?>>2 - Rail</option>
                                        <option value="air" <?= (in_array(($ewayBill['transport_mode'] ?? ''), [3, '3', 'air'])) ? 'selected' : '' ?>>3 - Air</option>
                                        <option value="ship" <?= (in_array(($ewayBill['transport_mode'] ?? ''), [4, '4', 'ship'])) ? 'selected' : '' ?>>4 - Ship</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Approx Distance (Km)</label>
                                    <input type="number" name="distance_km" id="distance_km" class="form-control" 
                                        value="<?= esc($ewayBill['distance_km'] ?? '0') ?>" min="0">
                                    <div class="form-text text-muted small">Keep 0 for Zoho / NIC auto-calculation by pincode.</div>
                                </div>
                            </div>

                            <div class="mb-3" id="group_vehicle_number">
                                <label class="form-label fw-bold">Vehicle Number <span class="text-danger" id="req_star_vehicle">*</span></label>
                                <input type="text" name="vehicle_number" id="vehicle_number" class="form-control text-uppercase" 
                                    placeholder="e.g. TN28AB1234" value="<?= esc($ewayBill['vehicle_number'] ?? (!empty($document['transport_name']) ? $document['transport_name'] : '')) ?>">
                                <div class="form-text small">Enter vehicle plate without spaces or hyphens.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vehicle Type</label>
                                <select name="vehicle_type" class="form-select">
                                    <option value="Regular" <?= (strtolower($ewayBill['vehicle_type'] ?? '') === 'regular') ? 'selected' : '' ?>>Regular</option>
                                    <option value="Over Dimensional Cargo" <?= (stripos($ewayBill['vehicle_type'] ?? '', 'over') !== false) ? 'selected' : '' ?>>Over Dimensional Cargo</option>
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Zoho Transporter ID</label>
                                    <input type="text" name="transporter_id" id="transporter_id" class="form-control bg-light" 
                                        placeholder="Auto-populated from Zoho" 
                                        value="<?= esc($ewayBill['transporter_id'] ?? '') ?>" readonly>
                                    <div class="form-text small text-muted">Zoho Books internal ID (uneditable).</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Transporter GSTIN / TRANSIN</label>
                                    <input type="text" name="transporter_gstin" id="transporter_gstin" class="form-control text-uppercase" 
                                        placeholder="15-digit GSTIN / TRANSIN"
                                        value="<?= esc($ewayBill['transporter_registration_id'] ?? '') ?>">
                                    <div class="form-text small text-muted">Transporter's 15-digit GSTIN number.</div>
                                </div>
                                <input type="hidden" name="transporter_name" id="transporter_name" value="<?= esc($ewayBill['transporter_name'] ?? '') ?>">
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label">LR / Waybill Number</label>
                                    <input type="text" name="transporter_doc_number" class="form-control" 
                                        value="<?= esc($ewayBill['transporter_doc_number'] ?? ($document['waybill_number'] ?? '')) ?>" placeholder="LR Number">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">LR Date</label>
                                    <input type="date" name="transporter_doc_date" class="form-control" 
                                        value="<?= esc($ewayBill['transporter_doc_date'] ?? ($document['waybill_date'] ?? date('Y-m-d'))) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light p-3">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning btn-lg shadow-sm fw-bold">
                                    <i class="fas fa-redo me-2"></i> Submit & Regenerate e-Way Bill
                                </button>
                                <?php if (in_array($ewayBill['status'], ['generated', 'part_a_generated']) && can('eway_bill.delete')): ?>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalCancelDirect">
                                        <i class="fas fa-ban me-1"></i> Cancel this e-Way Bill Instead
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Cancel e-Way Bill -->
<?php if (can('eway_bill.delete')): ?>
<div class="modal fade" id="modalCancelDirect" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="<?= site_url('eway_bills/cancel/' . $ewayBill['id']) ?>">
            <?= csrf_field() ?>
            <div class="modal-content shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i> Cancel e-Way Bill</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        <i class="fas fa-exclamation-triangle me-1"></i> <strong>Note:</strong> Under GST regulations, an e-Way Bill can only be cancelled within <strong>24 hours</strong> of its generation, provided goods have not been intercepted in transit.
                    </div>

                    <p>Are you sure you want to cancel e-Way Bill <strong>#<?= esc($ewayBill['ewaybill_number'] ?? $ewayBill['document_number']) ?></strong>?</p>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason for Cancellation <span class="text-danger">*</span></label>
                        <select name="cancel_reason_code" class="form-select" required>
                            <option value="1">1 - Duplicate Generation</option>
                            <option value="2">2 - Order Cancelled</option>
                            <option value="3">3 - Data Entry Mistake</option>
                            <option value="4">4 - Others</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Cancellation Remarks <span class="text-danger">*</span></label>
                        <textarea name="cancel_remarks" class="form-control" rows="3" placeholder="Provide detailed reason for cancellation..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-ban me-1"></i> Confirm Cancellation</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

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

document.addEventListener('DOMContentLoaded', function() {
    togglePartBRequirement();
});
</script>
<?= $this->endSection() ?>
