<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Customer Orders<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-swatchbook text-primary me-2"></i>Customer Orders</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('customer_orders/create') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> New Customer Order
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <form method="get" action="<?= site_url('customer_orders') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-1">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" value="<?= esc($search ?? '') ?>" placeholder="Design No, Product, Customer...">
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-1">Customer</label>
                <select name="customer_id" class="form-select form-select-sm select2">
                    <option value="">All Customers</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ((int) ($customerId ?? 0) === (int) $c['id']) ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label form-label-sm mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?= esc($st) ?>" <?= (($status ?? '') === $st) ? 'selected' : '' ?>><?= esc($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
                <a href="<?= site_url('customer_orders') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">Image</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Order Design No</th>
                        <th>Product Name</th>
                        <th class="text-end">Rate (₹)</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($o['image']) && file_exists(FCPATH . $o['image'])): ?>
                                        <a href="<?= base_url($o['image']) ?>" target="_blank">
                                            <img src="<?= base_url($o['image']) ?>" alt="Design" style="width:48px;height:48px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;">
                                        </a>
                                    <?php else: ?>
                                        <div class="bg-light text-muted d-flex align-items-center justify-content-center" style="width:48px;height:48px;border-radius:4px;border:1px solid #dee2e6;font-size:11px;">No Img</div>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($o['order_date']) ? date('d-m-Y', strtotime($o['order_date'])) : '-' ?></td>
                                <td class="fw-bold"><?= esc($o['customer_name'] ?? '-') ?></td>
                                <td><span class="badge bg-info text-dark"><?= esc($o['design_no']) ?></span></td>
                                <td><?= esc($o['product_name']) ?></td>
                                <td class="text-end fw-bold"><?= number_format((float) $o['rate'], 2) ?></td>
                                <td>
                                    <?php
                                    $badge = match ($o['status']) {
                                        'Completed', 'Ready' => 'bg-success',
                                        'In Progress'        => 'bg-primary',
                                        'Cancelled'          => 'bg-danger',
                                        default              => 'bg-warning text-dark',
                                    };
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= esc($o['status']) ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= site_url('customer_orders/edit/' . $o['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= site_url('customer_orders/delete/' . $o['id']) ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this customer order?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No customer orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    $('.select2').select2({ theme: 'bootstrap-5' });
});
</script>
<?= $this->endSection() ?>
