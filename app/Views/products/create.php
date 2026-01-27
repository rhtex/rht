<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Create Product<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Add New Product</h1>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= site_url('products/store') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="product_name" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Barcode (Scan to set)</label>
                        <input type="text" name="barcode" class="form-control" placeholder="Optional barcode for quick search">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= $category['indented_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">HSN Code</label>
                        <input type="text" name="hsn_code" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Unit (e.g., Pcs, Kg)</label>
                        <input type="text" name="unit" class="form-control" placeholder="Pcs">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Selling Price (₹)</label>
                        <input type="number" name="selling_price" class="form-control" step="0.01" value="0.00">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Default Tax</label>
                        <select name="tax_id" class="form-select">
                            <option value="">No Tax</option>
                            <?php foreach($taxes as $tax): ?>
                                <option value="<?= $tax['id'] ?>"><?= esc($tax['name']) ?> (<?= $tax['percentage'] ?>%)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Common Product Images (Thumbnails)</label>
                        <input type="file" name="product_images[]" class="form-control" multiple accept="image/*">
                        <small class="text-muted">You can select multiple images.</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Product</button>
            <a href="<?= site_url('products') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
