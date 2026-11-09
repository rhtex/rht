<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Permissions<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalPermissions = count($permissions ?? []);
$moduleSet = [];
if (!empty($permissions)) {
    foreach ($permissions as $p) {
        if (!empty($p['module_name'])) {
            $moduleSet[$p['module_name']] = true;
        }
    }
}
$distinctModules = count($moduleSet);
?>

<!-- Hero Header -->
<div class="bt-hero mb-3">
    <div class="row align-items-center g-3">
        <div class="col-md-7">
            <h2 class="h4 mb-1 text-white fw-bold"><i class="fas fa-shield-alt me-2"></i> Access Permissions</h2>
            <p class="mb-0 text-white-50 small">Define granular security keys, capability flags, and resource-level authorization policies.</p>
        </div>
        <div class="col-md-5 text-md-end">
            <a href="<?= site_url('roles') ?>" class="btn btn-light btn-sm fw-semibold shadow-sm me-2">
                <i class="fas fa-user-tag me-1 text-primary"></i> Roles
            </a>
            <a href="<?= site_url('modules') ?>" class="btn btn-outline-light btn-sm fw-semibold shadow-sm me-2">
                <i class="fas fa-cubes me-1"></i> Modules
            </a>
            <a href="<?= site_url('permissions/create') ?>" class="btn btn-warning btn-sm fw-semibold shadow-sm">
                <i class="fas fa-plus me-1"></i> Add Permission
            </a>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-6 col-lg-4">
        <div class="bt-stat bt-g-blue">
            <div class="bt-stat-title">Total Permissions</div>
            <div class="bt-stat-val"><?= number_format($totalPermissions) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-key me-1"></i> System Authorization Keys</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="bt-stat bt-g-purple">
            <div class="bt-stat-title">Modules Protected</div>
            <div class="bt-stat-val"><?= number_format($distinctModules) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-cubes me-1"></i> Functional Areas Covered</div>
        </div>
    </div>
    <div class="col-md-12 col-lg-4">
        <div class="bt-stat bt-g-green">
            <div class="bt-stat-title">RBAC Engine</div>
            <div class="bt-stat-val">Active</div>
            <div class="small opacity-75 mt-1"><i class="fas fa-lock me-1"></i> Role-Based Access Control</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2 text-primary"></i> All Defined Permissions</h6>
        <span class="badge text-bg-primary"><?= $totalPermissions ?> Permissions</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($permissions)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-key fa-3x mb-3 text-secondary opacity-50"></i>
                <p class="mb-2 fw-semibold">No permissions defined yet.</p>
                <a href="<?= site_url('permissions/create') ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add First Permission
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 list-ui-table">
                    <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Permission Name</th>
                            <th>Security Key</th>
                            <th>Target Module</th>
                            <th class="text-center" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($permissions as $index => $perm): ?>
                        <tr>
                            <td class="text-muted fw-bold small"><?= $index + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-2 me-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <div>
                                        <strong class="text-dark d-block"><?= esc($perm['permission_name']) ?></strong>
                                        <small class="text-muted">ID: #<?= esc($perm['id']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code class="badge text-bg-light border text-danger font-monospace px-2 py-1 fs-7">
                                    <?= esc($perm['permission_key']) ?>
                                </code>
                            </td>
                            <td>
                                <span class="badge text-bg-info text-dark">
                                    <i class="fas fa-cube me-1"></i><?= esc($perm['module_name'] ?? 'General') ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('permissions/edit/'.$perm['id']) ?>" class="btn btn-outline-primary" title="Edit Permission">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="<?= site_url('permissions/delete/'.$perm['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this permission?')" title="Delete Permission">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
