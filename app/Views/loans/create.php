<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Add New Loan<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>New Loan Request</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('loans') ?>" class="btn btn-secondary float-sm-end">Back to List</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-primary card-outline">
    <form action="<?= site_url('loans/store') ?>" method="post">
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
                <label for="employee_id" class="form-label">Employee</label>
                <select name="employee_id" class="form-select" required>
                    <option value="">Select Employee</option>
                    <?php foreach($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= old('employee_id') == $emp['id'] ? 'selected' : '' ?>>
                            <?= $emp['first_name'] . ' ' . $emp['last_name'] ?> (<?= $emp['mobile_number'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="loan_amount" class="form-label">Loan Amount</label>
                <input type="number" step="0.01" class="form-control" name="loan_amount" value="<?= old('loan_amount') ?>" required>
            </div>

            <div class="mb-3">
                <label for="monthly_deduction" class="form-label">Monthly Deduction Amount</label>
                <input type="number" step="0.01" class="form-control" name="monthly_deduction" value="<?= old('monthly_deduction') ?>" required>
                <small class="text-muted">Amount to be deducted from salary each month.</small>
            </div>

            <div class="mb-3">
                <label for="loan_date" class="form-label">Loan Date</label>
                <input type="date" class="form-control" name="loan_date" value="<?= old('loan_date', date('Y-m-d')) ?>" required>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Create Request</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
