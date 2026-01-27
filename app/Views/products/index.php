<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Products<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Products List</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('products/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">All Products</h3>
    </div>
    <div class="card-body">
        <!-- Filters Section -->
        <form action="<?= site_url('products') ?>" method="get" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Search Product / HSN</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name or HSN..." value="<?= esc($applied_filters['search']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $applied_filters['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                <?= $category['indented_name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" <?= $applied_filters['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $applied_filters['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="<?= site_url('products') ?>" class="btn btn-sm btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th style="width: 80px;">Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>HSN Code</th>
                        <th>Selling Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($products)): ?>
                        <?php foreach($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td>
                                <?php if($product['primary_image']): ?>
                                    <img src="<?= base_url($product['primary_image']) ?>" alt="Img" class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
                                <?php else: ?>
                                    <div class="bg-secondary text-white text-center p-1" style="width: 50px; height: 50px; font-size: 10px;">No Img</div>
                                <?php endif; ?>
                            </td>
                            <td><?= $product['product_name'] ?></td>
                            <td>
                                <small class="text-muted">
                                    <?= $category_paths[$product['category_id']] ?? $product['category_name'] ?>
                                </small>
                            </td>
                            <td><?= $product['hsn_code'] ?></td>
                            <td>₹<?= number_format($product['selling_price'], 2) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $product['total_stock'] > 0 ? 'info' : 'danger' ?>">
                                    <?= $product['total_stock'] ?> <?= $product['unit'] ?>
                                </span>
                            </td>
                            <td>Status: <?= ucfirst($product['status']) ?></td>
                            <td>
                                <a href="<?= site_url('products/view/'.$product['id']) ?>" class="btn btn-sm btn-info" title="Manage Quantity">
                                    <i class="fas fa-boxes"></i>
                                </a>
                                <a href="<?= site_url('products/edit/'.$product['id']) ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= site_url('products/delete/'.$product['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
