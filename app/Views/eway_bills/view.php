<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$hasPartB = !empty($ewayBill['vehicle_number']) || ($ewayBill['status'] ?? '') === 'generated';
$statusBadge = match ($ewayBill['status'] ?? '') {
    'generated'        => 'success',
    'part_a_generated' => 'info',
    'cancelled'        => 'danger',
    'failed'           => 'warning',
    default            => 'secondary',
};
$decodedResp = !empty($ewayBill['response_payload']) ? json_decode($ewayBill['response_payload'], true) : null;
?>
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-0 fw-bold"><i class="fas fa-truck text-primary me-2"></i>e-Way Bill #<?= esc($ewayBill['ewaybill_number'] ?: 'Pending') ?></h1>
            <small class="text-muted">Document: <?= esc($ewayBill['document_number']) ?> (<?= esc(strtoupper(str_replace('_', ' ', $ewayBill['document_type']))) ?>)</small>
        </div>
        <div class="d-flex flex-wrap gap-2 d-print-none">
            <a href="<?= site_url('eway_bills') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <?php if (in_array($ewayBill['status'] ?? '', ['generated', 'part_a_generated']) && can('eway_bill.edit')): ?>
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalExtendView">
                    <i class="fas fa-clock me-1"></i> Extend Validity
                </button>
            <?php endif; ?>
            <?php if ((!empty($ewayBill['ewaybill_number']) || in_array($ewayBill['status'] ?? '', ['generated', 'part_a_generated'])) && can('eway_bill.view')): ?>
                <a href="<?= site_url('eway_bills/sync/' . $ewayBill['id']) ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-sync-alt me-1"></i> Sync Status
                </a>
            <?php endif; ?>
            <button type="button" onclick="window.print()" class="btn btn-sm btn-dark">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Summary & Part-B Status -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card card-primary h-100">
                <div class="card-header"><h6 class="card-title mb-0"><i class="fas fa-file-invoice me-1"></i> e-Way Bill Status</h6></div>
                <div class="card-body">
                    <div class="mb-2"><strong>EWB Number:</strong> <span class="fs-5 fw-bold text-primary"><?= esc($ewayBill['ewaybill_number'] ?: 'Not Generated') ?></span></div>
                    <div class="mb-2"><strong>Status:</strong> <span class="badge bg-<?= $statusBadge ?>"><?= esc(strtoupper(str_replace('_', ' ', $ewayBill['status']))) ?></span></div>
                    <div class="mb-2"><strong>Part-B Status:</strong>
                        <?php if ($hasPartB): ?>
                            <span class="badge bg-success">Part-B Present (Full EWB)</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Part-A Only (Part-B Pending)</span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-1"><strong>Generated At:</strong> <?= !empty($ewayBill['ewaybill_date']) ? date('d M Y, h:i A', strtotime($ewayBill['ewaybill_date'])) : '-' ?></div>
                    <div><strong>Valid Until:</strong>
                        <?php if (!empty($ewayBill['valid_until'])): ?>
                            <span class="<?= strtotime($ewayBill['valid_until']) < time() ? 'text-danger fw-bold' : 'text-success fw-bold' ?>">
                                <?= date('d M Y, h:i A', strtotime($ewayBill['valid_until'])) ?>
                            </span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-info h-100">
                <div class="card-header"><h6 class="card-title mb-0"><i class="fas fa-route me-1"></i> Part-A (Consignment &amp; Route)</h6></div>
                <div class="card-body small">
                    <div><strong>Supply / Sub-Supply:</strong> <?= esc($ewayBill['supply_type']) ?> / <?= esc($ewayBill['sub_supply_type']) ?></div>
                    <div><strong>From:</strong> <?= esc($ewayBill['from_place'] ?: '-') ?> (State <?= esc($ewayBill['from_state_code']) ?>, <?= esc($ewayBill['from_pincode']) ?>)</div>
                    <div><strong>To:</strong> <?= esc($ewayBill['to_place'] ?: '-') ?> (State <?= esc($ewayBill['to_state_code']) ?>, <?= esc($ewayBill['to_pincode']) ?>)</div>
                    <div><strong>Distance:</strong> <?= (int) $ewayBill['distance_km'] ?> Km</div>
                    <div class="mt-2"><strong>Invoice Value:</strong> <span class="fw-bold text-success">₹<?= number_format((float) $ewayBill['total_amount'], 2) ?></span></div>
                    <div class="text-muted">CGST: ₹<?= number_format((float) $ewayBill['cgst_amount'], 2) ?> | SGST: ₹<?= number_format((float) $ewayBill['sgst_amount'], 2) ?> | IGST: ₹<?= number_format((float) $ewayBill['igst_amount'], 2) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-secondary h-100">
                <div class="card-header"><h6 class="card-title mb-0"><i class="fas fa-truck-moving me-1"></i> Part-B (Conveyance)</h6></div>
                <div class="card-body small">
                    <div><strong>Transport Mode:</strong> <?= esc($ewayBill['transport_mode']) ?></div>
                    <div><strong>Vehicle Number:</strong> <span class="fw-bold"><?= esc($ewayBill['vehicle_number'] ?: 'Not Updated (Part-A Only)') ?></span></div>
                    <div><strong>Vehicle Type:</strong> <?= esc($ewayBill['vehicle_type'] ?: '-') ?></div>
                    <div><strong>Transporter:</strong> <?= esc($ewayBill['transporter_name'] ?: '-') ?> <?= !empty($ewayBill['transporter_registration_id']) ? '(' . esc($ewayBill['transporter_registration_id']) . ')' : '' ?></div>
                    <div><strong>LR / Doc #:</strong> <?= esc($ewayBill['transporter_doc_number'] ?: '-') ?> <?= !empty($ewayBill['transporter_doc_date']) ? '(' . esc($ewayBill['transporter_doc_date']) . ')' : '' ?></div>
                    <?php if (($ewayBill['status'] ?? '') === 'cancelled'): ?>
                        <div class="alert alert-danger mt-2 mb-0 py-1 px-2">
                            <strong>Cancelled:</strong> <?= esc($ewayBill['cancel_remarks']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Stored API Sathi Response -->
    <div class="card card-warning">
        <div class="card-header"><h6 class="card-title mb-0"><i class="fas fa-code me-1"></i> Stored API Sathi Response</h6></div>
        <div class="card-body">
            <?php if (!empty($ewayBill['error_message'])): ?>
                <div class="alert alert-danger py-2"><?= esc($ewayBill['error_message']) ?></div>
            <?php endif; ?>
            <?php if (!empty($ewayBill['response_payload'])): ?>
                <pre class="bg-light border rounded p-3 mb-0 small" style="max-height: 360px; overflow: auto;"><?= esc($decodedResp ? json_encode($decodedResp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $ewayBill['response_payload']) ?></pre>
            <?php else: ?>
                <div class="text-muted small">No response payload stored yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (in_array($ewayBill['status'] ?? '', ['generated', 'part_a_generated']) && can('eway_bill.edit')): ?>
<div class="modal fade" id="modalExtendView" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="<?= site_url('eway_bills/extend-validity/' . $ewayBill['id']) ?>">
            <?= csrf_field() ?>
            <div class="modal-content shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-clock me-2"></i> Extend e-Way Bill Validity</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Remaining Distance (Km) <span class="text-danger">*</span></label>
                            <input type="number" name="remaining_distance" class="form-control" min="1" value="<?= max(1, (int) ($ewayBill['distance_km'] ?? 10)) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Reason <span class="text-danger">*</span></label>
                            <select name="extn_reason_code" class="form-select" required>
                                <option value="1">1 - Natural Calamity</option>
                                <option value="2">2 - Law and Order Situation</option>
                                <option value="3">3 - Transhipment</option>
                                <option value="4">4 - Accident</option>
                                <option value="5">5 - Others</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Vehicle Number</label>
                            <input type="text" name="vehicle_number" class="form-control text-uppercase" value="<?= esc($ewayBill['vehicle_number'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">From Pincode</label>
                            <input type="text" name="from_pincode" class="form-control" value="<?= esc($ewayBill['from_pincode'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">State Code</label>
                            <input type="text" name="from_state_code" class="form-control" value="<?= esc($ewayBill['from_state_code'] ?? '33') ?>">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Consignment Status</label>
                            <select name="consignment_status" class="form-select">
                                <option value="T">In Transit (T)</option>
                                <option value="M">Movement (M)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transit Type</label>
                            <select name="transit_type" class="form-select">
                                <option value="R">Road (R)</option>
                                <option value="W">Warehouse (W)</option>
                                <option value="O">Others (O)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">Extension Remarks <span class="text-danger">*</span></label>
                        <textarea name="extn_remarks" class="form-control" rows="2" placeholder="Reason for extending validity..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-clock me-1"></i> Extend Validity</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
