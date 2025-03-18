<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    
        <h1 class="mb-4">Edit Module</h1>
        <form action="/modules/update/<?= $module['id'] ?>" method="post">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="<?= esc($module['name']) ?>"
                    required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description"
                    class="form-control"><?= esc($module['description']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-success">Save</button>
        </form>
    </div>
</div>

<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>