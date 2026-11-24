<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-12 col-sm-6">
        <h1 class="m-0"><i class="fas fa-truck text-primary me-2"></i>Vehicles</h1>
        <p class="text-muted small mb-0">Manage fleet conveyance & transport vehicles for operations and e-Way Bill generation</p>
    </div>
    <div class="col-12 col-sm-6 d-flex flex-wrap gap-2 justify-content-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('vehicles') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
            <i class="fas fa-sync-alt me-1"></i> Refresh
        </a>
        <?php if (can('vehicle.create')): ?>
        <a href="<?= site_url('vehicles/create') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Vehicle
        </a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-2 g-md-3 mb-3">
    <div class="col-6 col-md-4">
        <div class="bt-stat bt-g-blue">
            <i class="fas fa-truck bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? 0) ?></div>
            <div class="bt-stat-label">Total Vehicles</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="bt-stat bt-g-teal">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['active'] ?? 0) ?></div>
            <div class="bt-stat-label">Active Fleet</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['active'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="bt-stat bt-g-purple">
            <i class="fas fa-layer-group bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['regular'] ?? 0) ?></div>
            <div class="bt-stat-label">Regular Conveyance</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['regular'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Search Vehicles</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('vehicles') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-9 col-sm-8">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by vehicle number, name, model, or type..." value="<?= esc($search ?? '') ?>">
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill text-nowrap" title="Apply Filter"><i class="fas fa-search me-1"></i>Search</button>
                <a href="<?= site_url('vehicles') ?>" class="btn btn-outline-secondary text-nowrap" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <!-- Mobile Card List -->
        <div class="erp-mobile-list p-2 bg-light">
            <?php if (!empty($vehicles)): ?>
                <?php foreach ($vehicles as $v): ?>
                    <a href="<?= can('vehicle.edit') ? site_url('vehicles/edit/' . $v['id']) : '#' ?>" class="erp-mobile-card">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-bold text-dark font-monospace" style="white-space: normal; word-break: break-word; line-height: 1.35; min-width: 0; flex: 1 1 auto;">
                                <?= esc($v['vehicle_number']) ?>
                            </div>
                            <span class="badge bg-<?= $v['status'] === 'active' ? 'success' : 'secondary' ?>" style="flex: 0 0 auto;">
                                <?= $v['status'] === 'active' ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mt-1">
                            <span class="text-muted small" style="white-space: normal; word-break: break-word; min-width: 0; flex: 1 1 auto;">
                                <?= esc(!empty($v['vehicle_name']) ? $v['vehicle_name'] : '-') ?>
                                <?php if (!empty($v['vehicle_model'])): ?>
                                    &middot; <?= esc($v['vehicle_model']) ?>
                                <?php endif; ?>
                                &middot; <?= $v['vehicle_type'] === 'Over Dimensional Cargo' ? 'Over Dimensional' : 'Regular' ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">No vehicles registered yet.</div>
            <?php endif; ?>
        </div>

        <!-- Desktop Table -->
        <div class="table-responsive erp-desktop-wrap">
            <table class="table table-hover align-middle mb-0 w-100">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Vehicle Number</th>
                        <th>Vehicle Name</th>
                        <th>Model</th>
                        <th>Vehicle Type</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($vehicles)): ?>
                        <?php foreach ($vehicles as $i => $v): ?>
                            <tr>
                                <td class="text-muted small"><?= $i + 1 ?></td>
                                <td>
                                    <span class="badge bg-dark fs-6 font-monospace px-2 py-1">
                                        <?= esc($v['vehicle_number']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">
                                    <?= esc(!empty($v['vehicle_name']) ? $v['vehicle_name'] : '-') ?>
                                </td>
                                <td><?= esc(!empty($v['vehicle_model']) ? $v['vehicle_model'] : '-') ?></td>
                                <td>
                                    <?php if ($v['vehicle_type'] === 'Over Dimensional Cargo'): ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-truck-monster me-1"></i> Over Dimensional</span>
                                    <?php else: ?>
                                        <span class="badge bg-info-subtle text-info border"><i class="fas fa-truck me-1"></i> Regular</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($v['status'] === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <?php if (can('vehicle.edit')): ?>
                                        <a href="<?= site_url('vehicles/edit/' . $v['id']) ?>" class="btn btn-outline-primary" title="Edit Vehicle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (can('vehicle.delete')): ?>
                                        <a href="<?= site_url('vehicles/delete/' . $v['id']) ?>" class="btn btn-outline-danger" 
                                            onclick="return confirm('Are you sure you want to delete vehicle <?= esc($v['vehicle_number']) ?>?');" title="Delete Vehicle">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-truck fa-3x mb-3 d-block text-secondary opacity-50"></i>
                                <strong>No vehicles registered yet.</strong>
                                <p class="small mb-2">Add vehicles to easily select them during e-Way Bill generation.</p>
                                <?php if (can('vehicle.create')): ?>
                                <a href="<?= site_url('vehicles/create') ?>" class="btn btn-sm btn-primary mt-1">
                                    <i class="fas fa-plus me-1"></i> Add Vehicle
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (!empty($pager)): ?>
        <div class="card-footer bg-white border-0 py-3">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
