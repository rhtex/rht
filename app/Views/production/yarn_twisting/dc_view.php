<?= $this->extend('layouts/master') ?>
<?= $this->section('title') ?>Twisting DC Details<?= $this->endSection() ?>
<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6"><h1>Twisting DC Details</h1></div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-twisting') ?>" class="btn btn-secondary"><i class="fas fa-list"></i> List</a>
        <?php if($dc['status'] !== 'Completed' && $dc['status'] !== 'Cancelled'): ?>
            <a href="<?= site_url('production/yarn-twisting/receipt-create/'.$dc['id']) ?>" class="btn btn-success"><i class="fas fa-download"></i> Receive Yarn</a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="card card-outline card-info">
    <div class="card-body">
        <?php if(session()->has('success')): ?><div class="alert alert-success"><?= session('success') ?></div><?php endif; ?>
        <?php if(session()->has('error')): ?><div class="alert alert-danger"><?= session('error') ?></div><?php endif; ?>
        <div class="row mb-4">
            <div class="col-sm-6">
                <h5>Vendor (Twister)</h5>
                <strong><?= esc($dc['vendor_name']) ?></strong>
            </div>
            <div class="col-sm-6">
                <b>DC No:</b> <?= esc($dc['dc_number']) ?><br>
                <b>Date:</b> <?= esc($dc['dc_date']) ?><br>
                <b>Status:</b> <span class="badge bg-primary"><?= esc($dc['status']) ?></span>
            </div>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr class="bg-light">
                    <th>Mill / Brand</th>
                    <th>Yarn Count</th>
                    <th>Warp/Weft</th>
                    <th>Issued Color</th>
                    
                    <th>Qty Issued (Kg)</th>
                    <th>Qty Received (Kg)</th>
                    <th>Qty Wastage (Kg)</th>
                    <th>Pending (Kg)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <?php 
                    $pending = $item['quantity_issued_kg'] - ($item['quantity_received_kg'] + $item['quantity_wastage_kg']);
                ?>
                <tr>
                    <td><?= esc($item['mill_name']) ?></td>
                    <td><?= esc($item['yarn_count']) ?></td>
                    <td><?= esc($item['warp_weft']) ?></td>
                    <td><?= esc($item['current_color']) ?></td>
                    
                    <td><?= number_format($item['quantity_issued_kg'], 2) ?> Kg</td>
                    <td class="text-success"><?= number_format($item['quantity_received_kg'], 2) ?> Kg</td>
                    <td class="text-warning"><?= number_format($item['quantity_wastage_kg'], 2) ?> Kg</td>
                    <td><strong><?= number_format(max(0, $pending), 2) ?> Kg</strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Receipts List -->
        <h5 class="mt-5 text-success"><i class="fas fa-file-invoice"></i> Received Receipts</h5>
        <hr>
        <?php if(!empty($receipts)): ?>
            <?php foreach($receipts as $r): ?>
                <div class="card card-outline card-success mb-3">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <strong>Receipt No: <?= esc($r['receipt_number']) ?></strong> | Date: <?= esc($r['receipt_date']) ?>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="<?= site_url('production/yarn-twisting/receipt-delete/'.$r['id']) ?>" class="btn btn-xs btn-danger" onclick="return confirm('Revert and delete this receipt?')"><i class="fas fa-trash"></i> Delete Receipt</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr class="bg-light text-xs">
                                    <th>Yarn Description</th>
                                    
                                    <th>Recd Qty (Kg)</th>
                                    <th>Wastage (Kg)</th>
                                    <th>Job Charges (₹/Kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($r['items'] as $ri): ?>
                                <tr>
                                    <td><?= esc($ri['mill_name']) ?> - <?= esc($ri['yarn_count']) ?></td>
                                    
                                    <td><?= number_format($ri['quantity_received_kg'], 2) ?> Kg</td>
                                    <td><?= number_format($ri['quantity_wastage_kg'], 2) ?> Kg</td>
                                    <td>₹<?= number_format($ri['job_work_charges'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">No receipts saved yet.</p>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>