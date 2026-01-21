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
