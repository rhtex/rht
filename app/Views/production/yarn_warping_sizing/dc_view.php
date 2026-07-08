<?= $this->extend('layouts/master') ?>
<?= $this->section('title') ?>Warping & Sizing DC Details<?= $this->endSection() ?>
<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="fw-bold text-dark"><i class="fas fa-file-invoice text-primary me-2"></i>Warping & Sizing DC Details</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-warping-sizing') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-none"><i class="fas fa-list me-1"></i> List</a>
        <?php if ($dc['status'] !== 'Completed' && $dc['status'] !== 'Cancelled'): ?>
            <a href="<?= site_url('production/yarn-warping-sizing/receipt-create/' . $dc['id']) ?>" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm"><i class="fas fa-download me-1"></i> Receive Beams</a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<style>
    .info-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0 !important;
        border-left: 4px solid #17a2b8 !important;
        border-radius: 8px !important;
        transition: all 0.25s ease;
    }
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.04);
    }
    .info-card.date-card {
        border-left-color: #0d6efd !important;
    }
    .info-card.status-card {
        border-left-color: #ffc107 !important;
    }
    .info-card.design-card {
        border-left-color: #6f42c1 !important;
    }
    .table-premium thead th {
        background-color: #1e293b !important;
        color: #f8fafc;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #0f172a !important;
    }
    .receipt-card {
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03) !important;
        transition: all 0.25s ease;
    }
    .receipt-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.06) !important;
        transform: translateY(-1px);
    }
    .expense-box {
        border-radius: 10px;
        padding: 12px;
        height: 100%;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .expense-box:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    .expense-transport {
        background-color: #f0f9ff !important;
        border: 1px solid #bae6fd !important;
        color: #0369a1 !important;
    }
    .expense-loading {
        background-color: #fdf2f8 !important;
        border: 1px solid #fbcfe8 !important;
        color: #be185d !important;
    }
    .expense-packing {
        background-color: #faf5ff !important;
        border: 1px solid #e9d5ff !important;
        color: #6b21a8 !important;
    }
    .expense-other {
        background-color: #fcf6e8 !important;
        border: 1px solid #fde68a !important;
        color: #b45309 !important;
    }
    .expense-total {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%) !important;
        border: none !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(22, 101, 52, 0.2) !important;
    }
    .list-group-item-custom {
        border: 1px solid #e2e8f0 !important;
        margin-bottom: 6px;
        border-radius: 8px !important;
        background: #f8fafc;
        transition: all 0.2s ease;
    }
    .list-group-item-custom:hover {
        background: #f1f5f9;
        transform: translateX(3px);
    }
    .table-receipt-items {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0 !important;
    }
    .table-receipt-items thead th {
        background-color: #334155 !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 0.68rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #1e293b !important;
        padding: 10px 8px !important;
    }
    .table-receipt-items tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }
    .table-receipt-items tbody td {
        padding: 10px 8px !important;
        font-size: 0.78rem;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    .table-beams-list {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #cbd5e1 !important;
    }
    .table-beams-list thead th {
        background-color: #475569 !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 0.68rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #334155 !important;
        padding: 8px 10px !important;
    }
    .table-beams-list tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }
    .table-beams-list tbody td {
        padding: 8px 10px !important;
        font-size: 0.76rem;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    .table-summary-box {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #cbd5e1 !important;
    }
    .table-summary-box td {
        padding: 8px 12px !important;
        font-size: 0.78rem;
    }
    .table-color-ends {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #cbd5e1 !important;
    }
    .table-color-ends thead th {
        background-color: #0f172a !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #1e293b !important;
        padding: 8px 12px !important;
    }
    .table-color-ends tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }
    .table-color-ends tbody td {
        padding: 8px 12px !important;
        font-size: 0.78rem;
        border-bottom: 1px solid #e2e8f0 !important;
    }
