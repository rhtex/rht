<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Module Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalModules = count($modules ?? []);
$activeModules = 0;
$inactiveModules = 0;
if (!empty($modules)) {
    foreach ($modules as $m) {
        if (($m['status'] ?? '') === 'active') $activeModules++;
        else $inactiveModules++;
    }
}
?>

<!-- Hero Header -->
<div class="bt-hero mb-3">
    <div class="row align-items-center g-3">
        <div class="col-md-7">
            <h2 class="h4 mb-1 text-white fw-bold"><i class="fas fa-cubes me-2"></i> Application Modules</h2>
            <p class="mb-0 text-white-50 small">Manage core functional subsystems, routing slugs, and access boundaries for RBAC permissions.</p>
        </div>
        <div class="col-md-5 text-md-end">
            <a href="<?= site_url('permissions') ?>" class="btn btn-light btn-sm fw-semibold shadow-sm me-2">
                <i class="fas fa-key me-1 text-primary"></i> Permissions
            </a>
            <a href="<?= site_url('modules/create') ?>" class="btn btn-warning btn-sm fw-semibold shadow-sm">
                <i class="fas fa-plus me-1"></i> Add Module
            </a>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="bt-stat bt-g-blue">
            <div class="bt-stat-title">Total Modules</div>
            <div class="bt-stat-val"><?= number_format($totalModules) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-th-large me-1"></i> Core Subsystems</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bt-stat bt-g-green">
            <div class="bt-stat-title">Active Modules</div>
            <div class="bt-stat-val"><?= number_format($activeModules) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-check-circle me-1"></i> Enabled & Accessible</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bt-stat bt-g-rose">
            <div class="bt-stat-title">Disabled Modules</div>
            <div class="bt-stat-val"><?= number_format($inactiveModules) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-ban me-1"></i> Deactivated</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2 text-primary"></i> Registered Modules</h6>
        <span class="badge text-bg-primary"><?= $totalModules ?> Modules</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($modules)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-cubes fa-3x mb-3 text-secondary opacity-50"></i>
                <p class="mb-2 fw-semibold">No modules configured yet.</p>
                <a href="<?= site_url('modules/create') ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add First Module
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 list-ui-table">
                    <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Module Name</th>
                            <th>System Slug</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($modules as $index => $module): ?>
                        <tr>
                            <td class="text-muted fw-bold small"><?= $index + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 me-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                    <div>
                                        <strong class="text-dark d-block"><?= esc($module['module_name']) ?></strong>
                                        <small class="text-muted">Module ID: #<?= esc($module['id']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code class="badge text-bg-light border text-dark font-monospace px-2 py-1 fs-7">
                                    <?= esc($module['module_slug']) ?>
                                </code>
                            </td>
                            <td>
                                <?php if (($module['status'] ?? '') === 'active'): ?>
                                    <span class="badge text-bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-danger"><i class="fas fa-times-circle me-1"></i>Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('modules/edit/'.$module['id']) ?>" class="btn btn-outline-primary" title="Edit Module">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="<?= site_url('modules/delete/'.$module['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this module?')" title="Delete Module">
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
