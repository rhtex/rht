<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Tax Settings<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-percentage text-primary me-2"></i>Tax Rate Settings</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= base_url('settings/taxes') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <?php if(can('tax.create')): ?>
            <a href="<?= base_url('settings/taxes/new') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Tax Rate
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalTaxes = count($taxes ?? []);
$activeTaxes = 0;
$inactiveTaxes = 0;

if (!empty($taxes)) {
    foreach ($taxes as $t) {
        if (($t['status'] ?? '') === 'Active') {
            $activeTaxes++;
        } else {
            $inactiveTaxes++;
        }
    }
}
?>

<!-- Summary Stat Cards -->
<div class="row g-2 g-md-3 mb-3">
    <div class="col-6 col-md-4">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-percentage bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalTaxes) ?></div>
            <div class="bt-stat-label">Configured Tax Rates</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="bt-stat bt-g-green" style="cursor:default;">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($activeTaxes) ?></div>
            <div class="bt-stat-label">Active Applicable Rates</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalTaxes > 0) ? min(100, round(($activeTaxes / $totalTaxes) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="bt-stat bt-g-slate" style="cursor:default;">
            <i class="fas fa-pause-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($inactiveTaxes) ?></div>
            <div class="bt-stat-label">Archived / Inactive Rates</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalTaxes > 0) ? min(100, round(($inactiveTaxes / $totalTaxes) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header py-2 bg-white">
        <h3 class="card-title fw-bold">GST & Applicable Tax Slabs</h3>
    </div>
    <div class="card-body p-0">
        <div class="erp-mobile-list p-2 bg-light">
            <?php if(!empty($taxes)): ?>
                <?php foreach ($taxes as $tax): ?>
                    <a href="<?= can('tax.edit') ? base_url('settings/taxes/edit/' . $tax['id']) : '#' ?>" class="erp-mobile-card">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-bold text-dark" style="white-space: normal; word-break: break-word; line-height: 1.35; min-width: 0; flex: 1 1 auto;">
                                <?= esc($tax['name']) ?>
                            </div>
                            <span class="badge text-bg-<?= ($tax['status'] ?? '') === 'Active' ? 'success' : 'secondary' ?>" style="flex: 0 0 auto;">
                                <?= esc($tax['status']) ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mt-1">
                            <span class="text-muted small">Tax Rate</span>
                            <span class="fw-bold small text-primary"><?= esc($tax['percentage']) ?>%</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">No tax rates configured.</div>
            <?php endif; ?>
        </div>
        <div class="table-responsive erp-desktop-wrap">
            <table class="table table-striped table-hover align-middle mb-0 w-100" id="taxesTable">
                <thead class="table-light">
                    <tr>
                        <th>Tax Name</th>
                        <th class="text-center">Percentage</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($taxes)): ?>
                        <?php foreach ($taxes as $tax): ?>
                            <tr>
                                <td>
                                    <strong class="text-dark"><i class="fas fa-percent text-primary me-2"></i><?= esc($tax['name']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge text-bg-primary px-3 py-1 fs-6">
                                        <?= esc($tax['percentage']) ?>%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge text-bg-<?= ($tax['status'] ?? '') === 'Active' ? 'success' : 'secondary' ?>">
                                        <?= esc($tax['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <?php if(can('tax.edit')): ?>
                                        <a href="<?= base_url('settings/taxes/edit/' . $tax['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if(can('tax.delete')): ?>
                                        <a href="<?= base_url('settings/taxes/delete/' . $tax['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this tax slab?')" title="Delete">
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
                                <i class="fas fa-percentage fa-2x text-muted mb-2 d-block"></i>
                                No tax rates configured.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
