<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($isEdit) ? 'Edit Receipt' : 'Receive Warping & Sizing' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($isEdit) ? 'Edit Receipt (#' . esc($receipt['receipt_number']) . ')' : 'Receive Yarn (Against DC: ' . esc($dc['dc_number']) . ')' ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-warping-sizing/view/'.$dc['id']) ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to DC</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-success">
    <?php 
        $formAction = isset($isEdit) 
            ? site_url('production/yarn-warping-sizing/receipt-update/'.$receipt['id']) 
            : site_url('production/yarn-warping-sizing/receipt-store/'.$dc['id']);
    ?>
    <form action="<?= $formAction ?>" method="post" id="receiptForm">
        <?= csrf_field() ?>
        <div class="card-body">
            <?php if (session()->has('error')) : ?>
                <div class="alert alert-danger"><?= session('error') ?></div>
            <?php endif ?>
            <?php if (session()->has('errors')) : ?>
                <div class="alert alert-danger">
                    <ul>
                    <?php foreach (session('errors') as $error) : ?>
                        <li><?= $error ?></li>
                    <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <!-- Receipt Header Details -->
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Receipt Number <span class="text-danger">*</span></label>
                    <input type="text" name="receipt_number" class="form-control" value="<?= old('receipt_number', isset($isEdit) ? $receipt['receipt_number'] : $nextReceiptNumber) ?>" required readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Receipt Date <span class="text-danger">*</span></label>
                    <input type="date" name="receipt_date" id="receipt_date" class="form-control" value="<?= old('receipt_date', isset($isEdit) ? $receipt['receipt_date'] : date('Y-m-d')) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Vendor (Job Worker)</label>
                    <input type="text" class="form-control" value="<?= esc($dc['vendor_name']) ?> (Warping & Sizing)" disabled>
                </div>

                <!-- Additional Landed Expenses -->
                <div class="col-md-12 mt-2"><h6 class="text-primary"><i class="fas fa-truck"></i> Shared Landed Expenses (Pro-rated by Weight)</h6><hr class="my-2"></div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Transport Charges (₹)</label>
                    <input type="number" step="0.01" name="transport_charges" id="transport_charges" class="form-control expense-input" value="<?= old('transport_charges', isset($isEdit) ? number_format($receipt['transport_charges'], 2, '.', '') : '0.00') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Loading / Unloading (₹)</label>
                    <input type="number" step="0.01" name="loading_charges" id="loading_charges" class="form-control expense-input" value="<?= old('loading_charges', isset($isEdit) ? number_format($receipt['loading_charges'], 2, '.', '') : '0.00') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Packing Charges (₹)</label>
                    <input type="number" step="0.01" name="packing_charges" id="packing_charges" class="form-control expense-input" value="<?= old('packing_charges', isset($isEdit) ? number_format($receipt['packing_charges'], 2, '.', '') : '0.00') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Other Expenses (₹)</label>
                    <input type="number" step="0.01" name="other_expenses" id="other_expenses" class="form-control expense-input" value="<?= old('other_expenses', isset($isEdit) ? number_format($receipt['other_expenses'], 2, '.', '') : '0.00') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-primary fw-bold">Job Work Charges (₹/Kg) <span class="text-danger">*</span></label>
                    <?php
                        $defaultJobCharges = '0.00';
                        if (isset($isEdit) && !empty($receiptItems)) {
                            $defaultJobCharges = number_format($receiptItems[0]['job_work_charges'], 2, '.', '');
                        }
                    ?>
                    <input type="number" step="0.01" name="job_work_charges" id="job_work_charges" class="form-control expense-input fw-bold border-primary" value="<?= old('job_work_charges', $defaultJobCharges) ?>" required>
                </div>
                <div class="col-md-9 mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="1" placeholder="Receipt notes..."><?= old('remarks', isset($isEdit) ? $receipt['remarks'] : '') ?></textarea>
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
                                        <div class="fw-bold text-primary mb-2"><i class="fas fa-ring"></i> Beam: <?= esc($beam['beam_number']) ?></div>
                                        
                                        <div class="mb-2">
                                            <label class="small fw-bold">Return Status</label>
                                            <select name="beams_return[<?= esc($beam['beam_number']) ?>][status]" class="form-select form-select-sm beam-status-select" data-beam="<?= esc($beam['beam_number']) ?>">
                                                <option value="Loaded" <?= $beam['status'] === 'Loaded' ? 'selected' : '' ?>>Loaded (Yarn Wound)</option>
                                                <option value="Empty" <?= $beam['status'] === 'Empty' ? 'selected' : '' ?>>Returned Empty</option>
                                                <option value="Not Returned" <?= $beam['status'] === 'Not Returned' ? 'selected' : '' ?>>Not Returned</option>
                                            </select>
                                        </div>

                                        <div class="beam-specs-fields" id="specs_<?= esc($beam['beam_number']) ?>" style="<?= $beam['status'] === 'Loaded' ? '' : 'display: none;' ?>">
                                            <div class="mb-2">
                                                <label class="small fw-bold">Meters <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" name="beams_return[<?= esc($beam['beam_number']) ?>][meters]" class="form-control form-control-sm input-beam-meters" value="<?= esc($beam['meters'] ?? '') ?>" placeholder="Length in meters">
                                            </div>
                                            <div class="mb-2">
                                                <label class="small fw-bold">Sizing No <span class="text-danger">*</span></label>
                                                <input type="text" name="beams_return[<?= esc($beam['beam_number']) ?>][sizing_no]" class="form-control form-control-sm input-beam-sizing-no" value="<?= esc($beam['sizing_no'] ?? '') ?>" placeholder="e.g. SZ-101">
                                            </div>
                                            <div class="mb-2">
                                                <label class="small fw-bold">Color <span class="text-danger">*</span></label>
                                                <input type="text" name="beams_return[<?= esc($beam['beam_number']) ?>][color]" class="form-control form-control-sm input-beam-color" value="<?= esc($beam['color'] ?? '') ?>" placeholder="e.g. Royal Blue">
                                            </div>
                                            <div class="mb-2">
                                                <label class="small fw-bold">Sizing Date <span class="text-danger">*</span></label>
                                                <input type="date" name="beams_return[<?= esc($beam['beam_number']) ?>][return_date]" class="form-control form-control-sm input-beam-date" value="<?= esc($beam['return_date'] ?? date('Y-m-d')) ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-warning mb-0"><i class="fas fa-exclamation-triangle me-1"></i> No pending beams to receive for this challan.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                        <!-- Receipt Items Table -->
            <div class="row mt-4">
                <div class="col-md-12"><h5 class="text-success"><i class="fas fa-check-double"></i> Receipt Items</h5><hr></div>
                
                <div class="col-md-12 table-responsive">
                    <!-- WARPING & SIZING TABLE -->
                    <table class="table table-bordered align-middle" id="receiptItemsTable">
                        <thead>
                            <tr class="bg-light text-sm">
                                <th>Yarn Description</th>
                                <th width="12%">Issued Qty (Kg)</th>
                                <th width="15%">Issued Qty Cost (₹)</th>
                                <th width="18%">Recd Qty (Loaded) (Kg) <span class="text-danger">*</span></th>
                                <th width="18%">Used Qty (Kg) <span class="text-danger">*</span></th>
                                <th width="15%">Used Yarn Cost (₹)</th>
                                <th width="18%">Received Yarn Cost (Landed) (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $item): ?>
                            <?php 
                                $db = \Config\Database::connect();
                                $ri = isset($isEdit) && isset($receiptItemsKeyed[$item['id']]) ? $receiptItemsKeyed[$item['id']] : null;

                                $issued = (float)$item['quantity_issued_kg'];
                                $receivedExcludingCurrent = (float)$item['quantity_received_kg'] - ($ri ? (float)$ri['quantity_received_kg'] : 0);
                                $wastageExcludingCurrent = (float)$item['quantity_wastage_kg'] - ($ri ? (float)$ri['quantity_wastage_kg'] : 0);
                                
                                $pending = $issued - ($receivedExcludingCurrent + $wastageExcludingCurrent);

                                $qtyReceivedVal = $ri ? (float)$ri['quantity_received_kg'] : 0.00;
                                $qtyUsedVal = $ri ? ($qtyReceivedVal + (float)$ri['quantity_wastage_kg']) : 0.00;

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
                                $rawCost = $issuanceMovement ? abs((float)$issuanceMovement['cost_per_kg']) : 0;
                                
                                $issuedQtyCost = $issued * $rawCost;
                            ?>
                            <tr class="item-row" data-item-id="<?= $item['id'] ?>" data-issued="<?= $issued ?>" data-pending="<?= $pending ?>" data-raw-cost="<?= $rawCost ?>">
                                <td>
                                    <strong><?= esc($item['mill_name']) ?> - <?= esc($item['yarn_count']) ?></strong><br>
                                    <small class="text-muted">
                                        Type: <?= esc($item['yarn_type']) ?> | Warp/Weft: <?= esc($item['warp_weft']) ?> | Color: <?= esc($item['current_color']) ?><br>
                                        Lot: <?= esc($item['lot_number'] ?: '-') ?> | CSP: <?= esc($item['csp'] ?: '-') ?><br>
                                        <span class="text-primary fw-bold">Yarn Cost: ₹<?= number_format($rawCost, 2) ?> / Kg</span>
                                    </small>
                                    <input type="hidden" name="items[<?= $item['id'] ?>][received_color]" value="<?= esc($item['current_color']) ?>">
                                </td>
                                <td><?= number_format($issued, 2) ?> Kg</td>
                                <td>
                                    <strong class="text-secondary">₹<?= number_format($issuedQtyCost, 2) ?></strong>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[<?= $item['id'] ?>][quantity_received_kg]" class="form-control form-control-sm input-recd" value="<?= number_format($qtyReceivedVal, 2, '.', '') ?>" min="0" required>
                                    <small class="text-danger qty-error-msg" style="display:none; font-weight:bold;"></small>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[<?= $item['id'] ?>][quantity_used_kg]" class="form-control form-control-sm input-used-qty" value="<?= number_format($qtyUsedVal, 2, '.', '') ?>" min="0" required>
                                </td>
                                <td>
                                    <span class="fw-bold lbl-used-yarn-cost" id="lbl-used-yarn-cost-<?= $item['id'] ?>">₹0.00</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success lbl-landed-cost">₹0.00</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Warping & Sizing Cost Per Meter Summary Card -->
            <div class="card card-outline card-success mt-4">
                <div class="card-header">
                    <h5 class="card-title text-success"><i class="fas fa-calculator"></i> Warping & Sizing Summary (Cost Per Meter)</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h6 class="text-muted">Total Landed Cost</h6>
                            <h3 class="text-primary fw-bold" id="summary-total-cost">₹0.00</h3>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">Total Loaded Meters</h6>
                            <h3 class="text-info fw-bold" id="summary-total-meters">0.00 M</h3>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">Landed Cost Per Meter</h6>
                            <h2 class="text-success fw-bold" id="summary-cost-per-meter">₹0.00 / M</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-success" id="btnSubmit"><i class="fas fa-check-circle"></i> <?= isset($isEdit) ? 'Update Yarn Receipt' : 'Save Yarn Receipt' ?></button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Toggle beam specifications inputs based on return status select
        $(document).on('change', '.beam-status-select', function() {
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
        $('#receiptForm').on('submit', function() {
            $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        });

        // Auto-calculate used qty for warping/sizing
        $(document).on('input', '.input-recd, .input-used-qty', function() {
            var $row = $(this).closest('tr');
            var pending = parseFloat($row.data('pending')) || 0;
            var recd = parseFloat($row.find('.input-recd').val()) || 0;
            
            // If the trigger was the received qty input, auto-populate the used qty as Issued Qty - Received Qty
            if ($(this).hasClass('input-recd')) {
                var issued = parseFloat($row.data('issued')) || 0;
                var recdVal = parseFloat($(this).val()) || 0;
                $row.find('.input-used-qty').val(Math.max(0, issued - recdVal).toFixed(2));
            }

            var used = parseFloat($row.find('.input-used-qty').val()) || 0;
            
            // Server-side / Client-side validation: Used Qty cannot exceed pending
            if (used > pending) {
                $row.find('.qty-error-msg').text('Used Qty exceeds pending (' + pending.toFixed(2) + ' Kg)').show();
                $row.addClass('table-danger');
            } else {
                $row.find('.qty-error-msg').hide();
                $row.removeClass('table-danger');
            }

            // Remaining Pending = Pending - Used Qty
            var remainingPending = Math.max(0, pending - used);
            $row.find('.lbl-pending-kgs').text(remainingPending.toFixed(2));
            
            calculateLandedCosts();
            validateSubmitButton();
        });

        function validateSubmitButton() {
            var hasError = false;
            $('.item-row').each(function() {
                var pending = parseFloat($(this).data('pending')) || 0;
                var used = parseFloat($(this).find('.input-used-qty').val()) || 0;
                if (used > pending) hasError = true;
            });

            if (hasError) {
                $('#btnSubmit').prop('disabled', true);
            } else {
                $('#btnSubmit').prop('disabled', false);
            }
        }

        // Recalculate on input change
        $(document).on('input', '.input-job-charges, .expense-input, .input-beam-meters', function() {
            calculateLandedCosts();
        });

        function calculateLandedCosts() {
            // Get total expenses from header
            var transport = parseFloat($('#transport_charges').val()) || 0;
            var loading = parseFloat($('#loading_charges').val()) || 0;
            var packing = parseFloat($('#packing_charges').val()) || 0;
            var other = parseFloat($('#other_expenses').val()) || 0;
            var totalExpenses = transport + loading + packing + other;

            // Calculate total received weight across all rows to pro-rate expenses
            var totalReceivedWeight = 0;
            $('.item-row').each(function() {
                var recd = parseFloat($(this).find('.input-recd').val()) || 0;
                totalReceivedWeight += recd;
            });

            var grandTotalLandedCost = 0;

            // Calculate for each row
            $('.item-row').each(function() {
                var $row = $(this);
                var itemId = $row.data('item-id');
                var rawCost = parseFloat($row.data('raw-cost')) || 0;
                
                var recd = parseFloat($row.find('.input-recd').val()) || 0;
                var jobRate = parseFloat($('#job_work_charges').val()) || 0;
                var shareOfExpense = totalReceivedWeight > 0 ? ((recd / totalReceivedWeight) * totalExpenses) : 0;

                var used = parseFloat($row.find('.input-used-qty').val()) || 0;
                
                // Used Yarn Cost = Used Qty * Raw Cost
                var usedYarnCost = used * rawCost;
                $('#lbl-used-yarn-cost-' + itemId).text('₹' + usedYarnCost.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                // Received Yarn Cost (Landed) = (Received Qty * Raw Cost) + (Received Qty * Job Charges) + Share of Expenses
                var landedCost = (recd * rawCost) + (recd * jobRate) + shareOfExpense;
                $row.find('.lbl-landed-cost').text('₹' + landedCost.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                
                grandTotalLandedCost += landedCost;
            });

            // Calculate overall per meter
            var totalMeters = 0;
            $('.input-beam-meters').each(function() {
                totalMeters += parseFloat($(this).val()) || 0;
            });

            $('#summary-total-cost').text('₹' + grandTotalLandedCost.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#summary-total-meters').text(totalMeters.toFixed(2) + ' M');

            var costPerMeter = totalMeters > 0 ? (grandTotalLandedCost / totalMeters) : 0;
            $('#summary-cost-per-meter').text('₹' + costPerMeter.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' / M');
        }

        // Run on load
        calculateLandedCosts();
    });
</script>
<?= $this->endSection() ?>
