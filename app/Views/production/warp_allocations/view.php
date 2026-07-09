<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= esc($title) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/warp-allocations') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Allocation #<?= esc($allocation['allocation_number']) ?> Details</h3>
            <div class="card-tools">
                <?php if($allocation['reference_document_type'] == 'DC'): ?>
                    <button class="btn btn-sm btn-info" onclick="window.print()"><i class="fas fa-print"></i> Print DC</button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Allocation Number</th>
                            <td><?= esc($allocation['allocation_number']) ?></td>
                        </tr>
                        <tr>
                            <th>Allocation Date</th>
                            <td><?= esc($allocation['allocation_date']) ?></td>
                        </tr>
                        <tr>
                            <th>Expected Return Date</th>
                            <td><?= esc($allocation['expected_return_date']) ?: 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-primary"><?= esc($allocation['status']) ?></span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Weaver</th>
                            <td><?= esc($allocation['weaver_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Loom</th>
                            <td><?= esc($allocation['loom_number']) ?></td>
                        </tr>
                        <tr>
                            <th>Contract Type</th>
                            <td><span class="badge bg-info"><?= esc($allocation['contract_type']) ?></span></td>
                        </tr>
                        <tr>
                            <th>Reference Document</th>
                            <td>
                                <?php if($allocation['reference_document_type'] == 'Sales Invoice'): ?>
                                    <a href="<?= site_url('bills/view/' . $allocation['reference_document_id']) ?>" target="_blank" class="btn btn-sm btn-warning">View Sales Invoice</a>
                                <?php else: ?>
                                    DC (Self)
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <h5 class="mt-4 mb-3">Warp Allocation</h5>
            <?php if(!empty($allocation['warp_beam_id'])): ?>
                <table class="table table-bordered table-sm w-50">
                    <tr>
                        <th width="40%">Warp Beam Number</th>
                        <td><?= esc($allocation['beam_number']) ?></td>
                    </tr>
                </table>
            <?php else: ?>
                <div class="alert alert-light">No Warp Beam Allocated.</div>
            <?php endif; ?>

            <h5 class="mt-4 mb-3">Weft Allocation</h5>
            <?php if(!empty($wefts) && count($wefts) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Yarn</th>
                                <th>Batch / Lot</th>
                                <th>Issued Weight (Kg)</th>
                                <th>Rate/Kg</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalWeftAmt = 0;
                            foreach($wefts as $weft): 
                                $amt = $weft['issued_weight'] * $weft['rate'];
                                $totalWeftAmt += $amt;
                            ?>
                                <tr>
                                    <td><?= esc($weft['yarn_name']) ?></td>
                                    <td><?= esc($weft['batch_number']) ?></td>
                                    <td><?= number_format($weft['issued_weight'], 3) ?></td>
                                    <td><?= number_format($weft['rate'], 2) ?></td>
                                    <td><?= number_format($amt, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <?php if($allocation['contract_type'] == 'Sale & Buy Back'): ?>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Total Weft Amount:</th>
                                <th><?= number_format($totalWeftAmt, 2) ?></th>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-light">No Weft Yarns Allocated.</div>
            <?php endif; ?>
            
            <?php if($allocation['remarks']): ?>
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="alert alert-light border">
                        <strong>Remarks:</strong><br>
                        <?= nl2br(esc($allocation['remarks'])) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
