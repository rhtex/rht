<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Calculate Salary<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Calculate Salary: <?= date('F Y', strtotime($month)) ?></h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('salaries?month='.$month) ?>" class="btn btn-secondary float-sm-end">Back to History</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<form action="<?= site_url('salaries/process') ?>" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="month" value="<?= $month ?>">

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Preview Calculation</h3>
            <div class="card-tools">
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-check"></i> Process Selected
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th style="width: 40px"><input type="checkbox" id="selectAll"></th>
                            <th>Employee</th>
                            <th>Basic Salary</th>
                            <th>Absents (Days)</th>
                            <th>Absent Ded.</th>
                            <th>Extra Pay</th>
                            <th>Loan Ded.</th>
                            <th>Exempt Loan</th>
                            <th>Net Pay</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($calculations)): ?>
                            <?php foreach($calculations as $calc): ?>
                            <tr class="<?= $calc['is_generated'] ? 'table-light text-muted' : '' ?>">
                                <td>
                                    <?php if(!$calc['is_generated']): ?>
                                        <input type="checkbox" name="employee_ids[]" value="<?= $calc['employee']['id'] ?>" class="emp-check" checked>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $calc['employee']['first_name'] . ' ' . $calc['employee']['last_name'] ?>
                                </td>
                                <td>₹<?= number_format($calc['basic'], 2) ?></td>
                                <td><?= $calc['absent_count'] ?></td>
                                <td class="text-danger">-₹<?= number_format($calc['absent_deduction'], 2) ?></td>
                                <td class="text-success">+₹<?= number_format($calc['extra_pay'], 2) ?></td>
                                <td class="text-danger">-₹<?= number_format($calc['loan_deduction'], 2) ?></td>
                                <td class="text-center">
                                    <?php if(!$calc['is_generated'] && $calc['loan_deduction'] > 0): ?>
                                        <input type="checkbox" name="exempt_loan[<?= $calc['employee']['id'] ?>]" value="1" title="Skip loan deduction for this employee">
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-success">₹<?= number_format($calc['net'], 2) ?></td>
                                <td>
                                    <?php if($calc['is_generated']): ?>
                                        <span class="badge bg-secondary">Generated</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Ready</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">No active employees found to calculate.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success float-end">Confirm & Process Salaries</button>
        </div>
    </div>
</form>

<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.emp-check');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });
</script>
<?= $this->endSection() ?>
