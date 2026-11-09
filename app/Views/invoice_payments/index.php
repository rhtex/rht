<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Customer Receipts<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Customer Receipts & Collections</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('invoice_payments') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('invoices') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-file-invoice me-1"></i> View Invoices
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('invoice_payments') ?>" class="bt-stat bt-g-blue text-decoration-none">
            <i class="fas fa-receipt bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($payments)) ?></div>
            <div class="bt-stat-label">Total Receipts</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-green" style="cursor:default;">
            <i class="fas fa-hand-holding-usd bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['total_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Total Cash / Bank Received</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-amber" style="cursor:default;">
            <i class="fas fa-percent bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['total_deductions'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Discounts & Deductions</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-check-double bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['total_settlement'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Total Credit Cleared</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Customer Receipts</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('invoice_payments') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label for="customer_id" class="form-label">Customer</label>
                <select name="customer_id" id="customer_id" class="form-select select2">
                    <option value="">All Customers</option>
                    <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (($filters['customer_id'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="payment_mode" class="form-label">Payment Mode</label>
                <select name="payment_mode" id="payment_mode" class="form-select">
                    <option value="">All Modes</option>
                    <option value="Cash" <?= (($filters['payment_mode'] ?? '') == 'Cash') ? 'selected' : '' ?>>Cash</option>
                    <option value="Bank Transfer" <?= (($filters['payment_mode'] ?? '') == 'Bank Transfer') ? 'selected' : '' ?>>Bank Transfer</option>
                    <option value="Cheque" <?= (($filters['payment_mode'] ?? '') == 'Cheque') ? 'selected' : '' ?>>Cheque</option>
                    <option value="Other" <?= (($filters['payment_mode'] ?? '') == 'Other') ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="<?= esc($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2 col-sm-4">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="<?= esc($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-2 col-sm-4 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i> Search</button>
                <a href="<?= site_url('invoice_payments') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Receipts Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 140px;">Receipt #</th>
                        <th style="min-width: 110px;">Date</th>
                        <th style="min-width: 190px;">Customer</th>
                        <th style="min-width: 130px;">Invoice #</th>
                        <th style="min-width: 120px;">Mode</th>
                        <th class="text-end" style="min-width: 120px;">Received</th>
                        <th class="text-end" style="min-width: 120px;">Deductions</th>
                        <th class="text-end" style="min-width: 130px;">Total Settled</th>
                        <th class="text-end" style="min-width: 110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $payment): ?>
                            <?php 
                            $deductions = ($payment['discount_amount'] ?? 0) + ($payment['mahimai_amount'] ?? 0) + ($payment['postal_charges'] ?? 0);
                            $settlement = $payment['amount'] + $deductions;
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= site_url('invoice_payments/view/' . $payment['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                        <?= esc($payment['payment_number']) ?>
                                    </a>
                                </td>
                                <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;min-width:32px;font-size:0.8rem;">
                                            <?= strtoupper(substr($payment['customer_name'] ?? 'C', 0, 1)) ?>
                                        </div>
                                        <span class="fw-semibold text-dark"><?= esc($payment['customer_name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?= site_url('invoices/view/' . $payment['invoice_id']) ?>" class="badge bg-light text-primary border text-decoration-none">
                                        <i class="fas fa-file-invoice-dollar me-1"></i><?= esc($payment['invoice_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge text-bg-info px-2 py-1">
                                        <?= esc($payment['payment_mode']) ?>
                                    </span>
                                </td>
                                <td class="text-end text-success fw-bold">₹<?= number_format($payment['amount'], 2) ?></td>
                                <td class="text-end text-danger">
                                    <?php if ($deductions > 0): ?>
                                        ₹<?= number_format($deductions, 2) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold text-dark">₹<?= number_format($settlement, 2) ?></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('invoice_payments/view/' . $payment['id']) ?>" class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= site_url('invoice_payments/edit/' . $payment['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" onclick="deletePayment(<?= $payment['id'] ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-file-invoice-dollar fa-2x text-muted mb-2 d-block"></i>
                                No customer receipts found matching the filters.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<form id="deleteForm" action="" method="post" style="display: none;">
    <?= csrf_field() ?>
</form>

<script>
function deletePayment(id) {
    if (confirm('Are you sure you want to delete this receipt? This will increase the invoice balance accordingly.')) {
        const form = document.getElementById('deleteForm');
        form.action = '<?= site_url('invoice_payments/delete/') ?>' + id;
        form.submit();
    }
}
</script>
<?= $this->endSection() ?>
