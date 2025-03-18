<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<div class="container mt-2">
    <h4 class="mt-4"><?= esc($pageTitle) ?></h4>

    <div class="mb-3">
        <a href="<?= site_url('agents/create') ?>" class="btn btn-primary">Add New Agent</a>
    </div>

    <table id="agentsTable" class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Mobile</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($agents as $agent): ?>
                <tr>
                    <td><?= esc($agent['name']) ?></td>
                    <td><?= esc($agent['address']) ?></td>
                    <td><?= esc($agent['mobile']) ?></td>
                    <td>
                        <a href="<?= site_url('agents/view/' . $agent['id']) ?>" class="btn btn-info btn-sm">View Details</a>
                        <a href="<?= site_url('agents/edit/' . $agent['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                        <!-- <a href="" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this agent?')">Delete</a> -->
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#agentsTable').DataTable();
    });
</script>
