<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Countries<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalCountries = count($countries ?? []);
$withIso = 0;
$withPhone = 0;
if (!empty($countries)) {
    foreach ($countries as $c) {
        if (!empty($c['iso_code_2']) || !empty($c['iso_code_3'])) $withIso++;
        if (!empty($c['phone_code'])) $withPhone++;
    }
}
?>

<!-- Hero Header -->
<div class="bt-hero mb-3">
    <div class="row align-items-center g-3">
        <div class="col-md-7">
            <h2 class="h4 mb-1 text-white fw-bold"><i class="fas fa-globe-americas me-2"></i> Countries Master</h2>
            <p class="mb-0 text-white-50 small">Manage international territories, regional ISO codes, and country dialing prefixes.</p>
        </div>
        <div class="col-md-5 text-md-end">
            <a href="<?= site_url('states') ?>" class="btn btn-light btn-sm fw-semibold shadow-sm me-2">
                <i class="fas fa-map-marker-alt me-1 text-primary"></i> View States
            </a>
            <a href="<?= site_url('countries/create') ?>" class="btn btn-warning btn-sm fw-semibold shadow-sm">
                <i class="fas fa-plus me-1"></i> Add Country
            </a>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="bt-stat bt-g-blue">
            <div class="bt-stat-title">Total Countries</div>
            <div class="bt-stat-val"><?= number_format($totalCountries) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-globe me-1"></i> Registered Territories</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bt-stat bt-g-purple">
            <div class="bt-stat-title">ISO Standardized</div>
            <div class="bt-stat-val"><?= number_format($withIso) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-barcode me-1"></i> ISO 3166-1 Alpha Codes</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bt-stat bt-g-teal">
            <div class="bt-stat-title">Dialing Codes Configured</div>
            <div class="bt-stat-val"><?= number_format($withPhone) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-phone me-1"></i> International Prefixes</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2 text-primary"></i> All Countries</h6>
        <span class="badge text-bg-primary"><?= $totalCountries ?> Records</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($countries)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-globe-americas fa-3x mb-3 text-secondary opacity-50"></i>
                <p class="mb-2 fw-semibold">No countries configured yet.</p>
                <a href="<?= site_url('countries/create') ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add First Country
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 list-ui-table">
                    <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Country Name</th>
                            <th>ISO Alpha-2</th>
                            <th>ISO Alpha-3</th>
                            <th>Calling Code</th>
                            <th class="text-center" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($countries as $index => $country): ?>
                        <tr>
                            <td class="text-muted fw-bold small"><?= $index + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 me-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fas fa-flag"></i>
                                    </div>
                                    <div>
                                        <strong class="text-dark d-block"><?= esc($country['name']) ?></strong>
                                        <small class="text-muted">ID: #<?= esc($country['id']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($country['iso_code_2'])): ?>
                                    <span class="badge text-bg-secondary font-monospace"><?= esc($country['iso_code_2']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($country['iso_code_3'])): ?>
                                    <span class="badge text-bg-light border font-monospace text-dark"><?= esc($country['iso_code_3']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($country['phone_code'])): ?>
                                    <span class="badge text-bg-info font-monospace">+<?= ltrim(esc($country['phone_code']), '+') ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('countries/edit/'.$country['id']) ?>" class="btn btn-outline-primary" title="Edit Country">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="<?= site_url('countries/delete/'.$country['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this country?')" title="Delete Country">
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
