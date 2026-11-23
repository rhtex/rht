<div class="card card-outline card-primary shadow-sm h-100">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-primary text-uppercase me-2">Vendor Credit</span>
            <h4 class="d-inline mb-0 fw-bold"><?= esc($credit['reference_no']) ?></h4>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="<?= site_url('vendor_credits/print/' . $credit['id']) ?>?pdf=1" target="_blank" class="btn btn-sm btn-info text-white" title="Print Debit Note">
                <i class="fas fa-print me-1"></i> Print
            </a>
            <span class="badge bg-<?= in_array($credit['status'], ['Closed', 'Used']) ? 'success' : ($credit['status'] === 'Partial' ? 'warning' : 'primary') ?> fs-6">
                <?= esc($credit['status']) ?>
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <small class="text-muted d-block text-uppercase fw-bold">Vendor</small>
                <div class="fs-5 fw-bold text-dark">
                    <?php if (!empty($credit['vendor_id'])): ?>
                        <a href="<?= site_url('vendors/view/' . $credit['vendor_id']) ?>" class="text-decoration-none text-primary" title="View Vendor Details">
                            <?= esc($credit['vendor_name'] ?? 'N/A') ?> <i class="fas fa-external-link-alt small ms-1" style="font-size: 0.72rem;"></i>
                        </a>
                    <?php else: ?>
                        <?= esc($credit['vendor_name'] ?? 'N/A') ?>
                    <?php endif; ?>
                </div>
                <?php if (!empty($credit['vendor_phone'])): ?>
                    <div class="small text-muted"><i class="fas fa-phone me-1"></i><?= esc($credit['vendor_phone']) ?></div>
                <?php endif; ?>
                <?php if (!empty($credit['vendor_gstin'])): ?>
                    <div class="small text-muted"><i class="fas fa-id-card me-1"></i>GSTIN: <?= esc($credit['vendor_gstin']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-sm-6 text-sm-end">
                <small class="text-muted d-block text-uppercase fw-bold">Credit Date</small>
                <div class="fs-6 fw-semibold text-dark">
                    <?= !empty($credit['credit_note_date']) ? date('d M Y', strtotime($credit['credit_note_date'])) : 'N/A' ?>
                </div>
                <?php if (!empty($credit['bill_number'])): ?>
                    <div class="mt-2">
                        <small class="text-muted d-block text-uppercase fw-bold">Applied / Linked Bill</small>
                        <a href="<?= site_url('bills') ?>?bill_number=<?= urlencode($credit['bill_number']) ?>" class="badge bg-light text-primary border text-decoration-none">
                            <i class="fas fa-file-invoice-dollar me-1"></i>Bill #<?= esc($credit['bill_number']) ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card bg-light border-0 mb-4">
            <div class="card-body p-3">
                <div class="row text-center">
                    <div class="col-4 border-end">
                        <small class="text-muted text-uppercase fw-bold d-block">Credit Amount</small>
                        <span class="fs-4 fw-bold text-primary">₹<?= number_format((float) ($credit['amount'] ?? 0), 2) ?></span>
                    </div>
                    <div class="col-4 border-end">
                        <small class="text-muted text-uppercase fw-bold d-block">Used Amount</small>
                        <span class="fs-4 fw-bold text-success">₹<?= number_format((float) ($credit['used_amount'] ?? 0), 2) ?></span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted text-uppercase fw-bold d-block">Remaining Balance</small>
                        <span class="fs-4 fw-bold <?= (float) ($credit['balance'] ?? 0) > 0.01 ? 'text-danger' : 'text-muted' ?>">
                            ₹<?= number_format((float) ($credit['balance'] ?? 0), 2) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($credit['notes'])): ?>
            <div class="mb-3">
                <small class="text-muted d-block text-uppercase fw-bold mb-1">Notes / Description</small>
                <div class="p-3 bg-white border rounded small">
                    <?= nl2br(esc($credit['notes'])) ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="text-muted small border-top pt-3 mt-4">
            <div><i class="fas fa-clock me-1"></i>Recorded: <?= date('d M Y, h:i A', strtotime($credit['created_at'])) ?></div>
        </div>
    </div>
</div>