</style>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border-radius: 12px;">
    <div class="card-header border-0 py-3" style="background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);">
        <h5 class="card-title text-white mb-0 fw-bold"><i class="fas fa-receipt me-2"></i>DC: <?= esc($dc['dc_number']) ?></h5>
    </div>
    <div class="card-body p-4 bg-white">
        <!-- Info Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="p-3 info-card h-100">
                    <span class="text-uppercase text-xs text-muted fw-bold d-block mb-1"><i class="fas fa-user-tie me-1"></i> Vendor (Sizer)</span>
                    <strong class="text-dark" style="font-size: 1rem;"><?= esc($dc['vendor_name']) ?></strong>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 info-card date-card h-100">
                    <span class="text-uppercase text-xs text-muted fw-bold d-block mb-1"><i class="far fa-calendar-alt me-1"></i> DC Date</span>
                    <strong class="text-secondary" style="font-size: 0.95rem;"><?= date('d-M-Y', strtotime($dc['dc_date'])) ?></strong>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 info-card status-card h-100">
                    <span class="text-uppercase text-xs text-muted fw-bold d-block mb-1"><i class="fas fa-info-circle me-1"></i> Status</span>
                    <?php 
                    $statusClass = 'secondary';
                    if ($dc['status'] === 'Open') $statusClass = 'info';
                    if ($dc['status'] === 'Partially Received') $statusClass = 'warning text-dark';
                    if ($dc['status'] === 'Completed') $statusClass = 'success';
                    if ($dc['status'] === 'Cancelled') $statusClass = 'danger';
                    ?>
                    <span class="badge rounded-pill bg-<?= $statusClass ?> text-uppercase mt-1" style="font-size: 0.75rem;"><?= esc($dc['status']) ?></span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 info-card design-card h-100">
                    <span class="text-uppercase text-xs text-muted fw-bold d-block mb-1"><i class="fas fa-fingerprint me-1"></i> Design Pattern / Ends</span>
                    <strong class="text-dark" style="font-size: 0.9rem;"><?= esc($dc['design_pattern'] ?: '-') ?></strong>
                    <span class="text-muted small d-block">Ends: <?= esc($dc['total_ends'] ?: '0') ?></span>
                </div>
            </div>
        </div>
 
        <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-cubes me-2"></i>Issued Yarns</h6>
        <div class="table-responsive rounded-3 border border-light-subtle shadow-sm mb-4">
            <table class="table table-hover table-striped align-middle mb-0 table-premium" style="font-size: 0.78rem;">
                <thead class="text-uppercase">
                    <tr>
                        <th class="ps-3 py-2">Mill / Brand</th>
                        <th class="text-center py-2">Yarn Count</th>
                        <th class="py-2">Warp/Weft</th>
                        <th class="text-center py-2">Color</th>
                        <th class="text-end py-2">Qty Issued</th>
                        <th class="text-center py-2">Cones Issued</th>
                        <th class="text-end py-2">Cost / Kg</th>
                        <th class="text-end py-2">Total Cost</th>
                        <th class="text-end py-2">Qty Recd</th>
                        <th class="text-center py-2">Cones Recd</th>
                        <th class="text-end py-2">Used Qty</th>
                        <th class="text-end pe-3 py-2">Pending Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grandTotalQty = 0;
                    $grandTotalCost = 0;
                    foreach ($items as $item): 
                    ?>
                        <?php
                        $pending = $item['quantity_issued_kg'] - ($item['quantity_received_kg'] + $item['quantity_wastage_kg']);
                        $rate = isset($item['cost_per_kg']) ? (float)$item['cost_per_kg'] : 0;
                        $rowTotalCost = $item['quantity_issued_kg'] * $rate;
                        
                        $grandTotalQty += $item['quantity_issued_kg'];
                        $grandTotalCost += $rowTotalCost;
                        ?>
                        <tr>
                            <td class="ps-3 fw-bold text-dark py-2"><?= esc($item['mill_name']) ?></td>
                            <td class="text-center py-2"><?= esc($item['yarn_count']) ?></td>
                            <td class="py-2"><?= esc($item['warp_weft']) ?><?= !empty($item['warp_yarn_type']) ? ' <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 0.65rem;">' . esc($item['warp_yarn_type']) . '</span>' : '' ?></td>
                            <td class="text-center py-2"><span class="badge bg-light border text-dark px-2 py-1" style="font-size: 0.7rem;"><?= esc($item['current_color']) ?></span></td>
                            <td class="text-end fw-bold py-2"><?= number_format($item['quantity_issued_kg'], 2) ?> Kg</td>
                            <td class="text-center py-2"><strong><?= (int)$item['cones_issued'] ?></strong></td>
                            <td class="text-end text-muted fw-bold py-2">₹<?= number_format($rate, 2) ?></td>
                            <td class="text-end fw-bold text-success py-2">₹<?= number_format($rowTotalCost, 2) ?></td>
                            <td class="text-end text-info fw-bold py-2"><?= number_format($item['quantity_received_kg'], 2) ?> Kg</td>
                            <td class="text-center text-info py-2"><strong><?= (int)$item['cones_received'] ?></strong></td>
                            <td class="text-end text-info fw-bold py-2"><?= number_format($item['quantity_wastage_kg'], 2) ?> Kg</td>
                            <td class="text-end pe-3 py-2"><strong class="<?= $pending > 0 ? 'text-danger' : 'text-success' ?>"><?= number_format(max(0, $pending), 2) ?> Kg</strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold table-light border-top border-secondary-subtle">
                        <td colspan="4" class="text-end ps-3 text-secondary py-2">Grand Total:</td>
                        <td class="text-end text-dark py-2"><?= number_format($grandTotalQty, 2) ?> Kg</td>
                        <td></td>
                        <td></td>
                        <td class="text-end text-success py-2" style="font-size: 0.85rem;">₹<?= number_format($grandTotalCost, 2) ?></td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
 
        <div class="row mt-4" style="font-size: 0.85rem;">
            <div class="col-md-6">
                <h6 class="text-info fw-bold mb-2"><i class="fas fa-palette"></i> Color-wise Ends</h6>
                <table class="table align-middle table-color-ends">
                    <thead>
                        <tr>
                            <th class="ps-3">Color</th>
                            <th class="text-center">Ends Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($colorEnds as $ce): ?>
                            <tr>
                                <td class="ps-3 fw-semibold text-dark"><?= esc($ce['color']) ?></td>
                                <td class="text-center fw-bold text-primary"><strong><?= esc($ce['ends_count']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-info fw-bold mb-2"><i class="fas fa-ring"></i> Empty Beams Sent</h6>
                <ul class="list-group shadow-none border-0">
                    <?php foreach ($beams as $b): ?>
                        <li class="list-group-item list-group-item-custom d-flex justify-content-between align-items-center py-2">
                            <span><i class="fas fa-ring text-muted me-2"></i><?= esc($b['beam_number']) ?></span>
                            <?php if ($b['receipt_id']): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1" style="font-size: 0.7rem;">Returned (<?= esc($b['returned_status']) ?>)</span>
                            <?php else: ?>
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1" style="font-size: 0.7rem;">At Job Work</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Receipts List -->
        <h5 class="mt-5 text-success"><i class="fas fa-file-invoice"></i> Received Receipts</h5>
        <hr>
        <?php if (!empty($receipts)): ?>
            <?php foreach ($receipts as $r): ?>
                <?php
                $totalExpenses = (float)$r['transport_charges'] + (float)$r['loading_charges'] + (float)$r['packing_charges'] + (float)$r['other_expenses'];
                $totalReceivedWeight = 0;
                foreach ($r['items'] as $ri) {
                    $totalReceivedWeight += (float)$ri['quantity_received_kg'];
                }

                $totalMeters = 0;
                foreach ($r['beams'] as $b) {
                    $totalMeters += (float)$b['meters'];
                }

                $grandTotalLandedCost = 0;
                $totalJobWorkCost = 0;
                $totalRawYarnCost = 0;
                ?>
                <div class="card receipt-card mb-4 overflow-hidden">
                    <div class="card-header bg-success-subtle border-bottom border-success-subtle py-2">
                        <div class="row align-items-center">
                            <div class="col-md-6 col-sm-12">
                                <span class="fw-bold text-success">
                                    <i class="fas fa-check-double me-1"></i> Receipt No: <?= esc($r['receipt_number']) ?>
                                </span>
                                <span class="text-muted ms-2">| Date: <?= date('d-M-Y', strtotime($r['receipt_date'])) ?></span>
                            </div>
                            <div class="col-md-6 col-sm-12 text-md-end mt-2 mt-md-0">
                                <a href="<?= site_url('production/yarn-warping-sizing/receipt-view/' . $r['id']) ?>"
                                    class="btn btn-xs btn-outline-info rounded-pill px-3"><i class="fas fa-eye me-1"></i> View Details</a>
                                <a href="<?= site_url('production/yarn-warping-sizing/receipt-edit/' . $r['id']) ?>"
                                    class="btn btn-xs btn-outline-warning rounded-pill px-3"><i class="fas fa-edit me-1"></i> Edit</a>
                                <a href="<?= site_url('production/yarn-warping-sizing/receipt-delete/' . $r['id']) ?>"
                                    class="btn btn-xs btn-outline-danger btn-danger-bg rounded-pill px-3" onclick="return confirm('Revert and delete this receipt?')"><i class="fas fa-trash me-1"></i> Delete</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <!-- Expenses & Summary Badges -->
                        <div class="row g-2 mb-3">
                             <div class="col-md-2 col-6">
                                 <div class="expense-box expense-transport text-center">
                                     <span class="d-block small fw-semibold">Transport</span>
                                     <strong class="fs-6">₹<?= number_format($r['transport_charges'], 2) ?></strong>
                                 </div>
                             </div>
                             <div class="col-md-2 col-6">
                                 <div class="expense-box expense-loading text-center">
                                     <span class="d-block small fw-semibold">Loading/Unloading</span>
                                     <strong class="fs-6">₹<?= number_format($r['loading_charges'], 2) ?></strong>
                                 </div>
                             </div>
                             <div class="col-md-2 col-6">
                                 <div class="expense-box expense-packing text-center">
                                     <span class="d-block small fw-semibold">Packing</span>
                                     <strong class="fs-6">₹<?= number_format($r['packing_charges'], 2) ?></strong>
                                 </div>
                             </div>
                             <div class="col-md-2 col-6">
                                 <div class="expense-box expense-other text-center">
                                     <span class="d-block small fw-semibold">Other Expenses</span>
                                     <strong class="fs-6">₹<?= number_format($r['other_expenses'], 2) ?></strong>
                                 </div>
                             </div>
                             <div class="col-md-4 col-12">
                                 <div class="expense-box expense-total text-center d-flex flex-column justify-content-center">
                                     <span class="d-block small fw-bold">Total Shared Expenses</span>
                                     <strong class="fs-5">₹<?= number_format($totalExpenses, 2) ?></strong>
                                 </div>
                             </div>
                         </div>
                                           <!-- Yarn Items Table -->
                         <div class="table-responsive mb-3">
                             <table class="table align-middle table-receipt-items">
                                 <thead>
                                     <tr>
                                         <th>Yarn Description</th>
                                         <th class="text-center">Recd Qty (Loaded) (Kg)</th>
                                         <th class="text-center">Recd Cones</th>
                                         <th class="text-center">Used Qty (Kg)</th>
                                         <th class="text-center">Used Cones</th>
                                         <th class="text-end">Raw Cost (₹/Kg)</th>
                                         <th class="text-end">Raw Yarn Cost (₹)</th>
                                         <th class="text-end">Job Charges (₹/Kg)</th>
                                         <th class="text-end">Job Work Cost (₹)</th>
                                         <th class="text-end">Landed Cost (₹)</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     <?php foreach ($r['items'] as $ri): ?>
                                         <?php
                                         $recd = (float)$ri['quantity_received_kg'];
                                         $recdCones = (int)($ri['cones_received'] ?? 0);
                                         $used = (float)$ri['quantity_wastage_kg']; // Form's Used Qty
                                         $usedCones = (int)($ri['cones_used'] ?? 0);
                                         $rawCost = (float)$ri['raw_cost'];
                                         $jobRate = (float)$ri['job_work_charges']; // Flat total charges

                                         // Sizing/warping loss is a process cost; thus, landed cost includes the value of the consumed/wasted yarn
                                         $rawYarnCost = ($recd + $used) * $rawCost;
                                         $shareOfJobWork = $totalReceivedWeight > 0 ? (($recd / $totalReceivedWeight) * $jobRate) : 0;
                                         $shareOfExpense = $totalReceivedWeight > 0 ? (($recd / $totalReceivedWeight) * $totalExpenses) : 0;
                                         $itemLandedCost = $rawYarnCost + $shareOfJobWork + $shareOfExpense;

                                         $totalRawYarnCost += $rawYarnCost;
                                         $totalJobWorkCost = $jobRate; // Flat total charges
                                         $grandTotalLandedCost += $itemLandedCost;
                                         ?>
                                         <tr>
                                             <td>
                                                 <strong><?= esc($ri['mill_name']) ?> - <?= esc($ri['yarn_count']) ?></strong><br>
                                                 <small class="text-muted"><?= esc($ri['yarn_type']) ?> | <?= esc($ri['warp_weft']) ?> | <?= esc($ri['current_color']) ?><?= !empty($ri['warp_yarn_type']) ? ' (' . esc($ri['warp_yarn_type']) . ')' : '' ?></small>
                                             </td>
                                             <td class="text-center fw-semibold"><?= number_format($recd, 2) ?> Kg</td>
                                             <td class="text-center"><?= $recdCones ?></td>
                                             <td class="text-center"><?= number_format($used, 2) ?> Kg</td>
                                             <td class="text-center"><?= $usedCones ?></td>
                                             <td class="text-end text-secondary">₹<?= number_format($rawCost, 2) ?></td>
                                             <td class="text-end text-dark">₹<?= number_format($rawYarnCost, 2) ?></td>
                                             <td class="text-end text-secondary">₹<?= number_format($totalReceivedWeight > 0 ? ($jobRate / $totalReceivedWeight) : 0, 2) ?> / Kg</td>
                                             <td class="text-end text-dark">₹<?= number_format($shareOfJobWork, 2) ?></td>
                                             <td class="text-end fw-bold text-success" style="font-size: 0.82rem;">₹<?= number_format($itemLandedCost, 2) ?></td>
                                         </tr>
                                     <?php endforeach; ?>
                                 </tbody>
                             </table>
                         </div>

                        <div class="row g-3">
                            <!-- Beams Received -->
                            <div class="col-12">
                                <h6 class="text-info fw-bold mb-2"><i class="fas fa-ring me-1"></i>Beams Returned / Received</h6>
                                <?php if (!empty($r['beams'])): ?>
                                    <?php $costPerMeter = $totalMeters > 0 ? ($grandTotalLandedCost / $totalMeters) : 0; ?>
                                    <div class="table-responsive">
                                        <table class="table align-middle table-beams-list">
                                            <thead>
                                                <tr class="bg-light text-muted">
                                                    <th>Beam No</th>
                                                    <th>Status</th>
                                                    <th>Ends</th>
                                                    <th>Sizing No</th>
                                                    <th>Color</th>
                                                    <th>Sizing Date</th>
                                                    <th class="text-end">Meters</th>
                                                    <th class="text-end">Landed Value (₹)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($r['beams'] as $rb): ?>
                                                    <?php 
                                                        $beamValue = $rb['returned_status'] === 'Loaded' && (float)$rb['meters'] > 0 
                                                            ? ((float)$rb['meters'] * $costPerMeter) 
                                                            : 0;
                                                    ?>
                                                    <tr>
                                                        <td><strong><?= esc($rb['beam_number']) ?></strong></td>
                                                        <td>
                                                            <span class="badge bg-<?= $rb['returned_status'] === 'Loaded' ? 'success' : 'secondary' ?> bg-opacity-10 text-<?= $rb['returned_status'] === 'Loaded' ? 'success' : 'secondary' ?>">
                                                                <?= esc($rb['returned_status']) ?>
                                                            </span>
                                                        </td>
                                                        <td class="fw-semibold"><?= esc($rb['ends'] ?: '-') ?></td>
                                                        <td><?= esc($rb['sizing_no'] ?: '-') ?></td>
                                                        <td><?= esc($rb['color'] ?: '-') ?></td>
                                                        <td><?= $rb['return_date'] ? date('d-m-Y', strtotime($rb['return_date'])) : '-' ?></td>
                                                        <td class="text-end fw-semibold"><?= $rb['meters'] ? number_format($rb['meters'], 2) . ' M' : '-' ?></td>
                                                        <td class="text-end fw-bold text-success"><?= $beamValue > 0 ? '₹' . number_format($beamValue, 2) : '-' ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-light border text-muted py-2 px-3 mb-0 text-xs">No beams returned on this receipt.</div>
                                <?php endif; ?>
                            </div>

                            <!-- Costing Summary -->
                            <div class="col-12 mt-3">
                                <h6 class="text-success fw-bold mb-2"><i class="fas fa-calculator me-1"></i>Receipt Landed Cost Summary</h6>
                                <div class="table-responsive">
                                    <table class="table align-middle table-summary-box" style="max-width: 400px;">
                                        <tbody>
                                            <tr>
                                                <td class="bg-light fw-semibold" width="50%">Total Loaded Meters</td>
                                                <td class="text-end fw-bold text-info"><?= number_format($totalMeters, 2) ?> M</td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light fw-semibold">Total Landed Cost</td>
                                                <td class="text-end fw-bold text-primary">₹<?= number_format($grandTotalLandedCost, 2) ?></td>
                                            </tr>
                                            <tr style="background: #e8f5e9;">
                                                <td class="fw-bold text-success">Landed Cost Per Meter</td>
                                                <td class="text-end fw-bold text-success" style="font-size: 0.95rem;">
                                                    ₹<?= $totalMeters > 0 ? number_format($grandTotalLandedCost / $totalMeters, 2) : '0.00' ?> / M
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info shadow-sm"><i class="fas fa-info-circle me-1"></i> No receipts saved yet.</div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>