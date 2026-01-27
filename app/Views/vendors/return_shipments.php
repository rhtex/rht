<?= $this->extend('layouts/master') ?>
<?= $this->section('title') ?>Return Log<?= $this->endSection() ?>
<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Return Shipments</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('vendors') ?>">Vendors</a></li>
            <li class="breadcrumb-item active">Return Shipments</li>
        </ol>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Return Shipments</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped" id="shipmentsTable">
            <thead>
                <tr>
                    <th>Ref #</th>
                    <th>Vendor</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Transport</th>
                    <th>Waybill / LR</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shipments as $shipment): ?>
                    <tr>
                        <td><?= esc($shipment['reference_no']) ?></td>
                        <td><?= esc($shipment['vendor_name']) ?></td>
                        <td><?= date('d M Y', strtotime($shipment['return_date'])) ?></td>
                        <td><?= $shipment['item_count'] ?></td>
                        <td><?= esc($shipment['transport_name']) ?></td>
                        <td>
                            <?php if($shipment['waybill_number']): ?>
                                <?= esc($shipment['waybill_number']) ?><br>
                                <small class="text-muted"><?= $shipment['waybill_date'] ? date('d M Y', strtotime($shipment['waybill_date'])) : '' ?></small>
                            <?php else: ?>
                                <span class="badge bg-warning">Missing LR</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($shipment['status'] == 'Pending'): ?>
                                <span class="badge bg-secondary">Pending</span>
                            <?php elseif($shipment['status'] == 'Shipped'): ?>
                                <span class="badge bg-info">Shipped</span>
                            <?php else: ?>
                                <span class="badge bg-success">Delivered</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $shipment['id'] ?>">
                                <i class="fas fa-edit"></i> Update LR
                            </button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?= $shipment['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="<?= site_url('vendors/returns/shipments/update/' . $shipment['id']) ?>" method="post">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Update Shipment Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Transport Name</label>
                                            <input type="text" name="transport_name" class="form-control" value="<?= esc($shipment['transport_name']) ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Waybill / LR Number</label>
                                            <input type="text" name="waybill_number" class="form-control" value="<?= esc($shipment['waybill_number']) ?>">
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Waybill Date</label>
                                                    <input type="date" name="waybill_date" class="form-control" value="<?= $shipment['waybill_date'] ?>">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-3">
                                                    <label class="form-label">E-Waybill No.</label>
                                                    <input type="text" name="ewaybill_number" class="form-control" value="<?= esc($shipment['ewaybill_number']) ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">No. of Packages</label>
                                            <input type="number" name="packages_count" class="form-control" value="<?= $shipment['packages_count'] ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="Pending" <?= $shipment['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Shipped" <?= $shipment['status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                                <option value="Delivered" <?= $shipment['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
