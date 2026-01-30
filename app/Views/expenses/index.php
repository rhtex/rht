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
<div class="card card-outline card-primary mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter me-1"></i> Filters</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('expenses') ?>" method="get" class="row g-3">
            <div class="col-md-3">
                <label for="category_id" class="form-label">Category</label>
                <select name="category_id" id="category_id" class="form-select select2">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $filters['category_id'] == $c['id'] ? 'selected' : '' ?>><?= esc($c['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="bank_account_id" class="form-label">Bank Account</label>
                <select name="bank_account_id" id="bank_account_id" class="form-select select2">
                    <option value="">All Accounts</option>
                    <?php foreach($bank_accounts as $ba): ?>
                        <option value="<?= $ba['id'] ?>" <?= $filters['bank_account_id'] == $ba['id'] ? 'selected' : '' ?>><?= esc($ba['bank_name']) ?> (<?= substr($ba['account_number'], -4) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="<?= $filters['date_from'] ?>">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="<?= $filters['date_to'] ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                    <a href="<?= site_url('expenses') ?>" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

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
