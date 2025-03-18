<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<div class="container mt-2">
    <h4 class="mt-4"><?= esc($pageTitle) ?></h4>

    <table class="table table-bordered">
        <tr>
            <th>Name</th>
            <td><?= esc($agent['name']) ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= esc($agent['email']) ?></td>
        </tr>
        <tr>
            <th>Mobile</th>
            <td><?= esc($agent['mobile']) ?></td>
        </tr>
        <tr>
            <th>Phone</th>
            <td><?= esc($agent['phone']) ?></td>
        </tr>
        <tr>
            <th>Remarks</th>
            <td><?= esc($agent['remarks']) ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?= esc($agent['address']) ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?= esc($agent['status']) ?></td>
        </tr>
        <tr>
            <th>Created By</th>
            <td><?= esc($agent['created_by']) ?></td>
        </tr>
        <tr>
            <th>Updated By</th>
            <td><?= esc($agent['updated_by']) ?></td>
        </tr>
        <tr>
            <th>Created At</th>
            <td><?= esc($agent['created_at']) ?></td>
        </tr>
        <tr>
            <th>Updated At</th>
            <td><?= esc($agent['updated_at']) ?></td>
        </tr>
    </table>

    <a href="<?= site_url('agents') ?>" class="btn btn-primary">Back to Agents List</a>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
