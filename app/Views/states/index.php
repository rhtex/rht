<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>States<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalStates = count($states ?? []);
$activeStates = 0;
$inactiveStates = 0;
$withGstCode = 0;
if (!empty($states)) {
    foreach ($states as $s) {
        if (($s['status'] ?? '') === 'active') $activeStates++;
        else $inactiveStates++;
        if (!empty($s['gst_state_code'])) $withGstCode++;
    }
}
?>

<!-- Hero Header -->
<div class="bt-hero mb-3">
    <div class="row align-items-center g-3">
        <div class="col-md-7">
            <h2 class="h4 mb-1 text-white fw-bold"><i class="fas fa-map-marked-alt me-2"></i> States & Territories</h2>
            <p class="mb-0 text-white-50 small">Configure administrative provinces, state codes, and 2-digit GST state tax identification codes.</p>
        </div>
        <div class="col-md-5 text-md-end">
            <a href="<?= site_url('countries') ?>" class="btn btn-light btn-sm fw-semibold shadow-sm me-2">
                <i class="fas fa-globe-americas me-1 text-primary"></i> Countries
            </a>
            <a href="<?= site_url('states/create') ?>" class="btn btn-warning btn-sm fw-semibold shadow-sm">
                <i class="fas fa-plus me-1"></i> Add State
            </a>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="bt-stat bt-g-blue">
            <div class="bt-stat-title">Total States</div>
            <div class="bt-stat-val"><?= number_format($totalStates) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-map me-1"></i> Listed Jurisdictions</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bt-stat bt-g-green">
            <div class="bt-stat-title">Active States</div>
            <div class="bt-stat-val"><?= number_format($activeStates) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-check-circle me-1"></i> Operational</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bt-stat bt-g-rose">
            <div class="bt-stat-title">Inactive States</div>
            <div class="bt-stat-val"><?= number_format($inactiveStates) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-ban me-1"></i> Disabled</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bt-stat bt-g-purple">
            <div class="bt-stat-title">GST Codes Assigned</div>
            <div class="bt-stat-val"><?= number_format($withGstCode) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-file-invoice me-1"></i> GSTIN 2-digit prefixes</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2 text-primary"></i> All States & Provinces</h6>
        <span class="badge text-bg-primary"><?= $totalStates ?> Records</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($states)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-map-marked-alt fa-3x mb-3 text-secondary opacity-50"></i>
                <p class="mb-2 fw-semibold">No states registered yet.</p>
                <a href="<?= site_url('states/create') ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add First State
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 list-ui-table">
                    <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>State Name</th>
                            <th>Country</th>
                            <th>State Code</th>
                            <th>GST State Code</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($states as $index => $state): ?>
                        <tr>
                            <td class="text-muted fw-bold small"><?= $index + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-2 me-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fas fa-map-pin"></i>
                                    </div>
                                    <div>
                                        <strong class="text-dark d-block"><?= esc($state['name']) ?></strong>
                                        <small class="text-muted">ID: #<?= esc($state['id']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge text-bg-light border text-dark">
                                    <i class="fas fa-globe me-1 text-primary"></i><?= esc($state['country_name'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($state['state_code'])): ?>
                                    <span class="badge text-bg-secondary font-monospace"><?= esc($state['state_code']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($state['gst_state_code'])): ?>
                                    <span class="badge text-bg-primary font-monospace px-2 py-1">
                                        <?= str_pad(esc($state['gst_state_code']), 2, '0', STR_PAD_LEFT) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (($state['status'] ?? '') === 'active'): ?>
                                    <span class="badge text-bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-danger"><i class="fas fa-times-circle me-1"></i>Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('states/edit/'.$state['id']) ?>" class="btn btn-outline-primary" title="Edit State">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="<?= site_url('states/delete/'.$state['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this state?')" title="Delete State">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
