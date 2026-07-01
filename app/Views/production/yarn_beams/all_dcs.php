<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>All Delivery Challans<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><i class="fas fa-file-invoice text-primary me-2"></i> All Job Work DCs</h1>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- All Challans Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border-radius: 12px;">
    <div class="card-header border-0 py-3 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #1565c0 0%, #1e88e5 100%);">
        <h5 class="card-title text-white mb-0 fw-bold"><i class="fas fa-list-ul me-2"></i>Delivery Challans History</h5>
        <div class="text-white-50 small"><i class="fas fa-database me-1"></i><?= count($dcs) ?> total records</div>
    </div>
    <div class="card-body p-3 bg-white">
        <!-- Header Filters -->
        <div class="row g-2 mb-3 p-2 rounded-3 bg-light border border-light-subtle align-items-end" style="border-radius: 6px;">
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.75rem;"><i class="fas fa-filter text-secondary me-1"></i> Job Work Type</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-tools"></i></span>
                    <select id="filterType" class="form-select form-select-sm border-start-0 ps-0 shadow-none">
                        <option value="">-- All Types --</option>
                        <option value="Dyeing">Dyeing</option>
                        <option value="Warping & Sizing">Warping & Sizing</option>
                        <option value="Twisting">Twisting</option>
                        <option value="Weaving">Weaving</option>
                    </select>
                </div>
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.75rem;"><i class="fas fa-check-circle text-secondary me-1"></i> Status</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-info-circle"></i></span>
                    <select id="filterStatus" class="form-select form-select-sm border-start-0 ps-0 shadow-none">
                        <option value="">-- All Statuses --</option>
                        <option value="Open">Open</option>
                        <option value="Partially Received">Partially Received</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2 d-grid">
                <button type="button" id="resetFilters" class="btn btn-outline-secondary btn-sm py-1" style="height: 31px; font-size: 0.8rem; font-weight: 500;"><i class="fas fa-sync me-1"></i> Reset</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle table-hover custom-premium-table mb-0" id="allDcsTable">
                <thead>
                    <tr>
                        <th class="py-2 px-3 text-secondary fw-bold text-uppercase border-bottom">DC Number</th>
                        <th class="py-2 px-2 text-secondary fw-bold text-uppercase border-bottom">DC Date</th>
                        <th class="py-2 px-2 text-secondary fw-bold text-uppercase border-bottom">Vendor Name</th>
                        <th class="py-2 px-2 text-secondary fw-bold text-uppercase border-bottom text-center">Job Work Type</th>
                        <th class="py-2 px-2 text-secondary fw-bold text-uppercase border-bottom text-center">Status</th>
                        <th width="15%" class="py-2 px-3 text-secondary fw-bold text-uppercase border-bottom text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php if(!empty($dcs)): ?>
                        <?php foreach($dcs as $dc): ?>
                            <tr class="table-row-card">
                                <td class="py-2 px-3 fw-bold text-dark">
                                    <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle me-2" style="width: 28px; height: 28px;">
                                        <i class="fas fa-file-invoice" style="font-size: 0.8rem;"></i>
                                    </span>
                                    <?= esc($dc['dc_number']) ?>
                                </td>
                                <td class="py-2 px-2 text-secondary" style="font-size: 0.85rem;">
                                    <i class="far fa-calendar-alt text-muted me-1"></i> <?= date('d-M-Y', strtotime($dc['dc_date'])) ?>
                                </td>
                                <td class="py-2 px-2 text-dark fw-semibold" style="font-size: 0.85rem;">
                                    <i class="fas fa-user-tie text-muted me-1"></i> <?= esc($dc['vendor_name']) ?>
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <?php
                                    $typeBadge = 'secondary';
                                    if ($dc['type'] === 'Dyeing') $typeBadge = 'primary';
                                    if ($dc['type'] === 'Warping & Sizing') $typeBadge = 'info';
                                    if ($dc['type'] === 'Twisting') $typeBadge = 'warning';
                                    if ($dc['type'] === 'Weaving') $typeBadge = 'danger';
                                    ?>
                                    <span class="badge rounded-pill bg-<?= $typeBadge ?> bg-opacity-10 text-<?= $typeBadge ?> px-2 py-1 border border-<?= $typeBadge ?> border-opacity-25" style="font-size: 0.7rem; font-weight: 600;">
                                        <?= esc($dc['type']) ?>
                                    </span>
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <?php 
                                    $statusClass = 'secondary';
                                    if ($dc['status'] === 'Open') $statusClass = 'info';
                                    if ($dc['status'] === 'Partially Received') $statusClass = 'warning text-dark';
                                    if ($dc['status'] === 'Completed') $statusClass = 'success';
                                    if ($dc['status'] === 'Cancelled') $statusClass = 'danger';
                                    ?>
                                    <span class="badge rounded-pill bg-<?= $statusClass ?> px-2 py-1 text-uppercase" style="font-size: 0.65rem; font-weight: 750;">
                                        <?= esc($dc['status']) ?>
                                    </span>
                                </td>
                                <td class="py-2 px-3 text-center">
                                    <a href="<?= site_url($dc['view_url'] . '/' . $dc['id']) ?>" class="btn btn-xs btn-primary rounded-pill px-3 shadow-sm transition-all" style="font-weight: 600;">
                                        <i class="fas fa-eye me-1"></i> View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-folder-open fa-2x text-muted mb-2 d-block"></i>
                                No Delivery Challans found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .custom-premium-table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
    }
    .custom-premium-table thead th {
        background-color: #f8f9fa !important;
        font-size: 0.72rem !important;
        letter-spacing: 0.3px;
        color: #5a6a85 !important;
        font-weight: 700 !important;
        border-top: 1px solid #eef1f6 !important;
        border-bottom: 2px solid #eef1f6 !important;
    }
    .custom-premium-table thead th:first-child {
        border-top-left-radius: 6px;
        border-left: 1px solid #eef1f6 !important;
    }
    .custom-premium-table thead th:last-child {
        border-top-right-radius: 6px;
        border-right: 1px solid #eef1f6 !important;
    }
    .table-row-card {
        transition: all 0.2s ease !important;
    }
    .table-row-card td {
        border-bottom: 1px solid #f1f3f7 !important;
        border-left: none !important;
        border-right: none !important;
    }
    .table-row-card td:first-child {
        border-left: 1px solid #f1f3f7 !important;
    }
    .table-row-card td:last-child {
        border-right: 1px solid #f1f3f7 !important;
    }
    .table-row-card:last-child td:first-child {
        border-bottom-left-radius: 6px;
    }
    .table-row-card:last-child td:last-child {
        border-bottom-right-radius: 6px;
    }
    .table-row-card:hover {
        background-color: #f8fbff !important;
        cursor: pointer;
    }
    .table-row-card:hover td {
        background-color: #f8fbff !important;
        border-color: #e0eeff !important;
    }
    .transition-all {
        transition: all 0.15s ease-in-out;
    }
    .transition-all:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12) !important;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof $ !== 'undefined') {
            // Destroy if already auto-initialized by other scripts
            if ($.fn.DataTable.isDataTable('#allDcsTable')) {
                $('#allDcsTable').DataTable().destroy();
            }

            var table = $('#allDcsTable').DataTable({
                "order": [[ 1, "desc" ]],
                "pageLength": 25,
                "searching": true,
                "dom": "lrtip", 
                "language": {
                    "emptyTable": "No Delivery Challans found"
                }
            });

            // Bind filter dropdowns to DataTables columns
            $('#filterType').on('change', function() {
                var val = $(this).val();
                table.column(3).search(val ? '^' + val + '$' : '', true, false).draw();
            });

            $('#filterStatus').on('change', function() {
                var val = $(this).val();
                table.column(4).search(val ? '^' + val + '$' : '', true, false).draw();
            });

            // Reset filters
            $('#resetFilters').on('click', function() {
                $('#filterType').val('');
                $('#filterStatus').val('');
                table.column(3).search('');
                table.column(4).search('');
                table.draw();
            });
        }
    });
</script>
<?= $this->endSection() ?>
