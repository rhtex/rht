<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($isEdit) ? 'Edit Receipt' : 'Receive Warping & Sizing' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($isEdit) ? 'Edit Receipt (#' . esc($receipt['receipt_number']) . ')' : 'Receive Yarn (Against DC: ' . esc($dc['dc_number']) . ')' ?>
        </h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-warping-sizing/view/' . $dc['id']) ?>" class="btn btn-secondary"><i
                class="fas fa-arrow-left"></i> Back to DC</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-success">
    <?php
    $formAction = isset($isEdit)
        ? site_url('production/yarn-warping-sizing/receipt-update/' . $receipt['id'])
        : site_url('production/yarn-warping-sizing/receipt-store/' . $dc['id']);
    ?>
    <form action="<?= $formAction ?>" method="post" id="receiptForm">
        <?= csrf_field() ?>
        <div class="card-body">
            <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <!-- Receipt Header Details -->
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Receipt Number <span class="text-danger">*</span></label>
                    <input type="text" name="receipt_number" class="form-control"
                        value="<?= old('receipt_number', isset($isEdit) ? $receipt['receipt_number'] : $nextReceiptNumber) ?>"
                        required readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Receipt Date <span class="text-danger">*</span></label>
                    <input type="date" name="receipt_date" id="receipt_date" class="form-control"
                        value="<?= old('receipt_date', isset($isEdit) ? $receipt['receipt_date'] : date('Y-m-d')) ?>"
                        required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Vendor (Job Worker)</label>
                    <input type="text" class="form-control" value="<?= esc($dc['vendor_name']) ?> (Warping & Sizing)"
                        disabled>
                </div>

                <!-- Additional Landed Expenses -->
                <div class="col-md-12 mt-2">
                    <h6 class="text-primary"><i class="fas fa-truck"></i> Shared Landed Expenses (Pro-rated by Weight)
                    </h6>
                    <hr class="my-2">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Transport Charges (₹)</label>
                    <input type="number" step="0.01" name="transport_charges" id="transport_charges"
                        class="form-control expense-input"
                        value="<?= old('transport_charges', isset($isEdit) ? number_format($receipt['transport_charges'], 2, '.', '') : '0.00') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Loading / Unloading (₹)</label>
                    <input type="number" step="0.01" name="loading_charges" id="loading_charges"
                        class="form-control expense-input"
                        value="<?= old('loading_charges', isset($isEdit) ? number_format($receipt['loading_charges'], 2, '.', '') : '0.00') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Packing Charges (₹)</label>
                    <input type="number" step="0.01" name="packing_charges" id="packing_charges"
                        class="form-control expense-input"
                        value="<?= old('packing_charges', isset($isEdit) ? number_format($receipt['packing_charges'], 2, '.', '') : '0.00') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Other Expenses (₹)</label>
                    <input type="number" step="0.01" name="other_expenses" id="other_expenses"
                        class="form-control expense-input"
                        value="<?= old('other_expenses', isset($isEdit) ? number_format($receipt['other_expenses'], 2, '.', '') : '0.00') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-primary fw-bold">Job Work Charges (₹) <span
                            class="text-danger">*</span></label>
                    <?php
                    $defaultJobCharges = '0.00';
                    if (isset($isEdit) && !empty($receiptItems)) {
                        $defaultJobCharges = number_format($receiptItems[0]['job_work_charges'], 2, '.', '');
                    }
                    ?>
                    <input type="number" step="0.01" name="job_work_charges" id="job_work_charges"
                        class="form-control expense-input fw-bold border-primary"
                        value="<?= old('job_work_charges', $defaultJobCharges) ?>" required>
                </div>
                <div class="col-md-9 mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="1"
                        placeholder="Receipt notes..."><?= old('remarks', isset($isEdit) ? $receipt['remarks'] : '') ?></textarea>
                </div>
            </div>

            <!-- Warping & Sizing Beams Return Section -->
            <div class="card card-outline card-info mt-3 mb-4">
                <div class="card-header">
                    <h5 class="card-title text-info"><i class="fas fa-ring"></i> Receive Warping Beams</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Specify details for each beam returned from job work:</p>
                    <div class="row">
                        <?php
                        $allBeamsToShow = [];
                        if (isset($isEdit) && $isEdit) {
                            foreach ($beamsReceived as $b) {
                                $allBeamsToShow[] = [
                                    'beam_number' => $b['beam_number'],
                                    'status' => $b['returned_status'] ?: 'Loaded',
                                    'ends' => $b['ends'] ?? '',
                                    'meters' => $b['meters'],
                                    'sizing_no' => $b['sizing_no'],
                                    'color' => $b['color'],
                                    'return_date' => $b['return_date']
                                ];
                            }
                            foreach ($beamsPending as $b) {
                                $allBeamsToShow[] = [
                                    'beam_number' => $b['beam_number'],
                                    'status' => 'Not Returned',
                                    'ends' => '',
                                    'meters' => '',
                                    'sizing_no' => '',
                                    'color' => '',
                                    'return_date' => ''
                                ];
                            }
                        } else {
                            foreach ($beamsSent as $b) {
                                $allBeamsToShow[] = [
                                    'beam_number' => $b['beam_number'],
                                    'status' => 'Loaded',
                                    'ends' => $b['ends'] ?? '',
                                    'meters' => '',
                                    'sizing_no' => '',
                                    'color' => '',
                                    'return_date' => ''
                                ];
                            }
                        }
                        ?>

                        <?php if (!empty($allBeamsToShow)): ?>
                            <?php foreach ($allBeamsToShow as $beam): ?>
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="border rounded p-3 bg-light h-100">
                                        <div class="fw-bold text-primary mb-2"><i class="fas fa-ring"></i> Beam:
                                            <?= esc($beam['beam_number']) ?></div>

                                        <div class="mb-2">
                                            <label class="small fw-bold">Return Status</label>
                                            <select name="beams_return[<?= esc($beam['beam_number']) ?>][status]"
                                                class="form-select form-select-sm beam-status-select"
                                                data-beam="<?= esc($beam['beam_number']) ?>">
                                                <option value="Loaded" <?= $beam['status'] === 'Loaded' ? 'selected' : '' ?>>Loaded
                                                    (Yarn Wound)</option>
                                                <option value="Empty" <?= $beam['status'] === 'Empty' ? 'selected' : '' ?>>Returned
                                                    Empty</option>
                                                <option value="Not Returned" <?= $beam['status'] === 'Not Returned' ? 'selected' : '' ?>>Not Returned</option>
                                            </select>
                                        </div>

                                        <div class="beam-specs-fields" id="specs_<?= esc($beam['beam_number']) ?>"
                                            style="<?= $beam['status'] === 'Loaded' ? '' : 'display: none;' ?>">
                                            <div class="mb-2">
                                                <label class="small fw-bold">Ends <span class="text-danger">*</span></label>
                                                <input type="number"
                                                    name="beams_return[<?= esc($beam['beam_number']) ?>][ends]"
                                                    class="form-control form-control-sm input-beam-ends"
                                                    value="<?= esc(!empty($beam['ends']) ? $beam['ends'] : (isset($dc['total_ends']) ? $dc['total_ends'] : '')) ?>" placeholder="Total Ends count" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="small fw-bold">Meters <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01"
                                                    name="beams_return[<?= esc($beam['beam_number']) ?>][meters]"
                                                    class="form-control form-control-sm input-beam-meters"
                                                    value="<?= esc($beam['meters'] ?? '') ?>" placeholder="Length in meters">
                                            </div>
                                            <div class="mb-2">
                                                <label class="small fw-bold">Sizing No <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    name="beams_return[<?= esc($beam['beam_number']) ?>][sizing_no]"
                                                    class="form-control form-control-sm input-beam-sizing-no"
                                                    value="<?= esc($beam['sizing_no'] ?? '') ?>" placeholder="e.g. SZ-101">
                                            </div>
                                            <?php
                                            $yarnColors = [];
                                            foreach ($items as $it) {
                                                if (!empty($it['current_color'])) {
                                                    $colorLabel = $it['current_color'];
                                                    if ($it['warp_weft'] === 'Warp' && !empty($it['warp_yarn_type'])) {
                                                        $colorLabel .= ' (' . $it['warp_yarn_type'] . ')';
                                                    }
                                                    $yarnColors[] = $colorLabel;
                                                }
                                            }
                                            $yarnColors = array_unique($yarnColors);
                                            $defaultBeamColor = !empty($yarnColors) ? implode(', ', $yarnColors) : 'Raw';
                                            ?>
                                            <div class="mb-2">
                                                <label class="small fw-bold">Color <span class="text-danger">*</span></label>
                                                <input type="text" name="beams_return[<?= esc($beam['beam_number']) ?>][color]"
                                                    class="form-control form-control-sm input-beam-color"
                                                    value="<?= esc(!empty($beam['color']) ? $beam['color'] : $defaultBeamColor) ?>"
                                                    list="designColorsList"
                                                    placeholder="Select or type color">
                                            </div>
                                            <div class="mb-2">
                                                <label class="small fw-bold">Sizing Date <span
                                                        class="text-danger">*</span></label>
                                                <input type="date"
                                                    name="beams_return[<?= esc($beam['beam_number']) ?>][return_date]"
                                                    class="form-control form-control-sm input-beam-date"
                                                    value="<?= esc($beam['return_date'] ?? date('Y-m-d')) ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-warning mb-0"><i class="fas fa-exclamation-triangle me-1"></i> No
                                    pending beams to receive for this challan.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Receipt Items Section -->
            <div class="card card-outline card-warning mt-4 shadow-sm">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #fff8e1 0%, #fff3e0 100%); border-bottom: 2px solid #ffca28;">
                    <h5 class="card-title mb-0" style="color: #e65100;">
                        <i class="fas fa-check-double me-2"></i>Receipt Items
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="table-responsive">
                        <!-- WARPING & SIZING TABLE -->
                        <table class="table table-bordered table-hover align-middle mb-0" id="receiptItemsTable">
                            <thead>
                                <tr class="text-sm" style="background: #f5f5f5;">
                                    <th class="py-3">Yarn Description</th>
                                    <th class="py-3 text-center" width="8%">Issued Qty</th>
                                    <th class="py-3 text-center" width="8%">Issued Cones</th>
                                    <th class="py-3 text-center" width="10%">Recd Qty (Loaded) <span class="text-danger">*</span></th>
                                    <th class="py-3 text-center" width="10%">Recd Cones <span class="text-danger">*</span></th>
                                    <th class="py-3 text-center" width="10%">Used Qty (Kg)</th>
                                    <th class="py-3 text-end" width="12%">Total Yarn Cost Given (₹)</th>
                                    <th class="py-3 text-end" width="12%">Used Yarn Cost (₹)</th>
                                    <th class="py-3 text-end" width="12%">Received Yarn Cost (Landed) (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                    $db = \Config\Database::connect();
                                    $ri = isset($isEdit) && isset($receiptItemsKeyed[$item['id']]) ? $receiptItemsKeyed[$item['id']] : null;

                                    $issued = (float) $item['quantity_issued_kg'];
                                    $receivedExcludingCurrent = (float) $item['quantity_received_kg'] - ($ri ? (float) $ri['quantity_received_kg'] : 0);
                                    $wastageExcludingCurrent = (float) $item['quantity_wastage_kg'] - ($ri ? (float) $ri['quantity_wastage_kg'] : 0);

                                    $pending = $issued - ($receivedExcludingCurrent + $wastageExcludingCurrent);

                                    $qtyReceivedVal = $ri ? (float) $ri['quantity_received_kg'] : 0.00;
                                    $qtyUsedVal = $ri ? (float) $ri['quantity_wastage_kg'] : 0.00;

                                    // Fetch original raw cost
                                    $issuanceQuery = $db->table('production_yarn_stock_movements')
                                        ->where('movement_type', 'Issue_Job_Work')
                                        ->where('reference_id', $dc['id'])
                                        ->where('yarn_count', $item['yarn_count'])
                                        ->where('brand_mill', $item['mill_name'])
                                        ->where('color', $item['current_color'] ?: 'Raw')
                                        ->where('yarn_type', $item['yarn_type'])
                                        ->where('warp_weft', $item['warp_weft']);

                                    if (!empty($item['lot_number'])) {
                                        $issuanceQuery->where('lot_number', $item['lot_number']);
                                    }
                                    if (!empty($item['csp'])) {
                                        $issuanceQuery->where('csp', $item['csp']);
                                    }
                                    $issuanceMovement = $issuanceQuery->get()->getRowArray();
                                    $rawCost = $issuanceMovement ? abs((float) $issuanceMovement['cost_per_kg']) : 0;

                                    $issuedQtyCost = $issued * $rawCost;
                                    ?>
                                    <?php
                                    $conesIssued = (int)$item['cones_issued'];
                                    $conesRecdExcludingCurrent = (int)$item['cones_received'] - ($ri ? (int)$ri['cones_received'] : 0);
                                    $conesUsedExcludingCurrent = (int)$item['cones_used'] - ($ri ? (int)$ri['cones_used'] : 0);
                                    $pendingCones = $conesIssued - ($conesRecdExcludingCurrent + $conesUsedExcludingCurrent);

                                    $conesReceivedVal = $ri ? (int)$ri['cones_received'] : 0;
                                    $conesUsedVal = $ri ? (int)$ri['cones_used'] : 0;
                                    ?>
                                    <tr class="item-row" data-item-id="<?= $item['id'] ?>" data-issued="<?= $issued ?>"
                                        data-pending="<?= $pending ?>" data-pending-cones="<?= $pendingCones ?>" data-raw-cost="<?= $rawCost ?>">
                                        <td class="py-3">
                                            <strong><?= esc($item['mill_name']) ?> - <?= esc($item['yarn_count']) ?></strong><br>
                                            <small class="text-muted">
                                                Type: <?= esc($item['yarn_type']) ?> | Warp/Weft: <?= esc($item['warp_weft']) ?> | Color: <?= esc($item['current_color']) ?><?= !empty($item['warp_yarn_type']) ? ' (' . esc($item['warp_yarn_type']) . ')' : '' ?><br>
                                                Lot: <?= esc($item['lot_number'] ?: '-') ?> | CSP: <?= esc($item['csp'] ?: '-') ?><br>
                                                <span class="badge bg-primary bg-opacity-10 text-primary mt-1"><i class="fas fa-tag me-1"></i>₹<?= number_format($rawCost, 2) ?> / Kg</span>
                                            </small>
                                            <input type="hidden" name="items[<?= $item['id'] ?>][received_color]" value="<?= esc($item['current_color']) ?>">
                                        </td>
                                        <td class="text-center fw-semibold"><?= number_format($issued, 2) ?> Kg</td>
                                        <td class="text-center fw-semibold"><?= $conesIssued ?></td>
                                        <td>
                                            <input type="number" step="0.01" name="items[<?= $item['id'] ?>][quantity_received_kg]"
                                                class="form-control form-control-sm input-recd text-center"
                                                value="<?= number_format($qtyReceivedVal, 2, '.', '') ?>" min="0" required>
                                            <small class="text-danger qty-error-msg" style="display:none; font-weight:bold;"></small>
                                        </td>
                                        <td>
                                            <input type="number" name="items[<?= $item['id'] ?>][cones_received]"
                                                class="form-control form-control-sm input-recd-cones text-center"
                                                value="<?= $conesReceivedVal ?>" min="0" required>
                                        </td>
                                        <td>
                                            <input type="hidden" name="items[<?= $item['id'] ?>][quantity_used_kg]"
                                                class="input-used-qty"
                                                value="<?= number_format($qtyUsedVal, 2, '.', '') ?>">
                                            <span class="fw-bold lbl-used-qty"><?= number_format($qtyUsedVal, 2) ?> Kg</span>
                                        </td>
                                        <td class="text-end fw-bold text-secondary">
                                            ₹<?= number_format($issuedQtyCost, 2) ?>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold lbl-used-yarn-cost" id="lbl-used-yarn-cost-<?= $item['id'] ?>">₹0.00</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-success lbl-landed-cost">₹0.00</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Warping & Sizing Cost Per Meter Summary Card -->
            <div class="card card-outline card-success mt-4 shadow-sm">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%); border-bottom: 2px solid #66bb6a;">
                    <h5 class="card-title mb-0" style="color: #2e7d32;">
                        <i class="fas fa-calculator me-2"></i>Warping & Sizing Summary (Cost Per Meter)
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <!-- Total Loaded Meters Highlight -->
                    <div class="row mb-4 justify-content-center">
                        <div class="col-lg-5 col-md-6 col-sm-8 col-12">
                            <div class="border rounded-3 p-3 text-center shadow-sm"
                                style="background: linear-gradient(135deg, #e0f7fa 0%, #e1f5fe 50%, #f3e5f5 100%); border-color: #b2ebf2 !important;">
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                                    <i class="fas fa-ruler-horizontal text-info" style="font-size: 1.3rem;"></i>
                                    <span class="text-muted fw-semibold"
                                        style="font-size: 0.9rem; letter-spacing: 0.5px; text-transform: uppercase;">Total Loaded Meters</span>
                                </div>
                                <h2 class="fw-bold mb-0" id="summary-total-meters"
                                    style="color: #0097a7; font-size: clamp(1.5rem, 4vw, 2.2rem);">0.00 M</h2>
                            </div>
                        </div>
                    </div>
                    <!-- Cost Breakdown Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead>
                                <tr style="background: #f5f5f5;">
                                    <th class="py-3"><i class="fas fa-layer-group text-muted me-1"></i> Cost Component</th>
                                    <th class="py-3 text-end" width="25%">Total Cost (₹)</th>
                                    <th class="py-3 text-end" width="25%">Landed Cost Per Meter (₹/M)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="py-2 ps-3"><i class="fas fa-truck text-info me-2"></i>Transport Charges</td>
                                    <td class="text-end py-2" id="summary-transport-total">₹0.00</td>
                                    <td class="text-end py-2 fw-bold" id="summary-transport-pm">₹0.00</td>
                                </tr>
                                <tr>
                                    <td class="py-2 ps-3"><i class="fas fa-boxes text-warning me-2"></i>Loading / Unloading</td>
                                    <td class="text-end py-2" id="summary-loading-total">₹0.00</td>
                                    <td class="text-end py-2 fw-bold" id="summary-loading-pm">₹0.00</td>
                                </tr>
                                <tr>
                                    <td class="py-2 ps-3"><i class="fas fa-box text-secondary me-2"></i>Packing Charges</td>
                                    <td class="text-end py-2" id="summary-packing-total">₹0.00</td>
                                    <td class="text-end py-2 fw-bold" id="summary-packing-pm">₹0.00</td>
                                </tr>
                                <tr>
                                    <td class="py-2 ps-3"><i class="fas fa-ellipsis-h text-muted me-2"></i>Other Expenses</td>
                                    <td class="text-end py-2" id="summary-other-total">₹0.00</td>
                                    <td class="text-end py-2 fw-bold" id="summary-other-pm">₹0.00</td>
                                </tr>
                                <tr>
                                    <td class="py-2 ps-3"><i class="fas fa-cogs text-danger me-2"></i>Job Work Charges</td>
                                    <td class="text-end py-2" id="summary-jobwork-total">₹0.00</td>
                                    <td class="text-end py-2 fw-bold" id="summary-jobwork-pm">₹0.00</td>
                                </tr>
                                <tr style="background: #f9fbe7;">
                                    <td class="py-2 ps-3"><i class="fas fa-hand-holding-usd text-success me-2"></i>Received Yarn Cost (Landed)</td>
                                    <td class="text-end py-2" id="summary-received-yarn-total">₹0.00</td>
                                    <td class="text-end py-2 fw-bold" id="summary-received-yarn-pm">₹0.00</td>
                                </tr>
                                <tr style="background: #f9fbe7;">
                                    <td class="py-2 ps-3"><i class="fas fa-money-bill-wave text-success me-2"></i>Used Yarn Cost</td>
                                    <td class="text-end py-2" id="summary-used-yarn-total">₹0.00</td>
                                    <td class="text-end py-2 fw-bold" id="summary-used-yarn-pm">₹0.00</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
                                    <td class="fw-bold fs-6 py-3"><i class="fas fa-calculator text-success me-2"></i>Total Landed Cost</td>
                                    <td class="text-end fw-bold fs-5 text-primary py-3" id="summary-total-cost">₹0.00</td>
                                    <td class="text-end fw-bold fs-5 py-3" id="summary-cost-per-meter" style="color: #2e7d32;">₹0.00 / M</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (!isset($isView) || !$isView): ?>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-success" id="btnSubmit"><i class="fas fa-check-circle"></i>
                        <?= isset($isEdit) ? 'Update Warping Sizing Receipt' : 'Save Warping Sizing Receipt' ?></button>
                </div>
            <?php endif; ?>
    </form>
