<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<h4>Salary History Details</h4>

<div class="card">
    <div class="card-header">
        Salary History for Employee ID: <?= esc($salaryHistory['employee_id']) ?>
    </div>
    <div class="card-body">
        <p><strong>Employee ID:</strong> <?= esc($salaryHistory['employee_id']) ?></p>
        <p><strong>Salary Amount:</strong> <?= esc($salaryHistory['salary_amount']) ?></p>
        <p><strong>Start Date:</strong> <?= esc($salaryHistory['start_date']) ?></p>
        <p><strong>End Date:</strong> <?= esc($salaryHistory['end_date']) ?: 'Ongoing' ?></p>
        <p><strong>Status:</strong> <?= esc($salaryHistory['status']) ?></p>
    </div>
    <div class="card-footer">
        <a href="/salary-history/edit/<?= esc($salaryHistory['salary_id']) ?>" class="btn btn-warning">Edit</a>
        <a href="/salary-history" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
