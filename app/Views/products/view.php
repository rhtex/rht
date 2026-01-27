<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>View Product<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= $product['product_name'] ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('products') ?>" class="btn btn-secondary">Back to List</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <!-- Product Summary -->
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Product Details</h3>
            </div>
            <div class="card-body">
                <p><strong>Category:</strong> <?= $product['category_name'] ?></p>
                <p><strong>HSN Code:</strong> <?= $product['hsn_code'] ?></p>
                <p><strong>Selling Price:</strong> ₹<?= number_format($product['selling_price'], 2) ?></p>
                <p><strong>Default Tax:</strong> <?= $product['tax_percentage'] ? $product['tax_percentage'] . '%' : 'No Tax' ?></p>
                <p><strong>Current Stock:</strong> <?= $product['total_stock'] ?> <?= $product['unit'] ?></p>
                <p><strong>Status:</strong> <?= ucfirst($product['status']) ?></p>
                <hr>
                <h6>Thumbnails</h6>
                <div class="row">
                    <?php if(!empty($images)): ?>
                        <?php foreach($images as $img): ?>
                        <div class="col-4 mb-2">
                            <img src="<?= base_url($img['image_path']) ?>" class="img-fluid rounded border shadow-sm">
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12"><p class="text-muted">No thumbnails uploaded.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Quantity (Individual Items) -->
    <div class="col-md-8">
        <!-- Add New Item Form -->
        <div class="card card-outline card-success mb-4">
            <div class="card-header">
                <h3 class="card-title">Add Individual Item (Increment Stock)</h3>
            </div>
            <div class="card-body">
                <form action="<?= site_url('products/addItem/'.$product['id']) ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Barcode (Unique for this unit)</label>
                            <input type="text" name="barcode" class="form-control" value="<?= $next_barcode ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Received Photo</label>
                            <input type="file" name="received_image" class="form-control" accept="image/*" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Purchase Price</label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control" value="<?= $last_item['purchase_price'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Selling Price</label>
                            <input type="number" step="0.01" name="selling_price" class="form-control" value="<?= $product['selling_price'] ?>" required>
                            <small class="text-muted">Defaults to product price.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vendor</label>
                            <select name="vendor_id" class="form-select">
                                <option value="">Select Vendor</option>
                                <?php foreach($vendors as $vendor): ?>
                                    <option value="<?= $vendor['id'] ?>" <?= (isset($last_item['vendor_id']) && $last_item['vendor_id'] == $vendor['id']) ? 'selected' : '' ?>>
                                        <?= $vendor['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vendor Invoice No</label>
                            <input type="text" name="vendor_invoice_no" class="form-control" value="<?= $last_item['vendor_invoice_no'] ?? '' ?>">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Add Item to Inventory</button>
                </form>
            </div>
        </div>

        <!-- Individual Items List -->
        <div class="card card-outline card-primary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Inventoried Units (Serialized Stock)</h3>
                <form method="get" class="d-flex gap-2 align-items-center">
                    <select name="status" class="form-select form-select-sm" style="width: auto;">
                        <option value="">All Statuses</option>
                        <option value="received" <?= ($filters['status'] == 'received') ? 'selected' : '' ?>>Received</option>
                        <option value="available" <?= ($filters['status'] == 'available') ? 'selected' : '' ?>>Available</option>
                        <option value="sold" <?= ($filters['status'] == 'sold') ? 'selected' : '' ?>>Sold</option>
                        <option value="damaged" <?= ($filters['status'] == 'damaged') ? 'selected' : '' ?>>Damaged</option>
                        <option value="returned" <?= ($filters['status'] == 'returned') ? 'selected' : '' ?>>Returned</option>
                        <option value="rejected" <?= ($filters['status'] == 'rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                    <select name="approval_status" class="form-select form-select-sm" style="width: auto;">
                        <option value="">All Approvals</option>
                        <option value="Pending" <?= ($filters['is_approved'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Approved" <?= ($filters['is_approved'] == 'Approved') ? 'selected' : '' ?>>Approved</option>
                        <option value="Rejected" <?= ($filters['is_approved'] == 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="<?= current_url() ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Photos (R / V)</th>
                                <th>Barcode</th>
                                <th>Price (P/S)</th>
                                <th>Vendor</th>
                                <th>Approved?</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($items)): ?>
                                <?php foreach($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <?php if($item['received_image']): ?>
                                                <a href="<?= base_url($item['received_image']) ?>" target="_blank">
                                                    <img src="<?= base_url($item['received_image']) ?>" width="40" height="40" class="img-thumbnail" title="Received">
                                                </a>
                                            <?php endif; ?>
                                            <?php if($item['verified_image']): ?>
                                                <a href="<?= base_url($item['verified_image']) ?>" target="_blank">
                                                    <img src="<?= base_url($item['verified_image']) ?>" width="40" height="40" class="img-thumbnail border-success" title="Verified">
                                                </a>
                                            <?php endif; ?>
                                            <?php if($item['rejection_image']): ?>
                                                <a href="<?= base_url($item['rejection_image']) ?>" target="_blank">
                                                    <img src="<?= base_url($item['rejection_image']) ?>" width="40" height="40" class="img-thumbnail border-danger" title="<?= ($item['status'] == 'damaged') ? 'Damage Proof' : 'Rejection Reason' ?>">
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><code><?= $item['barcode'] ?></code></td>
                                    <td><?= $item['purchase_price'] ?> / <?= $item['selling_price'] ?></td>
                                    <td>
                                        <?= $item['vendor_name'] ?: '-' ?><br>
                                        <small><?= $item['vendor_invoice_no'] ?></small>
                                    </td>
                                    <td>
                                        <?php if($item['status'] == 'damaged'): ?>
                                             <span class="badge text-bg-warning"><i class="fas fa-exclamation-triangle"></i> Damaged</span>
                                             <br><small class="text-muted">Reason: <?= $item['rejection_reason'] ?></small>
                                             <?php if($item['damage_price']): ?>
                                                 <br><small class="text-danger fw-bold">Loss: ₹<?= number_format($item['damage_price'], 2) ?></small>
                                             <?php endif; ?>
                                        <?php elseif($item['is_approved'] == 'Approved'): ?>
                                            <span class="badge text-bg-success"><i class="fas fa-check-circle"></i> Approved</span>
                                            <br><small class="text-muted"><?= $item['approver_name'] ?></small>
                                            <?php if($item['approved_at']): ?>
                                                <br><small class="text-muted"><?= date('d/m/y H:i', strtotime($item['approved_at'])) ?></small>
                                            <?php endif; ?>
                                        <?php elseif($item['is_approved'] == 'Rejected'): ?>
                                            <span class="badge text-bg-danger"><i class="fas fa-times-circle"></i> Rejected</span>
                                            <br><small class="text-muted"><?= $item['approver_name'] ?></small>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-<?= $item['status'] == 'available' ? 'success' : ($item['status'] == 'sold' ? 'info' : 'danger') ?>">
                                            <?= ucfirst($item['status']) ?>
                                        </span>
                                        <?php if($item['is_damaged'] == 'Yes'): ?>
                                            <span class="badge text-bg-warning">Damaged</span>
                                        <?php endif; ?>
                                        <br><small class="text-muted">By: <?= $item['creator_name'] ?></small>
                                    </td>
                                    <td>
                                    <td>
                                        <?php if($item['is_approved'] == 'Pending'): ?>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-xs btn-outline-success" data-bs-toggle="modal" data-bs-target="#approveModal<?= $item['id'] ?>">
                                                    Verify
                                                </button>
                                                <button type="button" class="btn btn-xs btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $item['id'] ?>">
                                                    Reject
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
                                                            <div class="modal-body text-start">
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
                                                            <div class="modal-body text-start">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Reason for Rejection</label>
                                                                    <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Damage/Rejection Image</label>
                                                                    <input type="file" name="rejection_image" class="form-control" accept="image/*" required>
                                                                    <small class="text-muted">Currently mandatory for rejections.</small>
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
                                        <?php elseif($item['is_approved'] == 'Rejected'): ?>
                                             <small class="text-danger">Reason: <?= $item['rejection_reason'] ?></small>
                                        <?php elseif($item['status'] == 'available'): ?>
                                            <button type="button" class="btn btn-xs btn-outline-warning" data-bs-toggle="modal" data-bs-target="#damageModal<?= $item['id'] ?>">
                                                Mark Damaged
                                            </button>

                                            <!-- Damage Modal -->
                                            <div class="modal fade" id="damageModal<?= $item['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-warning">
                                                            <h5 class="modal-title">Mark Item Damaged: <?= $item['barcode'] ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="<?= site_url('products/markDamaged/'.$item['id']) ?>" method="post" enctype="multipart/form-data">
                                                            <?= csrf_field() ?>
                                                            <div class="modal-body text-start">
                                                                <p class="text-danger">This will remove the item from available inventory.</p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Damage Reason / Remarks</label>
                                                                    <textarea name="damage_reason" class="form-control" rows="3" required></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Est. Damage Cost / Loss Price (₹)</label>
                                                                    <input type="number" name="damage_price" class="form-control" step="0.01" min="0" placeholder="0.00">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Damage Photo</label>
                                                                    <input type="file" name="damage_image" class="form-control" accept="image/*" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-warning">Confirm Damage</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">No individual items tracked yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
