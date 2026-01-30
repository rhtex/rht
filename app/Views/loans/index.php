<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Loans<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Loan Management</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('loans/create') ?>" class="btn btn-primary float-sm-end">
            <i class="fas fa-plus"></i> New Loan Request
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
        <form action="<?= site_url('loans') ?>" method="get" class="row g-3">
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
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Pending" <?= $filters['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Approved" <?= $filters['status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="Rejected" <?= $filters['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="Completed" <?= $filters['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="credit_status" class="form-label">Credit Status</label>
                <select name="credit_status" id="credit_status" class="form-select">
                    <option value="">All</option>
                    <option value="Pending" <?= $filters['credit_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Credited" <?= $filters['credit_status'] == 'Credited' ? 'selected' : '' ?>>Credited</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                    <a href="<?= site_url('loans') ?>" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Loans</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee</th>
                    <th>Amount</th>
                    <th>Remaining</th>
                    <th>Deduction/Month</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($loans)): ?>
                    <?php foreach($loans as $loan): ?>
                    <tr>
                        <td><?= $loan['id'] ?></td>
                        <td><?= $loan['first_name'] . ' ' . $loan['last_name'] ?></td>
                        <td><?= number_format($loan['loan_amount'], 2) ?></td>
                        <td><?= number_format($loan['remaining_amount'], 2) ?></td>
                        <td><?= number_format($loan['monthly_deduction'], 2) ?></td>
                        <td>
                            <?php
                                $badge = match($loan['status']) {
                                    'Approved' => 'success',
                                    'Pending' => 'warning',
                                    'Rejected' => 'danger',
                                    'Completed' => 'info',
                                    default => 'secondary'
                                };
                            ?>
                            <span class="badge text-bg-<?= $badge ?>"><?= $loan['status'] ?></span>
                        </td>
                        <td><?= date('d M Y', strtotime($loan['loan_date'])) ?></td>
                        <td>
                            <a href="<?= site_url('loans/view/'.$loan['id']) ?>" class="btn btn-sm btn-info" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= site_url('loans/edit/'.$loan['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= site_url('loans/delete/'.$loan['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No loans found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
