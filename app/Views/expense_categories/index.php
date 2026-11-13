<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Expense Categories<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-tags text-primary me-2"></i>Expense Head Categories</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('expense_categories') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('expenses') ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-receipt me-1"></i> View Expenses
            </a>
            <?php if (can('expense.create')): ?>
            <a href="<?= site_url('expense_categories/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Category
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalCats = count($categories ?? []);
$activeCount = 0;
$inactiveCount = 0;

if (!empty($categories)) {
    foreach ($categories as $c) {
        if (($c['status'] ?? '') === 'active') {
            $activeCount++;
        } else {
            $inactiveCount++;
        }
    }
}
?>

<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-4 col-sm-6">
        <a href="<?= site_url('expense_categories') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-tags bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalCats) ?></div>
            <div class="bt-stat-label">Total Expense Categories</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <a href="<?= site_url('expense_categories?status=active') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'active' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($activeCount) ?></div>
            <div class="bt-stat-label">Active Categories</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalCats > 0) ? min(100, round(($activeCount / $totalCats) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <a href="<?= site_url('expense_categories?status=inactive') ?>" class="bt-stat bt-g-slate text-decoration-none <?= ($filters['status'] ?? '') === 'inactive' ? 'active' : '' ?>">
            <i class="fas fa-pause-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($inactiveCount) ?></div>
            <div class="bt-stat-label">Inactive Categories</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalCats > 0) ? min(100, round(($inactiveCount / $totalCats) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Categories</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('expense_categories') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-7 col-sm-8">
                <label for="search" class="form-label">Search Name</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search category name..." value="<?= esc($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3 col-sm-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= (($filters['status'] ?? '') == 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= (($filters['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-12 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i> Search</button>
                <a href="<?= site_url('expense_categories') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Categories Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header py-2 bg-white">
        <h3 class="card-title fw-bold">Expense Heads & Categories</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 200px;">Category Name</th>
                        <th style="min-width: 250px;">Description</th>
                        <th class="text-center" style="min-width: 100px;">Status</th>
                        <th class="text-end" style="min-width: 110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($categories)): ?>
                        <?php foreach($categories as $index => $category): ?>
                        <tr>
                            <td class="text-muted fw-semibold"><?= $index + 1 ?></td>
                            <td>
                                <strong class="text-dark"><i class="fas fa-tag text-primary me-2"></i><?= esc($category['category_name']) ?></strong>
                            </td>
                            <td><?= esc($category['description'] ?: 'No description provided') ?></td>
                            <td class="text-center">
                                <span class="badge text-bg-<?= ($category['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($category['status'] ?? 'inactive') ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <?php if (can('expense.edit')): ?>
                                    <a href="<?= site_url('expense_categories/edit/'.$category['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (can('expense.delete')): ?>
                                    <a href="<?= site_url('expense_categories/delete/'.$category['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this expense category?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-tags fa-2x text-muted mb-2 d-block"></i>
                                No expense categories found matching your search.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
