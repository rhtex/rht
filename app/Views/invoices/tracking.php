<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Invoice Tracking<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">Invoice Tracking</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('invoices') ?>">Invoices</a></li>
                <li class="breadcrumb-item active">Tracking</li>
            </ol>
        </div>
    </div>

    <!-- Filters -->
    <div class="card card-outline card-info mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter me-2"></i>Filters</h3>
        </div>
        <div class="card-body">
            <form action="" method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select select2">
                        <option value="">All Customers</option>
                        <?php foreach($customers as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (strval($filters['customer_id']) === strval($c['id'])) ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Delivery Status</label>
                    <select name="delivery_status" class="form-select">
                        <option value="">All Pending statuses</option>
                        <option value="Pending" <?= ($filters['delivery_status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Booked" <?= ($filters['delivery_status'] == 'Booked') ? 'selected' : '' ?>>Booked</option>
                        <option value="In Transit" <?= ($filters['delivery_status'] == 'In Transit') ? 'selected' : '' ?>>In Transit</option>
                        <option value="Delivered" <?= ($filters['delivery_status'] == 'Delivered') ? 'selected' : '' ?>>Delivered</option>
                        <option value="Cancelled" <?= ($filters['delivery_status'] == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search Invoice #</label>
                    <input type="text" name="search" class="form-control" value="<?= esc($filters['search']) ?>" placeholder="Search...">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="<?= site_url('invoices/tracking') ?>" class="btn btn-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Pending Shipments (Total: <?= $total_count ?>)</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Transport</th>
                            <th>Waybill Status</th>
                            <th>Delivery Status</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tracking-tbody">
                        <?php if (empty($invoices)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                        <p class="mb-0">No pending shipments found!</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?= view('invoices/tracking_rows', ['invoices' => $invoices]) ?>
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
</div>

<!-- Waybill Update Modal -->
<div class="modal fade" id="updateWaybillModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="updateWaybillForm" action="" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Waybill Details: <span id="waybillInvoiceNumber"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waybill / LR Number <span class="text-danger">*</span></label>
                            <input type="text" name="waybill_number" id="modalWaybillNumber" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waybill Date <span class="text-danger">*</span></label>
                            <input type="date" name="waybill_date" id="modalWaybillDate" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Transport Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="transport_amount" id="modalTransportAmount" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Type</label>
                            <select name="transport_pay_type" id="modalTransportPayType" class="form-select">
                                <option value="To Pay">To Pay</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Shipping Charge (Booking/Extra)</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" name="waybill_shipping_charge" id="modalWaybillShippingCharge" class="form-control" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Waybill Image / Photo</label>
                        <input type="file" name="waybill_image" class="form-control" accept="image/*">
                        <small class="text-muted">Accepted formats: JPG, PNG. Max size: 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Waybill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delivery Status Update Modal -->
<div class="modal fade" id="updateDeliveryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="updateDeliveryForm" action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Delivery Status: <span id="deliveryInvoiceNumber"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Update Status <span class="text-danger">*</span></label>
                        <select name="delivery_status" id="modalDeliveryStatus" class="form-select" required>
                            <option value="Pending">Pending</option>
                            <option value="Booked">Booked</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3" id="deliveredDateGroup" style="display: none;">
                        <label class="form-label">Delivered Date <span class="text-danger">*</span></label>
                        <input type="date" name="delivered_date" id="modalDeliveredDate" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Status History: <span id="historyInvoiceNumber"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="historyTimeline" class="timeline-v2">
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-v2 {
    position: relative;
    padding: 20px 0;
}
.timeline-item {
    padding: 10px 0 10px 40px;
    position: relative;
    border-left: 2px solid #e9ecef;
    margin-left: 20px;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -9px;
    top: 15px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #007bff;
    border: 3px solid #fff;
}
.timeline-date {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 5px;
}
.timeline-content {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 8px;
}
.timeline-status {
    font-weight: 600;
    color: #343a40;
}
</style>
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
            url: '<?= site_url('invoices/tracking') ?>?' + urlParams.toString(),
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(html) {
                if (html.trim()) {
                    $('#tracking-tbody').append(html);
                    const newOffset = offset + <?= $limit ?>;
                    btn.data('offset', newOffset);
                    btn.prop('disabled', false).html(originalText);
                    
                    // Hide button if no more
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
    const modal = new bootstrap.Modal(document.getElementById('updateWaybillModal'));
    document.getElementById('waybillInvoiceNumber').textContent = data.invoice_number;
    document.getElementById('modalWaybillNumber').value = data.waybill_number || '';
    document.getElementById('modalWaybillDate').value = data.waybill_date || '';
    document.getElementById('modalTransportAmount').value = data.transport_amount || '';
    document.getElementById('modalTransportPayType').value = data.transport_pay_type || 'To Pay';
    document.getElementById('modalWaybillShippingCharge').value = data.waybill_shipping_charge || '';
    
    document.getElementById('updateWaybillForm').action = '<?= site_url('invoices/update-waybill') ?>/' + data.id;
    modal.show();
}

function openDeliveryModal(data) {
    const modal = new bootstrap.Modal(document.getElementById('updateDeliveryModal'));
    document.getElementById('deliveryInvoiceNumber').textContent = data.invoice_number;
    const defaultStatus = data.delivery_status ? data.delivery_status.trim() : 'Pending';
    document.getElementById('modalDeliveryStatus').value = defaultStatus || 'Pending';
    
    const delDateGroup = document.getElementById('deliveredDateGroup');
    const delStatusSelect = document.getElementById('modalDeliveryStatus');
    
    // Initial check
    delDateGroup.style.display = delStatusSelect.value === 'Delivered' ? 'block' : 'none';
    
    // Change listener
    delStatusSelect.onchange = function() {
        delDateGroup.style.display = this.value === 'Delivered' ? 'block' : 'none';
        if (this.value === 'Delivered') {
            document.getElementById('modalDeliveredDate').required = true;
        } else {
            document.getElementById('modalDeliveredDate').required = false;
        }
    };

    document.getElementById('updateDeliveryForm').action = '<?= site_url('invoices/update-delivery-status') ?>/' + data.id;
    modal.show();
}

function openHistoryModal(data) {
    const modal = new bootstrap.Modal(document.getElementById('historyModal'));
    document.getElementById('historyInvoiceNumber').textContent = data.invoice_number;
    const historyContainer = document.getElementById('historyTimeline');
    
    historyContainer.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';
    
    fetch('<?= site_url('invoices/history') ?>/' + data.id)
        .then(response => response.json())
        .then(history => {
            if (history.length === 0) {
                historyContainer.innerHTML = '<div class="alert alert-info">No history recorded yet.</div>';
            } else {
                let html = '';
                history.forEach(item => {
                    const date = new Date(item.created_at).toLocaleString();
                    html += `
                        <div class="timeline-item">
                            <div class="timeline-date">${date} by ${item.user_name || 'System'}</div>
                            <div class="timeline-content">
                                <span class="timeline-status text-primary">${item.status}</span>
                                <div class="mt-1">${item.description || ''}</div>
                            </div>
                        </div>
                    `;
                });
                historyContainer.innerHTML = html;
            }
        });
        
    modal.show();
}
</script>
<?= $this->endSection() ?>
