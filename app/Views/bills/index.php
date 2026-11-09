<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-file-invoice text-primary me-2"></i><?= esc($title) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('bills') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('bills/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Purchase Bill
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('bills') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-file-invoice bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($bills)) ?></div>
            <div class="bt-stat-label">Total Bills</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-coins bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['total_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Total Billed</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('bills?status=Paid') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'Paid' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['paid_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Paid (<?= $stats['paid_count'] ?? 0 ?> Bills)</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total_amount']) && $stats['total_amount'] > 0) ? min(100, round(($stats['paid_amount'] / $stats['total_amount']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('bills?status=Open') ?>" class="bt-stat bt-g-amber text-decoration-none <?= in_array($filters['status'] ?? '', ['Open', 'Overdue', 'Partially Paid']) ? 'active' : '' ?>">
            <i class="fas fa-clock bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['balance'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Payable Balance</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total_amount']) && $stats['total_amount'] > 0) ? min(100, round(($stats['balance'] / $stats['total_amount']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Purchase Bills</h3>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-2 col-sm-6">
                <label class="form-label">Bill # / Ref #</label>
                <input type="text" name="bill_number" class="form-control" placeholder="Search Bill #" value="<?= esc($filters['bill_number'] ?? '') ?>">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label">Vendor</label>
                <select name="vendor_id" class="form-select select2">
                    <option value="">All Vendors</option>
                    <?php foreach ($vendors as $vendor): ?>
                        <option value="<?= $vendor['id'] ?>" <?= (isset($filters['vendor_id']) && $filters['vendor_id'] == $vendor['id']) ? 'selected' : '' ?>>
                            <?= esc($vendor['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Draft" <?= (($filters['status'] ?? '') == 'Draft') ? 'selected' : '' ?>>Draft</option>
                    <option value="Open" <?= (($filters['status'] ?? '') == 'Open') ? 'selected' : '' ?>>Open</option>
                    <option value="Paid" <?= (($filters['status'] ?? '') == 'Paid') ? 'selected' : '' ?>>Paid</option>
                    <option value="Partially Paid" <?= (($filters['status'] ?? '') == 'Partially Paid') ? 'selected' : '' ?>>Partially Paid</option>
                    <option value="Overdue" <?= (($filters['status'] ?? '') == 'Overdue') ? 'selected' : '' ?>>Overdue</option>
                    <option value="Void" <?= (($filters['status'] ?? '') == 'Void') ? 'selected' : '' ?>>Void</option>
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
                <a href="<?= site_url('bills') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Bills Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 140px;">Bill #</th>
                        <th style="min-width: 200px;">Vendor</th>
                        <th style="min-width: 110px;">Bill Date</th>
                        <th style="min-width: 110px;">Due Date</th>
                        <th class="text-end" style="min-width: 110px;">Total Amount</th>
                        <th class="text-end" style="min-width: 110px;">Paid</th>
                        <th class="text-end" style="min-width: 110px;">Balance</th>
                        <th class="text-center" style="min-width: 100px;">Status</th>
                        <th class="text-end" style="min-width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bills)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-file-invoice fa-2x text-muted mb-2 d-block"></i>
                                No purchase bills found matching the criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bills as $b): ?>
                            <tr>
                                <td>
                                    <a href="<?= site_url('bills/view/' . $b['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                        <?= esc($b['bill_number']) ?>
                                    </a>
                                    <?php if (!empty($b['reference_number'])): ?>
                                        <br><small class="text-muted">Ref: <?= esc($b['reference_number']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-warning-subtle text-warning-emphasis fw-bold me-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;min-width:34px;font-size:0.85rem;">
                                            <?= strtoupper(substr($b['vendor_name'] ?? 'V', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-dark"><?= esc($b['vendor_name']) ?></span>
                                            <?php if (!empty($b['vendor_city'])): ?>
                                                <br><small class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= esc($b['vendor_city']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= date('d/m/Y', strtotime($b['bill_date'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($b['due_date'])) ?></td>
                                <td class="text-end fw-bold">₹<?= number_format($b['total_amount'], 2) ?></td>
                                <td class="text-end text-success fw-semibold">₹<?= number_format($b['paid_amount'], 2) ?></td>
                                <td class="text-end <?= ($b['balance'] > 0) ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    ₹<?= number_format($b['balance'], 2) ?>
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
                                    $color = $statusColors[$b['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge text-bg-<?= $color ?> px-2 py-1"><?= $b['status'] ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('bills/view/' . $b['id']) ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                        <?php if ($b['status'] != 'Void' && $b['status'] != 'Paid'): ?>
                                            <a href="<?= site_url('bills/edit/' . $b['id']) ?>" class="btn btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                        <?php endif; ?>
                                        <?php if ($b['balance'] > 0 && $b['status'] != 'Void'): ?>
                                            <a href="<?= site_url('bills/payment/' . $b['id']) ?>" class="btn btn-outline-success" title="Record Payment"><i class="fas fa-money-bill-wave"></i></a>
                                        <?php endif; ?>
                                        <a href="<?= site_url('bills/print/' . $b['id']) ?>" target="_blank" class="btn btn-outline-secondary" title="Print"><i class="fas fa-print"></i></a>
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
