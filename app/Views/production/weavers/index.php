<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Weavers<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-industry text-primary me-2"></i>Weaver Partners</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('production/weavers') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <?php if(in_array('weaver.create', session('permissions') ?? [])): ?>
            <a href="<?= site_url('production/weavers/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Weaver
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalWeavers = count($weavers ?? []);
$activeCount = 0;
$inactiveCount = 0;
$verifiedCount = 0;

if (!empty($weavers)) {
    foreach ($weavers as $w) {
        if (($w['status'] ?? '') === 'active') {
            $activeCount++;
        } else {
            $inactiveCount++;
        }
        if (!empty($w['address_proof'])) {
            $verifiedCount++;
        }
    }
}
?>

<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('production/weavers') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-industry bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalWeavers) ?></div>
            <div class="bt-stat-label">Total Weavers</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('production/weavers?status=active') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'active' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($activeCount) ?></div>
            <div class="bt-stat-label">Active Weavers</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalWeavers > 0) ? min(100, round(($activeCount / $totalWeavers) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('production/weavers?status=inactive') ?>" class="bt-stat bt-g-slate text-decoration-none <?= ($filters['status'] ?? '') === 'inactive' ? 'active' : '' ?>">
            <i class="fas fa-pause-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($inactiveCount) ?></div>
            <div class="bt-stat-label">Inactive Weavers</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalWeavers > 0) ? min(100, round(($inactiveCount / $totalWeavers) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-teal" style="cursor:default;">
            <i class="fas fa-id-card bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($verifiedCount) ?></div>
            <div class="bt-stat-label">Address Proof on File</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalWeavers > 0) ? min(100, round(($verifiedCount / $totalWeavers) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Weavers</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('production/weavers') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-7 col-sm-8">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by name, code, or phone..." value="<?= esc($filters['search'] ?? '') ?>">
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
                <a href="<?= site_url('production/weavers') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Weavers Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0" id="weaversTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 190px;">Weaver Name</th>
                        <th style="min-width: 90px;">Code</th>
                        <th style="min-width: 130px;">Phone</th>
                        <th style="min-width: 130px;">Location</th>
                        <th class="text-center" style="min-width: 120px;">Address Proof</th>
                        <th class="text-center" style="min-width: 90px;">Status</th>
                        <th class="text-end" style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($weavers)): ?>
                        <?php foreach($weavers as $weaver): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?= $weaver['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;min-width:34px;font-size:0.85rem;">
                                        <?= strtoupper(substr($weaver['name'] ?? 'W', 0, 1)) ?>
                                    </div>
                                    <a href="<?= site_url('production/weavers/view/'.$weaver['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= esc($weaver['name']) ?>
                                    </a>
                                </div>
                            </td>
                            <td><code class="text-secondary"><?= esc($weaver['code'] ?: '-') ?></code></td>
                            <td>
                                <?php if(!empty($weaver['phone'])): ?>
                                    <a href="tel:<?= esc($weaver['phone']) ?>" class="text-decoration-none text-dark fw-semibold">
                                        <i class="fas fa-phone-alt text-success me-1 small"></i><?= esc($weaver['phone']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($weaver['location'])): ?>
                                    <a href="<?= esc($weaver['location']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;padding:2px 8px;">
                                        <i class="fas fa-map-marker-alt text-danger me-1"></i> Open Map
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if(!empty($weaver['address_proof'])): ?>
                                    <a href="<?= base_url($weaver['address_proof']) ?>" target="_blank" class="badge text-bg-info text-decoration-none px-2 py-1">
                                        <i class="fas fa-file-alt me-1"></i> View KYC
                                    </a>
                                <?php else: ?>
                                    <span class="badge text-bg-light border text-muted">No Proof</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-<?= ($weaver['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($weaver['status'] ?? 'inactive') ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('production/weavers/view/'.$weaver['id']) ?>" class="btn btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if(in_array('weaver.edit', session('permissions') ?? []) || in_array('weaver.create', session('permissions') ?? [])): ?>
                                    <a href="<?= site_url('production/weavers/edit/'.$weaver['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if(in_array('weaver.delete', session('permissions') ?? [])): ?>
                                    <a href="<?= site_url('production/weavers/delete/'.$weaver['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this weaver partner?')" title="Delete">
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
                                <i class="fas fa-industry fa-2x text-muted mb-2 d-block"></i>
                                No weavers found matching the criteria.
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
        if ($('#weaversTable').length && $.fn.DataTable) {
            $('#weaversTable').DataTable();
        }
    });
</script>
<?= $this->endSection() ?>
