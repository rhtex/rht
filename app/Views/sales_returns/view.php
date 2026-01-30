<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Return Details - <?= esc($return['return_number']) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('sales_returns') ?>" class="btn btn-secondary"><i class="fas fa-list"></i> Back to List</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-9">
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header bg-white">
                <h3 class="card-title fw-bold">Return Information</h3>
                <div class="card-tools">
                    <span class="badge bg-<?= $return['status'] == 'Open' ? 'info' : ($return['status'] == 'Void' ? 'danger' : 'success') ?> fs-6">
                        <?= esc($return['status']) ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <label class="text-muted small text-uppercase fw-bold mb-1">Customer</label>
                        <p class="fs-5 fw-bold mb-1"><?= esc($return['customer_name']) ?></p>
                        <?php if($return['invoice_number']): ?>
                            <p class="text-muted mb-0">Against Invoice: <strong><?= esc($return['invoice_number']) ?></strong></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <label class="text-muted small text-uppercase fw-bold mb-1">Return Date</label>
                        <p class="fs-5 mb-0"><?= date('d F, Y', strtotime($return['return_date'])) ?></p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Description</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Rate</th>
                                <th class="text-center">Tax %</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $item): ?>
                            <tr>
                                <td><?= esc($item['description']) ?></td>
                                <td class="text-center"><?= number_format($item['quantity'], 2) ?></td>
                                <td class="text-end">₹<?= number_format($item['rate'], 2) ?></td>
                                <td class="text-center"><?= number_format($item['tax_percentage'], 2) ?>%</td>
                                <td class="text-end fw-bold">₹<?= number_format($item['amount'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Subtotal</th>
                                <th class="text-end">₹<?= number_format($return['subtotal'], 2) ?></th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Tax Amount</th>
                                <th class="text-end">₹<?= number_format($return['tax_amount'], 2) ?></th>
                            </tr>
                            <tr class="table-primary">
                                <th colspan="4" class="text-end fs-5">Total Amount</th>
                                <th class="text-end fs-5">₹<?= number_format($return['total_amount'], 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if($return['reason']): ?>
                    <div class="mt-4 p-3 bg-light rounded">
                        <label class="text-muted small text-uppercase fw-bold mb-1">Reason for Return</label>
                        <p class="mb-0 italic"><?= nl2br(esc($return['reason'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <!-- Zoho Status Card -->
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header">
                <h3 class="card-title fw-bold"><i class="fas fa-sync me-2"></i> Zoho Status</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small text-uppercase d-block">Sync Status</label>
                    <?php if($return['zoho_credit_note_id']): ?>
                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Synced</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> Not Synced</span>
                    <?php endif; ?>
                </div>
                
                <?php if($return['zoho_credit_note_id']): ?>
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase d-block">Zoho ID</label>
                        <code><?= esc($return['zoho_credit_note_id']) ?></code>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small text-uppercase d-block">Synced At</label>
                        <p class="mb-0 small text-muted"><?= date('d M Y, h:i A', strtotime($return['zoho_sync_at'])) ?></p>
                    </div>
                <?php else: ?>
                    <button type="button" class="btn btn-sm btn-primary w-100 mt-2">
                        <i class="fas fa-cloud-upload-alt me-1"></i> Push to Zoho
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
