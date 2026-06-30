<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Delivery Challan Details<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2 d-print-none">
    <div class="col-sm-6">
        <h1>Delivery Challan: <?= esc($dc['dc_number']) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="btn-group">
            <a href="<?= site_url('production/yarn-job-work?type='.$dc['job_work_type']) ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            <button onclick="window.print();" class="btn btn-dark"><i class="fas fa-print"></i> Print Challan</button>
            <?php if((in_array('weaver.edit', session('permissions') ?? []) || in_array('weaver.create', session('permissions') ?? [])) && $dc['status'] === 'Open'): ?>
            <a href="<?= site_url('production/yarn-job-work/edit/'.$dc['id']) ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit DC</a>
            <a href="<?= site_url('production/yarn-job-work/delete/'.$dc['id']) ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this Delivery Challan? This will restore the issued stock.')"><i class="fas fa-trash"></i> Delete DC</a>
            <?php endif; ?>
            <?php if(in_array('weaver.create', session('permissions') ?? []) && ($dc['status'] === 'Open' || $dc['status'] === 'Partially Received')): ?>
            <a href="<?= site_url('production/yarn-job-work/receipt/'.$dc['id']) ?>" class="btn btn-success"><i class="fas fa-arrow-down"></i> Receive Yarn</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Printable Invoice Header -->
