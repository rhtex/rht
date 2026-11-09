<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Job Work Vendors<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-tools text-primary me-2"></i>Job Work Vendors</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('production/vendors') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <?php if(in_array('weaver.create', session('permissions') ?? [])): ?>
            <a href="<?= site_url('production/vendors/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Vendor
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalVendors = count($vendors ?? []);
$activeCount = 0;
$inactiveCount = 0;
$gstCount = 0;

if (!empty($vendors)) {
    foreach ($vendors as $v) {
        if (($v['status'] ?? '') === 'active') {
            $activeCount++;
        } else {
            $inactiveCount++;
        }
        if (!empty($v['gst_number'])) {
            $gstCount++;
        }
    }
}
?>

<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('production/vendors') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-tools bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalVendors) ?></div>
            <div class="bt-stat-label">Total Job Work Units</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('production/vendors?status=active') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'active' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($activeCount) ?></div>
            <div class="bt-stat-label">Active Vendors</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalVendors > 0) ? min(100, round(($activeCount / $totalVendors) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('production/vendors?status=inactive') ?>" class="bt-stat bt-g-slate text-decoration-none <?= ($filters['status'] ?? '') === 'inactive' ? 'active' : '' ?>">
            <i class="fas fa-pause-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($inactiveCount) ?></div>
            <div class="bt-stat-label">Inactive Vendors</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalVendors > 0) ? min(100, round(($inactiveCount / $totalVendors) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-teal" style="cursor:default;">
            <i class="fas fa-file-invoice bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($gstCount) ?></div>
            <div class="bt-stat-label">GST Registered Units</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalVendors > 0) ? min(100, round(($gstCount / $totalVendors) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Job Work Vendors</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('production/vendors') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-7 col-sm-8">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by name, GST, PAN, or phone..." value="<?= esc($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3 col-sm-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= (($filters['status'] ?? '') == 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= (($filters['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-12 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i> Search</button>
                <a href="<?= site_url('production/vendors') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Vendors Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0" id="vendorsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 190px;">Vendor Name</th>
                        <th style="min-width: 130px;">Process / Business</th>
                        <th style="min-width: 140px;">GST Number</th>
                        <th style="min-width: 110px;">PAN</th>
                        <th style="min-width: 130px;">Phone</th>
                        <th style="min-width: 120px;">Location</th>
                        <th class="text-center" style="min-width: 90px;">Status</th>
                        <th class="text-end" style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($vendors)): ?>
                        <?php foreach($vendors as $vendor): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?= $vendor['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-warning-subtle text-warning-emphasis fw-bold me-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;min-width:34px;font-size:0.85rem;">
                                        <?= strtoupper(substr($vendor['name'] ?? 'V', 0, 1)) ?>
                                    </div>
                                    <a href="<?= site_url('production/vendors/view/'.$vendor['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= esc($vendor['name']) ?>
                                    </a>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= esc($vendor['business_type'] ?: 'Job Work') ?></span></td>
                            <td><?= !empty($vendor['gst_number']) ? '<code>'.esc($vendor['gst_number']).'</code>' : '<span class="text-muted">-</span>' ?></td>
                            <td><?= !empty($vendor['pan_number']) ? '<code class="text-secondary">'.esc($vendor['pan_number']).'</code>' : '<span class="text-muted">-</span>' ?></td>
                            <td>
                                <?php if(!empty($vendor['phone'])): ?>
                                    <a href="tel:<?= esc($vendor['phone']) ?>" class="text-decoration-none text-dark fw-semibold">
                                        <i class="fas fa-phone-alt text-success me-1 small"></i><?= esc($vendor['phone']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($vendor['location'])): ?>
                                    <a href="<?= esc($vendor['location']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;padding:2px 8px;">
                                        <i class="fas fa-map-marker-alt text-danger me-1"></i> Map
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-<?= ($vendor['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($vendor['status'] ?? 'inactive') ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('production/vendors/view/'.$vendor['id']) ?>" class="btn btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if(in_array('weaver.edit', session('permissions') ?? [])): ?>
                                    <a href="<?= site_url('production/vendors/edit/'.$vendor['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if(in_array('weaver.delete', session('permissions') ?? [])): ?>
                                    <a href="<?= site_url('production/vendors/delete/'.$vendor['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this job work vendor?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-tools fa-2x text-muted mb-2 d-block"></i>
                                No job work vendors found matching your filters.
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
    $(document).ready(function() {
        if ($('#vendorsTable').length && $.fn.DataTable) {
            $('#vendorsTable').DataTable();
        }
    });
</script>
<?= $this->endSection() ?>
