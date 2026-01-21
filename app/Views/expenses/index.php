<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Expenses<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Expense Management</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('expenses/create') ?>" class="btn btn-primary float-sm-end">
            <i class="fas fa-plus"></i> Add New Expense
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Expenses</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Payment Mode</th>
                        <th>Bank Account</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($expenses)): ?>
                        <?php foreach($expenses as $expense): ?>
                        <tr>
                            <td><?= $expense['id'] ?></td>
                            <td><?= date('d M Y', strtotime($expense['expense_date'])) ?></td>
                            <td><?= esc($expense['category_name'] ?? 'N/A') ?></td>
                            <td><?= esc($expense['description']) ?></td>
                            <td><?= number_format($expense['amount'], 2) ?></td>
                            <td><?= ucfirst($expense['payment_mode']) ?></td>
                            <td>
                                <?php if(!empty($expense['bank_name'])): ?>
                                    <?= esc($expense['bank_name']) ?> (<?= substr($expense['account_number'], -4) ?>)
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= site_url('expenses/edit/'.$expense['id']) ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= site_url('expenses/delete/'.$expense['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No expenses recorded</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
