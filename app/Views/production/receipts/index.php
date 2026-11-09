<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-box-open text-primary me-2"></i><?= esc($title) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('production/receipts') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('production/warp-allocations') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-network-wired me-1"></i> View Allocations
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalReceipts = count($receipts ?? []);
$approvedCount = 0;
$pendingQcCount = 0;
$cancelledCount = 0;

if (!empty($receipts)) {
    foreach ($receipts as $r) {
        $st = $r['status'] ?? '';
        if ($st === 'Approved') $approvedCount++;
        elseif ($st === 'Pending QC') $pendingQcCount++;
        elseif ($st === 'Cancelled') $cancelledCount++;
    }
}
?>

<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-box-open bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalReceipts) ?></div>
            <div class="bt-stat-label">Total Goods Inward</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-green" style="cursor:default;">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($approvedCount) ?></div>
            <div class="bt-stat-label">QC Approved Receipts</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalReceipts > 0) ? min(100, round(($approvedCount / $totalReceipts) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-amber" style="cursor:default;">
            <i class="fas fa-clipboard-check bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($pendingQcCount) ?></div>
            <div class="bt-stat-label">Pending Quality Inspection</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalReceipts > 0) ? min(100, round(($pendingQcCount / $totalReceipts) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-slate" style="cursor:default;">
            <i class="fas fa-times-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($cancelledCount) ?></div>
            <div class="bt-stat-label">Cancelled Receipts</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalReceipts > 0) ? min(100, round(($cancelledCount / $totalReceipts) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header py-2 bg-white">
        <h3 class="card-title fw-bold">Fabric & Goods Inward Receipts</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 140px;">Receipt #</th>
                        <th style="min-width: 110px;">Date</th>
                        <th style="min-width: 190px;">Weaver</th>
                        <th style="min-width: 140px;">Allocation #</th>
                        <th class="text-center" style="min-width: 120px;">QC Status</th>
                        <th style="min-width: 140px;">Ref Doc</th>
                        <th class="text-end" style="min-width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($receipts)): ?>
                        <?php foreach($receipts as $receipt): ?>
                            <tr>
                                <td>
                                    <a href="<?= site_url('production/receipts/view/' . $receipt['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                        <?= esc($receipt['receipt_number']) ?>
                                    </a>
                                </td>
                                <td><?= date('d/m/Y', strtotime($receipt['receipt_date'])) ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;min-width:32px;font-size:0.8rem;">
                                            <?= strtoupper(substr($receipt['weaver_name'] ?? 'W', 0, 1)) ?>
                                        </div>
                                        <span class="fw-semibold text-dark"><?= esc($receipt['weaver_name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">
                                        <?= esc($receipt['allocation_number']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $statusClass = match($receipt['status']) {
                                            'Pending QC' => 'warning',
                                            'Approved'   => 'success',
                                            'Cancelled'  => 'danger',
                                            default      => 'secondary'
                                        };
                                    ?>
                                    <span class="badge text-bg-<?= $statusClass ?> px-2 py-1"><?= esc($receipt['status']) ?></span>
                                </td>
                                <td>
                                    <?php if($receipt['reference_document_type'] == 'Purchase Entry'): ?>
                                        <a href="<?= site_url('bills/view/' . $receipt['reference_document_id']) ?>" target="_blank" class="badge bg-light text-primary border text-decoration-none">
                                            Purchase Bill #<?= $receipt['reference_document_id'] ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Settlement #<?= $receipt['reference_document_id'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('production/receipts/view/' . $receipt['id']) ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-box-open fa-2x text-muted mb-2 d-block"></i>
                                No fabric inward receipts recorded.
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
