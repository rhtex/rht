<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Roles<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-user-shield text-primary me-2"></i>Security Roles & Permissions</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('roles') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <?php if (can('role.create')): ?>
            <a href="<?= site_url('roles/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Role
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $totalRoles = count($roles ?? []); ?>

<div class="row g-3 mb-3">
    <div class="col-md-4 col-sm-6">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-shield-alt bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalRoles) ?></div>
            <div class="bt-stat-label">Configured Security Roles</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
</div>

<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header py-2 bg-white">
        <h3 class="card-title fw-bold">Defined Roles & Access Levels</h3>
    </div>
    <div class="card-body p-0">
        <div class="erp-mobile-list p-2 bg-light">
            <?php if(!empty($roles)): ?>
                <?php foreach($roles as $role): ?>
                    <a href="<?= can('role.edit') ? site_url('roles/permissions/'.$role['id']) : '#' ?>" class="erp-mobile-card">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-bold text-primary" style="white-space: normal; word-break: break-word; line-height: 1.35;">
                                <?= ucfirst(esc($role['role_name'])) ?>
                            </div>
                        </div>
                        <div class="text-muted small mt-1" style="white-space: normal; word-break: break-word;">
                            <?= esc($role['description'] ?: 'No description provided') ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">No roles defined.</div>
            <?php endif; ?>
        </div>
        <div class="table-responsive erp-desktop-wrap">
            <table class="table table-striped table-hover align-middle mb-0 w-100">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Role Name</th>
                        <th>Description</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($roles)): ?>
                        <?php foreach($roles as $role): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?= $role['id'] ?></td>
                            <td>
                                <span class="badge text-bg-primary px-3 py-1 fs-6">
                                    <i class="fas fa-shield-alt me-1"></i><?= ucfirst(esc($role['role_name'])) ?>
                                </span>
                            </td>
                            <td><?= esc($role['description'] ?: 'No description provided') ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <?php if (can('role.edit')): ?>
                                    <a href="<?= site_url('roles/permissions/'.$role['id']) ?>" class="btn btn-outline-info" title="Manage Permissions">
                                        <i class="fas fa-key me-1"></i> Permissions
                                    </a>
                                    <a href="<?= site_url('roles/edit/'.$role['id']) ?>" class="btn btn-outline-warning" title="Edit Role">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if(strtolower($role['role_name']) !== 'admin' && can('role.delete')): ?>
                                    <a href="<?= site_url('roles/delete/'.$role['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this role?')" title="Delete Role">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="fas fa-user-shield fa-2x text-muted mb-2 d-block"></i>
                                No roles defined.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
