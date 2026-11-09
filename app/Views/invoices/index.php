<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Invoices<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Invoices</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('invoices') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('invoices/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Invoice
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('invoices') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-file-invoice bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($invoices)) ?></div>
            <div class="bt-stat-label">Total Invoices</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-coins bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['total_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Total Invoiced</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('invoices?status=Paid') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'Paid' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['paid_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Paid (<?= $stats['paid_count'] ?? 0 ?> Invoices)</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total_amount']) && $stats['total_amount'] > 0) ? min(100, round(($stats['paid_amount'] / $stats['total_amount']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('invoices?status=Open') ?>" class="bt-stat bt-g-amber text-decoration-none <?= in_array($filters['status'] ?? '', ['Open', 'Overdue', 'Partially Paid']) ? 'active' : '' ?>">
            <i class="fas fa-clock bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['balance'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Outstanding Balance</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total_amount']) && $stats['total_amount'] > 0) ? min(100, round(($stats['balance'] / $stats['total_amount']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Invoices</h3>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-2 col-sm-6">
                <label class="form-label">Invoice #</label>
                <input type="text" name="invoice_number" class="form-control" placeholder="Search Invoice #" value="<?= esc($filters['invoice_number'] ?? '') ?>">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label">Customer</label>
                <select name="customer_id" class="form-select select2">
                    <option value="">All Customers</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['id'] ?>" <?= (isset($filters['customer_id']) && $filters['customer_id'] == $customer['id']) ? 'selected' : '' ?>>
                            <?= esc($customer['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Draft" <?= (isset($filters['status']) && $filters['status'] == 'Draft') ? 'selected' : '' ?>>Draft</option>
                    <option value="Open" <?= (isset($filters['status']) && $filters['status'] == 'Open') ? 'selected' : '' ?>>Open</option>
                    <option value="Paid" <?= (isset($filters['status']) && $filters['status'] == 'Paid') ? 'selected' : '' ?>>Paid</option>
                    <option value="Partially Paid" <?= (isset($filters['status']) && $filters['status'] == 'Partially Paid') ? 'selected' : '' ?>>Partially Paid</option>
                    <option value="Overdue" <?= (isset($filters['status']) && $filters['status'] == 'Overdue') ? 'selected' : '' ?>>Overdue</option>
                    <option value="Void" <?= (isset($filters['status']) && $filters['status'] == 'Void') ? 'selected' : '' ?>>Void</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filters"><i class="fas fa-search"></i></button>
                <a href="<?= site_url('invoices') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Invoices Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 130px;">Invoice #</th>
                        <th style="min-width: 200px;">Customer</th>
                        <th style="min-width: 100px;">Date</th>
                        <th class="text-end" style="min-width: 110px;">Total</th>
                        <th class="text-end" style="min-width: 110px;">Paid</th>
                        <th class="text-end" style="min-width: 110px;">Balance</th>
                        <th class="text-center" style="min-width: 100px;">Status</th>
                        <th class="text-center" style="min-width: 110px;">Commission</th>
                        <th class="text-end" style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-file-invoice fa-2x text-muted mb-2 d-block"></i>
                                No invoices found matching the criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td>
                                    <a href="<?= site_url('invoices/view/' . $invoice['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                        <?= esc($invoice['invoice_number']) ?>
                                    </a>
                                    <?php if (!empty($invoice['so_number'])): ?>
                                        <br><span class="badge bg-light text-secondary border small" style="font-size:0.7rem;">SO: <?= esc($invoice['so_number']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;min-width:34px;font-size:0.85rem;">
                                            <?= strtoupper(substr($invoice['customer_name'] ?? 'C', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-dark"><?= esc($invoice['customer_name']) ?></span>
                                            <?php if (!empty($invoice['customer_city'])): ?>
                                                <br><small class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= esc($invoice['customer_city']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-nowrap"><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></span>
                                    <?php if (!empty($invoice['due_date'])): ?>
                                        <br><small class="text-muted">Due: <?= date('d/m/Y', strtotime($invoice['due_date'])) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold">₹<?= number_format($invoice['total_amount'], 2) ?></td>
                                <td class="text-end text-success fw-semibold">₹<?= number_format($invoice['paid_amount'], 2) ?></td>
                                <td class="text-end <?= ($invoice['balance'] > 0) ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    ₹<?= number_format($invoice['balance'], 2) ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $statusColors = [
                                        'Draft'          => 'secondary',
                                        'Open'           => 'primary',
                                        'Paid'           => 'success',
                                        'Partially Paid' => 'info',
                                        'Overdue'        => 'danger',
                                        'Void'           => 'dark'
                                    ];
                                    $color = $statusColors[$invoice['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge text-bg-<?= $color ?> px-2 py-1"><?= $invoice['status'] ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($invoice['agent_id'])): ?>
                                        <?php if ($invoice['agent_commission_status'] == 'Paid'): ?>
                                            <span class="badge text-bg-success" title="Commission Paid"><i class="fas fa-check-circle me-1"></i>Paid</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-warning" title="Commission Pending"><i class="fas fa-clock me-1"></i>Pending</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('invoices/view/' . $invoice['id']) ?>" class="btn btn-outline-primary" title="View Invoice">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($invoice['status'] != 'Void' && $invoice['status'] != 'Paid'): ?>
                                            <?php if ($invoice['status'] == 'Draft'): ?>
                                                <a href="<?= site_url('invoices/mark-sent/' . $invoice['id']) ?>" class="btn btn-outline-success" title="Mark as Sent">
                                                    <i class="fas fa-paper-plane"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= site_url('invoices/edit/' . $invoice['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($invoice['status'] != 'Draft' && $invoice['balance'] > 0): ?>
                                                <a href="<?= site_url('invoices/payment/' . $invoice['id']) ?>" class="btn btn-outline-success" title="Record Payment">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </a>
                                            <?php endif; ?>
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
<?= $this->endSection() ?>
