<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Products<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-boxes text-primary me-2"></i>Product Catalog</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('products') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('products/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Product
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('products') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($applied_filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-cubes bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($products)) ?></div>
            <div class="bt-stat-label">Total Products</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('products?status=active') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($applied_filters['status'] ?? '') === 'active' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['active'] ?? 0) ?></div>
            <div class="bt-stat-label">Active Products</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['active'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('products?status=inactive') ?>" class="bt-stat bt-g-slate text-decoration-none <?= ($applied_filters['status'] ?? '') === 'inactive' ? 'active' : '' ?>">
            <i class="fas fa-pause-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['inactive'] ?? 0) ?></div>
            <div class="bt-stat-label">Inactive Products</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['inactive'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-red" style="cursor:default;">
            <i class="fas fa-exclamation-triangle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['out_stock'] ?? 0) ?></div>
            <div class="bt-stat-label">Out of Stock</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['out_stock'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Products</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('products') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-4 col-sm-12">
                <label class="form-label">Search Product / HSN</label>
                <input type="text" name="search" class="form-control" placeholder="Search product name or HSN code..." value="<?= esc($applied_filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-4 col-sm-6">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select select2">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= ($applied_filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                            <?= $category['indented_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($applied_filters['status'] ?? '') == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($applied_filters['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i></button>
                <a href="<?= site_url('products') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th style="width: 70px;">Image</th>
                        <th style="min-width: 220px;">Product Name</th>
                        <th style="min-width: 160px;">Category</th>
                        <th style="min-width: 100px;">HSN Code</th>
                        <th class="text-end" style="min-width: 120px;">Selling Price</th>
                        <th class="text-center" style="min-width: 110px;">Stock</th>
                        <th class="text-center" style="min-width: 100px;">Status</th>
                        <th class="text-end" style="min-width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($products)): ?>
                        <?php foreach($products as $product): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?= $product['id'] ?></td>
                            <td>
                                <?php if(!empty($product['primary_image'])): ?>
                                    <img src="<?= base_url($product['primary_image']) ?>" alt="<?= esc($product['product_name']) ?>" class="rounded-3 shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-3 bg-light text-muted d-flex align-items-center justify-content-center border" style="width: 44px; height: 44px; font-size: 18px;">
                                        <i class="fas fa-image opacity-50"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= site_url('products/view/' . $product['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                    <?= esc($product['product_name']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border small">
                                    <i class="fas fa-folder me-1 text-warning"></i>
                                    <?= esc($category_paths[$product['category_id']] ?? $product['category_name'] ?? 'Uncategorized') ?>
                                </span>
                            </td>
                            <td><code class="text-secondary"><?= esc($product['hsn_code'] ?: '-') ?></code></td>
                            <td class="text-end fw-bold text-dark">₹<?= number_format($product['selling_price'] ?? 0, 2) ?></td>
                            <td class="text-center">
                                <?php $stock = (float)($product['total_stock'] ?? 0); ?>
                                <span class="badge <?= $stock > 0 ? 'text-bg-success' : 'text-bg-danger' ?> px-2 py-1">
                                    <?= $stock ?> <?= esc($product['unit'] ?? '') ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if(($product['status'] ?? '') === 'active'): ?>
                                    <span class="badge text-bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary"><i class="fas fa-ban me-1"></i>Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('products/view/'.$product['id']) ?>" class="btn btn-outline-primary" title="Manage Stock">
                                        <i class="fas fa-boxes"></i>
                                    </a>
                                    <a href="<?= site_url('products/edit/'.$product['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= site_url('products/delete/'.$product['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this product?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-boxes fa-2x text-muted mb-2 d-block"></i>
                                No products found matching your search.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
