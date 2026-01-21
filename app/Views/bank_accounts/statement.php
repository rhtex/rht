<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Bank Statement<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Account Statement</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('bank_accounts') ?>" class="btn btn-secondary float-sm-end">Back to Accounts</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-4">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Account Details</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Bank:</th>
                        <td><?= esc($account['bank_name']) ?></td>
                    </tr>
                    <tr>
                        <th>A/C No:</th>
                        <td><?= esc($account['account_number']) ?></td>
                    </tr>
                    <tr>
                        <th>Branch:</th>
                        <td><?= esc($account['branch_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Current Balance:</th>
                        <td class="fw-bold text-primary"><?= number_format($account['current_balance'], 2) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card card-primary card-outline mt-3">
            <div class="card-header">
                <h3 class="card-title">Add Transaction</h3>
            </div>
            <form action="<?= site_url('bank_accounts/transactions/store') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="bank_account_id" value="<?= $account['id'] ?>">
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label">Date</label>
                        <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control" required>
                            <option value="credit">Credit (Deposit)</option>
                            <option value="debit">Debit (Withdrawal)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Reference No</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="Ref/Cheque No">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Purpose of transaction" required></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100">Record Transaction</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Transaction History</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Ref</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Balance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($transactions)): ?>
                                <?php foreach($transactions as $tx): ?>
                                <tr>
                                    <td><?= date('d-M-Y', strtotime($tx['transaction_date'])) ?></td>
                                    <td><?= esc($tx['description']) ?></td>
                                    <td><?= esc($tx['reference_number']) ?></td>
                                    <td class="text-danger">
                                        <?= $tx['type'] === 'debit' ? number_format($tx['amount'], 2) : '-' ?>
                                    </td>
                                    <td class="text-success">
                                        <?= $tx['type'] === 'credit' ? number_format($tx['amount'], 2) : '-' ?>
                                    </td>
                                    <td class="fw-bold"><?= number_format($tx['balance_after'], 2) ?></td>
                                    <td>
                                        <a href="<?= site_url('bank_accounts/transactions/edit/'.$tx['id']) ?>" class="btn btn-xs btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= site_url('bank_accounts/transactions/delete/'.$tx['id']) ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete this transaction?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No transactions yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
