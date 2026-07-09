<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Beam Tracker<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><i class="fas fa-ring text-info me-2"></i> Beam Tracker</h1>
    </div>
    <div class="col-sm-6 text-end">
        <?php if(in_array('weaver.create', session('permissions') ?? [])): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBeamModal">
                <i class="fas fa-plus"></i> Register New Beam
            </button>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Cards -->
<div class="row">
    <?php
        $total = count($beams);
        $empty = 0;
        $loaded = 0;
        $inHouse = 0;
        $atJobWork = 0;
        $damaged = 0;
        foreach($beams as $b) {
            if ($b['status'] === 'Empty') $empty++;
            if ($b['status'] === 'Loaded') $loaded++;
            if ($b['location'] === 'In-House') $inHouse++;
            if ($b['location'] === 'At Job Work') $atJobWork++;
            if ($b['condition_status'] === 'Damaged') $damaged++;
        }
    ?>
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-info h-100 mb-0">
            <div class="inner">
                <h3><?= $total ?></h3>
                <p>Total Registered Beams</p>
            </div>
            <div class="icon">
                <i class="fas fa-ring"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-secondary h-100 mb-0">
            <div class="inner">
                <h3><?= $empty ?></h3>
                <p>Empty Beams</p>
            </div>
            <div class="icon">
                <i class="fas fa-circle-notch"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-success h-100 mb-0">
            <div class="inner">
                <h3><?= $loaded ?></h3>
                <p>Loaded Beams (Ready)</p>
            </div>
            <div class="icon">
                <i class="fas fa-circle text-white"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-danger h-100 mb-0">
            <div class="inner">
                <h3><?= $damaged ?></h3>
                <p>Damaged / Inactive Beams</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Table Card -->
<div class="card card-outline card-info mt-3">
    <div class="card-header">
        <h3 class="card-title">Beam Inventory & Locations</h3>
    </div>
    <div class="card-body">
        <?php if (session()->has('success')) : ?>
            <div class="alert alert-success"><?= session('success') ?></div>
        <?php endif ?>
        <?php if (session()->has('error')) : ?>
            <div class="alert alert-danger"><?= session('error') ?></div>
        <?php endif ?>

        <!-- Filter Form -->
        <form method="get" action="<?= site_url('production/yarn-beams') ?>" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Filter Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- All Statuses --</option>
                    <option value="Empty" <?= $selectedStatus === 'Empty' ? 'selected' : '' ?>>Empty</option>
                    <option value="Loaded" <?= $selectedStatus === 'Loaded' ? 'selected' : '' ?>>Loaded</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Filter Location</label>
                <select name="location" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- All Locations --</option>
                    <option value="In-House" <?= $selectedLocation === 'In-House' ? 'selected' : '' ?>>In-House</option>
                    <option value="At Job Work" <?= $selectedLocation === 'At Job Work' ? 'selected' : '' ?>>At Job Work</option>
                    <option value="At Weaving" <?= $selectedLocation === 'At Weaving' ? 'selected' : '' ?>>At Weaving</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <a href="<?= site_url('production/yarn-beams') ?>" class="btn btn-sm btn-secondary me-2"><i class="fas fa-sync"></i> Reset Filters</a>
            </div>
        </form>

        <!-- Beams Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle" id="beamsTable">
                <thead>
                    <tr class="bg-light">
                        <th>Beam Number</th>
                        <th>Condition</th>
                        <th>Load Status</th>
                        <th>Current Location</th>
                        <th>Current Holder / Custodian</th>
                        <th>Remarks</th>
                        <th>Last Updated</th>
                        <th width="18%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($beams)): ?>
                        <?php foreach($beams as $beam): ?>
                            <tr>
                                <td>
                                    <strong style="font-size: 16px;"><?= esc($beam['beam_number']) ?></strong>
                                </td>
                                <td>
                                    <?php if($beam['condition_status'] === 'Active'): ?>
                                        <span class="badge bg-success-light text-success border border-success"><i class="fas fa-check-circle me-1"></i> Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-light text-danger border border-danger"><i class="fas fa-times-circle me-1"></i> Damaged</span>
                                        <?php if($beam['damaged_date']): ?>
                                            <div class="text-xs text-danger mt-1">on <?= date('d-M-Y', strtotime($beam['damaged_date'])) ?></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($beam['status'] === 'Loaded'): ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Loaded (Yarn Wound)</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fas fa-circle-notch me-1"></i> Empty</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($beam['location'] === 'In-House'): ?>
                                        <span class="badge bg-info"><i class="fas fa-warehouse me-1"></i> In-House</span>
                                    <?php elseif($beam['location'] === 'At Job Work'): ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-industry me-1"></i> At Job Work</span>
                                    <?php else: ?>
                                        <span class="badge bg-indigo"><i class="fas fa-cut me-1"></i> At Weaving</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= esc($beam['current_holder'] ?: 'In-House') ?>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= esc($beam['remarks'] ?: '-') ?></span>
                                </td>
                                <td>
                                    <?= date('d-M-Y h:i A', strtotime($beam['updated_at'])) ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= site_url('production/yarn-beams/ledger/'.$beam['id']) ?>" class="btn btn-sm btn-outline-primary" title="View Ledger History">
                                        <i class="fas fa-history"></i> View Ledger
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No beams registered in the system.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Beam Modal -->
<div class="modal fade" id="addBeamModal" tabindex="-1" aria-labelledby="addBeamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= site_url('production/yarn-beams/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="addBeamModalLabel"><i class="fas fa-ring text-info me-1"></i> Register New Beam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Beam Number <span class="text-danger">*</span></label>
                        <input type="text" name="beam_number" class="form-control" placeholder="e.g. BM-111" required style="text-transform: uppercase;">
                        <small class="text-muted">Must be a unique identifier.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Aluminum Beam, 1000mm flange"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang("App.cancel") ?></button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Beam</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
