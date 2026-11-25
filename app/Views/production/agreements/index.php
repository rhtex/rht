<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Agreements<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-file-signature text-primary me-2"></i>Production Agreements</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('production/agreements') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <?php if(can('weaver.create')): ?>
            <?php if (can('production_agreement.create')): ?><a href="<?= site_url('production/agreements/create') ?>" class="btn btn-sm btn-primary"><?php endif; ?>
                <i class="fas fa-plus me-1"></i> Add New Agreement
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalAgreements = count($agreements ?? []);
$activeCount = 0;
$expiredCount = 0;
$terminatedCount = 0;

if (!empty($agreements)) {
    foreach ($agreements as $a) {
        $st = $a['status'] ?? '';
        if ($st === 'active') {
            $activeCount++;
        } elseif ($st === 'expired') {
            $expiredCount++;
        } else {
            $terminatedCount++;
        }
    }
}
?>

<!-- Summary Stat Cards -->
<div class="row g-2 g-md-3 mb-3">
    <div class="col-6 col-xl-3">
        <a href="<?= site_url('production/agreements') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-file-contract bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalAgreements) ?></div>
            <div class="bt-stat-label">Total Contracts</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="<?= site_url('production/agreements?status=active') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'active' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($activeCount) ?></div>
            <div class="bt-stat-label">Active Agreements</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalAgreements > 0) ? min(100, round(($activeCount / $totalAgreements) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="<?= site_url('production/agreements?status=expired') ?>" class="bt-stat bt-g-amber text-decoration-none <?= ($filters['status'] ?? '') === 'expired' ? 'active' : '' ?>">
            <i class="fas fa-clock bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($expiredCount) ?></div>
            <div class="bt-stat-label">Expired Contracts</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalAgreements > 0) ? min(100, round(($expiredCount / $totalAgreements) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="<?= site_url('production/agreements?status=terminated') ?>" class="bt-stat bt-g-slate text-decoration-none <?= ($filters['status'] ?? '') === 'terminated' ? 'active' : '' ?>">
            <i class="fas fa-ban bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($terminatedCount) ?></div>
            <div class="bt-stat-label">Terminated Contracts</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalAgreements > 0) ? min(100, round(($terminatedCount / $totalAgreements) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Agreements</h3>
        <div class="card-tools ms-auto">
            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-label="Toggle Filters">
                <i class="fas fa-plus filter-toggle-icon"></i>
            </button>
        </div>
    </div>
    <div id="filterCollapse" class="collapse">
    <div class="card-body">
        <form action="<?= site_url('production/agreements') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-7 col-sm-8">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by title, party name..." value="<?= esc($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3 col-sm-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= (($filters['status'] ?? '') == 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="expired" <?= (($filters['status'] ?? '') == 'expired') ? 'selected' : '' ?>>Expired</option>
                    <option value="terminated" <?= (($filters['status'] ?? '') == 'terminated') ? 'selected' : '' ?>>Terminated</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-12 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i> Search</button>
                <a href="<?= site_url('production/agreements') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
    </div>
</div>

<!-- Agreements Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="erp-mobile-list p-2 bg-light">
            <?php if(!empty($agreements)): ?>
                <?php foreach($agreements as $agreement): ?>
                    <?php
                        $badgeClass = match($agreement['status']) {
                            'active'     => 'success',
                            'expired'    => 'warning',
                            'terminated' => 'danger',
                            default      => 'secondary'
                        };
                    ?>
                    <a href="<?= site_url('production/agreements/view/'.$agreement['id']) ?>" class="erp-mobile-card">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-bold text-dark" style="white-space: normal; word-break: break-word; line-height: 1.35; min-width: 0; flex: 1 1 auto;">
                                <?= esc($agreement['title']) ?>
                            </div>
                            <span class="badge text-bg-<?= $badgeClass ?>" style="flex: 0 0 auto;"><?= ucfirst($agreement['status'] ?? 'unknown') ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mt-1">
                            <span class="text-muted small" style="white-space: normal; word-break: break-word; min-width: 0; flex: 1 1 auto;">
                                <?= esc($agreement['party_name']) ?>
                                <?php if (!empty($agreement['start_date'])): ?>
                                    &middot; <?= date('d/m/Y', strtotime($agreement['start_date'])) ?>
                                <?php endif; ?>
                                <?php if (!empty($agreement['end_date'])): ?>
                                    - <?= date('d/m/Y', strtotime($agreement['end_date'])) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">No agreements found.</div>
            <?php endif; ?>
        </div>
        <div class="table-responsive erp-desktop-wrap">
            <table class="table table-striped table-hover align-middle mb-0 w-100" id="agreementsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Agreement Title</th>
                        <th>Partner / Party Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($agreements)): ?>
                        <?php foreach($agreements as $agreement): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?= $agreement['id'] ?></td>
                            <td>
                                <a href="<?= site_url('production/agreements/view/'.$agreement['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                    <i class="fas fa-file-contract text-primary me-2"></i><?= esc($agreement['title']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark"><?= esc($agreement['party_name']) ?></span>
                            </td>
                            <td><?= !empty($agreement['start_date']) ? date('d/m/Y', strtotime($agreement['start_date'])) : '-' ?></td>
                            <td><?= !empty($agreement['end_date']) ? date('d/m/Y', strtotime($agreement['end_date'])) : '-' ?></td>
                            <td class="text-center">
                                <?php 
                                    $badgeClass = match($agreement['status']) {
                                        'active'     => 'success',
                                        'expired'    => 'warning',
                                        'terminated' => 'danger',
                                        default      => 'secondary'
                                    };
                                ?>
                                <span class="badge text-bg-<?= $badgeClass ?> px-2 py-1">
                                    <?= ucfirst($agreement['status'] ?? 'unknown') ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('production/agreements/view/'.$agreement['id']) ?>" class="btn btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if(can('weaver.edit')): ?>
                                    <a href="<?= site_url('production/agreements/edit/'.$agreement['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if(can('weaver.delete')): ?>
                                    <a href="<?= site_url('production/agreements/delete/'.$agreement['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this agreement?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-file-signature fa-2x text-muted mb-2 d-block"></i>
                                No agreements found matching your filters.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
