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
