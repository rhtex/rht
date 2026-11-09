<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Yarns Stock<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-layer-group text-primary me-2"></i>Yarn Stock Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('production/yarns/master') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-cog me-1"></i> Predefined Yarns
            </a>
            <?php if(in_array('weaver.create', session('permissions') ?? [])): ?>
            <a href="<?= site_url('production/yarns/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Yarn Stock
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalYarns = count($yarns ?? []);
$totalStock = 0;
$dyedCount = 0;
$rawCount = 0;

if (!empty($yarns)) {
    foreach ($yarns as $y) {
        $totalStock += (float)($y['stock_kg'] ?? 0);
        if (($y['yarn_type'] ?? '') === 'Dyed') {
            $dyedCount++;
        } else {
            $rawCount++;
        }
    }
}
?>

<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('production/yarns') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['yarn_type']) ? 'active' : '' ?>">
            <i class="fas fa-layer-group bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalYarns) ?></div>
            <div class="bt-stat-label">Total Yarn Varieties</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-weight-hanging bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalStock, 2) ?> <small style="font-size:1rem;">KGs</small></div>
            <div class="bt-stat-label">Total Stock On Hand</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('production/yarns?yarn_type=Dyed') ?>" class="bt-stat bt-g-teal text-decoration-none <?= ($filters['yarn_type'] ?? '') === 'Dyed' ? 'active' : '' ?>">
            <i class="fas fa-palette bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($dyedCount) ?></div>
            <div class="bt-stat-label">Dyed Yarns</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalYarns > 0) ? min(100, round(($dyedCount / $totalYarns) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('production/yarns?yarn_type=Raw') ?>" class="bt-stat bt-g-amber text-decoration-none <?= ($filters['yarn_type'] ?? '') === 'Raw' ? 'active' : '' ?>">
            <i class="fas fa-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($rawCount) ?></div>
            <div class="bt-stat-label">Raw Yarns</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalYarns > 0) ? min(100, round(($rawCount / $totalYarns) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Yarns</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('production/yarns') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-5 col-sm-12">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by name, count, color, brand..." value="<?= esc($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2 col-sm-4">
                <label for="yarn_type" class="form-label">Yarn Type</label>
                <select name="yarn_type" id="yarn_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="Dyed" <?= (($filters['yarn_type'] ?? '') == 'Dyed') ? 'selected' : '' ?>>Dyed</option>
                    <option value="Raw" <?= (($filters['yarn_type'] ?? '') == 'Raw') ? 'selected' : '' ?>>Raw</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= (($filters['status'] ?? '') == 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= (($filters['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i> Search</button>
                <a href="<?= site_url('production/yarns') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Yarns Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header py-2 bg-white">
        <h3 class="card-title fw-bold">Yarn Stock Records</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0" id="yarnsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 180px;">Yarn Name</th>
                        <th class="text-center" style="min-width: 80px;">Count</th>
                        <th class="text-center" style="min-width: 90px;">Type</th>
                        <th style="min-width: 110px;">Color</th>
                        <th style="min-width: 140px;">Brand / Mill</th>
                        <th class="text-end" style="min-width: 120px;">Stock (KGs)</th>
                        <th class="text-center" style="min-width: 90px;">Status</th>
                        <th class="text-end" style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($yarns)): ?>
                        <?php foreach($yarns as $yarn): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?= $yarn['id'] ?></td>
                            <td>
                                <a href="<?= site_url('production/yarns/view/'.$yarn['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                    <?= esc($yarn['name']) ?>
                                </a>
                            </td>
                            <td class="text-center"><span class="badge text-bg-secondary"><?= esc($yarn['yarn_count']) ?></span></td>
                            <td class="text-center">
                                <span class="badge text-bg-<?= $yarn['yarn_type'] === 'Dyed' ? 'info' : 'secondary' ?> px-2 py-1">
                                    <?= esc($yarn['yarn_type']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= esc($yarn['color'] ?: 'Raw') ?>
                                </span>
                            </td>
                            <td><?= esc($yarn['brand'] ?: '-') ?></td>
                            <td class="text-end fw-bold text-primary fs-6"><?= number_format($yarn['stock_kg'], 2) ?> KGs</td>
                            <td class="text-center">
                                <span class="badge text-bg-<?= ($yarn['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($yarn['status'] ?? 'inactive') ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('production/yarns/view/'.$yarn['id']) ?>" class="btn btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if(in_array('weaver.edit', session('permissions') ?? [])): ?>
                                    <a href="<?= site_url('production/yarns/edit/'.$yarn['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if(in_array('weaver.delete', session('permissions') ?? [])): ?>
                                    <a href="<?= site_url('production/yarns/delete/'.$yarn['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this yarn stock?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-layer-group fa-2x text-muted mb-2 d-block"></i>
                                No yarn stock records found matching your filters.
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
    $(document).ready(function() {
        if ($('#yarnsTable').length && $.fn.DataTable) {
            $('#yarnsTable').DataTable();
        }
    });
</script>
<?= $this->endSection() ?>
