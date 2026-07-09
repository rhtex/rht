<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= esc($title) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/receipts') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Receipt #<?= esc($receipt['receipt_number']) ?> Details</h3>
            <div class="card-tools">
                <button class="btn btn-sm btn-info" onclick="window.print()"><i class="fas fa-print"></i> Print Receipt</button>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th width="40%">Receipt Number</th>
                            <td><?= esc($receipt['receipt_number']) ?></td>
                        </tr>
                        <tr>
                            <th>Receipt Date</th>
                            <td><?= esc($receipt['receipt_date']) ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-warning"><?= esc($receipt['status']) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>Reference Document</th>
                            <td>
                                <?php if($receipt['reference_document_type'] == 'Purchase Entry'): ?>
                                    <a href="<?= site_url('bills/view/' . $receipt['reference_document_id']) ?>" class="btn btn-sm btn-warning">View Purchase Bill</a>
                                <?php else: ?>
                                    Settlement #<?= esc($receipt['reference_document_id']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th width="40%">Weaver</th>
                            <td><?= esc($receipt['weaver_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Allocation #</th>
                            <td>
                                <a href="<?= site_url('production/warp-allocations/view/' . $receipt['allocation_id']) ?>">
                                    <?= esc($receipt['allocation_number']) ?>
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <h5 class="mt-4 mb-3">Receipt Items</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Item Type</th>
                            <th>Quality</th>
                            <th>Quantity</th>
                            <th>Rate</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalAmt = 0;
                        foreach($items as $item): 
                            $totalAmt += $item['wage_amount'];
                        ?>
                            <tr>
                                <td><?= esc($item['item_type']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $item['quality_status'] == 'Good' ? 'success' : 'danger' ?>">
                                        <?= esc($item['quality_status']) ?>
                                    </span>
                                </td>
                                <td><?= esc($item['quantity']) ?></td>
                                <td><?= esc($item['rate']) ?></td>
                                <td><?= number_format($item['wage_amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total Amount:</th>
                            <th><?= number_format($totalAmt, 2) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <?php if($receipt['remarks']): ?>
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="alert alert-light border">
                        <strong>Remarks:</strong><br>
                        <?= nl2br(esc($receipt['remarks'])) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
