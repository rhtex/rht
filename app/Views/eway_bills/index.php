<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark fw-bold">
                    <i class="fas fa-truck text-primary me-2"></i> e-Way Bill Management Console
                </h1>
                <p class="text-muted small mb-0">Generate Part A, Part B, Conveyance updates, Cancellations & Re-generation via Zoho Books API</p>
            </div>
            <div class="col-sm-6 text-end">
                <?php if (can('eway_bill.create')): ?>
                <a href="<?= site_url('eway_bills/create') ?>" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus-circle me-1"></i> Generate New e-Way Bill
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Stat KPI Widgets -->
        <div class="row mb-3">
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow-sm border-0">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-file-invoice"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Total e-Way Bills</span>
                        <span class="info-box-number fs-4"><?= number_format($counts['total']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow-sm border-0">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Generated (Active)</span>
                        <span class="info-box-number fs-4"><?= number_format($counts['generated']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow-sm border-0">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Part-A Generated</span>
                        <span class="info-box-number fs-4"><?= number_format($counts['part_a_generated']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow-sm border-0">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-ban"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Cancelled</span>
                        <span class="info-box-number fs-4"><?= number_format($counts['cancelled']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle me-1"></i> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle me-1"></i> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Table Card -->
        <div class="card card-outline card-primary shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <form method="get" action="<?= site_url('eway_bills') ?>" class="row g-2">
                            <div class="col-auto">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="generated" <?= $statusFilter === 'generated' ? 'selected' : '' ?>>Generated (Active)</option>
                                    <option value="part_a_generated" <?= $statusFilter === 'part_a_generated' ? 'selected' : '' ?>>Part-A Only</option>
                                    <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    <option value="failed" <?= $statusFilter === 'failed' ? 'selected' : '' ?>>Failed</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" placeholder="Search EWB / Doc / Vehicle..." value="<?= esc($search ?? '') ?>">
                                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                                    <?php if (!empty($search) || !empty($statusFilter)): ?>
                                        <a href="<?= site_url('eway_bills') ?>" class="btn btn-outline-danger" title="Clear Filters"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6 text-end text-muted small">
                        Showing <?= count($records) ?> latest e-Way Bill records
                    </div>
                </div>
            </div>

            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>e-Way Bill No. & Date</th>
                            <th>Document Ref</th>
                            <th>Vehicle & Transporter</th>
                            <th>From / To</th>
                            <th class="text-end">Value</th>
                            <th>Status</th>
                            <th>Valid Until</th>
                            <th class="text-center" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-shipping-fast fa-3x mb-3 text-secondary"></i>
                                    <p class="mb-2">No e-Way Bill records found.</p>
                                    <?php if (can('eway_bill.create')): ?>
                                    <a href="<?= site_url('eway_bills/create') ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-plus-circle me-1"></i> Generate Your First e-Way Bill
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $i => $row): ?>
                                <tr>
                                    <td class="text-muted"><?= $i + 1 ?></td>
                                    <td>
                                        <span class="fw-bold font-monospace text-primary fs-6">
                                            <?= esc($row['ewaybill_number'] ?: 'PENDING / PART-A') ?>
                                        </span>
                                        <div class="text-muted small">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?= $row['ewaybill_date'] ? date('d M Y, h:i A', strtotime($row['ewaybill_date'])) : '-' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary mb-1"><?= strtoupper(str_replace('_', ' ', $row['document_type'])) ?></span>
                                        <div class="fw-semibold">
                                            <?php if ($row['document_type'] === 'invoice'): ?>
                                                <a href="<?= site_url('invoices/view/' . $row['document_id']) ?>" target="_blank" class="text-decoration-none">
                                                    <?= esc($row['document_number']) ?> <i class="fas fa-external-link-alt fa-xs"></i>
                                                </a>
                                            <?php else: ?>
                                                <?= esc($row['document_number']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['vehicle_number'])): ?>
                                            <span class="badge bg-dark font-monospace"><i class="fas fa-truck me-1"></i> <?= esc($row['vehicle_number']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle me-1"></i> No Vehicle (Part-A)</span>
                                        <?php endif; ?>
                                        <?php if (!empty($row['transporter_name']) || !empty($row['transporter_id'])): ?>
                                            <div class="text-muted small text-truncate" style="max-width: 180px;" title="<?= esc($row['transporter_name'] ?? $row['transporter_id']) ?>">
                                                <?= esc($row['transporter_name'] ?? $row['transporter_id']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small">
                                        <div><strong>From:</strong> <?= esc($row['from_place'] ?? 'TN') ?></div>
                                        <div><strong>To:</strong> <?= esc($row['to_place'] ?? '-') ?> (<?= esc($row['distance_km']) ?> km)</div>
                                    </td>
                                    <td class="text-end fw-bold">
                                        ₹<?= number_format((float) $row['total_amount'], 2) ?>
                                    </td>
                                    <td>
                                        <?php
                                            $statusBadge = match($row['status']) {
                                                'generated'        => 'bg-success',
                                                'part_a_generated' => 'bg-warning text-dark',
                                                'cancelled'        => 'bg-danger',
                                                'expired'          => 'bg-secondary',
                                                'failed'           => 'bg-danger',
                                                default            => 'bg-info',
                                            };
                                        ?>
                                        <span class="badge <?= $statusBadge ?> px-2 py-1">
                                            <?= strtoupper(str_replace('_', ' ', $row['status'])) ?>
                                        </span>
                                        <?php if (!empty($row['error_message'])): ?>
                                            <div class="text-danger small mt-1" style="max-width: 180px;" title="<?= esc($row['error_message']) ?>">
                                                <i class="fas fa-info-circle"></i> <?= esc(substr($row['error_message'], 0, 40)) ?>...
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small">
                                        <?php if (!empty($row['valid_until'])): ?>
                                            <span class="<?= strtotime($row['valid_until']) < time() ? 'text-danger fw-bold' : 'text-success' ?>">
                                                <?= date('d M Y, H:i', strtotime($row['valid_until'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <!-- Update Part B / Vehicle -->
                                            <?php if (in_array($row['status'], ['generated', 'part_a_generated']) && can('eway_bill.edit')): ?>
                                                <button type="button" class="btn btn-outline-primary" title="Update Part-B / Vehicle" 
                                                    onclick="openPartBModal(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)">
                                                    <i class="fas fa-truck-moving"></i>
                                                </button>
                                            <?php endif; ?>

                                            <!-- Cancel e-Way Bill -->
                                            <?php if (in_array($row['status'], ['generated', 'part_a_generated']) && can('eway_bill.delete')): ?>
                                                <button type="button" class="btn btn-outline-danger" title="Cancel e-Way Bill"
                                                    onclick="openCancelModal(<?= $row['id'] ?>, '<?= esc($row['ewaybill_number'] ?? $row['document_number']) ?>')">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            <?php endif; ?>

                                            <!-- Regenerate e-Way Bill -->
                                            <?php if (in_array($row['status'], ['cancelled', 'failed']) && can('eway_bill.create')): ?>
                                                <button type="button" class="btn btn-outline-warning" title="Regenerate e-Way Bill"
                                                    onclick="openRegenerateModal(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            <?php endif; ?>

                                            <!-- Sync status with Zoho Books -->
                                            <?php if (!empty($row['zoho_ewaybill_id']) && can('eway_bill.view')): ?>
                                                <a href="<?= site_url('eway_bills/sync/' . $row['id']) ?>" class="btn btn-outline-secondary" title="Sync Status from Zoho Books">
                                                    <i class="fas fa-sync-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Update Part-B / Vehicle Details -->
<div class="modal fade" id="modalPartB" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="formPartB" action="">
            <?= csrf_field() ?>
            <div class="modal-content shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-truck-moving me-2"></i> Update Part-B / Conveyance Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Update conveyance details when goods shift to another vehicle, transshipment occurs, or when moving from Part-A to Full EWB.</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Vehicle Number <span class="text-danger">*</span></label>
                        <input type="text" name="vehicle_number" id="partb_vehicle_number" class="form-control text-uppercase" placeholder="e.g. TN01AB1234" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Vehicle Type</label>
                            <select name="vehicle_type" id="partb_vehicle_type" class="form-select">
                                <option value="Regular">Regular</option>
                                <option value="Over Dimensional Cargo">Over Dimensional Cargo</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transit Distance (Km)</label>
                            <input type="number" name="distance_km" id="partb_distance_km" class="form-control" value="0">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">From Place</label>
                            <input type="text" name="from_place" id="partb_from_place" class="form-control" placeholder="City / Town">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">From State Code</label>
                            <input type="text" name="from_state_code" id="partb_from_state_code" class="form-control" placeholder="e.g. 33">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason for Updating Conveyance</label>
                        <select name="reason" class="form-select">
                            <option value="Transshipment">Transshipment</option>
                            <option value="Vehicle Breakdown">Vehicle Breakdown</option>
                            <option value="First Transit">First Transit (Part-A Completion)</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Transporter Doc / LR No.</label>
                            <input type="text" name="transporter_doc_number" id="partb_transporter_doc_number" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transporter Doc Date</label>
                            <input type="date" name="transporter_doc_date" id="partb_transporter_doc_date" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Conveyance in Zoho</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Cancel e-Way Bill -->
<div class="modal fade" id="modalCancel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="formCancel" action="">
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

                    <p>Are you sure you want to cancel e-Way Bill <strong id="cancel_ewb_display"></strong>?</p>

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

<!-- Modal: Regenerate e-Way Bill -->
<div class="modal fade" id="modalRegenerate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="formRegenerate" action="">
            <?= csrf_field() ?>
            <div class="modal-content shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-redo me-2"></i> Regenerate e-Way Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Generate a brand new e-Way Bill for Document <strong id="regen_doc_display"></strong> after cancellation or failure.</p>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Vehicle Number <span class="text-danger">*</span></label>
                        <input type="text" name="vehicle_number" id="regen_vehicle_number" class="form-control text-uppercase" placeholder="e.g. TN01AB1234" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Distance (Km)</label>
                            <input type="number" name="distance_km" id="regen_distance_km" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transport Mode</label>
                            <select name="transport_mode" class="form-select">
                                <option value="1">1 - Road</option>
                                <option value="2">2 - Rail</option>
                                <option value="3">3 - Air</option>
                                <option value="4">4 - Ship</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Transporter ID (GSTIN)</label>
                        <input type="text" name="transporter_id" id="regen_transporter_id" class="form-control" placeholder="15-digit GSTIN / TRANSIN">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-check-circle me-1"></i> Regenerate Now</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openPartBModal(row) {
    document.getElementById('formPartB').action = '<?= site_url('eway_bills/update-part-b') ?>/' + row.id;
    document.getElementById('partb_vehicle_number').value = row.vehicle_number || '';
    document.getElementById('partb_vehicle_type').value = row.vehicle_type || 'Regular';
    document.getElementById('partb_distance_km').value = row.distance_km || '0';
    document.getElementById('partb_from_place').value = row.from_place || '';
    document.getElementById('partb_from_state_code').value = row.from_state_code || '';
    document.getElementById('partb_transporter_doc_number').value = row.transporter_doc_number || '';
    document.getElementById('partb_transporter_doc_date').value = row.transporter_doc_date || '';
    new bootstrap.Modal(document.getElementById('modalPartB')).show();
}

function openCancelModal(id, ewbNumber) {
    document.getElementById('formCancel').action = '<?= site_url('eway_bills/cancel') ?>/' + id;
    document.getElementById('cancel_ewb_display').innerText = '#' + ewbNumber;
    new bootstrap.Modal(document.getElementById('modalCancel')).show();
}

function openRegenerateModal(row) {
    document.getElementById('formRegenerate').action = '<?= site_url('eway_bills/regenerate') ?>/' + row.id;
    document.getElementById('regen_doc_display').innerText = '#' + row.document_number;
    document.getElementById('regen_vehicle_number').value = row.vehicle_number || '';
    document.getElementById('regen_distance_km').value = row.distance_km || '0';
    document.getElementById('regen_transporter_id').value = row.transporter_id || '';
    new bootstrap.Modal(document.getElementById('modalRegenerate')).show();
}
</script>
<?= $this->endSection() ?>

