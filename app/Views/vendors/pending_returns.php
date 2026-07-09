<?= $this->extend('layouts/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?= $title ?></h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('vendors') ?>">Vendors</a></li>
                <li class="breadcrumb-item active">Pending Returns</li>
            </ol>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-outline card-danger mb-3">
        <div class="card-header">
            <h3 class="card-title">Filter Returns</h3>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Vendor</label>
                    <select name="vendor_id" class="form-select select2">
                        <option value="">All Vendors</option>
                        <?php foreach ($vendors as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= ($filters['vendor_id'] == $v['id']) ? 'selected' : '' ?>>
                                <?= esc($v['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Return Status</label>
                    <select name="return_action" class="form-select">
                        <option value="Pending" <?= ($filters['return_action'] == 'Pending') ? 'selected' : '' ?>>Pending Action</option>
                        <option value="Returned" <?= ($filters['return_action'] == 'Returned') ? 'selected' : '' ?>>Returned</option>
                        <option value="Exchanged" <?= ($filters['return_action'] == 'Exchanged') ? 'selected' : '' ?>>Exchanged</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-search"></i> Filter</button>
                    <a href="<?= site_url('vendors/returns') ?>" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="card card-outline card-secondary">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Damaged/Rejected Image</th>
                            <th>Product Info</th>
                            <th>Vendor Info</th>
                            <th>Rejection Details</th>
                            <th>Current Action</th>
                            <th>Process</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No pending returns found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <?php if($item['rejection_image']): ?>
                                            <a href="<?= base_url($item['rejection_image']) ?>" target="_blank">
                                                <img src="<?= base_url($item['rejection_image']) ?>" width="60" height="60" class="img-thumbnail border-danger" title="Proof">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= esc($item['product_name']) ?></strong>
                                        <br>
                                        <small class="text-muted">Barcode: <?= $item['barcode'] ?></small>
                                        <?php if($item['purchase_price']): ?>
                                            <br><small class="text-danger">Cost: ₹<?= $item['purchase_price'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= esc($item['vendor_name']) ?>
                                        <br>
                                        <small class="text-muted">Inv: <?= $item['vendor_invoice_no'] ?></small>
                                    </td>
                                    <td>
                                        <strong><?= $item['rejected_by_name'] ?></strong>
                                        <p class="mb-0 text-danger small"><?= esc($item['rejection_reason']) ?></p>
                                        <small class="text-muted"><?= date('d/m/y H:i', strtotime($item['updated_at'])) ?></small>
                                    </td>
                                    <td>
                                        <?php if($item['return_action'] == 'Pending'): ?>
                                            <span class="badge text-bg-warning">Pending</span>
                                        <?php elseif($item['return_action'] == 'Returned'): ?>
                                            <span class="badge text-bg-primary">Returned to Vendor</span>
                                        <?php elseif($item['return_action'] == 'Exchanged'): ?>
                                            <span class="badge text-bg-success">Exchanged/Replaced</span>
                                            <br><small>Status: <?= ucfirst($item['status']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($item['return_action'] == 'Pending'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#processModal<?= $item['id'] ?>">
                                                Process Return
                                            </button>

                                            <!-- Process Modal -->
                                            <div class="modal fade" id="processModal<?= $item['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Process Return: <?= $item['barcode'] ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="<?= site_url('vendors/processReturn/'.$item['id']) ?>" method="post">
                                                            <?= csrf_field() ?>
                                                            <div class="modal-body">
                                                                <p>How was this rejection resolved with <strong><?= esc($item['vendor_name']) ?></strong>?</p>
                                                                
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input" type="radio" name="return_action" value="Returned" id="ret<?= $item['id'] ?>" required>
                                                                    <label class="form-check-label" for="ret<?= $item['id'] ?>">
                                                                        <strong>Returned for Refund/Credit</strong>
                                                                        <div class="text-muted small">The item was sent back. We are waiting for refund or credit note.</div>
                                                                    </label>
                                                                </div>

                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="return_action" value="Exchanged" id="exc<?= $item['id'] ?>">
                                                                    <label class="form-check-label" for="exc<?= $item['id'] ?>">
                                                                        <strong>Exchanged / Replaced</strong>
                                                                        <div class="text-muted small">Vendor provided a replacement item. (Ensure new item is added to inventory).</div>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang("App.cancel") ?></button>
                                                                <button type="submit" class="btn btn-primary">Update Status</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-success"><i class="fas fa-check"></i> Resolved</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
