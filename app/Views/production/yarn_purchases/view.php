<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Yarn Purchase Details<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Yarn Purchase Details</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-purchases') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-invoice me-1"></i> Purchase Invoice Information</h3>
            <div class="card-tools">
                <?php if(in_array('weaver.edit', session('permissions') ?? []) || in_array('weaver.create', session('permissions') ?? [])): ?>
                <a href="<?= site_url('production/yarn-purchases/edit/' . $purchase['id']) ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Supplier and Invoice -->
                <div class="col-md-6">
                    <h5 class="text-primary border-bottom pb-2">Invoice Details</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Supplier Name</th>
                            <td><strong><?= esc($purchase['supplier']) ?></strong></td>
                        </tr>
                        <tr>
                            <th>Invoice Number</th>
                            <td><?= esc($purchase['invoice_number']) ?></td>
                        </tr>
                        <tr>
                            <th>Purchase Date</th>
                            <td><?= esc($purchase['purchase_date']) ?></td>
                        </tr>
                        <tr>
                            <th>Warehouse Location</th>
                            <td><?= esc($purchase['warehouse_location'] ?: 'Main Warehouse') ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Yarn Specs -->
                <div class="col-md-6">
                    <h5 class="text-primary border-bottom pb-2">Yarn Specifications</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Mill Name</th>
                            <td><?= esc($purchase['mill_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Yarn Count</th>
                            <td><span class="badge bg-secondary"><?= esc($purchase['yarn_count']) ?></span></td>
                        </tr>
                        <tr>
                            <th>Material Type</th>
                            <td><?= esc($purchase['material_type']) ?></td>
                        </tr>
                        <tr>
                            <th>Warp / Weft</th>
                            <td><?= esc($purchase['warp_weft']) ?></td>
                        </tr>
                        <tr>
                            <th>CSP</th>
                            <td><?= esc($purchase['csp'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <th>Lot Number</th>
                            <td><?= esc($purchase['lot_number'] ?: '-') ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Financial details -->
                <div class="col-md-12 mt-4">
                    <h5 class="text-primary border-bottom pb-2">Financial & Costing Breakdown</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="50%">Number of Bags</th>
                                    <td><?= esc($purchase['number_bags']) ?></td>
                                </tr>
                                <tr>
                                    <th>Number of Cones</th>
                                    <td><?= esc($purchase['number_cones'] ?? '0') ?></td>
                                </tr>
                                <tr>
                                    <th>Total Weight (Kg)</th>
                                    <td><strong><?= number_format($purchase['total_weight_kg'], 2) ?> Kg</strong></td>
                                </tr>
                                <tr>
                                    <th>Rate per Kg</th>
                                    <td>₹<?= number_format($purchase['rate_per_kg'], 2) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <?php 
                                $weight = (float)$purchase['total_weight_kg'];
                                $rate = (float)$purchase['rate_per_kg'];
                                $transport = (float)($purchase['transport_charges'] ?? 0);
                                $other = (float)($purchase['other_charges'] ?? 0);

                                $baseCost = $weight * $rate;
                                $totalExclTax = $baseCost + $transport + $other;

                                $transportPerKg = $weight > 0 ? ($transport / $weight) : 0;
                                $otherPerKg = $weight > 0 ? ($other / $weight) : 0;
                                $landedCostPerKg = $weight > 0 ? ($totalExclTax / $weight) : 0;
                            ?>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="50%">Transport Charges (₹)</th>
                                    <td>₹<?= number_format($transport, 2) ?> <small class="text-muted">(₹<?= number_format($transportPerKg, 2) ?> / Kg)</small></td>
                                </tr>
                                <tr>
                                    <th>Other Charges (₹)</th>
                                    <td>₹<?= number_format($other, 2) ?> <small class="text-muted">(₹<?= number_format($otherPerKg, 2) ?> / Kg)</small></td>
                                </tr>
                                <tr>
                                    <th>Total Landed Value</th>
                                    <td><strong>₹<?= number_format($totalExclTax, 2) ?></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Final Landed Costs -->
                <div class="col-md-12 mt-3">
                    <div class="alert alert-info text-center py-3">
                        <span class="h5">
                            <strong>Landed Cost per Kg:</strong>
                            <span class="h4 ml-2 text-primary font-weight-bold">₹<?= number_format($landedCostPerKg, 2) ?></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
