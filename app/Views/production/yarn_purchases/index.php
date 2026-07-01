<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Yarn Purchases<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Yarn Purchases</h1>
    </div>
    <div class="col-sm-6 text-end">
        <?php if(in_array('weaver.create', session('permissions') ?? [])): ?>
        <a href="<?= site_url('production/yarn-purchases/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add Yarn Purchase</a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Yarn Purchase Invoices</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle" id="purchasesTable">
                <thead>
                    <tr class="bg-light">
                        <th width="10%">Date</th>
                        <th>Supplier</th>
                        <th width="10%">Invoice No</th>
                        <th>Mill Name</th>
                        <th width="8%" class="text-center">Count</th>
                        <th width="10%" class="text-center">Lot No</th>
                        <th width="10%" class="text-end">Weight (Kg)</th>
                        <th width="8%" class="text-center">Cones</th>
                        <th width="10%" class="text-end">Rate/Kg</th>
                        <th width="10%" class="text-end">Transport/Kg</th>
                        <th width="10%" class="text-end">Other/Kg</th>
                        <th width="12%" class="text-end">Landed/Kg (Excl. Tax)</th>
                        <th width="8%" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($purchases)): ?>
                        <?php foreach($purchases as $p): ?>
                        <?php
                            $weight = (float)$p['total_weight_kg'];
                            $rate = (float)$p['rate_per_kg'];
                            $transport = (float)($p['transport_charges'] ?? 0);
                            $other = (float)($p['other_charges'] ?? 0);

                            $baseCost = $weight * $rate;
                            
                            // Calculations per Kg
                            $transportPerKg = $weight > 0 ? ($transport / $weight) : 0;
                            $otherPerKg = $weight > 0 ? ($other / $weight) : 0;
                            
                            // Landed Cost per Kg (Exclusive of Tax, which is saved in ledger)
                            $totalExclTax = $baseCost + $transport + $other;
                            $landedCostPerKgExcl = $weight > 0 ? ($totalExclTax / $weight) : 0;
                        ?>
                        <tr>
                            <td><?= esc($p['purchase_date']) ?></td>
                            <td><strong><?= esc($p['supplier']) ?></strong></td>
                            <td><?= esc($p['invoice_number']) ?></td>
                            <td><?= esc($p['mill_name']) ?></td>
                            <td class="text-center"><span class="badge bg-secondary"><?= esc($p['yarn_count']) ?></span></td>
                            <td class="text-center"><?= esc($p['lot_number'] ?: '-') ?></td>
                            <td class="text-end"><strong><?= number_format($weight, 2) ?> Kg</strong></td>
                            <td class="text-center"><strong><?= (int)($p['number_cones'] ?? 0) ?></strong></td>
                            <td class="text-end">₹<?= number_format($rate, 2) ?></td>
                            <td class="text-end">₹<?= number_format($transportPerKg, 2) ?></td>
                            <td class="text-end">₹<?= number_format($otherPerKg, 2) ?></td>
                            <td class="text-end"><strong class="text-success">₹<?= number_format($landedCostPerKgExcl, 2) ?></strong></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="<?= site_url('production/yarn-purchases/view/'.$p['id']) ?>" class="btn btn-xs btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if(in_array('weaver.edit', session('permissions') ?? []) || in_array('weaver.create', session('permissions') ?? [])): ?>
                                    <a href="<?= site_url('production/yarn-purchases/edit/'.$p['id']) ?>" class="btn btn-xs btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if(in_array('weaver.delete', session('permissions') ?? [])): ?>
                                    <a href="<?= site_url('production/yarn-purchases/delete/'.$p['id']) ?>" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#purchasesTable').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 15
        });
    });
</script>
<?= $this->endSection() ?>
