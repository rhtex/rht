<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Transports<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-12 col-sm-6">
        <h1 class="m-0"><i class="fas fa-truck-moving text-primary me-2"></i>Transport & Couriers</h1>
    </div>
    <div class="col-12 col-sm-6 d-flex flex-wrap gap-2 justify-content-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('transports') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
            <i class="fas fa-sync-alt me-1"></i> Refresh
        </a>
        <?php if (can('transport.edit')): ?>
        <form action="<?= site_url('transports/sync-zoho') ?>" method="post" class="d-inline" id="syncZohoForm">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-outline-info" id="btnSyncZoho" title="Fetch & Update Transporters from Zoho Books">
                <i class="fas fa-cloud-download-alt me-1"></i> Sync from Zoho
            </button>
        </form>
        <?php endif; ?>
        <?php if (can('transport.create')): ?>
        <a href="<?= site_url('transports/create') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Transport
        </a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-2 g-md-3 mb-3">
    <div class="col-6 col-md-4">
        <a href="<?= site_url('transports') ?>" class="bt-stat bt-g-blue text-decoration-none">
            <i class="fas fa-truck bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($transports)) ?></div>
            <div class="bt-stat-label">Total Transporters</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <div class="bt-stat bt-g-teal" style="cursor:default;">
            <i class="fas fa-boxes bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['parcel'] ?? 0) ?></div>
            <div class="bt-stat-label">Parcel Services</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['parcel'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-shipping-fast bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['courier'] ?? 0) ?></div>
            <div class="bt-stat-label">Courier Services</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['courier'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Transports</h3>
        <div class="card-tools ms-auto">
            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-label="Toggle Filters">
                <i class="fas fa-plus filter-toggle-icon"></i>
            </button>
        </div>
    </div>
    <div id="filterCollapse" class="collapse">
    <div class="card-body">
        <form action="<?= site_url('transports') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-9 col-sm-8">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by name, transport code, GSTIN, Zoho ID, phone, or branch..." value="<?= esc($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill text-nowrap" title="Apply Filter"><i class="fas fa-search me-1"></i>Search</button>
                <a href="<?= site_url('transports') ?>" class="btn btn-outline-secondary text-nowrap" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
    </div>
</div>

<!-- Transports Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="erp-mobile-list p-2 bg-light">
            <?php if(!empty($transports)): ?>
                <?php foreach($transports as $transport): ?>
                    <a href="<?= site_url('transports/view/'.$transport['id']) ?>" class="erp-mobile-card">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-bold text-dark" style="white-space: normal; word-break: break-word; line-height: 1.35; min-width: 0; flex: 1 1 auto;">
                                <?= esc($transport['transport_name']) ?>
                            </div>
                            <span class="badge text-bg-<?= ($transport['transport_type'] ?? 'Parcel Service') == 'Courier Service' ? 'primary' : 'info' ?>" style="flex: 0 0 auto;">
                                <?= esc($transport['transport_type'] ?? 'Parcel Service') ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mt-1">
                            <span class="text-muted small" style="white-space: normal; word-break: break-word; min-width: 0; flex: 1 1 auto;">
                                <?= esc($transport['branch'] ?: '-') ?>
                                <?php if(!empty($transport['branch_phone_number'])): ?>
                                    &middot; <?= esc($transport['branch_phone_number']) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">No transports found matching the search criteria.</div>
            <?php endif; ?>
        </div>
        <div class="table-responsive erp-desktop-wrap">
            <table class="table table-striped table-hover align-middle mb-0 w-100">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Transport Name</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Branch</th>
                        <th>Phone</th>
                        <th>GST Number</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($transports)): ?>
                        <?php foreach($transports as $transport): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?= $transport['id'] ?></td>
                            <td>
                                <a href="<?= site_url('transports/view/'.$transport['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                    <i class="fas fa-truck text-primary me-2"></i><?= esc($transport['transport_name']) ?>
                                </a>
                            </td>
                            <td><code class="text-secondary"><?= esc($transport['transport_code'] ?: '-') ?></code></td>
                            <td>
                                <span class="badge text-bg-<?= ($transport['transport_type'] ?? 'Parcel Service') == 'Courier Service' ? 'primary' : 'info' ?> px-2 py-1">
                                    <?= esc($transport['transport_type'] ?? 'Parcel Service') ?>
                                </span>
                            </td>
                            <td><?= esc($transport['branch'] ?: '-') ?></td>
                            <td>
                                <?php if(!empty($transport['branch_phone_number'])): ?>
                                    <a href="tel:<?= esc($transport['branch_phone_number']) ?>" class="text-decoration-none text-dark fw-semibold">
                                        <i class="fas fa-phone-alt text-success me-1 small"></i><?= esc($transport['branch_phone_number']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= !empty($transport['branch_gst_number']) ? '<code>'.esc($transport['branch_gst_number']).'</code>' : '<span class="text-muted">-</span>' ?>
                                <?php if (!empty($transport['zoho_transporter_id'])): ?>
                                    <div class="small text-muted" title="Zoho Transporter ID">Zoho: <?= esc($transport['zoho_transporter_id']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('transports/view/'.$transport['id']) ?>" class="btn btn-outline-primary" title="View Invoices & Returns">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (can('transport.edit')): ?>
                                    <a href="<?= site_url('transports/edit/'.$transport['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (can('transport.delete')): ?>
                                    <a href="<?= site_url('transports/delete/'.$transport['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this transport?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-truck-moving fa-2x text-muted mb-2 d-block"></i>
                                No transports found matching the search criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var syncForm = document.getElementById('syncZohoForm');
    if (syncForm) {
        syncForm.addEventListener('submit', function() {
            var btn = document.getElementById('btnSyncZoho');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Syncing...';
            }
        });
    }
});
</script>
<?= $this->endSection() ?>
