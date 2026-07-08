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
                                <th width="120" class="text-center">Action</th>
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

                                    $viewUrl = null;
                                    $editUrl = null;
                                    $deleteUrl = null;

                                    $refId = $t['reference_id'];
                                    $remarksLower = strtolower($t['remarks']);

                                    if ($t['movement_type'] === 'Purchase') {
                                        $viewUrl = site_url("production/yarn-purchases/view/{$refId}");
                                        $editUrl = site_url("production/yarn-purchases/edit/{$refId}");
                                        $deleteUrl = site_url("production/yarn-purchases/delete/{$refId}");
                                    } elseif ($t['movement_type'] === 'Issue_Job_Work') {
                                        if (strpos($remarksLower, 'dyeing') !== false) {
                                            $viewUrl = site_url("production/yarn-dyeing/view/{$refId}");
                                            $editUrl = site_url("production/yarn-dyeing/edit/{$refId}");
                                            $deleteUrl = site_url("production/yarn-dyeing/delete/{$refId}");
                                        } elseif (strpos($remarksLower, 'warping') !== false || strpos($remarksLower, 'sizing') !== false) {
                                            $viewUrl = site_url("production/yarn-warping-sizing/view/{$refId}");
                                            $editUrl = site_url("production/yarn-warping-sizing/edit/{$refId}");
                                            $deleteUrl = site_url("production/yarn-warping-sizing/delete/{$refId}");
                                        } elseif (strpos($remarksLower, 'weaving') !== false) {
                                            $viewUrl = site_url("production/yarn-weaving/view/{$refId}");
                                            $editUrl = site_url("production/yarn-weaving/edit/{$refId}");
                                            $deleteUrl = site_url("production/yarn-weaving/delete/{$refId}");
                                        } elseif (strpos($remarksLower, 'twisting') !== false) {
                                            $viewUrl = site_url("production/yarn-twisting/view/{$refId}");
                                            $editUrl = site_url("production/yarn-twisting/edit/{$refId}");
                                            $deleteUrl = site_url("production/yarn-twisting/delete/{$refId}");
                                        }
                                    } elseif ($t['movement_type'] === 'Receipt_Job_Work') {
                                        if (strpos($remarksLower, 'dyeing') !== false) {
                                            $viewUrl = site_url("production/yarn-dyeing/receipt-view/{$refId}");
                                            $editUrl = site_url("production/yarn-dyeing/receipt-edit/{$refId}");
                                            $deleteUrl = site_url("production/yarn-dyeing/receipt-delete/{$refId}");
                                        } elseif (strpos($remarksLower, 'warping') !== false || strpos($remarksLower, 'sizing') !== false) {
                                            $viewUrl = site_url("production/yarn-warping-sizing/receipt-view/{$refId}");
                                            $editUrl = site_url("production/yarn-warping-sizing/receipt-edit/{$refId}");
                                            $deleteUrl = site_url("production/yarn-warping-sizing/receipt-delete/{$refId}");
                                        } elseif (strpos($remarksLower, 'weaving') !== false) {
                                            $viewUrl = site_url("production/yarn-weaving/receipt-view/{$refId}");
                                            $editUrl = site_url("production/yarn-weaving/receipt-edit/{$refId}");
                                            $deleteUrl = site_url("production/yarn-weaving/receipt-delete/{$refId}");
                                        } elseif (strpos($remarksLower, 'twisting') !== false) {
                                            $viewUrl = site_url("production/yarn-twisting/receipt-view/{$refId}");
                                            $editUrl = site_url("production/yarn-twisting/receipt-edit/{$refId}");
                                            $deleteUrl = site_url("production/yarn-twisting/receipt-delete/{$refId}");
                                        }
                                    }
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
                                    <td class="text-center">
                                         <?php if ($viewUrl || $editUrl || $deleteUrl): ?>
                                             <div class="btn-group">
                                                 <?php if ($viewUrl): ?>
                                                     <a href="<?= $viewUrl ?>" class="btn btn-xs btn-outline-info" title="View Source"><i class="fas fa-eye"></i></a>
                                                 <?php endif; ?>
                                                 <?php if ($editUrl): ?>
                                                     <a href="<?= $editUrl ?>" class="btn btn-xs btn-outline-primary mx-1" title="Edit Source"><i class="fas fa-edit"></i></a>
                                                 <?php endif; ?>
                                                 <?php if ($deleteUrl): ?>
                                                     <a href="<?= $deleteUrl ?>" class="btn btn-xs btn-outline-danger" title="Delete Source" onclick="return confirm('Are you sure you want to delete this source document? This cannot be undone.');"><i class="fas fa-trash"></i></a>
                                                 <?php endif; ?>
                                             </div>
                                         <?php else: ?>
                                             <span class="text-muted">-</span>
                                         <?php endif; ?>
                                     </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No transactions recorded.</td>
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
