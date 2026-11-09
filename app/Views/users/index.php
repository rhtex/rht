<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Users<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-users-cog text-primary me-2"></i>User & Access Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('users') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('users/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New User
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalUsers = count($users ?? []);
$activeUsers = 0;
$inactiveUsers = 0;

if (!empty($users)) {
    foreach ($users as $u) {
        if (($u['status'] ?? '') === 'active') {
            $activeUsers++;
        } else {
            $inactiveUsers++;
        }
    }
}
?>

<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-4 col-sm-6">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-users bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalUsers) ?></div>
            <div class="bt-stat-label">Total System Users</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="bt-stat bt-g-green" style="cursor:default;">
            <i class="fas fa-user-check bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($activeUsers) ?></div>
            <div class="bt-stat-label">Active Users</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalUsers > 0) ? min(100, round(($activeUsers / $totalUsers) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="bt-stat bt-g-slate" style="cursor:default;">
            <i class="fas fa-user-slash bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($inactiveUsers) ?></div>
            <div class="bt-stat-label">Inactive Users</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalUsers > 0) ? min(100, round(($inactiveUsers / $totalUsers) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header py-2 bg-white">
        <h3 class="card-title fw-bold">System Users</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 190px;">Full Name</th>
                        <th style="min-width: 200px;">Email Address</th>
                        <th style="min-width: 130px;">Security Role</th>
                        <th class="text-center" style="min-width: 100px;">Status</th>
                        <th style="min-width: 140px;">Last Login</th>
                        <th class="text-end" style="min-width: 110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($users)): ?>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?= $user['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;min-width:34px;font-size:0.85rem;">
                                        <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <span class="fw-bold text-dark"><?= esc($user['name']) ?></span>
                                </div>
                            </td>
                            <td><?= esc($user['email']) ?></td>
                            <td>
                                <span class="badge text-bg-primary px-2 py-1">
                                    <i class="fas fa-shield-alt me-1"></i><?= esc($user['role_name'] ?: 'Staff') ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-<?= ($user['status'] ?? '') == 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($user['status'] ?? 'inactive') ?>
                                </span>
                            </td>
                            <td>
                                <?php if(!empty($user['last_login_at'])): ?>
                                    <span class="small text-muted"><i class="fas fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($user['last_login_at'])) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">Never</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('users/edit/'.$user['id']) ?>" class="btn btn-outline-warning" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= site_url('users/delete/'.$user['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?')" title="Delete User">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-users fa-2x text-muted mb-2 d-block"></i>
                                No users found in the system.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
