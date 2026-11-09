<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Loans<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-hand-holding-usd text-primary me-2"></i>Employee Loan Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('loans') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('loans/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Loan Request
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('loans') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-handshake bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($loans)) ?></div>
            <div class="bt-stat-label">Total Loans Issued</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-coins bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['total_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Total Principal Granted</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-amber" style="cursor:default;">
            <i class="fas fa-hourglass-half bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['remaining_total'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Outstanding Balance</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total_amount']) && $stats['total_amount'] > 0) ? min(100, round(($stats['remaining_total'] / $stats['total_amount']) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('loans?status=Approved') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'Approved' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['approved_count'] ?? 0) ?></div>
            <div class="bt-stat-label">Active / Approved Loans</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['approved_count'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Loans</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('loans') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-4 col-sm-6">
                <label for="employee_id" class="form-label">Employee</label>
                <select name="employee_id" id="employee_id" class="form-select select2">
                    <option value="">All Employees</option>
                    <?php foreach($employees as $e): ?>
                        <option value="<?= $e['id'] ?>" <?= (($filters['employee_id'] ?? '') == $e['id']) ? 'selected' : '' ?>><?= esc($e['first_name'] . ' ' . $e['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Pending" <?= (($filters['status'] ?? '') == 'Pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="Approved" <?= (($filters['status'] ?? '') == 'Approved') ? 'selected' : '' ?>>Approved</option>
                    <option value="Rejected" <?= (($filters['status'] ?? '') == 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                    <option value="Completed" <?= (($filters['status'] ?? '') == 'Completed') ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label for="credit_status" class="form-label">Credit Status</label>
                <select name="credit_status" id="credit_status" class="form-select">
                    <option value="">All</option>
                    <option value="Pending" <?= (($filters['credit_status'] ?? '') == 'Pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="Credited" <?= (($filters['credit_status'] ?? '') == 'Credited') ? 'selected' : '' ?>>Credited</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i> Search</button>
                <a href="<?= site_url('loans') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Loans Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 180px;">Employee</th>
                        <th style="min-width: 100px;">Loan Date</th>
                        <th class="text-end" style="min-width: 120px;">Loan Amount</th>
                        <th class="text-end" style="min-width: 120px;">Remaining</th>
                        <th class="text-end" style="min-width: 120px;">Deduction / Mo</th>
                        <th class="text-center" style="min-width: 100px;">Status</th>
                        <th class="text-end" style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($loans)): ?>
                        <?php foreach($loans as $loan): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?= $loan['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;min-width:34px;font-size:0.85rem;">
                                        <?= strtoupper(substr($loan['first_name'] ?? 'E', 0, 1)) ?>
                                    </div>
                                    <span class="fw-semibold text-dark"><?= esc($loan['first_name'] . ' ' . $loan['last_name']) ?></span>
                                </div>
                            </td>
                            <td><?= date('d/m/Y', strtotime($loan['loan_date'])) ?></td>
                            <td class="text-end fw-bold text-dark">₹<?= number_format($loan['loan_amount'], 2) ?></td>
                            <td class="text-end fw-bold <?= ($loan['remaining_amount'] > 0) ? 'text-danger' : 'text-success' ?>">
                                ₹<?= number_format($loan['remaining_amount'], 2) ?>
                            </td>
                            <td class="text-end text-muted">₹<?= number_format($loan['monthly_deduction'], 2) ?></td>
                            <td class="text-center">
                                <?php
                                    $badge = match($loan['status']) {
                                        'Approved'  => 'success',
                                        'Pending'   => 'warning',
                                        'Rejected'  => 'danger',
                                        'Completed' => 'info',
                                        default     => 'secondary'
                                    };
                                ?>
                                <span class="badge text-bg-<?= $badge ?> px-2 py-1"><?= $loan['status'] ?></span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('loans/view/'.$loan['id']) ?>" class="btn btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= site_url('loans/edit/'.$loan['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= site_url('loans/delete/'.$loan['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this loan record?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-hand-holding-usd fa-2x text-muted mb-2 d-block"></i>
                                No loan records found matching the criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
