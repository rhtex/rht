<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Salary Management<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Salary History</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('salaries/calculate?month='.$month) ?>" class="btn btn-success text-nowrap">
            <i class="fas fa-calculator"></i> Calculate Monthly Salary
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter me-1"></i> Filters</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('salaries') ?>" method="get" class="row g-3">
            <div class="col-md-4">
                <label for="employee_id" class="form-label">Employee</label>
                <select name="employee_id" id="employee_id" class="form-select select2">
                    <option value="">All Employees</option>
                    <?php foreach($employees as $e): ?>
                        <option value="<?= $e['id'] ?>" <?= $filters['employee_id'] == $e['id'] ? 'selected' : '' ?>><?= esc($e['first_name'] . ' ' . $e['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="month" class="form-label">Month</label>
                <input type="month" name="month" id="month" class="form-control" value="<?= $month ?>">
            </div>
            <div class="col-md-3">
                <label for="is_paid" class="form-label">Status</label>
                <select name="is_paid" id="is_paid" class="form-select">
                    <option value="">All</option>
                    <option value="1" <?= $filters['is_paid'] === '1' ? 'selected' : '' ?>>Paid</option>
                    <option value="0" <?= $filters['is_paid'] === '0' ? 'selected' : '' ?>>Unpaid</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                    <a href="<?= site_url('salaries') ?>" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Salaries for <?= date('F Y', strtotime($month)) ?></h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee</th>
                    <th>Basic</th>
                    <th>Deductions</th>
                    <th>Net Salary</th>
                    <th>Status</th>
                    <th>Date Generated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($salaries)): ?>
                    <?php foreach($salaries as $salary): ?>
                    <tr>
                        <td><?= $salary['id'] ?></td>
                        <td><?= $salary['first_name'] . ' ' . $salary['last_name'] ?></td>
                        <td>₹<?= number_format($salary['basic_salary'], 2) ?></td>
                        <td>₹<?= number_format($salary['deductions'], 2) ?></td>
                        <td class="fw-bold text-success">₹<?= number_format($salary['net_salary'], 2) ?></td>
                        <td>
                            <?php if ($salary['is_paid'] == 1): ?>
                                <span class="badge bg-success">Paid</span>
                                <br><small class="text-muted"><?= date('d M Y', strtotime($salary['paid_date'])) ?></small>
                            <?php else: ?>
                                <span class="badge bg-warning">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d M Y', strtotime($salary['created_at'])) ?></td>
                        <td>
                            <a href="<?= site_url('salaries/payslip/'.$salary['id']) ?>" class="btn btn-sm btn-info" target="_blank" title="View Payslip">
                                <i class="fas fa-file-invoice"></i>
                            </a>
                            
                            <?php if ($salary['is_paid'] == 0): ?>
                                <a href="<?= site_url('salaries/mark-paid/'.$salary['id']) ?>" class="btn btn-sm btn-success" onclick="return confirm('Mark this salary as paid?')" title="Mark as Paid">
                                    <i class="fas fa-check-circle"></i>
                                </a>
                                <a href="<?= site_url('salaries/delete/'.$salary['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this salary? Loan deductions will be reverted.')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php else: ?>
                                <a href="<?= site_url('salaries/mark-unpaid/'.$salary['id']) ?>" class="btn btn-sm btn-warning" onclick="return confirm('Mark this salary as unpaid?')" title="Mark as Unpaid">
                                    <i class="fas fa-times-circle"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No salary records found for this month.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
