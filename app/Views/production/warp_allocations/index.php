<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-network-wired text-primary me-2"></i><?= esc($title) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('production/warp-allocations') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('production/warp-allocations/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Allocation
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalAllocations = count($allocations ?? []);
$allocatedCount = 0;
$weavingCount = 0;
$completedCount = 0;

if (!empty($allocations)) {
    foreach ($allocations as $a) {
        $st = $a['status'] ?? '';
        if ($st === 'Allocated') $allocatedCount++;
        elseif ($st === 'In Weaving' || $st === 'Partially Returned') $weavingCount++;
        elseif ($st === 'Completed') $completedCount++;
    }
}
?>

<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-network-wired bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalAllocations) ?></div>
            <div class="bt-stat-label">Total Warp Allocations</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-indigo" style="cursor:default;">
            <i class="fas fa-dolly bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($allocatedCount) ?></div>
            <div class="bt-stat-label">Allocated (Ready)</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalAllocations > 0) ? min(100, round(($allocatedCount / $totalAllocations) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-amber" style="cursor:default;">
            <i class="fas fa-cogs bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($weavingCount) ?></div>
            <div class="bt-stat-label">In Weaving Process</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalAllocations > 0) ? min(100, round(($weavingCount / $totalAllocations) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-green" style="cursor:default;">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($completedCount) ?></div>
            <div class="bt-stat-label">Fully Completed</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalAllocations > 0) ? min(100, round(($completedCount / $totalAllocations) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header py-2 bg-white">
        <h3 class="card-title fw-bold">Warp Allocation Records</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 130px;">Allocation #</th>
                        <th style="min-width: 100px;">Date</th>
                        <th style="min-width: 180px;">Weaver</th>
                        <th style="min-width: 90px;">Loom</th>
                        <th style="min-width: 120px;">Warp Beam #</th>
                        <th class="text-center" style="min-width: 110px;">Contract Type</th>
                        <th style="min-width: 130px;">Ref Doc</th>
                        <th class="text-center" style="min-width: 120px;">Status</th>
                        <th class="text-end" style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($allocations)): ?>
                        <?php foreach($allocations as $allocation): ?>
                            <tr>
                                <td>
                                    <a href="<?= site_url('production/warp-allocations/view/' . $allocation['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                        <?= esc($allocation['allocation_number']) ?>
                                    </a>
                                </td>
                                <td><?= date('d/m/Y', strtotime($allocation['allocation_date'])) ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:30px;height:30px;min-width:30px;font-size:0.75rem;">
                                            <?= strtoupper(substr($allocation['weaver_name'] ?? 'W', 0, 1)) ?>
                                        </div>
                                        <span class="fw-semibold text-dark"><?= esc($allocation['weaver_name']) ?></span>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= esc($allocation['loom_number'] ?: '-') ?></span></td>
                                <td><?= $allocation['warp_beam_id'] ? '<code class="text-dark">'.esc($allocation['beam_number']).'</code>' : '<span class="text-muted">None</span>' ?></td>
                                <td class="text-center"><span class="badge text-bg-info px-2 py-1"><?= esc($allocation['contract_type']) ?></span></td>
                                <td>
                                    <?php if($allocation['reference_document_type'] == 'Sales Invoice'): ?>
                                        <a href="<?= site_url('bills/view/' . $allocation['reference_document_id']) ?>" target="_blank" class="badge bg-light text-primary border text-decoration-none">
                                            Sales Inv #<?= $allocation['reference_document_id'] ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">DC</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $statusClass = match($allocation['status']) {
                                            'Allocated'          => 'primary',
                                            'In Weaving'         => 'warning',
                                            'Partially Returned' => 'info',
                                            'Completed'          => 'success',
                                            default              => 'secondary'
                                        };
                                    ?>
                                    <span class="badge text-bg-<?= $statusClass ?> px-2 py-1"><?= esc($allocation['status']) ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('production/warp-allocations/view/' . $allocation['id']) ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                        <?php if(in_array($allocation['status'], ['Allocated', 'In Weaving', 'Partially Returned'])): ?>
                                            <a href="<?= site_url('production/receipts/create/' . $allocation['id']) ?>" class="btn btn-outline-success" title="Receive Goods"><i class="fas fa-box-open"></i></a>
                                        <?php endif; ?>
                                        <?php if($allocation['status'] == 'Allocated' && in_array('weaver.delete', session('permissions') ?? [])): ?>
                                            <a href="<?= site_url('production/warp-allocations/delete/' . $allocation['id']) ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this allocation?');"><i class="fas fa-trash"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-network-wired fa-2x text-muted mb-2 d-block"></i>
                                No warp allocations found.
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
        if ($('.datatable').length && $.fn.DataTable) {
            $('.datatable').DataTable({
                "order": [[ 0, "desc" ]],
                "responsive": true
            });
        }
    });
</script>
<?= $this->endSection() ?>
