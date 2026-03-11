<?= $this->extend('layouts/master') ?>
<?= $this->section('title') ?>Return Shipment Tracking<?= $this->endSection() ?>
<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Return Shipment Tracking</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('vendors') ?>">Vendors</a></li>
            <li class="breadcrumb-item active">Tracking</li>
        </ol>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Filters -->
<div class="card card-outline card-info mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter me-2"></i>Filters</h3>
    </div>
    <div class="card-body">
        <form action="" method="get" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Vendor</label>
                <select name="vendor_id" class="form-select select2">
                    <option value="">All Vendors</option>
                    <?php foreach($vendors as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= (strval($filters['vendor_id']) === strval($v['id'])) ? 'selected' : '' ?>><?= esc($v['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Delivery Status</label>
                <select name="delivery_status" class="form-select">
                    <option value="">All statuses</option>
                    <option value="Pending" <?= ($filters['delivery_status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="In Transit" <?= ($filters['delivery_status'] == 'In Transit') ? 'selected' : '' ?>>In Transit</option>
                    <option value="Completed" <?= ($filters['delivery_status'] == 'Completed') ? 'selected' : '' ?>>Completed</option>
                    <option value="Cancelled" <?= ($filters['delivery_status'] == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Search Ref #</label>
                <input type="text" name="search" class="form-control" value="<?= esc($filters['search']) ?>" placeholder="Search...">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="<?= site_url('vendors/returns/shipments') ?>" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Vendor Return Log (Total: <?= $total_count ?>)</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ref #</th>
                        <th>Vendor</th>
                        <th>Date</th>
                        <th>Transport</th>
                        <th>Waybill Info</th>
                        <th>Delivery Status</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
                <tbody id="tracking-tbody">
                    <?php if (empty($shipments)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No shipments found!</td>
                        </tr>
                    <?php else: ?>
                        <?= view('vendors/return_shipment_rows', ['shipments' => $shipments]) ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($has_more): ?>
    <div class="card-footer text-center">
        <button id="load-more-btn" class="btn btn-outline-primary" data-offset="<?= $offset + $limit ?>">
            View More <i class="fas fa-chevron-down ms-2"></i>
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- Waybill Modal -->
<div class="modal fade" id="waybillModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="waybillForm" action="" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Waybill Details: <span id="waybillRef"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Transport Name <span class="text-danger">*</span></label>
                        <input type="text" name="transport_name" id="modalTransport" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Waybill / LR Number <span class="text-danger">*</span></label>
                        <input type="text" name="waybill_number" id="modalLR" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Waybill Date <span class="text-danger">*</span></label>
                                <input type="date" name="waybill_date" id="modalDate" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">No. of Packages</label>
                                <input type="number" name="packages_count" id="modalPackages" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Waybill Image / Photo</label>
                        <input type="file" name="waybill_image" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Waybill</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delivery Modal -->
<div class="modal fade" id="deliveryModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="deliveryForm" action="" method="post">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delivery Status: <span id="deliveryRef"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Status <span class="text-danger">*</span></label>
                        <select name="delivery_status" id="modalStatus" class="form-select" required>
                            <option value="Pending">Pending</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info">Update Status</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#load-more-btn').on('click', function() {
        const btn = $(this);
        const offset = btn.data('offset');
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Loading...');

        // Get current filter params
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('offset', offset);

        $.ajax({
            url: '<?= site_url('vendors/returns/shipments') ?>?' + urlParams.toString(),
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(html) {
                if (html.trim()) {
                    $('#tracking-tbody').append(html);
                    const newOffset = offset + <?= $limit ?>;
                    btn.data('offset', newOffset);
                    btn.prop('disabled', false).html(originalText);
                    
                    if (newOffset >= <?= $total_count ?>) {
                        btn.parent().hide();
                    }
                } else {
                    btn.parent().hide();
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalText);
                alert('Error loading more records.');
            }
        });
    });
});

function openWaybillModal(data) {
    const modal = new bootstrap.Modal(document.getElementById('waybillModal'));
    $('#waybillRef').text(data.reference_no);
    $('#modalTransport').val(data.transport_name || '');
    $('#modalLR').val(data.waybill_number || '');
    $('#modalDate').val(data.waybill_date || '');
    $('#modalPackages').val(data.packages_count || '');
    
    $('#waybillForm').attr('action', '<?= site_url('vendors/returns/shipments/update/') ?>' + data.id);
    modal.show();
}

function openDeliveryModal(data) {
    const modal = new bootstrap.Modal(document.getElementById('deliveryModal'));
    $('#deliveryRef').text(data.reference_no);
    const defaultStatus = data.delivery_status ? data.delivery_status.trim() : 'Pending';
    $('#modalStatus').val(defaultStatus || 'Pending');
    
    $('#deliveryForm').attr('action', '<?= site_url('vendors/returns/shipments/update-status/') ?>' + data.id);
    modal.show();
}
</script>
<?= $this->endSection() ?>
