<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= $order ? 'Edit Customer Order' : 'New Customer Order' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><?= $order ? 'Edit Customer Order' : 'New Customer Order' ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('customer_orders') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-lg-8">
        <div class="card card-outline card-primary">
            <form action="<?= site_url($order ? 'customer_orders/update/' . $order['id'] : 'customer_orders/store') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select select2" required>
                                <option value="">-- Select Customer --</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ((int) old('customer_id', $order['customer_id'] ?? 0) === (int) $c['id']) ? 'selected' : '' ?>>
                                        <?= esc($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Order Date <span class="text-danger">*</span></label>
                            <input type="date" name="order_date" class="form-control" value="<?= esc(old('order_date', $order['order_date'] ?? date('Y-m-d'))) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Select Existing Product (Optional)</label>
                            <select name="product_id" id="coProductSelect" class="form-select select2">
                                <option value="">-- Custom / Manual Product Name --</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>"
                                            data-name="<?= esc($p['product_name']) ?>"
                                            data-rate="<?= esc($p['selling_price']) ?>"
                                            <?= ((int) old('product_id', $order['product_id'] ?? 0) === (int) $p['id']) ? 'selected' : '' ?>>
                                        <?= esc($p['product_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="product_name" id="coProductName" class="form-control" value="<?= esc(old('product_name', $order['product_name'] ?? '')) ?>" required placeholder="Enter product name">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Order Design No <span class="text-danger">*</span></label>
                            <input type="text" name="design_no" class="form-control" value="<?= esc(old('design_no', $order['design_no'] ?? '')) ?>" required placeholder="e.g. D-101">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Rate (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="rate" id="coRate" class="form-control text-end" step="0.01" min="0" value="<?= esc(old('rate', $order['rate'] ?? '0.00')) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php $curStatus = old('status', $order['status'] ?? 'Pending'); ?>
                                <?php foreach ($statuses as $st): ?>
                                    <option value="<?= esc($st) ?>" <?= $curStatus === $st ? 'selected' : '' ?>><?= esc($st) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Design Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <?php if (!empty($order['image']) && file_exists(FCPATH . $order['image'])): ?>
                                <div class="mt-2">
                                    <img src="<?= base_url($order['image']) ?>" alt="Current Design" style="max-height: 90px; border: 1px solid #ddd; border-radius: 4px; padding: 2px;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Any design or color notes..."><?= esc(old('notes', $order['notes'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> <?= $order ? 'Update Order' : 'Save Order' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    $('.select2').select2({ theme: 'bootstrap-5' });
    $('#coProductSelect').on('change', function() {
        const opt = $(this).find('option:selected');
        if (opt.val()) {
            $('#coProductName').val(opt.data('name'));
            if (parseFloat($('#coRate').val()) === 0 || !$('#coRate').val()) {
                $('#coRate').val(opt.data('rate') || '0.00');
            }
        }
    });
});
</script>
<?= $this->endSection() ?>
