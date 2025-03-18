<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<h4>Add New Salary Record</h4>

<form action="/salary-history/store" method="post">

    <div class="row form-group mt-5">
        <div class="mb-3 col-md-6">
            <label for="employee_id">Employee Name</label>
            <select name="employee_id" id="employee_id" class="form-control" required>
                <option value="">Select Employee</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?= $employee['id']; ?>"><?= $employee['first_name'] . ' ' . $employee['last_name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3 col-md-6">
            <label for="salary_amount" class="form-label">Salary Amount</label>
            <input type="number" class="form-control" id="salary_amount" name="salary_amount" step="100" required>
        </div>

        <div class="mb-3 col-md-6">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="start_date" name="start_date" required>
        </div>

        <div class="mb-3 col-md-6">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" class="form-control" id="end_date" name="end_date">
        </div>

        <div class="mb-3 col-md-6">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Save Salary History</button>
</form>


<?php include __DIR__ . '/../layouts/footer.php'; ?>