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
            <?php if (can('color.create')): ?><a href="<?= site_url('production/colors/create') ?>" class="btn btn-sm btn-primary"><?php endif; ?>
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
<div class="row g-2 g-md-3 mb-3">
    <div class="col-6 col-md-4">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-palette bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalColors) ?></div>
            <div class="bt-stat-label">Total Color Shades</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="bt-stat bt-g-green" style="cursor:default;">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($activeColors) ?></div>
            <div class="bt-stat-label">Active Shades</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalColors > 0) ? min(100, round(($activeColors / $totalColors) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-12 col-md-4">
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
        <div class="erp-mobile-list p-2 bg-light">
            <?php if(!empty($colors)): ?>
                <?php foreach($colors as $color): ?>
                    <a href="<?= can('color.edit') ? site_url('production/colors/edit/'.$color['id']) : '#' ?>" class="erp-mobile-card">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-bold text-dark d-flex align-items-center gap-2" style="white-space: normal; word-break: break-word; line-height: 1.35; min-width: 0; flex: 1 1 auto;">
                                <?php if(!empty($color['color_palette'])): ?>
                                    <span class="d-inline-block rounded-circle border shadow-sm" style="width: 18px; height: 18px; flex: 0 0 auto; background-color: <?= esc($color['color_palette']) ?>;"></span>
                                <?php endif; ?>
                                <span><?= esc($color['name']) ?></span>
                            </div>
                            <span class="badge text-bg-<?= ($color['status'] ?? '') === 'Active' ? 'success' : 'secondary' ?>" style="flex: 0 0 auto;">
                                <?= esc($color['status'] ?? 'Inactive') ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mt-1">
                            <span class="text-muted small">
                                Code: <?= esc($color['code'] ?: '-') ?>
                                <?php if(!empty($color['color_palette'])): ?>
                                    &middot; <?= esc($color['color_palette']) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted py-4">No colors found in the catalog.</div>
            <?php endif; ?>
        </div>
        <div class="table-responsive erp-desktop-wrap">
            <table class="table table-striped table-hover align-middle mb-0 w-100" id="colorsTable">
                <thead class="table-light">
                    <tr>
                        <th>Color Name</th>
                        <th>Color Code</th>
                        <th class="text-center">Color Swatch</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
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
                                    <?php if (can('color.edit')): ?>
                                    <a href="<?= site_url('production/colors/edit/'.$color['id']) ?>" class="btn btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                    <?php endif; ?>
                                    <?php if (can('color.delete')): ?>
                                    <a href="<?= site_url('production/colors/delete/'.$color['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this color?')" title="Delete"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
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
                "searching": true,
                "language": {
                    "emptyTable": "No colors found in the catalog."
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>
