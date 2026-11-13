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
<div class="row g-3 mb-3">
    <div class="col-md-4 col-sm-6">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-percentage bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalTaxes) ?></div>
            <div class="bt-stat-label">Configured Tax Rates</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="bt-stat bt-g-green" style="cursor:default;">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($activeTaxes) ?></div>
            <div class="bt-stat-label">Active Applicable Rates</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalTaxes > 0) ? min(100, round(($activeTaxes / $totalTaxes) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
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
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0" id="taxesTable">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 180px;">Tax Name</th>
                        <th class="text-center" style="min-width: 120px;">Percentage</th>
                        <th class="text-center" style="min-width: 100px;">Status</th>
                        <th class="text-end" style="min-width: 110px;">Actions</th>
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

<?= $this->section('scripts') ?>
<script>
    $(function () {
        if ($("#taxesTable").length && $.fn.DataTable) {
            $("#taxesTable").DataTable({
                "responsive": true,
                "autoWidth": false,
            });
        }
    });
</script>
<?= $this->endSection() ?>
