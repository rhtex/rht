<?= $this->extend('layouts/master') ?>
<?= $this->section('title') ?><?= isset($isEdit) ? 'Edit Dyeing Receipt' : 'Receive Dyed Yarn Stock' ?><?= $this->endSection() ?>
<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6"><h1><?= isset($isEdit) ? 'Edit Dyeing Receipt' : 'Receive Dyed Yarn' ?> (Against DC: <?= esc($dc['dc_number']) ?>)</h1></div>
    <div class="col-sm-6 text-end"><a href="<?= site_url('production/yarn-dyeing/view/'.$dc['id']) ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a></div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="card card-outline card-success">
    <form action="<?= isset($isEdit) ? site_url('production/yarn-dyeing/receipt-update/'.$receipt['id']) : site_url('production/yarn-dyeing/receipt-store/'.$dc['id']) ?>" method="post" id="receiptForm">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Receipt Number <span class="text-danger">*</span></label>
                    <input type="text" name="receipt_number" class="form-control" value="<?= isset($isEdit) ? esc($receipt['receipt_number']) : $nextReceiptNumber ?>" required readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Receipt Date <span class="text-danger">*</span></label>
                    <input type="date" name="receipt_date" class="form-control" value="<?= isset($isEdit) ? date('Y-m-d', strtotime($receipt['receipt_date'])) : date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Vendor (Dyer)</label>
                    <input type="text" class="form-control" value="<?= esc($dc['vendor_name']) ?>" disabled>
                </div>
            </div>

            <!-- Shared Expenses -->
            <div class="row mt-2">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Transport Charges (₹)</label>
                    <input type="number" step="0.01" name="transport_charges" id="transport_charges" class="form-control expense-input" value="<?= isset($isEdit) ? esc($receipt['transport_charges']) : '0.00' ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Loading Charges (₹)</label>
                    <input type="number" step="0.01" name="loading_charges" id="loading_charges" class="form-control expense-input" value="<?= isset($isEdit) ? esc($receipt['loading_charges']) : '0.00' ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Packing Charges (₹)</label>
                    <input type="number" step="0.01" name="packing_charges" id="packing_charges" class="form-control expense-input" value="<?= isset($isEdit) ? esc($receipt['packing_charges']) : '0.00' ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Other Expenses (₹)</label>
                    <input type="number" step="0.01" name="other_expenses" id="other_expenses" class="form-control expense-input" value="<?= isset($isEdit) ? esc($receipt['other_expenses']) : '0.00' ?>">
                </div>
            </div>

            <!-- Items Table -->
            <!-- Items Cards Grid Layout -->
            <div class="mt-4">
                <h5 class="text-dark mb-3 fw-bold"><i class="fas fa-cubes text-primary me-2"></i>Receipt Items & Balance Tracking</h5>
                <div class="row g-3">
                    <?php foreach($items as $item): ?>
                    <?php 
                        $issued = (float)$item['quantity_issued_kg'];
                        
                        // If editing, exclude current receipt values from "already received" tally
                        $received = (float)$item['quantity_received_kg'];
                        $wastage = (float)$item['quantity_wastage_kg'];
                        $conesIssued = (int)$item['cones_issued'];
                        $conesReceived = (int)$item['cones_received'];

                        $currentRecd = 0.00;
                        $currentCones = 0;
                        $currentWastage = 0.00;
                        $currentJobCharges = 0.00;
                        $currentLotNo = '';
                        $currentReceivedColor = $item['required_color'];

                        if (isset($isEdit) && isset($receiptItemsKeyed[$item['id']])) {
                            $oi = $receiptItemsKeyed[$item['id']];
                            $currentRecd = (float)$oi['quantity_received_kg'];
                            $currentCones = (int)$oi['cones_received'];
                            $currentWastage = (float)$oi['quantity_wastage_kg'];
                            $currentJobCharges = (float)$oi['job_work_charges'];
                            $currentLotNo = $oi['dyeing_lot_number'] ?? '';
                            $currentReceivedColor = $oi['received_color'];

                            // Subtract current receipt items from accumulated received tally to display correct previous levels
                            $received = max(0, $received - $currentRecd);
                            $wastage = max(0, $wastage - $currentWastage);
                            $conesReceived = max(0, $conesReceived - $currentCones);
                        }

                        $pending = $issued - ($received + $wastage);
                        
                        $rawCost = 0; // Fetch avg cost
                        $db = \Config\Database::connect();
                        $m = $db->table('production_yarn_stock_movements')
                            ->where('movement_type', 'Issue_Job_Work')
                            ->where('reference_id', $dc['id'])
                            ->where('yarn_count', $item['yarn_count'])
                            ->where('brand_mill', $item['mill_name'])
                            ->get()->getRowArray();
                        if ($m) $rawCost = abs((float)$m['cost_per_kg']);
                    ?>
                    <div class="col-12">
                        <div class="card card-item-row shadow-sm border border-light-subtle rounded-3" data-item-id="<?= $item['id'] ?>" data-pending="<?= $pending ?>" data-raw-cost="<?= $rawCost ?>">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                                <span class="fw-bold font-monospace small"><i class="fas fa-dolly me-2"></i><?= esc($item['mill_name']) ?> - <?= esc($item['yarn_count']) ?></span>
                                <div style="width: 220px;">
                                    <select name="items[<?= $item['id'] ?>][received_color]" class="form-select form-select-sm select2-target-color" required>
                                        <option value="">-- Choose Color --</option>
                                        <?php if(!empty($colorsList)): ?>
                                            <?php foreach($colorsList as $c): ?>
                                                <option value="<?= esc($c['name']) ?>" <?= esc($currentReceivedColor) === $c['name'] ? 'selected' : '' ?> data-palette="<?= esc($c['color_palette'] ?: '') ?>"><?= esc($c['name']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2 mb-3 bg-light p-2 rounded-2 border border-light-subtle">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Issued Quantity</small>
                                        <strong class="text-dark font-monospace"><?= number_format($issued, 2) ?> Kg</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Issued Cones</small>
                                        <strong class="text-dark font-monospace"><?= $conesIssued ?> Cones</strong>
                                    </div>
                                    <div class="col-6 border-top pt-1 mt-1">
                                        <small class="text-muted d-block">Already Received</small>
                                        <strong class="text-dark font-monospace"><?= number_format($received, 2) ?> Kg</strong>
                                    </div>
                                    <div class="col-6 border-top pt-1 mt-1">
                                        <small class="text-muted d-block">Already Cones</small>
                                        <strong class="text-dark font-monospace"><?= $conesReceived ?> Cones</strong>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <!-- Row 1 -->
                                    <div class="col-md-4 col-sm-6">
                                        <label class="form-label small mb-1 fw-semibold text-dark">Dyeing Lot No <span class="text-danger">*</span></label>
                                        <input type="text" name="items[<?= $item['id'] ?>][dyeing_lot_number]" class="form-control form-control-sm border-secondary-subtle" placeholder="Lot No." value="<?= esc($currentLotNo) ?>" required>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <label class="form-label small mb-1 fw-semibold text-success">Received Qty (Kg) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="items[<?= $item['id'] ?>][quantity_received_kg]" class="form-control form-control-sm input-recd border-success text-center fw-bold" value="<?= number_format($currentRecd, 2, '.', '') ?>" min="0" required>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <label class="form-label small mb-1 fw-semibold text-success">Received Cones <span class="text-danger">*</span></label>
                                        <input type="number" name="items[<?= $item['id'] ?>][cones_received]" class="form-control form-control-sm input-recd-cones border-success text-center fw-bold" value="<?= $currentCones ?>" min="0" required>
                                    </div>
                                    
                                    <!-- Row 2 -->
                                    <div class="col-md-4 col-sm-6">
                                        <label class="form-label small mb-1 fw-semibold text-danger">Wastage (Kg) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="items[<?= $item['id'] ?>][quantity_wastage_kg]" class="form-control form-control-sm input-wastage border-danger text-center fw-bold" value="<?= number_format($currentWastage, 2, '.', '') ?>" min="0" required>
                                        <input type="hidden" name="items[<?= $item['id'] ?>][cones_wastage]" value="0">
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <label class="form-label small mb-1 fw-semibold text-secondary">Job Charges (₹/Kg) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="items[<?= $item['id'] ?>][job_work_charges]" class="form-control form-control-sm input-job-charges border-secondary-subtle text-center fw-bold" value="<?= number_format($currentJobCharges, 2, '.', '') ?>" min="0" required>
                                    </div>
                                    <div class="col-md-4 col-sm-6 d-flex flex-column justify-content-center align-items-center bg-success-subtle rounded border border-success-subtle py-1 px-2" style="height: 52px; margin-top: 24px;">
                                        <small class="text-success-emphasis fw-bold" style="font-size: 0.75rem; line-height: 1;">Est. Landed Cost</small>
                                        <span class="text-success font-monospace" style="font-size: 0.8rem; line-height: 1.1;">
                                            Total: <strong class="lbl-total-landed">₹0.00</strong><br>
                                            Rate: <strong class="lbl-landed-cost">₹0.00</strong>/Kg
                                        </span>
                                    </div>
                                </div>
                                <div class="alert alert-danger py-2 px-3 mt-3 qty-warning-alert font-monospace small" style="display: none; border-radius: 6px;">
                                    <i class="fas fa-exclamation-triangle me-1"></i> <strong>Error:</strong> Received Quantity + Wastage cannot exceed Pending Quantity (<span class="lbl-alert-max"></span> Kg).
                                </div>
                            </div>
                            <div class="card-footer bg-light py-2 d-flex justify-content-between">
                                <span class="small text-muted">Remaining Balance:</span>
                                <strong class="text-primary font-monospace"><span class="lbl-pending-kgs"><?= number_format($pending, 2) ?></span> Kg Pending</strong>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="card-footer text-end bg-light border-top">
            <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm" id="btnSubmit"><i class="fas fa-check-circle me-1"></i> <?= isset($isEdit) ? 'Update Receipt' : 'Save Receipt' ?></button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Initial cost calculation on load
    calculateLandedCosts();

    // Recalculate calculations on changes
    $(document).on('input', '.input-recd, .input-wastage, .input-job-charges, .expense-input, .input-recd-cones', function() {
        calculateLandedCosts();
    });

    // Auto-calculate wastage as the remaining pending amount when Recd Qty changes
    $(document).on('input', '.input-recd', function() {
        var $row = $(this).closest('.card-item-row');
        var pendingTotal = parseFloat($row.data('pending')) || 0;
        var recdVal = parseFloat($(this).val()) || 0;
        
        // wastage = pendingTotal - recdVal (limit to 0 if negative)
        var wastageVal = Math.max(0, pendingTotal - recdVal);
        $row.find('.input-wastage').val(wastageVal.toFixed(2));
        
        calculateLandedCosts();
    });

    function calculateLandedCosts() {
        var transport = parseFloat($('#transport_charges').val()) || 0;
        var loading = parseFloat($('#loading_charges').val()) || 0;
        var packing = parseFloat($('#packing_charges').val()) || 0;
        var other = parseFloat($('#other_expenses').val()) || 0;
        var totalExpenses = transport + loading + packing + other;

        var totalReceivedWeight = 0;
        $('.card-item-row').each(function() {
            totalReceivedWeight += parseFloat($(this).find('.input-recd').val()) || 0;
        });

        var hasError = false;

        $('.card-item-row').each(function() {
            var $row = $(this);
            var pending = parseFloat($row.data('pending')) || 0;
            var rawCost = parseFloat($row.data('raw-cost')) || 0;
            var recd = parseFloat($row.find('.input-recd').val()) || 0;
            var wastage = parseFloat($row.find('.input-wastage').val()) || 0;
            var jobRate = parseFloat($row.find('.input-job-charges').val()) || 0;

            // Check if user is trying to receive/wastage more than remaining pending
            if ((recd + wastage) > (pending + 0.005)) { // 0.005 for float tolerance
                $row.find('.qty-warning-alert').show();
                $row.find('.lbl-alert-max').text(pending.toFixed(2));
                $row.addClass('border-danger').removeClass('border-light-subtle');
                hasError = true;
            } else {
                $row.find('.qty-warning-alert').hide();
                $row.removeClass('border-danger').addClass('border-light-subtle');
            }

            var shareOfExpense = totalReceivedWeight > 0 ? ((recd / totalReceivedWeight) * totalExpenses) : 0;
            // Calculate raw material cost consumed + job charges on total issued (or received + wastage)
            var totalLandedCost = ((recd + wastage) * rawCost) + ((recd + wastage) * jobRate) + shareOfExpense;
            var landedCostPerKg = recd > 0 ? (totalLandedCost / recd) : 0;

            $row.find('.lbl-total-landed').text('₹' + totalLandedCost.toFixed(2));
            $row.find('.lbl-landed-cost').text('₹' + landedCostPerKg.toFixed(2));
            
            // Remaining pending becomes exactly: pending - recd - wastage
            var remPending = Math.max(0, pending - recd - wastage);
            $row.find('.lbl-pending-kgs').text(remPending.toFixed(2));
        });

        // Disable submit button if any row has validation error
        if (hasError) {
            $('#btnSubmit').prop('disabled', true).addClass('btn-secondary').removeClass('btn-success');
        } else {
            $('#btnSubmit').prop('disabled', false).addClass('btn-success').removeClass('btn-secondary');
        }
    }

    // Format option template with color swatch
    function formatColorOption(state) {
        if (!state.id) {
            return state.text;
        }
        var palette = $(state.element).data('palette');
        if (palette) {
            var $state = $(
                '<span><span class="d-inline-block rounded-circle me-2 border border-light-subtle shadow-sm" style="width: 18px; height: 18px; background-color: ' + palette + '; vertical-align: middle;"></span>' + state.text + '</span>'
            );
            return $state;
        }
        return state.text;
    }

    // Initialize Select2 on page load
    $('.select2-target-color').select2({
        theme: 'bootstrap-5',
        width: '100%',
        templateResult: formatColorOption,
        templateSelection: formatColorOption
    });
});
</script>
<?= $this->endSection() ?>