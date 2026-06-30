<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($purchase) ? 'Edit Yarn Purchase' : 'Add Yarn Purchase' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($purchase) ? 'Edit Yarn Purchase' : 'Add Yarn Purchase' ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-purchases') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= isset($purchase) ? site_url('production/yarn-purchases/update/'.$purchase['id']) : site_url('production/yarn-purchases/store') ?>" method="post">
        <?= csrf_field() ?>
        <div class="card-body">
            <?php if (session()->has('errors')) : ?>
                <div class="alert alert-danger">
                    <ul>
                    <?php foreach (session('errors') as $error) : ?>
                        <li><?= $error ?></li>
                    <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <div class="row">
                <!-- Basic Purchase Details -->
                <div class="col-md-12 mb-2"><h5 class="text-primary"><i class="fas fa-file-invoice"></i> Purchase Invoice Details</h5><hr></div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
                    <input type="date" name="purchase_date" class="form-control" value="<?= old('purchase_date', $purchase['purchase_date'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Supplier / Vendor <span class="text-danger">*</span></label>
                    <input type="text" name="supplier" class="form-control" value="<?= old('supplier', $purchase['supplier'] ?? '') ?>" required placeholder="Supplier Name">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Invoice Number <span class="text-danger">*</span></label>
                    <input type="text" name="invoice_number" class="form-control" value="<?= old('invoice_number', $purchase['invoice_number'] ?? '') ?>" required placeholder="Invoice No">
                </div>

                <!-- Yarn Specifications -->
                <div class="col-md-12 mt-3 mb-2"><h5 class="text-primary"><i class="fas fa-tags"></i> Yarn Specifications</h5><hr></div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Mill Name <span class="text-danger">*</span></label>
                    <input type="text" name="mill_name" class="form-control" value="<?= old('mill_name', $purchase['mill_name'] ?? '') ?>" required placeholder="e.g. Birla, Raymond">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Yarn Count <span class="text-danger">*</span></label>
                    <input type="text" name="yarn_count" class="form-control" value="<?= old('yarn_count', $purchase['yarn_count'] ?? '') ?>" required placeholder="e.g. 40s, 60s, 80s, 2/40s">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Material Type <span class="text-danger">*</span></label>
                    <input type="text" name="material_type" class="form-control" value="<?= old('material_type', $purchase['material_type'] ?? 'Cotton Yarn') ?>" required placeholder="e.g. Cotton, Polyester, Silk">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Warp / Weft <span class="text-danger">*</span></label>
                    <select name="warp_weft" class="form-select" required>
                        <option value="Warp" <?= (old('warp_weft', $purchase['warp_weft'] ?? '') === 'Warp') ? 'selected' : '' ?>>Warp</option>
                        <option value="Weft" <?= (old('warp_weft', $purchase['warp_weft'] ?? '') === 'Weft') ? 'selected' : '' ?>>Weft</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">CSP (Strength)</label>
                    <input type="text" name="csp" class="form-control" value="<?= old('csp', $purchase['csp'] ?? '') ?>" placeholder="e.g. 2400, 2600">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Lot Number</label>
                    <input type="text" name="lot_number" class="form-control" value="<?= old('lot_number', $purchase['lot_number'] ?? '') ?>" placeholder="e.g. LOT-552">
                </div>

                <!-- Weight and Costing -->
                <div class="col-md-12 mt-3 mb-2"><h5 class="text-primary"><i class="fas fa-coins"></i> Weight & Costing</h5><hr></div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Number of Bags</label>
                    <input type="number" name="number_bags" id="number_bags" class="form-control" value="<?= old('number_bags', $purchase['number_bags'] ?? '0') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Weight per Bag (Kg)</label>
                    <input type="number" step="0.01" id="weight_per_bag" class="form-control" value="<?= isset($purchase) && ($purchase['number_bags'] ?? 0) > 0 ? number_format($purchase['total_weight_kg'] / $purchase['number_bags'], 2, '.', '') : '' ?>" placeholder="Optional helper">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Total Weight (Kg) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="total_weight_kg" id="total_weight_kg" class="form-control" value="<?= old('total_weight_kg', $purchase['total_weight_kg'] ?? '') ?>" required placeholder="0.00">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Rate per Kg (Excl. Tax) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="rate_per_kg" id="rate_per_kg" class="form-control" value="<?= old('rate_per_kg', $purchase['rate_per_kg'] ?? '') ?>" required placeholder="₹ 0.00">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">GST Percent (%)</label>
                    <input type="number" step="0.01" name="gst_percent" id="gst_percent" class="form-control" value="<?= old('gst_percent', $purchase['gst_percent'] ?? '5.00') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Transport Charges</label>
                    <input type="number" step="0.01" name="transport_charges" id="transport_charges" class="form-control" value="<?= old('transport_charges', $purchase['transport_charges'] ?? '0.00') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Other Charges</label>
                    <input type="number" step="0.01" name="other_charges" id="other_charges" class="form-control" value="<?= old('other_charges', $purchase['other_charges'] ?? '0.00') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Warehouse Location</label>
                    <input type="text" name="warehouse_location" class="form-control" value="<?= old('warehouse_location', $purchase['warehouse_location'] ?? 'Main Warehouse') ?>" placeholder="e.g. Warehouse A">
                </div>

                <!-- Live Costing Preview Card -->
                <div class="col-md-12 mt-3">
                    <div class="card card-outline card-success bg-light">
                        <div class="card-body py-3">
                            <h6 class="text-success mb-3"><i class="fas fa-calculator"></i> Live Landed Cost Estimation</h6>
                            <div class="row g-3 text-center">
                                <div class="col-md-4 mb-2">
                                    <span class="text-muted d-block text-sm">Base Value (Excl. Tax)</span>
                                    <h5 id="lbl_base_value" class="mb-0 font-weight-bold">₹0.00</h5>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <span class="text-muted d-block text-sm">Tax Amount (GST)</span>
                                    <h5 id="lbl_tax_amount" class="mb-0 text-danger font-weight-bold">₹0.00</h5>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <span class="text-muted d-block text-sm">Landed / Kg (Excl. Tax)</span>
                                    <h5 id="lbl_landed_excl_tax" class="mb-0 text-primary font-weight-bold">₹0.00</h5>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted d-block text-sm">Transport per Kg</span>
                                    <h5 id="lbl_transport_per_kg" class="mb-0 font-weight-bold">₹0.00</h5>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted d-block text-sm">Other Charges per Kg</span>
                                    <h5 id="lbl_other_per_kg" class="mb-0 font-weight-bold">₹0.00</h5>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted d-block text-sm">Landed / Kg (Incl. Tax)</span>
                                    <h5 id="lbl_landed_incl_tax" class="mb-0 text-success font-weight-bold">₹0.00</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Purchase Invoice</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        var $bags = $('#number_bags');
        var $weightPerBag = $('#weight_per_bag');
        var $totalWeight = $('#total_weight_kg');
        var $rate = $('#rate_per_kg');
        var $gst = $('#gst_percent');
        var $transport = $('#transport_charges');
        var $other = $('#other_charges');

        // Auto-calculate Total Weight from Bags * Weight per Bag
        function calculateTotalWeight() {
            var bagsVal = parseFloat($bags.val()) || 0;
            var wpbVal = parseFloat($weightPerBag.val()) || 0;
            if (bagsVal > 0 && wpbVal > 0) {
                $totalWeight.val((bagsVal * wpbVal).toFixed(2));
            }
            calculateLandedCost();
        }

        $bags.on('input', calculateTotalWeight);
        $weightPerBag.on('input', calculateTotalWeight);
        $totalWeight.on('input', calculateLandedCost);
        $rate.on('input', calculateLandedCost);
        $gst.on('input', calculateLandedCost);
        $transport.on('input', calculateLandedCost);
        $other.on('input', calculateLandedCost);

        // Live Landed Cost Calculation
        function calculateLandedCost() {
            var weight = parseFloat($totalWeight.val()) || 0;
            var rateVal = parseFloat($rate.val()) || 0;
            var gstVal = parseFloat($gst.val()) || 0;
            var transportVal = parseFloat($transport.val()) || 0;
            var otherVal = parseFloat($other.val()) || 0;

            var baseValue = weight * rateVal;
            var gstCost = baseValue * (gstVal / 100);
            
            var totalExclTax = baseValue + transportVal + otherVal;
            var totalInclTax = baseValue + gstCost + transportVal + otherVal;

            var transportPerKg = weight > 0 ? (transportVal / weight) : 0;
            var otherPerKg = weight > 0 ? (otherVal / weight) : 0;
            var landedExclTaxPerKg = weight > 0 ? (totalExclTax / weight) : 0;
            var landedInclTaxPerKg = weight > 0 ? (totalInclTax / weight) : 0;

            $('#lbl_base_value').text('₹' + baseValue.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#lbl_tax_amount').text('₹' + gstCost.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#lbl_transport_per_kg').text('₹' + transportPerKg.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#lbl_other_per_kg').text('₹' + otherPerKg.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#lbl_landed_excl_tax').text('₹' + landedExclTaxPerKg.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#lbl_landed_incl_tax').text('₹' + landedInclTaxPerKg.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }

        // Initial calculation
        calculateLandedCost();
    });
</script>
<?= $this->endSection() ?>
