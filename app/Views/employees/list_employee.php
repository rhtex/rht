<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

    
        <h4> List Employees</h4>
        <a href="/add_employee" class="btn btn-primary mb-3">Add Employee</a>

        <table id="employees-table" class="display table table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Guardian Name</th>
                    <th>Employee Mobile</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td><?= $employee['id'] ?></td>
                        <td><?= $employee['first_name'] ?> <?= $employee['last_name'] ?></td>
                        <td><?= $employee['guardian_name'] ?></td>
                        <td><?= $employee['employee_mobile'] ?></td>
                        <td><?= $employee['email'] ?></td>
                        <td>
                            <a href="/view_employee/<?= $employee['id'] ?>" class="btn btn-sm btn-info">View</a>
                            <a href="/edit_employee/<?= $employee['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="/employees/delete/<?= $employee['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
