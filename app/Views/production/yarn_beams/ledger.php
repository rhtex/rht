<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Beam Ledger - <?= esc($beam['beam_number']) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><i class="fas fa-history text-info me-2"></i> Beam Ledger History</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-beams') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Beams</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <!-- Beam Details Card -->
    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Beam Specifications</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="40%">Beam Number:</th>
                        <td><strong class="text-info" style="font-size: 18px;"><?= esc($beam['beam_number']) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <?php if($beam['status'] === 'Loaded'): ?>
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Loaded (Yarn Wound)</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><i class="fas fa-circle-notch me-1"></i> Empty</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Location:</th>
                        <td>
                            <?php if($beam['location'] === 'In-House'): ?>
                                <span class="badge bg-info"><i class="fas fa-warehouse me-1"></i> In-House</span>
                            <?php elseif($beam['location'] === 'At Job Work'): ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-industry me-1"></i> At Job Work</span>
                            <?php else: ?>
                                <span class="badge bg-indigo"><i class="fas fa-cut me-1"></i> At Weaving</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Custodian:</th>
                        <td><?= esc($beam['current_holder'] ?: 'In-House') ?></td>
                    </tr>
                    <tr>
                        <th>Remarks:</th>
                        <td><span class="text-muted small"><?= esc($beam['remarks'] ?: '-') ?></span></td>
                    </tr>
                </table>

                <?php if(in_array('weaver.create', session('permissions') ?? [])): ?>
                    <hr>
                    <div class="text-center">
                        <?php if($beam['condition_status'] === 'Active'): ?>
                            <form action="<?= site_url('production/yarn-beams/update-condition/'.$beam['id']) ?>" method="post" onsubmit="return confirm('Are you sure you want to mark this beam as DAMAGED?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="condition_status" value="Damaged">
                                <button type="submit" class="btn btn-danger btn-sm w-100"><i class="fas fa-exclamation-triangle me-1"></i> Mark as Damaged</button>
                            </form>
                        <?php else: ?>
                            <form action="<?= site_url('production/yarn-beams/update-condition/'.$beam['id']) ?>" method="post" onsubmit="return confirm('Are you sure you want to restore this beam to ACTIVE?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="condition_status" value="Active">
                                <button type="submit" class="btn btn-success btn-sm w-100"><i class="fas fa-check me-1"></i> Mark as Active</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Ledger Timeline Card -->
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> Movement Ledger Timeline</h3>
            </div>
            <div class="card-body">
                <?php if(!empty($ledger)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="bg-light text-sm">
                                <tr>
                                    <th>Date</th>
                                    <th>Activity / Type</th>
                                    <th>Movement Route</th>
                                    <th>Status Transition</th>
                                    <th>Yarn Details</th>
                                    <th>Ref Document</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ledger as $entry): ?>
                                    <tr class="text-sm">
                                        <td>
                                            <strong><?= date('d-M-Y', strtotime($entry['transaction_date'])) ?></strong>
                                        </td>
                                        <td>
                                            <?php if($entry['transaction_type'] === 'Issue_Warping_Sizing'): ?>
                                                <span class="text-warning fw-bold"><i class="fas fa-arrow-up me-1"></i> Issued for Sizing</span>
                                            <?php elseif($entry['transaction_type'] === 'Receipt_Warping_Sizing'): ?>
                                                <span class="text-success fw-bold"><i class="fas fa-arrow-down me-1"></i> Received Loaded</span>
                                            <?php elseif($entry['transaction_type'] === 'Issue_Weaving'): ?>
                                                <span class="text-indigo fw-bold"><i class="fas fa-sign-out-alt me-1"></i> Issued for Weaving</span>
                                            <?php elseif($entry['transaction_type'] === 'Receipt_Weaving'): ?>
                                                <span class="text-secondary fw-bold"><i class="fas fa-sign-in-alt me-1"></i> Empty Returned</span>
                                            <?php else: ?>
                                                <span class="text-muted fw-bold"><i class="fas fa-adjust me-1"></i> Manual Adjust</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark"><?= esc($entry['from_location']) ?></span> 
                                            <i class="fas fa-long-arrow-alt-right mx-1 text-muted"></i> 
                                            <span class="badge bg-light text-dark"><?= esc($entry['to_location']) ?></span>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?= esc($entry['status_from']) ?></span>
                                            <i class="fas fa-caret-right mx-1 text-muted"></i>
                                            <strong class="<?= $entry['status_to'] === 'Loaded' ? 'text-success' : 'text-secondary' ?>"><?= esc($entry['status_to']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="small fw-bold"><?= esc($entry['yarn_details'] ?: '-') ?></span>
                                        </td>
                                        <td>
                                            <?php if($entry['reference_id']): ?>
                                                <?php if($entry['transaction_type'] === 'Issue_Warping_Sizing'): ?>
                                                    <a href="<?= site_url('production/yarn-warping-sizing/view/' . $entry['reference_id']) ?>" class="btn btn-xs btn-outline-info">
                                                        <i class="fas fa-file-alt"></i> View DC (ID: <?= $entry['reference_id'] ?>)
                                                    </a>
                                                <?php elseif($entry['transaction_type'] === 'Receipt_Warping_Sizing'): ?>
                                                    <a href="<?= site_url('production/yarn-warping-sizing/view/' . $entry['reference_id']) ?>" class="btn btn-xs btn-outline-success">
                                                        <i class="fas fa-file-invoice"></i> View Receipt
                                                    </a>
                                                <?php elseif($entry['transaction_type'] === 'Issue_Weaving' || $entry['transaction_type'] === 'Receipt_Weaving'): ?>
                                                    <a href="<?= site_url('production/yarn-weaving/view/' . $entry['reference_id']) ?>" class="btn btn-xs btn-outline-info">
                                                        <i class="fas fa-file-alt"></i> View Weaving
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?= site_url('production/yarn-warping-sizing/view/' . $entry['reference_id']) ?>" class="btn btn-xs btn-outline-info">
                                                        <i class="fas fa-file-alt"></i> View DC (ID: <?= $entry['reference_id'] ?>)
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light text-center py-4">
                        <i class="fas fa-history fa-2x text-muted mb-2"></i>
                        <p class="mb-0 text-muted">No transactions recorded for this beam yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
