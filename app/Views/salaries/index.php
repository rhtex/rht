<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Salary Management<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-money-check-alt text-primary me-2"></i>Salary & Payroll Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('salaries') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('salaries/calculate?month='.$month) ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-calculator me-1"></i> Calculate <?= date('M Y', strtotime($month)) ?> Payroll
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('salaries?month='.$month) ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['is_paid']) ? 'active' : '' ?>">
            <i class="fas fa-users-cog bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($salaries)) ?></div>
            <div class="bt-stat-label">Processed Payslips</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-wallet bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['total_net'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Total Net Disbursable</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('salaries?month='.$month.'&is_paid=1') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['is_paid'] ?? '') === '1' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['paid_count'] ?? 0) ?></div>
            <div class="bt-stat-label">Paid Payslips</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['paid_count'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('salaries?month='.$month.'&is_paid=0') ?>" class="bt-stat bt-g-amber text-decoration-none <?= ($filters['is_paid'] ?? '') === '0' ? 'active' : '' ?>">
            <i class="fas fa-clock bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['unpaid_count'] ?? 0) ?></div>
            <div class="bt-stat-label">Pending Payment</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['unpaid_count'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Payroll</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('salaries') ?>" method="get" class="row g-2 align-items-end">
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
                <label for="month" class="form-label">Payroll Month</label>
                <input type="month" name="month" id="month" class="form-control" value="<?= esc($month) ?>">
            </div>
            <div class="col-md-2 col-sm-4">
                <label for="is_paid" class="form-label">Status</label>
                <select name="is_paid" id="is_paid" class="form-select">
                    <option value="">All</option>
                    <option value="1" <?= (($filters['is_paid'] ?? '') === '1') ? 'selected' : '' ?>>Paid</option>
                    <option value="0" <?= (($filters['is_paid'] ?? '') === '0') ? 'selected' : '' ?>>Unpaid</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i> Search</button>
                <a href="<?= site_url('salaries') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Salaries Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header py-2 bg-white">
        <h3 class="card-title fw-bold">Salaries for <?= date('F Y', strtotime($month)) ?></h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 190px;">Employee</th>
                        <th class="text-end" style="min-width: 120px;">Basic Salary</th>
                        <th class="text-end" style="min-width: 120px;">Deductions</th>
                        <th class="text-end" style="min-width: 120px;">Net Salary</th>
                        <th class="text-center" style="min-width: 110px;">Status</th>
                        <th style="min-width: 110px;">Generated On</th>
                        <th class="text-end" style="min-width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($salaries)): ?>
                        <?php foreach($salaries as $salary): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?= $salary['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;min-width:34px;font-size:0.85rem;">
                                        <?= strtoupper(substr($salary['first_name'] ?? 'E', 0, 1)) ?>
                                    </div>
                                    <span class="fw-semibold text-dark"><?= esc($salary['first_name'] . ' ' . $salary['last_name']) ?></span>
                                </div>
                            </td>
                            <td class="text-end">₹<?= number_format($salary['basic_salary'], 2) ?></td>
                            <td class="text-end text-danger">₹<?= number_format($salary['deductions'], 2) ?></td>
                            <td class="text-end fw-bold text-success fs-6">₹<?= number_format($salary['net_salary'], 2) ?></td>
                            <td class="text-center">
                                <?php if ($salary['is_paid'] == 1): ?>
                                    <span class="badge text-bg-success px-2 py-1"><i class="fas fa-check-circle me-1"></i>Paid</span>
                                    <?php if (!empty($salary['paid_date'])): ?>
                                        <br><small class="text-muted"><?= date('d/m/Y', strtotime($salary['paid_date'])) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge text-bg-warning px-2 py-1"><i class="fas fa-clock me-1"></i>Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($salary['created_at'])) ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('salaries/payslip/'.$salary['id']) ?>" class="btn btn-outline-primary" target="_blank" title="View / Print Payslip">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                    
                                    <?php if ($salary['is_paid'] == 0): ?>
                                        <a href="<?= site_url('salaries/mark-paid/'.$salary['id']) ?>" class="btn btn-outline-success" onclick="return confirm('Mark this salary as paid?')" title="Mark as Paid">
                                            <i class="fas fa-check-circle"></i>
                                        </a>
                                        <a href="<?= site_url('salaries/delete/'.$salary['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this salary? Loan deductions will be reverted.')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= site_url('salaries/mark-unpaid/'.$salary['id']) ?>" class="btn btn-outline-warning" onclick="return confirm('Mark this salary as unpaid?')" title="Mark as Unpaid">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-money-check-alt fa-2x text-muted mb-2 d-block"></i>
                                No salary records found for <?= date('F Y', strtotime($month)) ?>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
