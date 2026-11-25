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
<div class="row g-2 g-md-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-file-invoice bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalPurchases) ?></div>
            <div class="bt-stat-label">Total Invoices</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-weight-hanging bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalWeight, 2) ?> <small style="font-size:1rem;">Kg</small></div>
            <div class="bt-stat-label">Total Weight Inward</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="bt-stat bt-g-amber" style="cursor:default;">
            <i class="fas fa-cubes bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalCones) ?></div>
            <div class="bt-stat-label">Total Cones Received</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
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
        <!-- Mobile Card List -->
        <div class="erp-mobile-list p-2 bg-light">
            <?php if (!empty($purchases)): ?>
                <?php foreach ($purchases as $p): ?>
                    <?php
                        $weight = (float)$p['total_weight_kg'];
                        $rate = (float)$p['rate_per_kg'];
                        $transport = (float)($p['transport_charges'] ?? 0);
                        $other = (float)($p['other_charges'] ?? 0);
                        $totalExclTax = ($weight * $rate) + $transport + $other;
                        $landedCostPerKgExcl = $weight > 0 ? ($totalExclTax / $weight) : 0;
                    ?>
                    <a href="<?= site_url('production/yarn-purchases/view/'.$p['id']) ?>" class="erp-mobile-card">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-bold text-dark" style="white-space: normal; word-break: break-word; line-height: 1.35; min-width: 0; flex: 1 1 auto;">
                                <?= esc($p['supplier']) ?>
                            </div>
                            <span class="badge text-bg-secondary" style="flex: 0 0 auto;"><?= esc($p['yarn_count']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mt-1">
                            <span class="text-muted small" style="white-space: normal; word-break: break-word; min-width: 0; flex: 1 1 auto;">
                                <?= esc($p['invoice_number']) ?> &middot; <?= date('d/m/Y', strtotime($p['purchase_date'])) ?> &middot; <?= number_format($weight, 2) ?> Kg
                            </span>
                            <span class="fw-bold small text-success" style="flex: 0 0 auto; white-space: nowrap;">
                                ₹<?= number_format($landedCostPerKgExcl, 2) ?>/Kg
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">No yarn purchase invoices recorded.</div>
            <?php endif; ?>
        </div>

        <!-- Desktop Table -->
        <div class="table-responsive erp-desktop-wrap">
            <table class="table table-striped table-hover align-middle mb-0 w-100" id="purchasesTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 48px;">#</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Invoice #</th>
                        <th>Mill Name</th>
                        <th class="text-center">Count</th>
                        <th class="text-center">Lot #</th>
                        <th class="text-end">Weight (Kg)</th>
                        <th class="text-center">Cones</th>
                        <th class="text-end">Rate/Kg</th>
                        <th class="text-end">Landed/Kg</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($purchases)): ?>
                        <?php foreach($purchases as $idx => $p): ?>
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
                            <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                            <td class="text-nowrap"><?= date('d/m/Y', strtotime($p['purchase_date'])) ?></td>
                            <td style="white-space: normal; word-break: break-word;">
                                <span class="fw-semibold text-dark"><?= esc($p['supplier']) ?></span>
                            </td>
                            <td class="text-nowrap">
                                <a href="<?= site_url('production/yarn-purchases/view/'.$p['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= esc($p['invoice_number']) ?>
                                </a>
                            </td>
                            <td style="white-space: normal; word-break: break-word;"><?= esc($p['mill_name'] ?: '-') ?></td>
                            <td class="text-center text-nowrap"><span class="badge text-bg-secondary"><?= esc($p['yarn_count']) ?></span></td>
                            <td class="text-center text-nowrap"><code class="text-dark"><?= esc($p['lot_number'] ?: '-') ?></code></td>
                            <td class="text-end fw-bold text-dark text-nowrap"><?= number_format($weight, 2) ?> Kg</td>
                            <td class="text-center fw-semibold text-nowrap"><?= (int)($p['number_cones'] ?? 0) ?></td>
                            <td class="text-end text-nowrap">₹<?= number_format($rate, 2) ?></td>
                            <td class="text-end text-nowrap"><span class="badge text-bg-success px-2 py-1">₹<?= number_format($landedCostPerKgExcl, 2) ?></span></td>
                            <td class="text-end text-nowrap">
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
                            <td colspan="12" class="text-center py-4 text-muted">
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
