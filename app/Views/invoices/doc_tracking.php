<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Invoice Document Tracking<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">Invoice Document Tracking</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('invoices') ?>">Invoices</a></li>
                <li class="breadcrumb-item active">Doc Tracking</li>
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
                    <label class="form-label">Document Status</label>
                    <select name="doc_status" class="form-select">
                        <option value="">All Pending statuses</option>
                        <option value="Pending" <?= ($filters['doc_status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Dispatched" <?= ($filters['doc_status'] == 'Dispatched') ? 'selected' : '' ?>>Dispatched</option>
                        <option value="Delivered" <?= ($filters['doc_status'] == 'Delivered') ? 'selected' : '' ?>>Delivered</option>
                        <option value="Returned" <?= ($filters['doc_status'] == 'Returned') ? 'selected' : '' ?>>Returned</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search Invoice #</label>
                    <input type="text" name="search" class="form-control" value="<?= esc($filters['search']) ?>" placeholder="Search...">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="<?= site_url('invoices/doc-tracking') ?>" class="btn btn-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Pending Document Deliveries (Total: <?= $total_count ?>)</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Courier Details</th>
                            <th>Doc Status</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="doc-tracking-tbody">
                        <?php if (empty($invoices)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-file-check fa-3x mb-3 text-success"></i>
                                        <p class="mb-0">No pending document deliveries found!</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?= view('invoices/doc_tracking_rows', ['invoices' => $invoices]) ?>
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

<!-- Doc Details Update Modal -->
<div class="modal fade" id="updateDocDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="updateDocDetailsForm" action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Doc Dispatch Details: <span id="docInvoiceNumber"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Courier Name <span class="text-danger">*</span></label>
                        <input type="text" name="doc_courier_name" id="modalDocCourierName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tracking Number <span class="text-danger">*</span></label>
                        <input type="text" name="doc_tracking_number" id="modalDocTrackingNumber" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dispatch Date <span class="text-danger">*</span></label>
                        <input type="date" name="doc_dispatched_date" id="modalDocDispatchedDate" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Details</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Doc Status Update Modal -->
<div class="modal fade" id="updateDocStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="updateDocStatusForm" action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Doc Status: <span id="docStatusInvoiceNumber"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Update Status <span class="text-danger">*</span></label>
                        <select name="doc_status" id="modalDocStatus" class="form-select" required>
                            <option value="Pending">Pending</option>
                            <option value="Dispatched">Dispatched</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Returned">Returned</option>
                        </select>
                    </div>
                    <div class="mb-3" id="docReceivedDateGroup" style="display: none;">
                        <label class="form-label">Received Date <span class="text-danger">*</span></label>
                        <input type="date" name="doc_received_date" id="modalDocReceivedDate" class="form-control" value="<?= date('Y-m-d') ?>">
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

<!-- History Modal (Reused) -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Status History: <span id="historyInvoiceNumber"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="historyTimeline" class="timeline-v2">
                    <!-- Loaded via JS -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-v2 { position: relative; padding: 20px 0; }
.timeline-item { padding: 10px 0 10px 40px; position: relative; border-left: 2px solid #e9ecef; margin-left: 20px; }
.timeline-item::before { content: ''; position: absolute; left: -9px; top: 15px; width: 16px; height: 16px; border-radius: 50%; background: #007bff; border: 3px solid #fff; }
.timeline-date { font-size: 0.85rem; color: #6c757d; margin-bottom: 5px; }
.timeline-content { background: #f8f9fa; padding: 10px 15px; border-radius: 8px; }
.timeline-status { font-weight: 600; color: #343a40; }
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

        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('offset', offset);

        $.ajax({
            url: '<?= site_url('invoices/doc-tracking') ?>?' + urlParams.toString(),
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(html) {
                if (html.trim()) {
                    $('#doc-tracking-tbody').append(html);
                    const newOffset = offset + <?= $limit ?>;
                    btn.data('offset', newOffset);
                    btn.prop('disabled', false).html(originalText);
                    
                    if (newOffset >= <?= $total_count ?>) { btn.parent().hide(); }
                } else { btn.parent().hide(); }
            },
            error: function() {
                btn.prop('disabled', false).html(originalText);
                alert('Error loading more records.');
            }
        });
    });
});

function openDocDetailsModal(data) {
    const modal = new bootstrap.Modal(document.getElementById('updateDocDetailsModal'));
    document.getElementById('docInvoiceNumber').textContent = data.invoice_number;
    document.getElementById('modalDocCourierName').value = data.doc_courier_name || '';
    document.getElementById('modalDocTrackingNumber').value = data.doc_tracking_number || '';
    document.getElementById('modalDocDispatchedDate').value = data.doc_dispatched_date || '<?= date('Y-m-d') ?>';
    
    document.getElementById('updateDocDetailsForm').action = '<?= site_url('invoices/update-doc-details') ?>/' + data.id;
    modal.show();
}

function openDocStatusModal(data) {
    const modal = new bootstrap.Modal(document.getElementById('updateDocStatusModal'));
    document.getElementById('docStatusInvoiceNumber').textContent = data.invoice_number;
    document.getElementById('modalDocStatus').value = data.doc_status || 'Pending';
    
    const dateGroup = document.getElementById('docReceivedDateGroup');
    const statusSelect = document.getElementById('modalDocStatus');
    
    dateGroup.style.display = statusSelect.value === 'Delivered' ? 'block' : 'none';
    
    statusSelect.onchange = function() {
        dateGroup.style.display = this.value === 'Delivered' ? 'block' : 'none';
        document.getElementById('modalDocReceivedDate').required = this.value === 'Delivered';
    };

    document.getElementById('updateDocStatusForm').action = '<?= site_url('invoices/update-doc-status') ?>/' + data.id;
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