</div>

<!-- Datalist for design colors -->
<datalist id="designColorsList">
    <?php if (!empty($yarnColors)): ?>
        <?php foreach ($yarnColors as $c): ?>
            <option value="<?= esc($c) ?>"></option>
        <?php endforeach; ?>
    <?php endif; ?>
</datalist>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        // Toggle beam specifications inputs based on return status select
        $(document).on('change', '.beam-status-select', function () {
            var beamNum = $(this).data('beam');
            var status = $(this).val();
            var $fieldsDiv = $('#specs_' + beamNum);

            if (status === 'Loaded') {
                $fieldsDiv.slideDown();
                $fieldsDiv.find('input').prop('required', true);
            } else {
                $fieldsDiv.slideUp();
                $fieldsDiv.find('input').prop('required', false).val('');
            }
            calculateLandedCosts();
        });

        // Trigger beam status change on load to set validation attributes
        $('.beam-status-select').trigger('change');

        // Prevent double submission
        $('#receiptForm').on('submit', function () {
            $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        });

        // Auto-calculate used qty for warping/sizing
        $(document).on('input', '.input-recd, .input-recd-cones', function () {
            var $row = $(this).closest('tr');
            var pending = parseFloat($row.data('pending')) || 0;
            var pendingCones = parseInt($row.data('pending-cones')) || 0;
            var recd = parseFloat($row.find('.input-recd').val()) || 0;

            // If the trigger was the received qty input, auto-populate the used qty as Issued Qty - Received Qty
            if ($(this).hasClass('input-recd')) {
                var issued = parseFloat($row.data('issued')) || 0;
                var recdVal = parseFloat($(this).val()) || 0;
                var calculatedUsed = Math.max(0, issued - recdVal);
                $row.find('.input-used-qty').val(calculatedUsed.toFixed(2));
                $row.find('.lbl-used-qty').text(calculatedUsed.toFixed(2) + ' Kg');
            }

            var used = parseFloat($row.find('.input-used-qty').val()) || 0;
            var totalAccounted = recd + used;

            var recdCones = parseInt($row.find('.input-recd-cones').val()) || 0;

            // Server-side / Client-side validation: Total (Recd + Used) cannot exceed pending
            if (totalAccounted > pending) {
                $row.find('.qty-error-msg').text('Total (Recd + Used) exceeds pending (' + pending.toFixed(2) + ' Kg)').show();
                $row.addClass('table-danger');
                $row.find('.input-recd').addClass('is-invalid border-danger');
            } else {
                $row.find('.qty-error-msg').hide();
                $row.removeClass('table-danger');
                $row.find('.input-recd').removeClass('is-invalid border-danger');
            }

            // Remaining Pending = Pending - Total Accounted
            var remainingPending = Math.max(0, pending - totalAccounted);
            var remainingPendingCones = Math.max(0, pendingCones - recdCones);

            calculateLandedCosts();
            validateSubmitButton();
        });

        function validateSubmitButton() {
            var hasError = false;
            $('.item-row').each(function () {
                var pending = parseFloat($(this).data('pending')) || 0;
                var recd = parseFloat($(this).find('.input-recd').val()) || 0;
                var used = parseFloat($(this).find('.input-used-qty').val()) || 0;
                if ((recd + used) > pending) hasError = true;
            });

            if (hasError) {
                $('#btnSubmit').prop('disabled', true);
            } else {
                $('#btnSubmit').prop('disabled', false);
            }
        }

        // Recalculate on input change
        $(document).on('input change', '.input-job-charges, .expense-input, .input-beam-meters, .input-recd-cones', function () {
            calculateLandedCosts();
        });

        function calculateLandedCosts() {
            // Get total expenses from header
            var transport = parseFloat($('#transport_charges').val()) || 0;
            var loading = parseFloat($('#loading_charges').val()) || 0;
            var packing = parseFloat($('#packing_charges').val()) || 0;
            var other = parseFloat($('#other_expenses').val()) || 0;
            var totalExpenses = transport + loading + packing + other;
            var jobRate = parseFloat($('#job_work_charges').val()) || 0;

            // Calculate total received weight across all rows to pro-rate expenses and flat job charges
            var totalReceivedWeight = 0;
            $('.item-row').each(function () {
                var recd = parseFloat($(this).find('.input-recd').val()) || 0;
                totalReceivedWeight += recd;
            });

            var grandTotalLandedCost = 0;
            var totalReceivedYarnCost = 0;
            var totalUsedYarnCost = 0;
            var totalJobWorkCost = jobRate;

            // Calculate for each row
            $('.item-row').each(function () {
                var $row = $(this);
                var itemId = $row.data('item-id');
                var rawCost = parseFloat($row.data('raw-cost')) || 0;

                var recd = parseFloat($row.find('.input-recd').val()) || 0;
                var shareOfExpense = totalReceivedWeight > 0 ? ((recd / totalReceivedWeight) * totalExpenses) : 0;
                var shareOfJobWork = totalReceivedWeight > 0 ? ((recd / totalReceivedWeight) * jobRate) : 0;

                var used = parseFloat($row.find('.input-used-qty').val()) || 0;

                // Used Yarn Cost = Used Qty * Raw Cost
                var usedYarnCost = used * rawCost;
                $('#lbl-used-yarn-cost-' + itemId).text('₹' + usedYarnCost.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                totalUsedYarnCost += usedYarnCost;

                // Received Yarn Cost (Landed) = Received Raw Cost + share of Sizing Job Work + share of Sizing Expenses
                var recdRawCost = recd * rawCost;
                var rowLandedCost = recdRawCost + shareOfJobWork + shareOfExpense;
                totalReceivedYarnCost += recdRawCost; // Keep as raw received cost in summary card component
                $row.find('.lbl-landed-cost').text('₹' + rowLandedCost.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                grandTotalLandedCost += rowLandedCost + usedYarnCost;
            });

            // Calculate overall per meter
            var totalMeters = 0;
            $('.input-beam-meters').each(function () {
                totalMeters += parseFloat($(this).val()) || 0;
            });

            // Helper to format currency
            function fmtCurrency(val) {
                return '₹' + val.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            function perMeter(val) {
                return totalMeters > 0 ? (val / totalMeters) : 0;
            }

            // Update summary table - Total Cost column
            $('#summary-transport-total').text(fmtCurrency(transport));
            $('#summary-loading-total').text(fmtCurrency(loading));
            $('#summary-packing-total').text(fmtCurrency(packing));
            $('#summary-other-total').text(fmtCurrency(other));
            $('#summary-jobwork-total').text(fmtCurrency(totalJobWorkCost));
            $('#summary-received-yarn-total').text(fmtCurrency(totalReceivedYarnCost));
            $('#summary-used-yarn-total').text(fmtCurrency(totalUsedYarnCost));

            // Update summary table - Per Meter column
            $('#summary-transport-pm').text(fmtCurrency(perMeter(transport)));
            $('#summary-loading-pm').text(fmtCurrency(perMeter(loading)));
            $('#summary-packing-pm').text(fmtCurrency(perMeter(packing)));
            $('#summary-other-pm').text(fmtCurrency(perMeter(other)));
            $('#summary-jobwork-pm').text(fmtCurrency(perMeter(totalJobWorkCost)));
            $('#summary-received-yarn-pm').text(fmtCurrency(perMeter(totalReceivedYarnCost)));
            $('#summary-used-yarn-pm').text(fmtCurrency(perMeter(totalUsedYarnCost)));

            // Grand totals
            $('#summary-total-cost').text(fmtCurrency(grandTotalLandedCost));
            $('#summary-total-meters').text(totalMeters.toFixed(2) + ' M');

            var costPerMeter = totalMeters > 0 ? (grandTotalLandedCost / totalMeters) : 0;
            $('#summary-cost-per-meter').text(fmtCurrency(costPerMeter) + ' / M');
        }

        // Run on load
        $('.input-used-qty').trigger('input');

        // If in View Mode, disable all inputs and destroy datepickers
        <?php if (isset($isView) && $isView): ?>
            $('#receiptForm input, #receiptForm select, #receiptForm textarea').prop('disabled', true);
            // Disable flatpickr altInputs
            setTimeout(function() {
                $('#receiptForm .flatpickr-input').each(function() {
                    if (this._flatpickr) {
                        this._flatpickr.destroy();
                    }
                });
                $('#receiptForm input').prop('disabled', true);
            }, 100);
        <?php endif; ?>
    });
</script>
<?= $this->endSection() ?>