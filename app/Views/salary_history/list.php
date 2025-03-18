<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<h4>List Salary History</h4>
<a href="/salary-history/create" class="btn btn-primary mb-3">Add Salary History</a>
<a href="/salary-history/viewemployeesalaryhistory" class="btn btn-primary mb-3">View Employee Salary History</a>

<table id="salary-history-table" class="display table table-bordered" style="width:100%">
    <thead>
        <tr>
            <th>#</th>
            <th>Employee</th>
            <th>Salary Amount</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($salaryHistories as $salaryHistory): ?>
            <tr>
                <td><?= $salaryHistory['salary_id'] ?></td>
                <td>
                    <?php
                    foreach ($employees as $employeess) {
                        if ($employeess['id'] == $salaryHistory['employee_id']) {
                            echo $employeess['first_name'] . ' ' . $employeess['last_name'];
                            break;
                        }
                    }
                    ?>
                </td>
                <td><?= $salaryHistory['salary_amount'] ?></td>
                <td><?= $salaryHistory['start_date'] ?></td>
                <td><?= $salaryHistory['end_date'] ?></td>
                <td><?= $salaryHistory['status'] ?></td>
                <td>
                    <a href="/salary-history/show/<?= $salaryHistory['salary_id'] ?>" class="btn btn-sm btn-info">View</a>
                    <a href="/salary-history/edit/<?= $salaryHistory['salary_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="/salary-history/delete/<?= $salaryHistory['salary_id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
<script>
    $(document).ready(function() {
        var table = $('#salary-history-table').DataTable();

    });
</script>