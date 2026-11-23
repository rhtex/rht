<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Customer Order <?= esc($order['order_number'] ?? '') ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-swatchbook text-primary me-2"></i>
            <?= esc($order['order_number'] ?: ('CO-' . str_pad((string) $order['id'], 4, '0', STR_PAD_LEFT))) ?>
            <span class="badge <?= ($order['status'] === 'Closed') ? 'bg-secondary' : 'bg-success' ?> fs-6 ms-2"><?= esc($order['status'] ?: 'Active') ?></span>
        </h1>
    </div>
    <div class="col-sm-6 text-end d-flex justify-content-end gap-2">
        <?php $isClosed = ($order['status'] === 'Closed'); ?>
        <a href="<?= site_url('customer_orders/toggle-status/' . $order['id']) ?>" class="btn btn-sm btn-<?= $isClosed ? 'success' : 'warning' ?>" title="<?= $isClosed ? 'Reopen Order' : 'Close Order' ?>">
            <i class="fas fa-<?= $isClosed ? 'lock-open' : 'lock' ?> me-1"></i> <?= $isClosed ? 'Reopen Order' : 'Close Order' ?>
        </a>
        <a href="<?= site_url('customer_orders/edit/' . $order['id']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit me-1"></i> Edit</a>
        <a href="<?= site_url('customer_orders') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <small class="text-muted d-block">Customer Name</small>
                <strong class="fs-6"><?= esc($order['customer_name'] ?? '-') ?></strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Order Date</small>
                <strong><?= !empty($order['order_date']) ? date('d-m-Y', strtotime($order['order_date'])) : '-' ?></strong>
            </div>
            <div class="col-md-2">
                <small class="text-muted d-block">Order Status</small>
                <span class="badge <?= $isClosed ? 'bg-secondary' : 'bg-success' ?>"><?= esc($order['status'] ?: 'Active') ?></span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Total Designs</small>
                <strong><?= count($order['designs'] ?? []) ?></strong>
            </div>
            <?php if (!empty($order['notes'])): ?>
                <div class="col-12">
                    <small class="text-muted d-block">Notes</small>
                    <div><?= nl2br(esc($order['notes'])) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h6 class="m-0 fw-bold">Design Line Items</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 100px;">Design No.</th>
                        <th>Product Name</th>
                        <th class="text-center" style="width: 110px;">Image</th>
                        <th class="text-end" style="width: 140px;">Rate (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($order['designs'])): ?>
                        <?php foreach ($order['designs'] as $d): ?>
                            <tr>
                                <td class="text-center fw-bold"><span class="badge bg-info text-dark fs-6"><?= (int) $d['design_no'] ?></span></td>
                                <td class="fw-bold"><?= esc($d['product_name']) ?></td>
                                <td class="text-center">
                                    <?php if (!empty($d['image']) && file_exists(FCPATH . $d['image'])): ?>
                                        <a href="<?= base_url($d['image']) ?>" target="_blank">
                                            <img src="<?= base_url($d['image']) ?>" alt="Design <?= (int) $d['design_no'] ?>" style="width:52px;height:52px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold">₹<?= number_format((float) $d['rate'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-3 text-muted">No designs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
