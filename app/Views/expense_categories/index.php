<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Expense Categories<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Expense Categories</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('expense_categories/create') ?>" class="btn btn-primary float-sm-end">
            <i class="fas fa-plus"></i> Add New Category
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter me-1"></i> Filters</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('expense_categories') ?>" method="get" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Search Name</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search category name..." value="<?= $filters['search'] ?>">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= $filters['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $filters['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                    <a href="<?= site_url('expense_categories') ?>" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Categories</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($categories)): ?>
                    <?php foreach($categories as $category): ?>
                    <tr>
                        <td><?= $category['id'] ?></td>
                        <td><?= esc($category['category_name']) ?></td>
                        <td><?= esc($category['description']) ?></td>
                        <td>
                            <span class="badge bg-<?= $category['status'] === 'active' ? 'success' : 'danger' ?>">
                                <?= ucfirst($category['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= site_url('expense_categories/edit/'.$category['id']) ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= site_url('expense_categories/delete/'.$category['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No categories found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
