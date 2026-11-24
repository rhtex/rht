<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Yarn Twisting Job Work<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-sync-alt text-primary me-2"></i>Yarn Twisting Job Work</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('production/yarn-twisting') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <?php if (can('yarn_twisting.create')): ?><a href="<?= site_url('production/yarn-twisting/create') ?>" class="btn btn-sm btn-primary"><?php endif; ?>
                <i class="fas fa-plus me-1"></i> New Twisting DC
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalDcs = count($dcs ?? []);
$openCount = 0;
$partialCount = 0;
$completedCount = 0;

if (!empty($dcs)) {
    foreach ($dcs as $dc) {
        $st = $dc['status'] ?? '';
        if ($st === 'Open') $openCount++;
        elseif ($st === 'Partially Received') $partialCount++;
        elseif ($st === 'Completed') $completedCount++;
    }
}
?>

<!-- Summary Stat Cards -->
<div class="row g-2 g-md-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-sync-alt bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalDcs) ?></div>
            <div class="bt-stat-label">Total Delivery Challans</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="bt-stat bt-g-indigo" style="cursor:default;">
            <i class="fas fa-clock bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($openCount) ?></div>
            <div class="bt-stat-label">Open / At Twister</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalDcs > 0) ? min(100, round(($openCount / $totalDcs) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="bt-stat bt-g-amber" style="cursor:default;">
            <i class="fas fa-hourglass-half bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($partialCount) ?></div>
            <div class="bt-stat-label">Partially Received</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalDcs > 0) ? min(100, round(($partialCount / $totalDcs) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="bt-stat bt-g-green" style="cursor:default;">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($completedCount) ?></div>
            <div class="bt-stat-label">Fully Completed</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalDcs > 0) ? min(100, round(($completedCount / $totalDcs) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header py-2 bg-white">
        <h3 class="card-title fw-bold">Twisting Delivery Challans</h3>
    </div>
    <div class="card-body p-0">
        <div class="erp-mobile-list p-2 bg-light">
            <?php if(!empty($dcs)): ?>
                <?php foreach($dcs as $dc): ?>
                    <?php
                    $statusClass = match($dc['status']) {
                        'Open'               => 'primary',
                        'Partially Received' => 'warning',
                        'Completed'          => 'success',
                        'Cancelled'          => 'danger',
                        default              => 'secondary'
                    };
                    ?>
                    <a href="<?= site_url('production/yarn-twisting/view/'.$dc['id']) ?>" class="erp-mobile-card">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-bold text-dark" style="white-space: normal; word-break: break-word; line-height: 1.35; min-width: 0; flex: 1 1 auto;">
                                <?= esc($dc['vendor_name']) ?>
                            </div>
                            <span class="badge text-bg-<?= $statusClass ?>" style="flex: 0 0 auto;"><?= esc($dc['status']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mt-1">
                            <span class="text-muted small">
                                <?= esc($dc['dc_number']) ?> &middot; <?= date('d/m/Y', strtotime($dc['dc_date'])) ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">No twisting delivery challans found.</div>
            <?php endif; ?>
        </div>
        <div class="table-responsive erp-desktop-wrap">
            <table class="table table-striped table-hover align-middle mb-0 w-100">
                <thead class="table-light">
                    <tr>
                        <th>DC Number</th>
                        <th>Date</th>
                        <th>Vendor (Twister)</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($dcs)): ?>
                        <?php foreach($dcs as $dc): ?>
                        <tr>
                            <td>
                                <a href="<?= site_url('production/yarn-twisting/view/'.$dc['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                    <i class="fas fa-file-invoice text-primary me-2"></i><?= esc($dc['dc_number']) ?>
                                </a>
                            </td>
                            <td><?= date('d/m/Y', strtotime($dc['dc_date'])) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;min-width:32px;font-size:0.8rem;">
                                        <?= strtoupper(substr($dc['vendor_name'] ?? 'T', 0, 1)) ?>
                                    </div>
                                    <span class="fw-semibold text-dark"><?= esc($dc['vendor_name']) ?></span>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php 
                                $statusClass = match($dc['status']) {
                                    'Open'               => 'primary',
                                    'Partially Received' => 'warning',
                                    'Completed'          => 'success',
                                    'Cancelled'          => 'danger',
                                    default              => 'secondary'
                                };
                                ?>
                                <span class="badge text-bg-<?= $statusClass ?> px-2 py-1">
                                    <?= esc($dc['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('production/yarn-twisting/view/'.$dc['id']) ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    <?php if($dc['status'] === 'Open'): ?>
                                        <?php if (can('yarn_twisting.edit')): ?>
                                        <a href="<?= site_url('production/yarn-twisting/edit/'.$dc['id']) ?>" class="btn btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                        <?php endif; ?>
                                        <?php if (can('yarn_twisting.delete')): ?>
                                        <a href="<?= site_url('production/yarn-twisting/delete/'.$dc['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this DC?')" title="Delete"><i class="fas fa-trash"></i></a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-sync-alt fa-2x text-muted mb-2 d-block"></i>
                                No twisting delivery challans found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
