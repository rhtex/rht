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
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= site_url('eway_bills') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <?php if (!empty($ewayBill['ewaybill_number']) && can('eway_bill.view')): ?>
                <a href="<?= site_url('eway_bills/sync/' . $ewayBill['id']) ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-sync-alt me-1"></i> Sync Status
                </a>
            <?php endif; ?>
            <button type="button" onclick="window.print()" class="btn btn-sm btn-dark d-print-none">
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
<?= $this->endSection() ?>
