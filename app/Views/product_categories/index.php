<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Product Categories<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Product Categories</h1>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Add New Category</h3>
            </div>
            <div class="card-body">
                <form action="<?= site_url('product_categories/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="category_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Category (Optional)</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None (Root)</option>
                            <?php foreach($categoryTree as $cat): ?>
                                <?php if($cat['level'] < 3): // Only allow up to 3 levels to be parents ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['indented_name'] ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">You can nest categories up to 4 levels deep.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Category</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Existing Categories</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Hierarchy (Steps)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($categories as $category): ?>
                            <tr>
                                <td>
                                    <?= str_repeat('<span class="ms-3"></span>', $category['level']) ?>
                                    <i class="fas fa-folder-open text-warning me-1"></i>
                                    <strong><?= $category['category_name'] ?></strong>
                                </td>
                                <td>
                                    <small class="text-muted"><?= $categoryPaths[$category['id']] ?></small>
                                </td>
                                <td>
                                    <span class="badge text-bg-<?= $category['status'] == 'active' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($category['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= site_url('product_categories/edit/'.$category['id']) ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= site_url('product_categories/delete/'.$category['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
