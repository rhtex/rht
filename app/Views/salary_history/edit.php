<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<h4>Edit Salary Record</h4>

<form action="/salary-history/update/<?= esc($salaryHistory['salary_id']) ?>" method="post">
    <div class="row form-group mt-5">
        <div class="mb-3 col-md-6">
            <label for="employee_id">Employee Name</label>
            <select name="employee_id" id="employee_id" class="form-control" required>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?= $employee['id']; ?>" <?= ($salaryHistory['employee_id'] == $employee['id']) ? 'selected' : ''; ?>>
                        <?= $employee['first_name'] . ' ' . $employee['last_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3 col-md-6">
            <label for="salary_amount" class="form-label">Salary Amount</label>
            <input type="number" class="form-control" id="salary_amount" name="salary_amount" value="<?= esc($salaryHistory['salary_amount']) ?>" step="0.01" required>
        </div>

        <div class="mb-3 col-md-6">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= esc($salaryHistory['start_date']) ?>" required>
        </div>

        <div class="mb-3 col-md-6">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= esc($salaryHistory['end_date']) ?>">
        </div>

        <div class="mb-3 col-md-6">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status">
                <option value="Active" <?= esc($salaryHistory['status']) == 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= esc($salaryHistory['status']) == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
</form>

<?php include __DIR__ . '/../layouts/footer.php'; ?>