<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Yarn Reports<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$summaryStock = 0;
$summaryValuation = 0;
if (!empty($inventorySummary)) {
    foreach ($inventorySummary as $item) {
        $st = (float)($item['stock'] ?? 0);
        $cs = (float)($item['avg_cost'] ?? 0);
        $summaryStock += $st;
        $summaryValuation += ($st * $cs);
    }
}
$pendingDcCount = !empty($pendingDcs) ? count($pendingDcs) : 0;
$pendingWeight = 0;
if (!empty($pendingDcs)) {
    foreach ($pendingDcs as $dc) {
        $pendingWeight += max(0, (float)($dc['total_issued'] ?? 0) - (float)($dc['total_received'] ?? 0));
    }
}
?>

<!-- Hero Header -->
<div class="bt-hero mb-3 d-print-none">
    <div class="row align-items-center g-3">
        <div class="col-md-7">
            <h2 class="h4 mb-1 text-white fw-bold"><i class="fas fa-chart-pie me-2"></i> Yarn Inventory & Job Work Reports</h2>
            <p class="mb-0 text-white-50 small">Consolidated analytics across raw materials, vendor job work pipelines, wastage, and inventory valuation.</p>
        </div>
        <div class="col-md-5 text-md-end">
            <a href="<?= site_url('production/yarn-inventory') ?>" class="btn btn-light btn-sm fw-semibold shadow-sm me-2">
                <i class="fas fa-warehouse me-1 text-primary"></i> Live Inventory
            </a>
            <button onclick="window.print();" class="btn btn-warning btn-sm fw-semibold shadow-sm">
                <i class="fas fa-print me-1"></i> Print All Reports
            </button>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-3 d-print-none">
    <div class="col-6 col-lg-3">
        <div class="bt-stat bt-g-blue">
            <div class="bt-stat-title">Available Yarn Stock</div>
            <div class="bt-stat-val"><?= number_format($summaryStock, 2) ?> <small class="fs-6 fw-normal">Kg</small></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-cubes me-1"></i> In Warehouse Stock</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bt-stat bt-g-green">
            <div class="bt-stat-title">Total Valuation</div>
            <div class="bt-stat-val">₹<?= number_format($summaryValuation, 2) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-coins me-1"></i> Landed Value in Hand</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bt-stat <?= $pendingDcCount > 0 ? 'bt-g-amber' : 'bt-g-slate' ?>">
            <div class="bt-stat-title">Open Job Work DCs</div>
            <div class="bt-stat-val"><?= number_format($pendingDcCount) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-tasks me-1"></i> Pending at Vendors</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bt-stat bt-g-purple">
            <div class="bt-stat-title">Yarn in Process</div>
            <div class="bt-stat-val"><?= number_format($pendingWeight, 2) ?> <small class="fs-6 fw-normal">Kg</small></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-truck-moving me-1"></i> Under Outsourced Work</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm card-tabs">
    <div class="card-header bg-white p-0 border-bottom d-print-none">
        <ul class="nav nav-tabs border-0 px-3 pt-2" id="reportTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active fw-semibold" id="inventory-tab" data-bs-toggle="pill" href="#inventory" role="tab">
                    <i class="fas fa-warehouse me-1 text-primary"></i> Inventory Report
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-semibold" id="jobwork-tab" data-bs-toggle="pill" href="#jobwork" role="tab">
                    <i class="fas fa-tasks me-1 text-warning"></i> Pending Job Works
                    <?php if ($pendingDcCount > 0): ?>
                        <span class="badge rounded-pill bg-danger ms-1"><?= $pendingDcCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-semibold" id="wastage-tab" data-bs-toggle="pill" href="#wastage" role="tab">
                    <i class="fas fa-recycle me-1 text-danger"></i> Wastage Analysis
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-semibold" id="costing-tab" data-bs-toggle="pill" href="#costing" role="tab">
                    <i class="fas fa-calculator me-1 text-success"></i> Costing & Valuation
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-4">
        <div class="tab-content" id="reportTabsContent">
            <!-- 1. Inventory Report Tab -->
            <div class="tab-pane fade show active" id="inventory" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-boxes me-2 text-primary"></i> Current Available Yarn Stock</h5>
                    <span class="badge text-bg-light border text-muted">Auto-calculated from Inward & Allocations</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-report">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th>Yarn Count</th>
                                <th>Type</th>
                                <th>Color</th>
                                <th>Mill / Brand</th>
                                <th>Stock (Kg)</th>
                                <th>Avg Cost/Kg</th>
                                <th>Valuation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $totalStock = 0;
                                $totalValuation = 0;
                            ?>
                            <?php if(!empty($inventorySummary)): ?>
                                <?php foreach($inventorySummary as $item): ?>
                                <?php 
                                    $stock = (float)$item['stock'];
                                    $cost = (float)$item['avg_cost'];
                                    $valuation = $stock * $cost;
                                    $totalStock += $stock;
                                    $totalValuation += $valuation;
                                ?>
                                <tr>
                                    <td><span class="badge text-bg-primary font-monospace"><?= esc($item['yarn_count']) ?></span></td>
                                    <td>
                                        <span class="badge text-bg-<?= $item['yarn_type'] === 'Dyed' ? 'info' : 'secondary' ?>">
                                            <?= esc($item['yarn_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?= esc($item['color'] ?: 'Raw') ?></span>
                                    </td>
                                    <td><?= esc($item['brand_mill']) ?></td>
                                    <td><strong class="text-primary"><?= number_format($stock, 2) ?> Kg</strong></td>
                                    <td>₹<?= number_format($cost, 2) ?></td>
                                    <td><strong class="text-success">₹<?= number_format($valuation, 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light font-weight-bold">
                            <tr>
                                <td colspan="4" class="text-end"><strong>Total Stock & Valuation:</strong></td>
                                <td><strong class="text-primary"><?= number_format($totalStock, 2) ?> Kg</strong></td>
                                <td>-</td>
                                <td><strong class="text-success fs-6">₹<?= number_format($totalValuation, 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- 2. Job Work Tab -->
            <div class="tab-pane fade" id="jobwork" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-truck-loading me-2 text-warning"></i> Pending Delivery Challans & Stock at Vendors</h5>
                    <span class="badge text-bg-warning text-dark">Open / In-Progress DCs</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-report">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th>DC Number</th>
                                <th>DC Date</th>
                                <th>Vendor Name</th>
                                <th>Job Work Type</th>
                                <th>Issued Qty</th>
                                <th>Received Qty</th>
                                <th>Pending Qty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($pendingDcs)): ?>
                                <?php foreach($pendingDcs as $dc): ?>
                                <?php 
                                    $issued = (float)$dc['total_issued'];
                                    $received = (float)$dc['total_received'];
                                    $pending = $issued - $received;
                                ?>
                                <tr>
                                    <td>
                                        <a href="<?= site_url('production/yarn-job-work/view/'.$dc['id']) ?>" class="fw-bold text-decoration-none">
                                            <?= esc($dc['dc_number']) ?>
                                        </a>
                                    </td>
                                    <td><?= esc($dc['dc_date']) ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= esc($dc['vendor_name']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-secondary"><?= esc($dc['job_work_type']) ?></span>
                                    </td>
                                    <td><?= number_format($issued, 2) ?> Kg</td>
                                    <td><?= number_format($received, 2) ?> Kg</td>
                                    <td>
                                        <strong class="text-danger"><?= number_format(max(0, $pending), 2) ?> Kg</strong>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-warning text-dark"><?= esc($dc['status']) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No pending job work challans. All clear!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Wastage Report Tab -->
            <div class="tab-pane fade" id="wastage" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-recycle me-2 text-danger"></i> Vendor-wise Yarn Wastage Analysis</h5>
                    <span class="badge text-bg-light border text-muted">Benchmark Threshold: 3.00%</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-report">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th>Vendor Name</th>
                                <th>Total Issued (Kg)</th>
                                <th>Total Received (Kg)</th>
                                <th>Wastage (Kg)</th>
                                <th>Wastage (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($vendorWastage)): ?>
                                <?php foreach($vendorWastage as $w): ?>
                                <?php 
                                    $issued = (float)$w['issued'];
                                    $received = (float)$w['received'];
                                    $wastage = (float)$w['wastage'];
                                    $wastagePercent = $issued > 0 ? ($wastage / $issued) * 100 : 0;
                                ?>
                                <tr>
                                    <td><strong class="text-dark"><?= esc($w['vendor_name']) ?></strong></td>
                                    <td><?= number_format($issued, 2) ?> Kg</td>
                                    <td><?= number_format($received, 2) ?> Kg</td>
                                    <td><strong class="text-warning"><?= number_format($wastage, 2) ?> Kg</strong></td>
                                    <td>
                                        <span class="badge text-bg-<?= $wastagePercent > 3 ? 'danger' : 'success' ?> px-2 py-1 fs-7">
                                            <?= number_format($wastagePercent, 2) ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. Costing & Valuation Tab -->
            <div class="tab-pane fade" id="costing" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-coins me-2 text-success"></i> Yarn Cost & Inventory Valuation Breakdown</h5>
                    <span class="badge text-bg-success">Financial Balance Asset</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-report">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th>Yarn Count</th>
                                <th>Yarn Type</th>
                                <th>Color</th>
                                <th>Total Stock (Kg)</th>
                                <th>Total Valuation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $grandValuation = 0;
                            ?>
                            <?php if(!empty($valuation)): ?>
                                <?php foreach($valuation as $v): ?>
                                <?php 
                                    $val = (float)$v['total_value'];
                                    $grandValuation += $val;
                                ?>
                                <tr>
                                    <td><span class="badge text-bg-primary font-monospace"><?= esc($v['yarn_count']) ?></span></td>
                                    <td>
                                        <span class="badge text-bg-<?= $v['yarn_type'] === 'Dyed' ? 'info' : 'secondary' ?>">
                                            <?= esc($v['yarn_type']) ?>
                                        </span>
                                    </td>
                                    <td><span class="fw-semibold text-dark"><?= esc($v['color'] ?: 'Raw') ?></span></td>
                                    <td><strong class="text-primary"><?= number_format($v['stock'], 2) ?> Kg</strong></td>
                                    <td><strong class="text-success">₹<?= number_format($val, 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light font-weight-bold">
                            <tr>
                                <td colspan="4" class="text-end"><strong>Grand Total Valuation:</strong></td>
                                <td><strong class="text-success fs-5">₹<?= number_format($grandValuation, 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .main-sidebar,
    .app-header,
    .app-footer,
    .d-print-none,
    .nav-tabs,
    .btn {
        display: none !important;
    }
    .content-wrapper,
    .app-content {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .tab-pane {
        display: block !important;
        opacity: 1 !important;
        margin-bottom: 40px;
        page-break-after: always;
    }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        if ($.fn.DataTable && !$.fn.DataTable.isDataTable('.datatable-report')) {
            $('.datatable-report').DataTable({
                "pageLength": 25,
                "responsive": true
            });
        }
    });
</script>
<?= $this->endSection() ?>
