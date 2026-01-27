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
                <li class="breadcrumb-item"><a href="<?= base_url('products') ?>">Products</a></li>
                <li class="breadcrumb-item active">Approvals</li>
            </ol>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title">Filter Approvals</h3>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Barcode</label>
                    <input type="text" name="barcode" class="form-control" value="<?= esc($filters['barcode']) ?>" placeholder="Scan or type barcode">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="is_approved" class="form-select">
                        <option value="Pending" <?= ($filters['is_approved'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Approved" <?= ($filters['is_approved'] == 'Approved') ? 'selected' : '' ?>>Approved</option>
                        <option value="Rejected" <?= ($filters['is_approved'] == 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="<?= site_url('products/approvals') ?>" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Approvals Table -->
    <div class="card card-outline card-info">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Image</th>
                            <th>Product Details</th>
                            <th>Barcode</th>
                            <th>Status Details</th>
                            <th>Creator</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No items found matching criteria.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <?php if($item['received_image']): ?>
                                            <a href="<?= base_url($item['received_image']) ?>" target="_blank">
                                                <img src="<?= base_url($item['received_image']) ?>" width="50" height="50" class="img-thumbnail" title="Received">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= esc($item['product_name']) ?></strong>
                                        <br>
                                        <small class="text-muted">ID: <?= $item['product_id'] ?></small>
                                    </td>
                                    <td><code><?= $item['barcode'] ?></code></td>
                                    <td>
                                        <?php if($item['is_approved'] == 'Pending'): ?>
                                            <span class="badge text-bg-secondary">Pending Verification</span>
                                            <br><small class="text-muted">Status: <?= ucfirst($item['status']) ?></small>
                                        <?php elseif($item['is_approved'] == 'Approved'): ?>
                                            <span class="badge text-bg-success">Approved</span>
                                        <?php elseif($item['is_approved'] == 'Rejected'): ?>
                                            <span class="badge text-bg-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $item['creator_name'] ?>
                                        <br><small class="text-muted"><?= date('d/m/y H:i', strtotime($item['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <?php if($item['is_approved'] == 'Pending'): ?>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal<?= $item['id'] ?>">
                                                    <i class="fas fa-check"></i> Verify
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $item['id'] ?>">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>

                                            <!-- Approval Modal -->
                                            <div class="modal fade" id="approveModal<?= $item['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Verify Item: <?= $item['barcode'] ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="<?= site_url('products/approveItem/'.$item['id']) ?>" method="post" enctype="multipart/form-data">
                                                            <?= csrf_field() ?>
                                                            <div class="modal-body">
                                                                <p>Please upload the <strong>Verification Image</strong> to approve this quantity.</p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Verified Photo</label>
                                                                    <input type="file" name="verified_image" class="form-control" accept="image/*" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-success">Approve Stock</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal<?= $item['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">Reject Item: <?= $item['barcode'] ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="<?= site_url('products/rejectItem/'.$item['id']) ?>" method="post" enctype="multipart/form-data">
                                                            <?= csrf_field() ?>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Reason for Rejection</label>
                                                                    <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Damage/Rejection Image</label>
                                                                    <input type="file" name="rejection_image" class="form-control" accept="image/*" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <a href="<?= site_url('products/view/'.$item['product_id']) ?>" class="btn btn-sm btn-outline-primary">View Product</a>
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
