<?= $this->extend('layouts/master') ?>
<?= $this->section('title') ?>Receive Woven Yarn Stock<?= $this->endSection() ?>
<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6"><h1>Receive Woven Yarn (Against DC: <?= esc($dc['dc_number']) ?>)</h1></div>
    <div class="col-sm-6 text-end"><a href="<?= site_url('production/yarn-weaving/view/'.$dc['id']) ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a></div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="card card-outline card-success">
    <form action="<?= site_url('production/yarn-weaving/receipt-store/'.$dc['id']) ?>" method="post" id="receiptForm">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Receipt Number <span class="text-danger">*</span></label>
                    <input type="text" name="receipt_number" class="form-control" value="<?= $nextReceiptNumber ?>" required readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Receipt Date <span class="text-danger">*</span></label>
                    <input type="date" name="receipt_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Vendor (Weaver)</label>
                    <input type="text" class="form-control" value="<?= esc($dc['vendor_name']) ?>" disabled>
                </div>
            </div>

            <!-- Shared Expenses -->
            <div class="row mt-2">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Transport Charges (₹)</label>
                    <input type="number" step="0.01" name="transport_charges" id="transport_charges" class="form-control expense-input" value="0.00">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Loading Charges (₹)</label>
                    <input type="number" step="0.01" name="loading_charges" id="loading_charges" class="form-control expense-input" value="0.00">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Packing Charges (₹)</label>
                    <input type="number" step="0.01" name="packing_charges" id="packing_charges" class="form-control expense-input" value="0.00">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Other Expenses (₹)</label>
                    <input type="number" step="0.01" name="other_expenses" id="other_expenses" class="form-control expense-input" value="0.00">
                </div>
            </div>

            <!-- Items Table -->
            <h5 class="text-success mt-4">Receipt Items</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr class="bg-light">
                            <th>Yarn Description</th>
                            
                            
                            <th width="12%">Issued Qty</th>
                            <th width="12%">Already Recd</th>
                            <th width="15%">Recd Qty (Kg) <span class="text-danger">*</span></th>
                            <th width="12%">Wastage (Kg) <span class="text-danger">*</span></th>
                            <th width="12%">Pending KGs</th>
                            <th width="12%">Job Charges (₹/Kg) <span class="text-danger">*</span></th>
                            <th width="15%">Est. Landed Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): ?>
                        <?php 
                            $issued = (float)$item['quantity_issued_kg'];
                            $received = (float)$item['quantity_received_kg'];
                            $wastage = (float)$item['quantity_wastage_kg'];
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
                        <tr class="item-row" data-item-id="<?= $item['id'] ?>" data-pending="<?= $pending ?>" data-raw-cost="<?= $rawCost ?>">
                            <td>
                                <strong><?= esc($item['mill_name']) ?> - <?= esc($item['yarn_count']) ?></strong>
                            </td>
                            
                            
                            <td><?= number_format($issued, 2) ?> Kg</td>
                            <td><?= number_format($received, 2) ?> Kg</td>
                            <td>
                                <input type="number" step="0.01" name="items[<?= $item['id'] ?>][quantity_received_kg]" class="form-control form-control-sm input-recd" value="0.00" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[<?= $item['id'] ?>][quantity_wastage_kg]" class="form-control form-control-sm input-wastage" value="0.00" required>
                            </td>
                            <td>
                                <strong class="text-primary"><span class="lbl-pending-kgs"><?= number_format($pending, 2) ?></span> Kg</strong>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[<?= $item['id'] ?>][job_work_charges]" class="form-control form-control-sm input-job-charges" value="0.00" required>
                            </td>
                            <td>
                                <span class="lbl-landed-cost text-success fw-bold">₹0.00</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-success" id="btnSubmit"><i class="fas fa-check-circle"></i> Save Receipt</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $(document).on('input', '.input-recd, .input-wastage, .input-job-charges, .expense-input', function() {
        calculateLandedCosts();
    });

    function calculateLandedCosts() {
        var transport = parseFloat($('#transport_charges').val()) || 0;
        var loading = parseFloat($('#loading_charges').val()) || 0;
        var packing = parseFloat($('#packing_charges').val()) || 0;
        var other = parseFloat($('#other_expenses').val()) || 0;
        var totalExpenses = transport + loading + packing + other;

        var totalReceivedWeight = 0;
        $('.item-row').each(function() {
            totalReceivedWeight += parseFloat($(this).find('.input-recd').val()) || 0;
        });

        $('.item-row').each(function() {
            var $row = $(this);
            var pending = parseFloat($row.data('pending')) || 0;
            var rawCost = parseFloat($row.data('raw-cost')) || 0;
            var recd = parseFloat($row.find('.input-recd').val()) || 0;
            var wastage = parseFloat($row.find('.input-wastage').val()) || 0;
            var jobRate = parseFloat($row.find('.input-job-charges').val()) || 0;

            var shareOfExpense = totalReceivedWeight > 0 ? ((recd / totalReceivedWeight) * totalExpenses) : 0;
            var totalLandedCost = ((recd + wastage) * rawCost) + (recd * jobRate) + shareOfExpense;
            var landedCostPerKg = recd > 0 ? (totalLandedCost / recd) : 0;

            $row.find('.lbl-landed-cost').text('₹' + landedCostPerKg.toFixed(2));
            $row.find('.lbl-pending-kgs').text(Math.max(0, pending - recd - wastage).toFixed(2));
        });
    }
});
</script>
<?= $this->endSection() ?>