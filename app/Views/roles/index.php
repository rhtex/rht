<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    
        <h1 class="mb-4">Roles</h1>
        <a href="/roles/create" class="btn btn-success mb-3">Create Role</a>
        <table id="rolesTable" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td>
                            <?= esc($role['id']) ?>
                        </td>
                        <td>
                            <?= esc($role['name']) ?>
                        </td>
                        <td>
                            <?= esc($role['description']) ?>
                        </td>
                        <td>
                            <a href="/roles/edit/<?= $role['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="/roles/delete/<?= $role['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <script>
            $(document).ready(function () {
                $('#rolesTable').DataTable();
            });
        </script>
    </div>
</div>

<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>