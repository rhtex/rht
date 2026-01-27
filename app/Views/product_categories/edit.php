<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Edit Category<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Edit Category: <?= $category['category_name'] ?></h1>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title">Update Category Information</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('product_categories/update/'.$category['id']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Category Name</label>
                <input type="text" name="category_name" class="form-control" value="<?= $category['category_name'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Parent Category</label>
                <select name="parent_id" class="form-select">
                    <option value="">None (Root)</option>
                    <?php foreach($categoryTree as $cat): ?>
                        <?php 
                            // Don't allow selecting itself or its children as parent
                            // For simplicity in this step, just exclude itself. 
                            // Level check: Level 0, 1, 2 can be parents (results in Level 1, 2, 3)
                            if($cat['id'] != $category['id'] && $cat['level'] < 3): 
                        ?>
                            <option value="<?= $cat['id'] ?>" <?= $category['parent_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= $cat['indented_name'] ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= $category['description'] ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active" <?= $category['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $category['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-warning">Update Category</button>
            <a href="<?= site_url('product_categories') ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
