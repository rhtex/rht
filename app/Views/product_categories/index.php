<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Product Categories<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-sitemap text-primary me-2"></i>Product Hierarchy & Categories</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('product_categories') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('products') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-boxes me-1"></i> View Products
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalCats = count($categories ?? []);
$rootCount = 0;
$subCount = 0;

if (!empty($categories)) {
    foreach ($categories as $c) {
        if (empty($c['parent_id'])) {
            $rootCount++;
        } else {
            $subCount++;
        }
    }
}
?>

<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-4 col-sm-6">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-folder-tree bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalCats) ?></div>
            <div class="bt-stat-label">Total Categories</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-folder bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($rootCount) ?></div>
            <div class="bt-stat-label">Root Level Categories</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalCats > 0) ? min(100, round(($rootCount / $totalCats) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="bt-stat bt-g-teal" style="cursor:default;">
            <i class="fas fa-folder-open bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($subCount) ?></div>
            <div class="bt-stat-label">Sub Categories (Nested)</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalCats > 0) ? min(100, round(($subCount / $totalCats) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-primary shadow-sm mb-3">
            <div class="card-header py-2 bg-white">
                <h3 class="card-title fw-bold"><i class="fas fa-plus me-1 text-primary"></i> Create Category</h3>
            </div>
            <div class="card-body">
                <form action="<?= site_url('product_categories/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category Name</label>
                        <input type="text" name="category_name" class="form-control" placeholder="e.g. Silk Sarees" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Parent Category (Optional)</label>
                        <select name="parent_id" class="form-select select2">
                            <option value="">None (Top-Level Root)</option>
                            <?php foreach($categoryTree as $cat): ?>
                                <?php if($cat['level'] < 3): ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['indented_name'] ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted d-block mt-1">You can nest categories up to 4 levels deep.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Category brief..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 shadow-sm"><i class="fas fa-save me-1"></i> Save Category</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header py-2 bg-white">
                <h3 class="card-title fw-bold">Product Category Hierarchy</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 200px;">Category Name</th>
                                <th style="min-width: 220px;">Breadcrumb Path</th>
                                <th class="text-center" style="min-width: 100px;">Status</th>
                                <th class="text-end" style="min-width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($categories)): ?>
                                <?php foreach($categories as $category): ?>
                                <tr>
                                    <td>
                                        <?= str_repeat('<span class="ms-3"></span>', $category['level']) ?>
                                        <i class="fas fa-folder<?= $category['level'] > 0 ? '-open' : '' ?> text-warning me-1"></i>
                                        <strong class="text-dark"><?= esc($category['category_name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border">
                                            <?= esc($categoryPaths[$category['id']] ?? $category['category_name']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge text-bg-<?= ($category['status'] ?? '') == 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($category['status'] ?? 'inactive') ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('product_categories/edit/'.$category['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= site_url('product_categories/delete/'.$category['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this category?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-folder-tree fa-2x text-muted mb-2 d-block"></i>
                                        No categories defined yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
