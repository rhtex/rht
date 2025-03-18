<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    
        <h1 class="mb-4">Modules</h1>
        <a href="/modules/create" class="btn btn-success mb-3">Create Module</a>
        <table id="modulesTable" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modules as $module): ?>
                    <tr>
                        <td>
                            <?= esc($module['id']) ?>
                        </td>
                        <td>
                            <?= esc($module['name']) ?>
                        </td>
                        <td>
                            <?= esc($module['description']) ?>
                        </td>
                        <td>
                            <a href="/modules/edit/<?= $module['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="/modules/delete/<?= $module['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <script>
            $(document).ready(function () {
                $('#modulesTable').DataTable();
            });
        </script>
    </div>
</div>

<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>