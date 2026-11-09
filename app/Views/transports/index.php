<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Transports<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-truck-moving text-primary me-2"></i>Transport & Couriers</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('transports') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('transports/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Transport
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-4 col-sm-6">
        <a href="<?= site_url('transports') ?>" class="bt-stat bt-g-blue text-decoration-none">
            <i class="fas fa-truck bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($transports)) ?></div>
            <div class="bt-stat-label">Total Transporters</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="bt-stat bt-g-teal" style="cursor:default;">
            <i class="fas fa-boxes bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['parcel'] ?? 0) ?></div>
            <div class="bt-stat-label">Parcel Services</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['parcel'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
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
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Transports</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('transports') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-10 col-sm-9">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by name, transport code, phone, or branch..." value="<?= esc($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2 col-sm-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i> Search</button>
                <a href="<?= site_url('transports') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Transports Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 200px;">Transport Name</th>
                        <th style="min-width: 100px;">Code</th>
                        <th style="min-width: 130px;">Type</th>
                        <th style="min-width: 140px;">Branch</th>
                        <th style="min-width: 130px;">Phone</th>
                        <th style="min-width: 140px;">GST Number</th>
                        <th class="text-end" style="min-width: 110px;">Actions</th>
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
                            <td><?= !empty($transport['branch_gst_number']) ? '<code>'.esc($transport['branch_gst_number']).'</code>' : '<span class="text-muted">-</span>' ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('transports/view/'.$transport['id']) ?>" class="btn btn-outline-primary" title="View Invoices & Returns">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= site_url('transports/edit/'.$transport['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= site_url('transports/delete/'.$transport['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this transport?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
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
