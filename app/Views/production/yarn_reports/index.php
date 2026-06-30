<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Yarn Reports<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2 d-print-none">
    <div class="col-sm-6">
        <h1 class="m-0">Yarn Inventory & Job Work Reports</h1>
    </div>
    <div class="col-sm-6 text-end">
        <button onclick="window.print();" class="btn btn-dark"><i class="fas fa-print"></i> Print Report Page</button>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-primary card-tabs">
    <div class="card-header p-0 pt-1 d-print-none">
        <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="inventory-tab" data-bs-toggle="pill" href="#inventory" role="tab" aria-controls="inventory" aria-selected="true">
                    <i class="fas fa-warehouse me-1"></i> Inventory Report
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="jobwork-tab" data-bs-toggle="pill" href="#jobwork" role="tab" aria-controls="jobwork" aria-selected="false">
                    <i class="fas fa-tasks me-1"></i> Pending Job Works
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="wastage-tab" data-bs-toggle="pill" href="#wastage" role="tab" aria-controls="wastage" aria-selected="false">
                    <i class="fas fa-recycle me-1"></i> Wastage Report
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="costing-tab" data-bs-toggle="pill" href="#costing" role="tab" aria-controls="costing" aria-selected="false">
                    <i class="fas fa-calculator me-1"></i> Costing & Valuation
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="custom-tabs-one-tabContent">
            <!-- 1. Inventory Report Tab -->
            <div class="tab-pane fade show active" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                <h4 class="mb-3">Current Available Yarn Stock</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped datatable-report">
                        <thead>
                            <tr class="bg-light">
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
                                    <td><span class="badge bg-secondary"><?= esc($item['yarn_count']) ?></span></td>
                                    <td>
                                        <span class="badge bg-<?= $item['yarn_type'] === 'Dyed' ? 'info' : 'light text-dark border' ?>">
                                            <?= esc($item['yarn_type']) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($item['color'] ?: 'Raw') ?></td>
                                    <td><?= esc($item['brand_mill']) ?></td>
                                    <td><strong><?= number_format($stock, 2) ?> Kg</strong></td>
                                    <td>₹<?= number_format($cost, 2) ?></td>
                                    <td><strong>₹<?= number_format($valuation, 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light font-weight-bold">
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td><strong><?= number_format($totalStock, 2) ?> Kg</strong></td>
                                <td>-</td>
                                <td><strong class="text-success">₹<?= number_format($totalValuation, 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- 2. Job Work Tab -->
            <div class="tab-pane fade" id="jobwork" role="tabpanel" aria-labelledby="jobwork-tab">
                <h4 class="mb-3">Pending Delivery Challans & Stock at Vendors</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped datatable-report">
                        <thead>
                            <tr class="bg-light">
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
                                    <td><strong><a href="<?= site_url('production/yarn-job-work/view/'.$dc['id']) ?>"><?= esc($dc['dc_number']) ?></a></strong></td>
                                    <td><?= esc($dc['dc_date']) ?></td>
                                    <td><?= esc($dc['vendor_name']) ?></td>
                                    <td><span class="badge bg-secondary"><?= esc($dc['job_work_type']) ?></span></td>
                                    <td><?= number_format($issued, 2) ?> Kg</td>
                                    <td><?= number_format($received, 2) ?> Kg</td>
                                    <td><strong class="text-danger"><?= number_format(max(0, $pending), 2) ?> Kg</strong></td>
                                    <td>
                                        <span class="badge bg-warning"><?= esc($dc['status']) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No pending job work challans.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Wastage Report Tab -->
            <div class="tab-pane fade" id="wastage" role="tabpanel" aria-labelledby="wastage-tab">
                <h4 class="mb-3">Vendor-wise Yarn Wastage Report</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped datatable-report">
                        <thead>
                            <tr class="bg-light">
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
                                    <td><strong><?= esc($w['vendor_name']) ?></strong></td>
                                    <td><?= number_format($issued, 2) ?> Kg</td>
                                    <td><?= number_format($received, 2) ?> Kg</td>
                                    <td class="text-warning"><strong><?= number_format($wastage, 2) ?> Kg</strong></td>
                                    <td>
                                        <strong class="text-<?= $wastagePercent > 3 ? 'danger' : 'warning' ?>">
                                            <?= number_format($wastagePercent, 2) ?>%
                                        </strong>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. Costing & Valuation Tab -->
            <div class="tab-pane fade" id="costing" role="tabpanel" aria-labelledby="costing-tab">
                <h4 class="mb-3">Yarn Cost & Inventory Valuation</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped datatable-report">
                        <thead>
                            <tr class="bg-light">
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
                                    <td><span class="badge bg-secondary"><?= esc($v['yarn_count']) ?></span></td>
                                    <td>
                                        <span class="badge bg-<?= $v['yarn_type'] === 'Dyed' ? 'info' : 'light text-dark border' ?>">
                                            <?= esc($v['yarn_type']) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($v['color'] ?: 'Raw') ?></td>
                                    <td><strong><?= number_format($v['stock'], 2) ?> Kg</strong></td>
                                    <td><strong>₹<?= number_format($val, 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light font-weight-bold">
                                <td colspan="4" class="text-end"><strong>Grand Total Valuation:</strong></td>
                                <td><strong class="text-success text-lg">₹<?= number_format($grandValuation, 2) ?></strong></td>
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
        $('.datatable-report').DataTable({
            "pageLength": 25
        });
    });
</script>
<?= $this->endSection() ?>
