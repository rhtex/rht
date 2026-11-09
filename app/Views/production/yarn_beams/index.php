<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Beam Tracker<?= $this->endSection() ?>

<?php
    $perms     = session('permissions') ?? [];
    $canCreate = in_array('yarn_beam.create', $perms) || in_array('weaver.create', $perms);
    $s = $stats ?? ['total' => 0, 'empty' => 0, 'loaded' => 0, 'inHouse' => 0, 'atJobWork' => 0, 'atWeaving' => 0, 'damaged' => 0];
    $pct = function ($n) use ($s) { return $s['total'] > 0 ? round($n / $s['total'] * 100) : 0; };
    $hasFilter = !empty($selectedStatus) || !empty($selectedLocation);
?>

<?= $this->section('header') ?>
<div class="bt-hero mb-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="bt-hero-icon"><i class="fas fa-ring"></i></div>
            <div>
                <h1 class="mb-0 fw-bold text-white">Beam Tracker</h1>
                <div class="text-white-50 small">Track every warp beam — its load status, location and custodian at a glance.</div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('production/yarn-beams') ?>" class="btn btn-light btn-sm bt-btn-glass" title="Refresh">
                <i class="fas fa-sync-alt"></i>
            </a>
            <?php if ($canCreate): ?>
                <button type="button" class="btn btn-warning fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#addBeamModal">
                    <i class="fas fa-plus-circle me-1"></i> Register New Beam
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .bt-hero {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #db2777 100%);
        border-radius: 16px;
        padding: 22px 26px;
        box-shadow: 0 10px 30px rgba(124, 58, 237, .25);
    }
    .bt-hero-icon {
        width: 56px; height: 56px; border-radius: 14px;
        background: rgba(255,255,255,.18); color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 26px;
        backdrop-filter: blur(4px);
    }
    .bt-btn-glass { background: rgba(255,255,255,.2) !important; border: 1px solid rgba(255,255,255,.35) !important; color: #fff !important; }

    /* Stat cards */
    .bt-stat {
        position: relative; overflow: hidden; border: 0; border-radius: 14px; color: #fff;
        padding: 18px 18px 14px; height: 100%; cursor: pointer; text-decoration: none; display: block;
        transition: transform .18s ease, box-shadow .18s ease;
        box-shadow: 0 6px 18px rgba(0,0,0,.08);
    }
    .bt-stat:hover { transform: translateY(-3px); box-shadow: 0 12px 26px rgba(0,0,0,.15); color: #fff; }
    .bt-stat.active { outline: 3px solid rgba(255,255,255,.9); outline-offset: -6px; }
    .bt-stat .bt-stat-icon { position: absolute; right: 14px; top: 12px; font-size: 42px; opacity: .22; }
    .bt-stat .bt-stat-num { font-size: 2rem; font-weight: 800; line-height: 1; }
    .bt-stat .bt-stat-label { font-size: .8rem; text-transform: uppercase; letter-spacing: .5px; font-weight: 600; opacity: .95; }
    .bt-stat .progress { height: 5px; background: rgba(255,255,255,.25); margin-top: 10px; }
    .bt-stat .progress-bar { background: #fff; }
    .bt-g-blue   { background: linear-gradient(135deg, #3b82f6, #06b6d4); }
    .bt-g-slate  { background: linear-gradient(135deg, #64748b, #94a3b8); }
    .bt-g-green  { background: linear-gradient(135deg, #10b981, #84cc16); }
    .bt-g-red    { background: linear-gradient(135deg, #ef4444, #f97316); }
    .bt-g-amber  { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .bt-g-purple { background: linear-gradient(135deg, #8b5cf6, #d946ef); }
    .bt-g-teal   { background: linear-gradient(135deg, #14b8a6, #22d3ee); }

    /* Main card */
    .bt-card { border: 0; border-radius: 16px; box-shadow: 0 6px 22px rgba(15, 23, 42, .07); overflow: hidden; }
    .bt-card .card-header { background: #fff; border-bottom: 1px solid #eef0f5; padding: 14px 18px; }
    .bt-toolbar { background: linear-gradient(180deg, #f8fafc, #fff); border-bottom: 1px solid #eef0f5; padding: 14px 18px; }
    .bt-search { position: relative; }
    .bt-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .bt-search input { padding-left: 34px; border-radius: 999px; }
    .bt-chip {
        border-radius: 999px; font-size: .78rem; font-weight: 600; padding: 5px 12px;
        border: 1px solid #e2e8f0; background: #fff; color: #475569; text-decoration: none; white-space: nowrap;
        transition: all .15s;
    }
    .bt-chip:hover { border-color: #7c3aed; color: #7c3aed; }
    .bt-chip.on { background: #7c3aed; border-color: #7c3aed; color: #fff; }
    .bt-view-toggle .btn { border-radius: 10px !important; }

    /* Table */
    #beamsTable thead th {
        background: #f1f5f9; color: #334155; font-size: .72rem; text-transform: uppercase;
        letter-spacing: .5px; border-bottom: 2px solid #e2e8f0; white-space: nowrap;
    }
    #beamsTable tbody tr { transition: background .12s; }
    #beamsTable tbody tr:hover { background: #faf5ff; }
    #beamsTable tbody tr.bt-damaged { background: #fff5f5; }
    .bt-beam-no { display: inline-flex; align-items: center; gap: 10px; font-weight: 800; font-size: 15px; color: #1e293b; }
    .bt-beam-dot { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; }
    .bt-pill { border-radius: 999px; padding: 5px 11px; font-weight: 600; font-size: .74rem; display: inline-flex; align-items: center; gap: 5px; }
    .bt-pill-green  { background: #dcfce7; color: #15803d; }
    .bt-pill-slate  { background: #f1f5f9; color: #475569; }
    .bt-pill-red    { background: #fee2e2; color: #b91c1c; }
    .bt-pill-blue   { background: #dbeafe; color: #1d4ed8; }
    .bt-pill-amber  { background: #fef3c7; color: #b45309; }
    .bt-pill-purple { background: #ede9fe; color: #6d28d9; }
    .bt-holder { display: inline-flex; align-items: center; gap: 8px; }
    .bt-avatar { width: 28px; height: 28px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-weight: 700; font-size: 12px; display: flex; align-items: center; justify-content: center; }

    /* Grid view */
    .bt-tile { border: 0; border-radius: 14px; box-shadow: 0 4px 14px rgba(15,23,42,.07); transition: transform .15s, box-shadow .15s; height: 100%; overflow: hidden; }
    .bt-tile:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(15,23,42,.12); }
    .bt-tile-top { height: 6px; }
    .bt-empty-state { padding: 50px 20px; text-align: center; color: #94a3b8; }
    .bt-empty-state i { font-size: 54px; margin-bottom: 12px; color: #c4b5fd; }

    .bt-modal .modal-content { border: 0; border-radius: 18px; overflow: hidden; }
    .bt-modal .modal-header { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; border: 0; }
    .bt-modal .btn-close { filter: invert(1); }
</style>

<!-- Flash messages -->
<?php if (session()->has('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= session('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif ?>
<?php if (session()->has('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= session('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif ?>
<?php if (session()->has('errors')) : ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php foreach ((array) session('errors') as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif ?>

<!-- Stat cards (click to filter) -->
<div class="row g-3 mb-3">
    <div class="col-xl col-md-4 col-6">
        <a href="<?= site_url('production/yarn-beams') ?>" class="bt-stat bt-g-blue <?= !$hasFilter ? 'active' : '' ?>">
            <i class="fas fa-ring bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $s['total'] ?></div>
            <div class="bt-stat-label">Total Beams</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl col-md-4 col-6">
        <a href="<?= site_url('production/yarn-beams?status=Loaded') ?>" class="bt-stat bt-g-green <?= $selectedStatus === 'Loaded' ? 'active' : '' ?>">
            <i class="fas fa-layer-group bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $s['loaded'] ?></div>
            <div class="bt-stat-label">Loaded (Ready)</div>
            <div class="progress"><div class="progress-bar" style="width:<?= $pct($s['loaded']) ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl col-md-4 col-6">
        <a href="<?= site_url('production/yarn-beams?status=Empty') ?>" class="bt-stat bt-g-slate <?= $selectedStatus === 'Empty' ? 'active' : '' ?>">
            <i class="fas fa-circle-notch bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $s['empty'] ?></div>
            <div class="bt-stat-label">Empty Beams</div>
            <div class="progress"><div class="progress-bar" style="width:<?= $pct($s['empty']) ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl col-md-4 col-6">
        <a href="<?= site_url('production/yarn-beams?location=In-House') ?>" class="bt-stat bt-g-teal <?= $selectedLocation === 'In-House' ? 'active' : '' ?>">
            <i class="fas fa-warehouse bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $s['inHouse'] ?></div>
            <div class="bt-stat-label">In-House</div>
            <div class="progress"><div class="progress-bar" style="width:<?= $pct($s['inHouse']) ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl col-md-4 col-6">
        <a href="<?= site_url('production/yarn-beams?location=At+Job+Work') ?>" class="bt-stat bt-g-amber <?= $selectedLocation === 'At Job Work' ? 'active' : '' ?>">
            <i class="fas fa-industry bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $s['atJobWork'] ?></div>
            <div class="bt-stat-label">At Job Work</div>
            <div class="progress"><div class="progress-bar" style="width:<?= $pct($s['atJobWork']) ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl col-md-4 col-6">
        <a href="<?= site_url('production/yarn-beams?location=At+Weaving') ?>" class="bt-stat bt-g-purple <?= $selectedLocation === 'At Weaving' ? 'active' : '' ?>">
            <i class="fas fa-cut bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $s['atWeaving'] ?></div>
            <div class="bt-stat-label">At Weaving</div>
            <div class="progress"><div class="progress-bar" style="width:<?= $pct($s['atWeaving']) ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl col-md-4 col-6">
        <div class="bt-stat bt-g-red" style="cursor:default;">
            <i class="fas fa-exclamation-triangle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $s['damaged'] ?></div>
            <div class="bt-stat-label">Damaged</div>
            <div class="progress"><div class="progress-bar" style="width:<?= $pct($s['damaged']) ?>%"></div></div>
        </div>
    </div>
</div>

<!-- Main card -->
<div class="card bt-card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h3 class="card-title fw-bold mb-0">
            <i class="fas fa-boxes text-primary me-2"></i>Beam Inventory &amp; Locations
            <span class="badge rounded-pill bg-primary-subtle text-primary ms-2" id="btVisibleCount"><?= count($beams) ?></span>
        </h3>
        <div class="btn-group bt-view-toggle" role="group">
            <button type="button" class="btn btn-sm btn-primary" id="btViewTable" title="Table view"><i class="fas fa-list"></i></button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btViewGrid" title="Card view"><i class="fas fa-th-large"></i></button>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="bt-toolbar">
        <form method="get" action="<?= site_url('production/yarn-beams') ?>" class="row g-2 align-items-center">
            <div class="col-lg-4 col-md-12">
                <div class="bt-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="btSearch" class="form-control form-control-sm" placeholder="Search beam no., holder, remarks...">
                </div>
            </div>
            <div class="col-lg-8 col-md-12">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-lg-end">
                    <span class="small text-muted fw-semibold me-1"><i class="fas fa-filter me-1"></i>Status:</span>
                    <?php foreach (['' => 'All', 'Loaded' => 'Loaded', 'Empty' => 'Empty'] as $val => $lbl):
                        $q = array_filter(['status' => $val, 'location' => $selectedLocation]); ?>
                        <a href="<?= site_url('production/yarn-beams') . ($q ? '?' . http_build_query($q) : '') ?>"
                           class="bt-chip <?= (string) $selectedStatus === (string) $val ? 'on' : '' ?>"><?= $lbl ?></a>
                    <?php endforeach; ?>
                    <span class="small text-muted fw-semibold ms-2 me-1"><i class="fas fa-map-marker-alt me-1"></i>Location:</span>
                    <?php foreach (['' => 'All', 'In-House' => 'In-House', 'At Job Work' => 'Job Work', 'At Weaving' => 'Weaving'] as $val => $lbl):
                        $q = array_filter(['status' => $selectedStatus, 'location' => $val]); ?>
                        <a href="<?= site_url('production/yarn-beams') . ($q ? '?' . http_build_query($q) : '') ?>"
                           class="bt-chip <?= (string) $selectedLocation === (string) $val ? 'on' : '' ?>"><?= $lbl ?></a>
                    <?php endforeach; ?>
                    <?php if ($hasFilter): ?>
                        <a href="<?= site_url('production/yarn-beams') ?>" class="btn btn-sm btn-outline-danger rounded-pill ms-1">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <?php if (empty($beams)): ?>
            <div class="bt-empty-state">
                <i class="fas fa-ring d-block"></i>
                <h5 class="fw-bold text-secondary">No beams found</h5>
                <p class="mb-3"><?= $hasFilter ? 'No beams match the selected filters.' : 'Start by registering your first warp beam.' ?></p>
                <?php if ($hasFilter): ?>
                    <a href="<?= site_url('production/yarn-beams') ?>" class="btn btn-outline-primary btn-sm">Clear filters</a>
                <?php elseif ($canCreate): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBeamModal">
                        <i class="fas fa-plus me-1"></i>Register New Beam
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>

        <!-- TABLE VIEW -->
        <div id="btTableWrap" class="table-responsive">
            <table class="table align-middle mb-0" id="beamsTable">
                <thead>
                    <tr>
                        <th class="ps-3">Beam</th>
                        <th>Condition</th>
                        <th>Load Status</th>
                        <th>Location</th>
                        <th>Holder / Custodian</th>
                        <th>Remarks</th>
                        <th>Last Updated</th>
                        <th class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($beams as $beam):
                    $isLoaded  = $beam['status'] === 'Loaded';
                    $isDamaged = $beam['condition_status'] === 'Damaged';
                    $loc       = $beam['location'];
                    $dotClass  = $isDamaged ? 'bt-g-red' : ($isLoaded ? 'bt-g-green' : 'bt-g-slate');
                    $holder    = $beam['current_holder'] ?: 'In-House';
                    $searchKey = strtolower($beam['beam_number'] . ' ' . $holder . ' ' . ($beam['remarks'] ?? '') . ' ' . $loc . ' ' . $beam['status']);
                ?>
                    <tr class="bt-row <?= $isDamaged ? 'bt-damaged' : '' ?>" data-search="<?= esc($searchKey) ?>">
                        <td class="ps-3">
                            <span class="bt-beam-no">
                                <span class="bt-beam-dot <?= $dotClass ?>"><i class="fas fa-ring"></i></span>
                                <?= esc($beam['beam_number']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!$isDamaged): ?>
                                <span class="bt-pill bt-pill-green"><i class="fas fa-shield-alt"></i> Active</span>
                            <?php else: ?>
                                <span class="bt-pill bt-pill-red"><i class="fas fa-heart-broken"></i> Damaged</span>
                                <?php if (!empty($beam['damaged_date'])): ?>
                                    <div class="small text-danger mt-1">since <?= date('d M Y', strtotime($beam['damaged_date'])) ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isLoaded): ?>
                                <span class="bt-pill bt-pill-green"><i class="fas fa-layer-group"></i> Loaded</span>
                            <?php else: ?>
                                <span class="bt-pill bt-pill-slate"><i class="far fa-circle"></i> Empty</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($loc === 'In-House'): ?>
                                <span class="bt-pill bt-pill-blue"><i class="fas fa-warehouse"></i> In-House</span>
                            <?php elseif ($loc === 'At Job Work'): ?>
                                <span class="bt-pill bt-pill-amber"><i class="fas fa-industry"></i> At Job Work</span>
                            <?php else: ?>
                                <span class="bt-pill bt-pill-purple"><i class="fas fa-cut"></i> <?= esc($loc ?: 'At Weaving') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="bt-holder">
                                <span class="bt-avatar"><?= esc(strtoupper(mb_substr($holder, 0, 1))) ?></span>
                                <span class="fw-semibold"><?= esc($holder) ?></span>
                            </span>
                        </td>
                        <td><span class="text-muted small"><?= esc($beam['remarks'] ?: '—') ?></span></td>
                        <td>
                            <div class="small fw-semibold"><?= date('d M Y', strtotime($beam['updated_at'])) ?></div>
                            <div class="small text-muted"><?= date('h:i A', strtotime($beam['updated_at'])) ?></div>
                        </td>
                        <td class="text-center pe-3 text-nowrap">
                            <a href="<?= site_url('production/yarn-beams/ledger/' . $beam['id']) ?>" class="btn btn-sm btn-primary rounded-pill px-3" title="View Ledger History">
                                <i class="fas fa-history me-1"></i>Ledger
                            </a>
                            <?php if ($canCreate): ?>
                                <form action="<?= site_url('production/yarn-beams/update-condition/' . $beam['id']) ?>" method="post" class="d-inline"
                                      onsubmit="return confirm('Mark beam <?= esc($beam['beam_number'], 'js') ?> as <?= $isDamaged ? 'Active' : 'Damaged' ?>?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="condition_status" value="<?= $isDamaged ? 'Active' : 'Damaged' ?>">
                                    <?php if ($isDamaged): ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill" title="Mark as Active"><i class="fas fa-tools"></i></button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="Mark as Damaged"><i class="fas fa-exclamation-triangle"></i></button>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- GRID VIEW -->
        <div id="btGridWrap" class="p-3 d-none">
            <div class="row g-3">
                <?php foreach ($beams as $beam):
                    $isLoaded  = $beam['status'] === 'Loaded';
                    $isDamaged = $beam['condition_status'] === 'Damaged';
                    $loc       = $beam['location'];
                    $topClass  = $isDamaged ? 'bt-g-red' : ($isLoaded ? 'bt-g-green' : 'bt-g-slate');
                    $holder    = $beam['current_holder'] ?: 'In-House';
                    $searchKey = strtolower($beam['beam_number'] . ' ' . $holder . ' ' . ($beam['remarks'] ?? '') . ' ' . $loc . ' ' . $beam['status']);
                    $locPill   = $loc === 'In-House' ? 'bt-pill-blue' : ($loc === 'At Job Work' ? 'bt-pill-amber' : 'bt-pill-purple');
                    $locIcon   = $loc === 'In-House' ? 'fa-warehouse' : ($loc === 'At Job Work' ? 'fa-industry' : 'fa-cut');
                ?>
                    <div class="col-xl-3 col-lg-4 col-sm-6 bt-row" data-search="<?= esc($searchKey) ?>">
                        <div class="card bt-tile">
                            <div class="bt-tile-top <?= $topClass ?>"></div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="bt-beam-no">
                                        <span class="bt-beam-dot <?= $topClass ?>"><i class="fas fa-ring"></i></span>
                                        <?= esc($beam['beam_number']) ?>
                                    </span>
                                    <?php if ($isDamaged): ?>
                                        <span class="bt-pill bt-pill-red"><i class="fas fa-heart-broken"></i></span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <span class="bt-pill <?= $isLoaded ? 'bt-pill-green' : 'bt-pill-slate' ?>">
                                        <i class="<?= $isLoaded ? 'fas fa-layer-group' : 'far fa-circle' ?>"></i> <?= $isLoaded ? 'Loaded' : 'Empty' ?>
                                    </span>
                                    <span class="bt-pill <?= $locPill ?>"><i class="fas <?= $locIcon ?>"></i> <?= esc($loc) ?></span>
                                </div>
                                <div class="small text-muted mb-1"><i class="fas fa-user-tie me-1"></i><?= esc($holder) ?></div>
                                <div class="small text-muted mb-3 text-truncate" title="<?= esc($beam['remarks'] ?? '') ?>"><i class="fas fa-sticky-note me-1"></i><?= esc($beam['remarks'] ?: '—') ?></div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted"><i class="far fa-clock me-1"></i><?= date('d M Y', strtotime($beam['updated_at'])) ?></span>
                                    <a href="<?= site_url('production/yarn-beams/ledger/' . $beam['id']) ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                                        <i class="fas fa-history me-1"></i>Ledger
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="btNoMatch" class="bt-empty-state d-none">
            <i class="fas fa-search d-block"></i>
            <h6 class="fw-bold text-secondary">No beams match your search.</h6>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Beam Modal -->
<div class="modal fade bt-modal" id="addBeamModal" tabindex="-1" aria-labelledby="addBeamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= site_url('production/yarn-beams/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addBeamModalLabel"><i class="fas fa-ring me-2"></i>Register New Beam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Beam Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary-subtle text-primary"><i class="fas fa-hashtag"></i></span>
                            <input type="text" name="beam_number" class="form-control form-control-lg" placeholder="e.g. BM-111" required
                                   value="<?= esc(old('beam_number') ?? '') ?>" style="text-transform: uppercase;" autocomplete="off">
                        </div>
                        <small class="text-muted">Must be a unique identifier. New beams start as <b>Empty</b> and <b>In-House</b>.</small>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-bold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Aluminum Beam, 1000mm flange"><?= esc(old('remarks') ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal"><?= lang('App.cancel') ?></button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-1"></i>Save Beam</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    var search   = document.getElementById('btSearch');
    var tableW   = document.getElementById('btTableWrap');
    var gridW    = document.getElementById('btGridWrap');
    var btnTable = document.getElementById('btViewTable');
    var btnGrid  = document.getElementById('btViewGrid');
    var counter  = document.getElementById('btVisibleCount');
    var noMatch  = document.getElementById('btNoMatch');
    if (!tableW) return;

    function setView(v) {
        var grid = v === 'grid';
        gridW.classList.toggle('d-none', !grid);
        tableW.classList.toggle('d-none', grid);
        btnGrid.className  = 'btn btn-sm ' + (grid ? 'btn-primary' : 'btn-outline-primary');
        btnTable.className = 'btn btn-sm ' + (grid ? 'btn-outline-primary' : 'btn-primary');
        try { localStorage.setItem('beamTrackerView', v); } catch (e) {}
        applySearch();
    }

    function applySearch() {
        var q = (search.value || '').toLowerCase().trim();
        var wrap = gridW.classList.contains('d-none') ? tableW : gridW;
        var rows = wrap.querySelectorAll('.bt-row');
        var visible = 0;
        rows.forEach(function (r) {
            var show = !q || r.getAttribute('data-search').indexOf(q) !== -1;
            r.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        counter.textContent = visible;
        noMatch.classList.toggle('d-none', visible !== 0);
    }

    btnTable.addEventListener('click', function () { setView('table'); });
    btnGrid.addEventListener('click', function () { setView('grid'); });
    search.addEventListener('input', applySearch);

    var saved = null;
    try { saved = localStorage.getItem('beamTrackerView'); } catch (e) {}
    setView(saved === 'grid' ? 'grid' : 'table');
})();

<?php if (session()->has('errors')): ?>
document.addEventListener('DOMContentLoaded', function () {
    var m = document.getElementById('addBeamModal');
    if (m && window.bootstrap) new bootstrap.Modal(m).show();
});
<?php endif; ?>
</script>
<?= $this->endSection() ?>