<div class="invoice p-3 mb-3">
    <!-- title row -->
    <div class="row">
        <div class="col-12">
            <h4>
                <i class="fas fa-file-contract"></i> Rasi Handloom Textiles
                <small class="float-end">Date: <?= esc($dc['dc_date']) ?></small>
            </h4>
        </div>
    </div>
    <hr>
    <!-- info row -->
    <div class="row invoice-info mb-4">
        <div class="col-sm-4 invoice-col">
            From
            <address>
                <strong>Rasi Handloom Textiles</strong><br>
                Rasipuram, Namakkal District<br>
                Tamil Nadu, India<br>
                Email: info@rhtex.in
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            To (Job Worker)
            <address>
                <strong><?= esc($dc['vendor_name']) ?></strong><br>
                Job Work Type: <strong><?= esc($dc['job_work_type']) ?></strong>
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            <b>Challan No:</b> <?= esc($dc['dc_number']) ?><br>
            <b>Challan Date:</b> <?= esc($dc['dc_date']) ?><br>
            <b>Expected Return:</b> <?= esc($dc['expected_return_date'] ?: '-') ?><br>
            <b>Vehicle Details:</b> <?= esc($dc['vehicle_details'] ?: '-') ?><br>
            <b>Status:</b> 
            <span class="badge bg-<?php 
                if ($dc['status'] === 'Open') echo 'primary';
                elseif ($dc['status'] === 'Partially Received') echo 'warning';
                elseif ($dc['status'] === 'Completed') echo 'success';
                else echo 'danger';
            ?>"><?= esc($dc['status']) ?></span>
        </div>
    </div>

    <!-- Table row -->
    <div class="row">
        <div class="col-12 table-responsive">
            <h5 class="text-primary mb-2"><i class="fas fa-list"></i> Issued Items & Status</h5>
            <table class="table table-bordered">
                <thead>
                    <tr class="bg-light">
                        <th>Mill / Brand</th>
                        <th>Yarn Count</th>
                        <th>Warp/Weft</th>
                        <th>Lot No</th>
                        <th>CSP</th>
                        <th>Issued Color</th>
                        <th>Required Color</th>
                        <th>Qty Issued (Kg)</th>
                        <th>Qty Received (Kg)</th>
                        <th>Qty Returned (Kg)</th>
                        <th>Qty Wastage (Kg)</th>
                        <th>Balance Pending (Kg)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): ?>
                    <?php 
                        $issued = (float)$item['quantity_issued_kg'];
                        $received = (float)$item['quantity_received_kg'];
                        $returned = (float)($item['quantity_returned_kg'] ?? 0);
                        $wastage = (float)$item['quantity_wastage_kg'];
                        $pending = $issued - ($received + $returned + $wastage);
                    ?>
                    <tr>
                        <td><?= esc($item['mill_name']) ?></td>
                        <td><span class="badge bg-secondary"><?= esc($item['yarn_count']) ?></span></td>
                        <td><?= esc($item['warp_weft']) ?></td>
                        <td><?= esc($item['lot_number'] ?: '-') ?></td>
                        <td><?= esc($item['csp'] ?: '-') ?></td>
                        <td><?= esc($item['current_color'] ?: 'Raw') ?></td>
                        <td><?= esc($item['required_color'] ?: '-') ?></td>
                        <td><strong><?= number_format($issued, 2) ?> Kg</strong></td>
                        <td class="text-success"><?= number_format($received, 2) ?> Kg</td>
                        <td class="text-info"><?= number_format($returned, 2) ?> Kg</td>
                        <td class="text-warning"><?= number_format($wastage, 2) ?> Kg</td>
                        <td>
                            <strong class="text-<?= $pending > 0 ? 'danger' : 'success' ?>">
                                <?= number_format(max(0, $pending), 2) ?> Kg
                            </strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Warping & Sizing Technical Specifications Sheet -->
    <?php if ($dc['job_work_type'] === 'Warping & Sizing' || $dc['job_work_type'] === 'Warping' || $dc['job_work_type'] === 'Sizing'): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h5 class="card-title text-info"><i class="fas fa-drafting-compass"></i> Warping & Sizing Technical Sheet</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Design Pattern:</strong> 
                            <span class="text-muted"><?= esc($dc['design_pattern'] ?: 'Not specified') ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Total Ends:</strong> 
                            <span class="badge bg-primary" style="font-size: 14px;"><?= esc($dc['total_ends'] ?: '0') ?> Ends</span>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Beams Column -->
                        <div class="col-md-6">
                            <h6 class="text-info mb-2"><i class="fas fa-ring"></i> Empty Beams Sent</h6>
                            <?php if(!empty($beams)): ?>
                                <table class="table table-bordered table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Beam Number</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($beams as $beam): ?>
                                            <tr>
                                                <td><strong><?= esc($beam['beam_number']) ?></strong></td>
                                                <td><?= esc($beam['remarks'] ?: '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted small">No beams recorded.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Color Ends Column -->
                        <div class="col-md-6">
                            <h6 class="text-info mb-2"><i class="fas fa-palette"></i> Color-wise Ends Breakdown</h6>
                            <?php if(!empty($colorEnds)): ?>
                                <table class="table table-bordered table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Color</th>
                                            <th>Ends Count</th>
                                            <th>Proportion (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $totalEnds = (int)($dc['total_ends'] ?: 0);
                                        foreach($colorEnds as $ce): 
                                            $pct = $totalEnds > 0 ? (($ce['ends_count'] / $totalEnds) * 100) : 0;
                                        ?>
                                            <tr>
                                                <td><strong><?= esc($ce['color']) ?></strong></td>
                                                <td><?= number_format($ce['ends_count']) ?> Ends</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="me-2"><?= number_format($pct, 1) ?>%</span>
                                                        <div class="progress flex-grow-1" style="height: 6px;">
                                                            <div class="progress-bar bg-info" style="width: <?= $pct ?>%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted small">No color ends breakdown recorded.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-6">
            <p class="lead">Remarks:</p>
            <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
                <?= esc($dc['remarks'] ?: 'No remarks or instructions provided.') ?>
            </p>
        </div>
        <div class="col-6 text-end d-flex align-items-end justify-content-end pr-5">
            <div style="border-top: 1px solid #ddd; width: 200px; text-align: center; padding-top: 10px;">
                Authorized Signatory
            </div>
        </div>
    </div>
</div>

<!-- Receipts Section -->
<?php if(!empty($receipts)): ?>
<div class="card card-outline card-success mt-4 d-print-none">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-arrow-down me-1"></i> Receipts Against this Challan</h3>
    </div>
    <div class="card-body">
        <?php foreach($receipts as $r): ?>
        <div class="card card-secondary card-outline mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    Receipt: <strong><?= esc($r['receipt_number']) ?></strong> on <strong><?= esc($r['receipt_date']) ?></strong>
                </h5>
                <div>
                    <?php if(in_array('weaver.create', session('permissions') ?? [])): ?>
                    <a href="<?= site_url('production/yarn-job-work/receipt-edit/'.$r['id']) ?>" class="btn btn-xs btn-warning me-1">
                        <i class="fas fa-edit"></i> Edit Receipt
                    </a>
                    <?php endif; ?>
                    <?php if(in_array('weaver.delete', session('permissions') ?? []) || in_array('weaver.create', session('permissions') ?? [])): ?>
                    <a href="<?= site_url('production/yarn-job-work/receipt-delete/'.$r['id']) ?>" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to delete this receipt? This will revert the received stock from your inventory.')">
                        <i class="fas fa-trash"></i> Delete Receipt
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr class="bg-light text-sm">
                            <th>Yarn Count</th>
                            <th>Required Color</th>
                            <th>Received Qty (Kg)</th>
                            <th>Wastage (Kg)</th>
                            <th>Job Charges (₹/Kg)</th>
                            <th>Transport/Kg</th>
                            <th>Loading/Kg</th>
                            <th>Packing/Kg</th>
                            <th>Other/Kg</th>
                            <th>Est. Landed Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalWeight = 0;
                        foreach($r['items'] as $ri) {
                            $totalWeight += (float)$ri['quantity_received_kg'];
                        }
                        ?>
                        <?php foreach($r['items'] as $ri): ?>
                        <?php 
                            $issued = (float)$ri['quantity_issued_kg'];
                            $wastage = (float)$ri['quantity_wastage_kg'];
                            $recd = (float)$ri['quantity_received_kg'];
                            $wastagePct = $issued > 0 ? (($wastage / $issued) * 100) : 0;
                            $landedCost = (float)($ri['landed_cost_per_kg'] ?? 0);

                            // Pro-rated charges per Kg
                            $transPerKg = ($totalWeight > 0) ? ($r['transport_charges'] / $totalWeight) : 0;
                            $loadPerKg = ($totalWeight > 0) ? ($r['loading_charges'] / $totalWeight) : 0;
                            $packPerKg = ($totalWeight > 0) ? ($r['packing_charges'] / $totalWeight) : 0;
                            $otherPerKg = ($totalWeight > 0) ? ($r['other_expenses'] / $totalWeight) : 0;
                        ?>
                        <tr class="text-sm">
                            <td><?= esc($ri['yarn_count']) ?></td>
                            <td><?= esc($ri['required_color'] ?: '-') ?></td>
                            <td><strong class="text-success"><?= number_format($recd, 2) ?> Kg</strong></td>
                            <td>
                                <span class="text-warning"><?= number_format($wastage, 2) ?> Kg</span>
                                <?php if ($wastage > 0): ?>
                                    <span class="badge bg-warning-light text-dark ms-1" style="font-size: 10px;"><?= number_format($wastagePct, 2) ?>%</span>
                                <?php endif; ?>
                            </td>
                            <td>₹<?= number_format($ri['job_work_charges'], 2) ?></td>
                            <td>₹<?= number_format($transPerKg, 2) ?></td>
                            <td>₹<?= number_format($loadPerKg, 2) ?></td>
                            <td>₹<?= number_format($packPerKg, 2) ?></td>
                            <td>₹<?= number_format($otherPerKg, 2) ?></td>
                            <td><strong class="text-success">₹<?= number_format($landedCost, 2) ?> / Kg</strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if(!empty($r['remarks'])): ?>
            <div class="card-footer text-muted text-sm py-2">
                <strong>Remarks:</strong> <?= esc($r['remarks']) ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<style>
@media print {
    /* Hide sidebar, headers, footers, and other UI elements */
    .main-sidebar,
    .sidebar,
    aside,
    .app-header,
    .main-header,
    .main-footer,
    .app-footer,
    .d-print-none,
    .btn,
    .btn-group,
    .nav,
    .navbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
    }
    
    /* Reset margins and paddings for the main content wrapper */
    .content-wrapper,
    .app-content,
    body,
    html {
        margin: 0 !important;
        margin-left: 0 !important;
        padding: 0 !important;
        padding-left: 0 !important;
        background: #fff !important;
        width: 100% !important;
    }
    
    .invoice {
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
}
</style>
<?= $this->endSection() ?>
