<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Edit Transaction<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Edit Transaction</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('bank_accounts/statement/'.$account['id']) ?>" class="btn btn-secondary float-sm-end">Back to Statement</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">Transaction Details</h3>
            </div>
            <form action="<?= site_url('bank_accounts/transactions/update/'.$transaction['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">
                    <?php if (session()->has('errors')) : ?>
                        <div class="alert alert-danger">
                            <ul>
                            <?php foreach (session('errors') as $error) : ?>
                                <li><?= $error ?></li>
                            <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif ?>

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="transaction_date" class="form-control" value="<?= old('transaction_date', $transaction['transaction_date']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control" required>
                            <option value="credit" <?= old('type', $transaction['type']) === 'credit' ? 'selected' : '' ?>>Credit (Deposit)</option>
                            <option value="debit" <?= old('type', $transaction['type']) === 'debit' ? 'selected' : '' ?>>Debit (Withdrawal)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="<?= old('amount', $transaction['amount']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference No</label>
                        <input type="text" name="reference_number" class="form-control" value="<?= old('reference_number', $transaction['reference_number']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required><?= old('description', $transaction['description']) ?></textarea>
                    </div>
                    <p class="text-muted small">Note: Updating this transaction will automatically adjust the bank account's current balance.</p>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-warning">Update Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
