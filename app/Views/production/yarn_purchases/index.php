<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Yarn Purchases<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-shopping-basket text-primary me-2"></i>Yarn Purchases</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('production/yarn-purchases') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <?php if(can('weaver.create')): ?>
            <?php if (can('yarn_purchase.create')): ?><a href="<?= site_url('production/yarn-purchases/create') ?>" class="btn btn-sm btn-primary"><?php endif; ?>
                <i class="fas fa-plus me-1"></i> Add Yarn Purchase
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalPurchases = count($purchases ?? []);
$totalWeight = 0;
$totalCones = 0;
$totalAmount = 0;
if (!empty($purchases)) {
    foreach ($purchases as $p) {
        $w = (float)($p['total_weight_kg'] ?? 0);
        $r = (float)($p['rate_per_kg'] ?? 0);
        $tc = (float)($p['transport_charges'] ?? 0);
        $oc = (float)($p['other_charges'] ?? 0);
        $totalWeight += $w;
        $totalCones += (int)($p['number_cones'] ?? 0);
        $totalAmount += ($w * $r) + $tc + $oc;
    }
}
$avgLanded = ($totalWeight > 0) ? ($totalAmount / $totalWeight) : 0;
?>

<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-file-invoice bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalPurchases) ?></div>
            <div class="bt-stat-label">Total Invoices</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-weight-hanging bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalWeight, 2) ?> <small style="font-size:1rem;">Kg</small></div>
            <div class="bt-stat-label">Total Weight Inward</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-amber" style="cursor:default;">
            <i class="fas fa-cubes bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalCones) ?></div>
            <div class="bt-stat-label">Total Cones Received</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-green" style="cursor:default;">
            <i class="fas fa-coins bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($avgLanded, 2) ?></div>
            <div class="bt-stat-label">Avg Landed Cost / Kg</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
</div>

<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header py-2 bg-white">
        <h3 class="card-title fw-bold">List of Yarn Purchase Invoices</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0" id="purchasesTable">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 100px;">Date</th>
                        <th style="min-width: 180px;">Supplier</th>
                        <th style="min-width: 110px;">Invoice #</th>
                        <th style="min-width: 150px;">Mill Name</th>
                        <th class="text-center" style="min-width: 80px;">Count</th>
                        <th class="text-center" style="min-width: 90px;">Lot #</th>
                        <th class="text-end" style="min-width: 110px;">Weight (Kg)</th>
                        <th class="text-center" style="min-width: 80px;">Cones</th>
                        <th class="text-end" style="min-width: 100px;">Rate / Kg</th>
                        <th class="text-end" style="min-width: 120px;">Landed / Kg</th>
                        <th class="text-end" style="min-width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($purchases)): ?>
                        <?php foreach($purchases as $p): ?>
                        <?php
                            $weight = (float)$p['total_weight_kg'];
                            $rate = (float)$p['rate_per_kg'];
                            $transport = (float)($p['transport_charges'] ?? 0);
                            $other = (float)($p['other_charges'] ?? 0);

                            $baseCost = $weight * $rate;
                            $totalExclTax = $baseCost + $transport + $other;
                            $landedCostPerKgExcl = $weight > 0 ? ($totalExclTax / $weight) : 0;
                        ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($p['purchase_date'])) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;min-width:32px;font-size:0.8rem;">
                                        <?= strtoupper(substr($p['supplier'] ?? 'S', 0, 1)) ?>
                                    </div>
                                    <span class="fw-semibold text-dark"><?= esc($p['supplier']) ?></span>
                                </div>
                            </td>
                            <td>
                                <a href="<?= site_url('production/yarn-purchases/view/'.$p['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= esc($p['invoice_number']) ?>
                                </a>
                            </td>
                            <td><?= esc($p['mill_name'] ?: '-') ?></td>
                            <td class="text-center"><span class="badge text-bg-secondary"><?= esc($p['yarn_count']) ?></span></td>
                            <td class="text-center"><code class="text-dark"><?= esc($p['lot_number'] ?: '-') ?></code></td>
                            <td class="text-end fw-bold text-dark"><?= number_format($weight, 2) ?> Kg</td>
                            <td class="text-center fw-semibold"><?= (int)($p['number_cones'] ?? 0) ?></td>
                            <td class="text-end">₹<?= number_format($rate, 2) ?></td>
                            <td class="text-end"><span class="badge text-bg-success px-2 py-1">₹<?= number_format($landedCostPerKgExcl, 2) ?></span></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('production/yarn-purchases/view/'.$p['id']) ?>" class="btn btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if(can('weaver.edit') || can('weaver.create')): ?>
                                    <a href="<?= site_url('production/yarn-purchases/edit/'.$p['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if(can('weaver.delete')): ?>
                                    <a href="<?= site_url('production/yarn-purchases/delete/'.$p['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this purchase?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                <i class="fas fa-shopping-basket fa-2x text-muted mb-2 d-block"></i>
                                No yarn purchase invoices recorded.
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
        if ($('#purchasesTable').length && $.fn.DataTable) {
            $('#purchasesTable').DataTable({
                "order": [[0, "desc"]],
                "pageLength": 15
            });
        }
    });
</script>
<?= $this->endSection() ?>
