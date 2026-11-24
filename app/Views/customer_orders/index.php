<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Customer Orders<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
    <h1 class="m-0 fs-4"><i class="fas fa-swatchbook text-primary me-2"></i>Customer Orders</h1>
    <a href="<?= site_url('customer_orders/create') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> New Customer Order
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .mobile-order-card {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 10px;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
</style>

<div class="card card-outline card-primary">
    <div class="card-header">
        <form method="get" action="<?= site_url('customer_orders') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label form-label-sm mb-1">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" value="<?= esc($search ?? '') ?>" placeholder="Order No, Customer, Product, Design...">
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label form-label-sm mb-1">Customer</label>
                <select name="customer_id" class="form-select form-select-sm select2">
                    <option value="">All Customers</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ((int) ($customerId ?? 0) === (int) $c['id']) ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-sm-6 col-md-2">
                <label class="form-label form-label-sm mb-1">Order Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?= esc($st) ?>" <?= (($status ?? '') === $st) ? 'selected' : '' ?>><?= esc($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill flex-md-grow-0"><i class="fas fa-filter me-1"></i> Filter</button>
                <a href="<?= site_url('customer_orders') ?>" class="btn btn-sm btn-outline-secondary flex-fill flex-md-grow-0">Reset</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <!-- Desktop / Tablet Table -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order Number</th>
                        <th>Order Date</th>
                        <th>Customer Name</th>
                        <th class="text-center">Design Count</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 170px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $o): ?>
                            <?php
                            $ordNo    = $o['order_number'] ?: ('CO-' . str_pad((string) $o['id'], 4, '0', STR_PAD_LEFT));
                            $isClosed = ($o['status'] === 'Closed');
                            ?>
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

        <!-- Mobile Card List -->
        <div class="d-md-none p-2 bg-light">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $o): ?>
                    <?php
                    $ordNo    = $o['order_number'] ?: ('CO-' . str_pad((string) $o['id'], 4, '0', STR_PAD_LEFT));
                    $isClosed = ($o['status'] === 'Closed');
                    ?>
                    <div class="mobile-order-card">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <a href="<?= site_url('customer_orders/view/' . $o['id']) ?>" class="fw-bold fs-6 text-decoration-none">
                                <?= esc($ordNo) ?>
                            </a>
                            <span class="badge <?= $isClosed ? 'bg-secondary' : 'bg-success' ?>"><?= esc($o['status'] ?: 'Active') ?></span>
                        </div>

                        <div class="fw-bold text-dark mb-1"><?= esc($o['customer_name'] ?? '-') ?></div>

                        <div class="d-flex justify-content-between align-items-center text-muted small mb-2">
                            <span><i class="far fa-calendar-alt me-1"></i><?= !empty($o['order_date']) ? date('d-m-Y', strtotime($o['order_date'])) : '-' ?></span>
                            <span class="badge bg-info text-dark"><?= (int) ($o['design_count'] ?? 0) ?> Designs</span>
                        </div>

                        <div class="d-flex gap-2 pt-2 border-top">
                            <a href="<?= site_url('customer_orders/view/' . $o['id']) ?>" class="btn btn-sm btn-outline-info flex-fill" title="View Designs">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="<?= site_url('customer_orders/toggle-status/' . $o['id']) ?>" class="btn btn-sm btn-outline-<?= $isClosed ? 'success' : 'warning' ?> flex-fill" title="<?= $isClosed ? 'Reopen Order' : 'Close Order' ?>">
                                <i class="fas fa-<?= $isClosed ? 'lock-open' : 'lock' ?>"></i> <?= $isClosed ? 'Reopen' : 'Close' ?>
                            </a>
                            <a href="<?= site_url('customer_orders/edit/' . $o['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit Order">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= site_url('customer_orders/delete/' . $o['id']) ?>" class="btn btn-sm btn-outline-danger" title="Delete Order" onclick="return confirm('Delete this customer order and all its designs?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">No customer orders found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
});
</script>
<?= $this->endSection() ?>
