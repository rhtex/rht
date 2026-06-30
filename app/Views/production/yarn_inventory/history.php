<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Stock Transaction Ledger<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Yarn Transaction Ledger</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-inventory') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Inventory</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <!-- Yarn Info Card -->
    <div class="col-md-4">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Yarn Specifications</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <tr>
                        <th width="40%">Mill / Brand</th>
                        <td><strong><?= esc($yarn_details['mill']) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Yarn Count</th>
                        <td><span class="badge bg-secondary"><?= esc($yarn_details['count']) ?></span></td>
                    </tr>
                    <tr>
                        <th>Warp / Weft</th>
                        <td><?= esc($yarn_details['warp_weft']) ?></td>
                    </tr>
                    <tr>
                        <th>CSP</th>
                        <td><?= esc($yarn_details['csp'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Lot Number</th>
                        <td><?= esc($yarn_details['lot']) ?: '-' ?></td>
                    </tr>
                    <tr>
                        <th>Color</th>
                        <td><?= esc($yarn_details['color'] ?: 'Raw') ?></td>
                    </tr>
                    <tr>
                        <th>Yarn Type</th>
                        <td>
                            <span class="badge bg-<?= $yarn_details['type'] === 'Dyed' ? 'info' : 'light text-dark border' ?>">
                                <?= esc($yarn_details['type']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Warehouse</th>
                        <td><?= esc($yarn_details['warehouse'] ?: 'Main Warehouse') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> Transaction History</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="ledgerTable">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Type</th>
                                <th>Qty (Kg)</th>
                                <th>Cost/Kg</th>
                                <th>Running Bal</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $runningBalance = 0;
                            ?>
                            <?php if(!empty($transactions)): ?>
                                <?php foreach($transactions as $t): ?>
                                <?php 
                                    $qty = (float)$t['quantity_kg'];
                                    $runningBalance += $qty;
                                ?>
                                <tr>
                                    <td><?= esc($t['created_at']) ?></td>
                                    <td>
                                        <?php 
                                            $badge = 'secondary';
                                            if ($t['movement_type'] === 'Purchase') $badge = 'success';
                                            elseif ($t['movement_type'] === 'Issue_Job_Work') $badge = 'warning';
                                            elseif ($t['movement_type'] === 'Receipt_Job_Work') $badge = 'info';
                                            elseif ($t['movement_type'] === 'Return') $badge = 'danger';
                                        ?>
                                        <span class="badge bg-<?= $badge ?>">
                                            <?= str_replace('_', ' ', $t['movement_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-<?= $qty >= 0 ? 'success' : 'danger' ?>">
                                            <?= $qty >= 0 ? '+' : '' ?><?= number_format($qty, 2) ?> Kg
                                        </strong>
                                    </td>
                                    <td>₹<?= number_format($t['cost_per_kg'], 2) ?></td>
                                    <td><strong><?= number_format($runningBalance, 2) ?> Kg</strong></td>
                                    <td><?= esc($t['remarks']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No transactions recorded.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
