<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= lang("App.edit") ?> Loan<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= lang("App.edit") ?> Loan</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('loans') ?>" class="btn btn-secondary float-sm-end">Back to List</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-warning card-outline">
    <form action="<?= site_url('loans/update/'.$loan['id']) ?>" method="post">
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
                <label class="form-label">Employee</label>
                <input type="text" class="form-control" value="<?= $loan['employee_id'] ?>" disabled>
                <!-- Usually don't change employee on edit, just details -->
            </div>

            <div class="mb-3">
                <label for="loan_amount" class="form-label">Total Loan Amount</label>
                <input type="number" step="0.01" class="form-control" name="loan_amount" value="<?= old('loan_amount', $loan['loan_amount']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="remaining_amount" class="form-label">Remaining Amount</label>
                <input type="number" step="0.01" class="form-control" name="remaining_amount" value="<?= old('remaining_amount', $loan['remaining_amount']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="monthly_deduction" class="form-label">Monthly Deduction</label>
                <input type="number" step="0.01" class="form-control" name="monthly_deduction" value="<?= old('monthly_deduction', $loan['monthly_deduction']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Pending" <?= old('status', $loan['status']) == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Approved" <?= old('status', $loan['status']) == 'Approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="Rejected" <?= old('status', $loan['status']) == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="Completed" <?= old('status', $loan['status']) == 'Completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="credited_date" class="form-label">Amount Credited Date</label>
                <input type="date" class="form-control" name="credited_date" value="<?= old('credited_date', $loan['credited_date'] ?? '') ?>">
                <small class="text-muted">Date when loan amount was credited to employee</small>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">Update Loan</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
