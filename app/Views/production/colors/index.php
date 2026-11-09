<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-palette text-primary me-2"></i>Color Palette Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('production/colors') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('production/colors/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add Color
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalColors = count($colors ?? []);
$activeColors = 0;
$inactiveColors = 0;

if (!empty($colors)) {
    foreach ($colors as $c) {
        if (($c['status'] ?? '') === 'Active') {
            $activeColors++;
        } else {
            $inactiveColors++;
        }
    }
}
?>

<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-4 col-sm-6">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-palette bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalColors) ?></div>
            <div class="bt-stat-label">Total Color Shades</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="bt-stat bt-g-green" style="cursor:default;">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($activeColors) ?></div>
            <div class="bt-stat-label">Active Shades</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalColors > 0) ? min(100, round(($activeColors / $totalColors) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="bt-stat bt-g-slate" style="cursor:default;">
            <i class="fas fa-pause-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($inactiveColors) ?></div>
            <div class="bt-stat-label">Inactive Shades</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalColors > 0) ? min(100, round(($inactiveColors / $totalColors) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header py-2 bg-white">
        <h3 class="card-title fw-bold">Yarn & Fabric Color Master</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0" id="colorsTable">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 180px;">Color Name</th>
                        <th style="min-width: 120px;">Color Code</th>
                        <th class="text-center" style="min-width: 150px;">Color Swatch</th>
                        <th class="text-center" style="min-width: 100px;">Status</th>
                        <th class="text-end" style="min-width: 110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($colors)): ?>
                        <?php foreach($colors as $color): ?>
                        <tr>
                            <td>
                                <span class="fw-bold text-dark"><?= esc($color['name']) ?></span>
                            </td>
                            <td><code class="text-secondary"><?= esc($color['code'] ?: '-') ?></code></td>
                            <td class="text-center">
                                <?php if($color['color_palette']): ?>
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <span class="d-inline-block rounded-circle border border-2 shadow-sm" style="width: 26px; height: 26px; background-color: <?= esc($color['color_palette']) ?>;"></span>
                                        <code class="text-dark"><?= esc($color['color_palette']) ?></code>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-<?= ($color['status'] ?? '') === 'Active' ? 'success' : 'secondary' ?>">
                                    <?= esc($color['status'] ?? 'Inactive') ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('production/colors/edit/'.$color['id']) ?>" class="btn btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= site_url('production/colors/delete/'.$color['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this color?')" title="Delete"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-palette fa-2x text-muted mb-2 d-block"></i>
                                No colors found in the catalog.
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
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            if ($.fn.DataTable.isDataTable('#colorsTable')) {
                $('#colorsTable').DataTable().destroy();
            }
            $('#colorsTable').DataTable({
                "order": [[ 0, "asc" ]],
                "pageLength": 25,
                "searching": true
            });
        }
    });
</script>
<?= $this->endSection() ?>
