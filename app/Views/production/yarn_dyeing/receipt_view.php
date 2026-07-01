<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="fw-bold text-dark"><i class="fas fa-file-invoice text-success me-2"></i>Receipt details: <?= esc($receipt['receipt_number']) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-dyeing/view/'.$dc['id']) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-none"><i class="fas fa-arrow-left me-1"></i> Back to DC</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row g-3">
    <!-- Receipt Overview Card -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-header bg-dark text-white py-2 fw-bold"><i class="fas fa-info-circle me-1"></i> Receipt Details</div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted py-1" width="40%">Receipt No</td><td class="fw-bold py-1"><?= esc($receipt['receipt_number']) ?></td></tr>
                    <tr><td class="text-muted py-1">Receipt Date</td><td class="fw-bold py-1"><?= date('d-M-Y', strtotime($receipt['receipt_date'])) ?></td></tr>
                    <tr><td class="text-muted py-1">Dyer Name</td><td class="fw-bold py-1"><?= esc($dc['vendor_name']) ?></td></tr>
                    <tr><td class="text-muted py-1">Challan Ref</td><td class="fw-bold py-1 font-monospace text-primary"><?= esc($dc['dc_number']) ?></td></tr>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-secondary text-white py-2 fw-bold"><i class="fas fa-coins me-1"></i> Receipt Expenses</div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted py-1">Transport Charges</td><td class="fw-bold text-end py-1">₹<?= number_format($receipt['transport_charges'], 2) ?></td></tr>
                    <tr><td class="text-muted py-1">Loading Charges</td><td class="fw-bold text-end py-1">₹<?= number_format($receipt['loading_charges'], 2) ?></td></tr>
                    <tr><td class="text-muted py-1">Packing Charges</td><td class="fw-bold text-end py-1">₹<?= number_format($receipt['packing_charges'], 2) ?></td></tr>
                    <tr><td class="text-muted py-1">Other Expenses</td><td class="fw-bold text-end py-1">₹<?= number_format($receipt['other_expenses'], 2) ?></td></tr>
                    <tr class="border-top"><td class="fw-bold py-2">Total Expenses</td><td class="fw-bold text-end text-primary py-2">₹<?= number_format($receipt['transport_charges'] + $receipt['loading_charges'] + $receipt['packing_charges'] + $receipt['other_expenses'], 2) ?></td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Receipt Items Card Grid -->
    <div class="col-md-8">
        <h5 class="text-dark fw-bold mb-3"><i class="fas fa-cubes text-primary me-1"></i> Received Yarn Batches</h5>
        <div class="row g-3">
            <?php foreach($items as $item): ?>
            <?php 
                $bgCol = $colorsList[$item['received_color']] ?? '#3498db';
                $recdValue = $item['quantity_received_kg'] * $item['landed_cost_per_kg'];
            ?>
            <div class="col-12">
                <div class="card shadow-sm border border-light-subtle rounded-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 border-bottom border-light-subtle">
                        <strong class="font-monospace text-dark small"><i class="fas fa-dolly text-secondary me-2"></i><?= esc($item['mill_name']) ?> - <?= esc($item['yarn_count']) ?></strong>
                        <span class="badge px-3 py-1 text-uppercase" style="background-color: <?= $bgCol ?>; color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.5); font-size: 0.75rem; border: 1px solid rgba(255,255,255,0.2);"><?= esc($item['received_color']) ?></span>
                    </div>
                    <div class="card-body p-3 bg-white">
                        <div class="row g-2 text-center">
                            <div class="col-md-3 bg-light rounded p-2">
                                <small class="text-muted d-block">Received Weight</small>
                                <strong class="text-dark font-monospace"><?= number_format($item['quantity_received_kg'], 2) ?> Kg</strong>
                            </div>
                            <div class="col-md-3 bg-light rounded p-2">
                                <small class="text-muted d-block">Received Cones</small>
                                <strong class="text-dark font-monospace"><?= (int)$item['cones_received'] ?> Cones</strong>
                            </div>
                            <div class="col-md-3 bg-light rounded p-2">
                                <small class="text-muted d-block">Wasted Weight</small>
                                <strong class="text-danger font-monospace"><?= number_format($item['quantity_wastage_kg'], 2) ?> Kg</strong>
                            </div>
                            <div class="col-md-3 bg-light rounded p-2">
                                <small class="text-muted d-block">Job Rate charges</small>
                                <strong class="text-dark font-monospace">₹<?= number_format($item['job_work_charges'], 2) ?>/Kg</strong>
                            </div>
                        </div>

                        <!-- Costing breakdown footer panel -->
                        <div class="row mt-3 pt-3 border-top border-light-subtle align-items-center">
                            <div class="col-6 text-start">
                                <small class="text-muted d-block">Unit Landed Cost</small>
                                <strong class="text-success font-monospace" style="font-size: 1.15rem;">₹<?= number_format($item['landed_cost_per_kg'], 2) ?> / Kg</strong>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted d-block">Landed Value Valuation</small>
                                <strong class="text-success font-monospace" style="font-size: 1.15rem;">₹<?= number_format($recdValue, 2) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
