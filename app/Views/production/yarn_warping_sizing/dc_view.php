<?= $this->extend('layouts/master') ?>
<?= $this->section('title') ?>Warping & Sizing DC Details<?= $this->endSection() ?>
<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Warping & Sizing DC Details</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-warping-sizing') ?>" class="btn btn-secondary"><i
                class="fas fa-list"></i> List</a>
        <?php if ($dc['status'] !== 'Completed' && $dc['status'] !== 'Cancelled'): ?>
            <a href="<?= site_url('production/yarn-warping-sizing/receipt-create/' . $dc['id']) ?>" class="btn btn-success"><i
                    class="fas fa-download"></i> Receive Beams</a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="card card-outline card-info">
    <div class="card-body">

        <div class="row mb-4">
            <div class="col-sm-4">
                <h5>Vendor (Sizer)</h5>
                <strong><?= esc($dc['vendor_name']) ?></strong>
            </div>
            <div class="col-sm-4">
                <b>DC No:</b> <?= esc($dc['dc_number']) ?><br>
                <b>Date:</b> <?= date('d-m-Y', strtotime($dc['dc_date'])) ?><br>
                <b>Status:</b> <span class="badge bg-primary"><?= esc($dc['status']) ?></span>
            </div>
            <div class="col-sm-4">
                <b>Design Pattern:</b> <?= esc($dc['design_pattern'] ?: '-') ?><br>
                <b>Total Ends:</b> <strong><?= esc($dc['total_ends'] ?: '0') ?></strong>
            </div>
        </div>

        <h5 class="text-primary mb-2">Issued Yarns</h5>
        <table class="table table-bordered">
            <thead>
                <tr class="bg-light">
                    <th>Mill / Brand</th>
                    <th>Yarn Count</th>
                    <th>Warp/Weft</th>
                    <th>Color</th>
                    <th>Qty Issued (Kg)</th>
                    <th>Qty Received (Kg)</th>
                    <th>Qty Wastage (Kg)</th>
                    <th>Pending (Kg)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <?php
                    $pending = $item['quantity_issued_kg'] - ($item['quantity_received_kg'] + $item['quantity_wastage_kg']);
                    ?>
                    <tr>
                        <td><?= esc($item['mill_name']) ?></td>
                        <td><?= esc($item['yarn_count']) ?></td>
                        <td><?= esc($item['warp_weft']) ?><?= !empty($item['warp_yarn_type']) ? ' (' . esc($item['warp_yarn_type']) . ')' : '' ?></td>
                        <td><?= esc($item['current_color']) ?><?= !empty($item['warp_yarn_type']) ? ' (' . esc($item['warp_yarn_type']) . ')' : '' ?></td>
                        <td><?= number_format($item['quantity_issued_kg'], 2) ?> Kg</td>
                        <td class="text-success"><?= number_format($item['quantity_received_kg'], 2) ?> Kg</td>
                        <td class="text-warning"><?= number_format($item['quantity_wastage_kg'], 2) ?> Kg</td>
                        <td><strong><?= number_format(max(0, $pending), 2) ?> Kg</strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="row mt-4">
            <div class="col-md-6">
                <h5 class="text-info"><i class="fas fa-palette"></i> Color-wise Ends</h5>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="bg-light">
                            <th>Color</th>
                            <th>Ends Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($colorEnds as $ce): ?>
                            <tr>
                                <td><?= esc($ce['color']) ?></td>
                                <td><strong><?= esc($ce['ends_count']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <h5 class="text-info"><i class="fas fa-ring"></i> Empty Beams Sent</h5>
                <ul class="list-group">
                    <?php foreach ($beams as $b): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-ring text-muted me-2"></i><?= esc($b['beam_number']) ?></span>
                            <?php if ($b['receipt_id']): ?>
                                <span class="badge bg-success">Returned (<?= esc($b['returned_status']) ?>)</span>
                            <?php else: ?>
                                <span class="badge bg-warning">At Job Work</span>
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
                <div class="card card-outline card-success mb-4 shadow-sm">
                    <div class="card-header bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-6 col-sm-12">
                                <h5 class="mb-0 text-success fw-bold">
                                    <i class="fas fa-file-invoice me-2"></i>Receipt No: <?= esc($r['receipt_number']) ?>
                                </h5>
                                <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>Date: <?= date('d-m-Y', strtotime($r['receipt_date'])) ?></small>
                            </div>
                            <div class="col-md-6 col-sm-12 text-md-end mt-2 mt-md-0">
                                <a href="<?= site_url('production/yarn-warping-sizing/receipt-view/' . $r['id']) ?>"
                                    class="btn btn-xs btn-info"><i class="fas fa-eye"></i> View</a>
                                <a href="<?= site_url('production/yarn-warping-sizing/receipt-edit/' . $r['id']) ?>"
                                    class="btn btn-xs btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <a href="<?= site_url('production/yarn-warping-sizing/receipt-delete/' . $r['id']) ?>"
                                    class="btn btn-xs btn-danger" onclick="return confirm('Revert and delete this receipt?')"><i
                                        class="fas fa-trash"></i> Delete</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <!-- Expenses & Summary Badges -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-2 col-6">
                                <div class="border rounded p-2 text-center bg-light">
                                    <span class="text-muted d-block small">Transport</span>
                                    <strong class="text-dark">₹<?= number_format($r['transport_charges'], 2) ?></strong>
                                </div>
                            </div>
                            <div class="col-md-2 col-6">
                                <div class="border rounded p-2 text-center bg-light">
                                    <span class="text-muted d-block small">Loading/Unloading</span>
                                    <strong class="text-dark">₹<?= number_format($r['loading_charges'], 2) ?></strong>
                                </div>
                            </div>
                            <div class="col-md-2 col-6">
                                <div class="border rounded p-2 text-center bg-light">
                                    <span class="text-muted d-block small">Packing</span>
                                    <strong class="text-dark">₹<?= number_format($r['packing_charges'], 2) ?></strong>
                                </div>
                            </div>
                            <div class="col-md-2 col-6">
                                <div class="border rounded p-2 text-center bg-light">
                                    <span class="text-muted d-block small">Other Expenses</span>
                                    <strong class="text-dark">₹<?= number_format($r['other_expenses'], 2) ?></strong>
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="border rounded p-2 text-center text-white" style="background: linear-gradient(135deg, #00b0ff 0%, #0091ea 100%);">
                                    <span class="d-block small fw-bold">Total Shared Expenses</span>
                                    <strong class="fs-5">₹<?= number_format($totalExpenses, 2) ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Yarn Items Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm align-middle text-sm mb-3">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th>Yarn Description</th>
                                        <th class="text-center">Recd Qty (Loaded) (Kg)</th>
                                        <th class="text-center">Used Qty (Kg)</th>
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
                                        $used = (float)$ri['quantity_wastage_kg']; // Form's Used Qty
                                        $rawCost = (float)$ri['raw_cost'];
                                        $jobRate = (float)$ri['job_work_charges'];

                                        $rawYarnCost = $recd * $rawCost;
                                        $jobWorkCost = $used * $jobRate;
                                        $shareOfExpense = $totalReceivedWeight > 0 ? (($recd / $totalReceivedWeight) * $totalExpenses) : 0;
                                        $itemLandedCost = $rawYarnCost + $jobWorkCost + $shareOfExpense;

                                        $totalRawYarnCost += $rawYarnCost;
                                        $totalJobWorkCost += $jobWorkCost;
                                        $grandTotalLandedCost += $itemLandedCost;
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($ri['mill_name']) ?> - <?= esc($ri['yarn_count']) ?></strong><br>
                                                <small class="text-muted"><?= esc($ri['yarn_type']) ?> | <?= esc($ri['warp_weft']) ?> | <?= esc($ri['current_color']) ?><?= !empty($ri['warp_yarn_type']) ? ' (' . esc($ri['warp_yarn_type']) . ')' : '' ?></small>
                                            </td>
                                            <td class="text-center fw-semibold"><?= number_format($recd, 2) ?> Kg</td>
                                            <td class="text-center"><?= number_format($used, 2) ?> Kg</td>
                                            <td class="text-end">₹<?= number_format($rawCost, 2) ?></td>
                                            <td class="text-end">₹<?= number_format($rawYarnCost, 2) ?></td>
                                            <td class="text-end">₹<?= number_format($jobRate, 2) ?></td>
                                            <td class="text-end">₹<?= number_format($jobWorkCost, 2) ?></td>
                                            <td class="text-end fw-bold text-success">₹<?= number_format($itemLandedCost, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="row g-3">
                            <!-- Beams Received -->
                            <div class="col-lg-7 col-md-12">
                                <h6 class="text-info fw-bold mb-2"><i class="fas fa-ring me-1"></i>Beams Returned / Received</h6>
                                <?php if (!empty($r['beams'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm text-xs mb-0">
                                            <thead>
                                                <tr class="bg-light text-muted">
                                                    <th>Beam No</th>
                                                    <th>Status</th>
                                                    <th>Sizing No</th>
                                                    <th>Color</th>
                                                    <th>Sizing Date</th>
                                                    <th class="text-end">Meters</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($r['beams'] as $rb): ?>
                                                    <tr>
                                                        <td><strong><?= esc($rb['beam_number']) ?></strong></td>
                                                        <td>
                                                            <span class="badge bg-<?= $rb['returned_status'] === 'Loaded' ? 'success' : 'secondary' ?> bg-opacity-10 text-<?= $rb['returned_status'] === 'Loaded' ? 'success' : 'secondary' ?>">
                                                                <?= esc($rb['returned_status']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= esc($rb['sizing_no'] ?: '-') ?></td>
                                                        <td><?= esc($rb['color'] ?: '-') ?></td>
                                                        <td><?= $rb['return_date'] ? date('d-m-Y', strtotime($rb['return_date'])) : '-' ?></td>
                                                        <td class="text-end fw-semibold"><?= $rb['meters'] ? number_format($rb['meters'], 2) . ' M' : '-' ?></td>
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
                            <div class="col-lg-5 col-md-12">
                                <h6 class="text-success fw-bold mb-2"><i class="fas fa-calculator me-1"></i>Receipt Landed Cost Summary</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm text-xs mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="bg-light fw-semibold">Total Loaded Meters</td>
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