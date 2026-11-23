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
                <input type="text" name="search" class="form-control form-control-sm" value="<?= esc($search ?? '') ?>" placeholder="Order No, Customer, Product, Design...">
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
                <label class="form-label form-label-sm mb-1">Order Status</label>
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
                        <th>Order Number</th>
                        <th>Order Date</th>
                        <th>Customer Name</th>
                        <th class="text-center">Design Count</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $o): ?>
                            <?php $ordNo = $o['order_number'] ?: ('CO-' . str_pad((string) $o['id'], 4, '0', STR_PAD_LEFT)); ?>
                            <tr>
                                <td class="fw-bold">
                                    <a href="<?= site_url('customer_orders/view/' . $o['id']) ?>" class="text-decoration-none"><?= esc($ordNo) ?></a>
                                </td>
                                <td><?= !empty($o['order_date']) ? date('d-m-Y', strtotime($o['order_date'])) : '-' ?></td>
                                <td class="fw-bold"><?= esc($o['customer_name'] ?? '-') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark"><?= (int) ($o['design_count'] ?? 0) ?></span>
                                </td>
                                <td class="text-center">
                                    <?php $isClosed = ($o['status'] === 'Closed'); ?>
                                    <span class="badge <?= $isClosed ? 'bg-secondary' : 'bg-success' ?>"><?= esc($o['status'] ?: 'Active') ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= site_url('customer_orders/view/' . $o['id']) ?>" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="View Order Designs"><i class="fas fa-eye"></i></a>
                                    <a href="<?= site_url('customer_orders/toggle-status/' . $o['id']) ?>" class="btn btn-sm btn-outline-<?= $isClosed ? 'success' : 'warning' ?>" data-bs-toggle="tooltip" title="<?= $isClosed ? 'Reopen Order (Set Active)' : 'Close Order' ?>"><i class="fas fa-<?= $isClosed ? 'lock-open' : 'lock' ?>"></i></a>
                                    <a href="<?= site_url('customer_orders/edit/' . $o['id']) ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit Order"><i class="fas fa-edit"></i></a>
                                    <a href="<?= site_url('customer_orders/delete/' . $o['id']) ?>" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Delete Order" onclick="return confirm('Delete this customer order and all its designs?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No customer orders found.</td>
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
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
});
</script>
<?= $this->endSection() ?>
