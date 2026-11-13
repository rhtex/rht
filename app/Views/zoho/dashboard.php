<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Zoho Books Synchronization Dashboard<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-exchange-alt text-primary me-2"></i>Zoho Books Synchronization</h1>
        <small class="text-muted">Manage manual &amp; automated bidirectional CRUD sync across Sales, Purchase, Inventory, and Finance.</small>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex flex-wrap gap-2 align-items-center">
            <a href="<?= site_url('settings/integrations/zoho') ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-cog me-1"></i> Configuration
            </a>
            <a href="<?= site_url('settings/integrations/zoho/sync') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-sync-alt me-1"></i> Sync Dashboard
            </a>
            <a href="<?= site_url('settings/integrations/zoho/conflicts') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-code-branch me-1"></i> Conflicts
                <?php if (!empty($totalConflicts)): ?>
                    <span class="badge bg-danger ms-1"><?= (int) $totalConflicts ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= site_url('settings/integrations/zoho/reconciliation') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-balance-scale me-1"></i> Reconciliation
            </a>
            <a href="<?= site_url('settings/integrations/zoho/logs') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-history me-1"></i> Audit Logs
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- 7 Required Manual Synchronization Action Bar -->
<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header bg-white py-2">
        <h3 class="card-title fw-bold m-0 text-dark"><i class="fas fa-play-circle text-primary me-2"></i>Manual Synchronization Controls</h3>
    </div>
    <div class="card-body p-3">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
            <div>
                <div class="text-muted small">
                    Last Sync: <strong><?= !empty($config['last_sync_at']) ? date('d M Y, h:i:s A', strtotime($config['last_sync_at'])) : 'Never' ?></strong>
                    &bull; Organization: <strong><?= esc($config['organization_name'] ?: ($config['organization_id'] ?: 'Not Configured')) ?></strong>
                    <?php if (!empty($rateUsage)): ?>
                        &bull; Daily API Quota: <span class="badge bg-success-subtle text-success border"><?= number_format($rateUsage['daily_used']) ?> / <?= number_format($rateUsage['daily_limit']) ?> calls today</span>
                        &bull; Rate Limit: <span class="badge bg-primary-subtle text-primary border"><?= (int) $rateUsage['minute_used'] ?> / <?= (int) $rateUsage['minute_limit'] ?> req/min (&le;1.6/s)</span>
                        &bull; Concurrency: <span class="badge bg-secondary-subtle text-dark border"><?= (int) $rateUsage['active_concurrent'] ?> / <?= (int) $rateUsage['max_concurrent_connections'] ?> connections</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <!-- 1. Sync from Zoho Books -->
                <form action="<?= site_url('settings/integrations/zoho/sync-from-zoho') ?>" method="post" class="d-inline" data-live-sync="1" data-sync-title="Sync All Modules from Zoho Books">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary fw-bold shadow-sm">
                        <i class="fas fa-cloud-download-alt me-1"></i> Sync from Zoho Books
                    </button>
                </form>

                <!-- 2. Sync to Zoho Books -->
                <form action="<?= site_url('settings/integrations/zoho/sync-to-zoho') ?>" method="post" class="d-inline" data-live-sync="1" data-sync-title="Sync All Modules to Zoho Books">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success fw-bold shadow-sm">
                        <i class="fas fa-cloud-upload-alt me-1"></i> Sync to Zoho Books
                    </button>
                </form>

                <!-- 3. Full Bidirectional Sync -->
                <form action="<?= site_url('settings/integrations/zoho/full-sync') ?>" method="post" class="d-inline" data-live-sync="1" data-sync-title="Full Bidirectional Synchronization">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-indigo fw-bold shadow-sm" style="background:#4f46e5;color:#fff;">
                        <i class="fas fa-exchange-alt me-1"></i> Full Bidirectional Sync
                    </button>
                </form>

                <!-- 4. Sync Selected Module -->
                <button type="button" class="btn btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#syncModuleModal">
                    <i class="fas fa-layer-group me-1"></i> Sync Selected Module
                </button>

                <!-- 5. Sync Selected Records -->
                <button type="button" class="btn btn-outline-dark fw-bold" data-bs-toggle="modal" data-bs-target="#syncRecordsModal">
                    <i class="fas fa-check-square me-1"></i> Sync Selected Records
                </button>

                <!-- 6. Retry Failed Sync -->
                <form action="<?= site_url('settings/integrations/zoho/retry-failed') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-warning text-dark fw-bold">
                        <i class="fas fa-redo me-1"></i> Retry Failed Sync
                        <?php if ($totalFailed > 0): ?>
                            <span class="badge bg-danger ms-1"><?= (int) $totalFailed ?></span>
                        <?php endif; ?>
                    </button>
                </form>

                <!-- 7. Reconciliation -->
                <a href="<?= site_url('settings/integrations/zoho/reconciliation') ?>" class="btn btn-outline-info text-dark fw-bold">
                    <i class="fas fa-balance-scale me-1"></i> Reconciliation
                </a>
            </div>
        </div>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="bt-stat bt-g-green" style="cursor:default;">
            <i class="fas fa-check-double bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalMapped) ?></div>
            <div class="bt-stat-label">Synced &amp; Mapped</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="bt-stat bt-g-blue" style="cursor:default;">
            <i class="fas fa-hourglass-half bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalUnsynced) ?></div>
            <div class="bt-stat-label">Unsynced (Queue: <?= number_format($pendingQueue) ?>)</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="<?= site_url('settings/integrations/zoho/conflicts') ?>" class="bt-stat <?= $totalConflicts > 0 ? 'bt-g-amber' : 'bt-g-purple' ?> text-decoration-none">
            <i class="fas fa-code-branch bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalConflicts) ?></div>
            <div class="bt-stat-label">Open Conflicts</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="<?= site_url('settings/integrations/zoho/logs?status=FAILED') ?>" class="bt-stat <?= $totalFailed > 0 ? 'bt-g-red' : 'bt-g-slate' ?> text-decoration-none">
            <i class="fas fa-exclamation-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalFailed) ?></div>
            <div class="bt-stat-label">Failed Syncs</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
</div>
</div>

<!-- Module-by-Module Synchronization Status Matrix -->
<div class="card card-outline card-secondary shadow-sm mb-3">
    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
        <h3 class="card-title fw-bold m-0 text-dark"><i class="fas fa-cubes text-primary me-2"></i>Module Synchronization Matrix (13 Modules)</h3>
        <span class="text-muted small">Click any module action to sync only that module</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Module</th>
                        <th class="text-center">Local ERP Records</th>
                        <th class="text-center">Synced in Zoho</th>
                        <th class="text-center">Unsynced</th>
                        <th class="text-center">Conflicts</th>
                        <th class="text-center">Failed</th>
                        <th>Last Successful Sync</th>
                        <th class="text-end pe-4">Quick Module Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($moduleStats as $modKey => $ms): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary p-2 rounded-3">
                                    <i class="fas <?= esc($ms['icon']) ?>"></i>
                                </span>
                                <div>
                                    <div class="fw-bold text-dark"><?= esc($ms['label']) ?></div>
                                    <div class="text-muted small"><code><?= esc($modKey) ?></code></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center fw-semibold"><?= number_format($ms['local_count']) ?></td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <?= number_format($ms['synced_count']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ($ms['unsynced_count'] > 0): ?>
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1"><?= number_format($ms['unsynced_count']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">0</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($ms['conflict_count'] > 0): ?>
                                <a href="<?= site_url('settings/integrations/zoho/conflicts?module=' . urlencode($modKey)) ?>" class="badge bg-warning text-dark text-decoration-none px-2 py-1">
                                    <?= number_format($ms['conflict_count']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">0</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($ms['failed_count'] > 0): ?>
                                <a href="<?= site_url('settings/integrations/zoho/logs?status=FAILED&module=' . urlencode($modKey)) ?>" class="badge bg-danger text-white text-decoration-none px-2 py-1">
                                    <?= number_format($ms['failed_count']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">0</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($ms['last_sync_at'])): ?>
                                <span class="small text-dark"><?= date('d M Y, h:i A', strtotime($ms['last_sync_at'])) ?></span>
                            <?php else: ?>
                                <span class="text-muted small">Never synced</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <form action="<?= site_url('settings/integrations/zoho/sync-module') ?>" method="post" class="d-inline" data-live-sync="1" data-sync-title="Pull <?= esc($ms['label']) ?> from Zoho Books">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="module" value="<?= esc($modKey) ?>">
                                    <input type="hidden" name="direction" value="from_zoho">
                                    <button type="submit" class="btn btn-outline-primary" title="Pull <?= esc($ms['label']) ?> from Zoho Books">
                                        <i class="fas fa-download me-1"></i> Pull
                                    </button>
                                </form>
                                <form action="<?= site_url('settings/integrations/zoho/sync-module') ?>" method="post" class="d-inline" data-live-sync="1" data-sync-title="Push <?= esc($ms['label']) ?> to Zoho Books">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="module" value="<?= esc($modKey) ?>">
                                    <input type="hidden" name="direction" value="to_zoho">
                                    <button type="submit" class="btn btn-outline-success" title="Push <?= esc($ms['label']) ?> to Zoho Books">
                                        <i class="fas fa-upload me-1"></i> Push
                                    </button>
                                </form>
                                <form action="<?= site_url('settings/integrations/zoho/sync-module') ?>" method="post" class="d-inline" data-live-sync="1" data-sync-title="Sync <?= esc($ms['label']) ?> (Bidirectional)">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="module" value="<?= esc($modKey) ?>">
                                    <input type="hidden" name="direction" value="bidirectional">
                                    <button type="submit" class="btn btn-outline-dark" title="Full Bidirectional Sync for <?= esc($ms['label']) ?>">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Synchronization Runs & Audit Logs -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold"><i class="fas fa-tasks text-info me-2"></i>Recent Synchronization Runs</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Batch / Trigger</th>
                                <th>Module</th>
                                <th class="text-center">Created</th>
                                <th class="text-center">Updated</th>
                                <th class="text-center">Failed</th>
                                <th>Status</th>
                                <th class="pe-3">Started</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentHistory)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">No synchronization runs recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentHistory as $h): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold small"><?= esc($h['trigger_type']) ?></div>
                                            <code class="small text-muted"><?= esc($h['batch_id']) ?></code>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= esc($h['module_filter'] ?: 'ALL') ?></span></td>
                                        <td class="text-center text-success fw-bold"><?= (int) $h['created_count'] ?></td>
                                        <td class="text-center text-primary fw-bold"><?= (int) $h['updated_count'] ?></td>
                                        <td class="text-center text-danger fw-bold"><?= (int) $h['failed_count'] ?></td>
                                        <td>
                                            <?php
                                                $st = $h['status'] ?? 'COMPLETED';
                                                $cls = match ($st) {
                                                    'COMPLETED' => 'bg-success-subtle text-success',
                                                    'PARTIAL'   => 'bg-warning-subtle text-warning',
                                                    'FAILED'    => 'bg-danger-subtle text-danger',
                                                    default     => 'bg-info-subtle text-info',
                                                };
                                            ?>
                                            <span class="badge <?= $cls ?>"><?= esc($st) ?></span>
                                        </td>
                                        <td class="pe-3 small text-muted"><?= date('d M H:i', strtotime($h['started_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold"><i class="fas fa-satellite-dish text-primary me-2"></i>Recent API Calls</h6>
                <a href="<?= site_url('settings/integrations/zoho/logs') ?>" class="btn btn-xs btn-outline-primary fw-semibold">View All Logs</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Time</th>
                                <th>Module</th>
                                <th>Direction</th>
                                <th>Operation</th>
                                <th>HTTP</th>
                                <th class="pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentLogs)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No API calls logged yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentLogs as $log): ?>
                                    <tr>
                                        <td class="ps-3 small text-muted"><?= date('d M H:i:s', strtotime($log['created_at'])) ?></td>
                                        <td><code><?= esc($log['module']) ?></code></td>
                                        <td><span class="badge bg-light text-dark border"><?= esc($log['direction']) ?></span></td>
                                        <td class="small"><?= esc($log['operation']) ?></td>
                                        <td><code><?= esc($log['http_method']) ?> <?= (int) $log['response_code'] ?></code></td>
                                        <td class="pe-3">
                                            <?php if ($log['status'] === 'SUCCESS'): ?>
                                                <span class="badge bg-success-subtle text-success">SUCCESS</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger" title="<?= esc($log['error_message']) ?>">
                                                    <?= esc($log['status']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Sync Selected Module -->
<div class="modal fade" id="syncModuleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <form action="<?= site_url('settings/integrations/zoho/sync-module') ?>" method="post" class="modal-content" id="syncModuleForm">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-layer-group text-primary me-2"></i>Sync Selected Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="syncModuleCloseBtn"></button>
            </div>
            <div class="modal-body">
                <div id="syncModuleFields">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Module</label>
                        <select name="module" id="syncModalModule" class="form-select" required>
                            <?php foreach ($modulesList as $mKey => $mMeta): ?>
                                <option value="<?= esc($mKey) ?>"><?= esc($mMeta['label']) ?> (<?= esc($mKey) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Synchronization Direction</label>
                        <select name="direction" id="syncModalDirection" class="form-select">
                            <option value="bidirectional">Full Bidirectional (Zoho &harr; CI4 ERP)</option>
                            <option value="from_zoho" selected>Pull from Zoho Books (Zoho &rarr; CI4 ERP)</option>
                            <option value="to_zoho">Push to Zoho Books (CI4 ERP &rarr; Zoho)</option>
                        </select>
                    </div>
                </div>

                <!-- Live Progress Display -->
                <div id="syncProgressContainer" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold text-dark text-truncate me-2" id="syncProgressStage">Starting sync...</span>
                        <span class="small fw-bold text-primary" id="syncProgressPct">0%</span>
                    </div>
                    <div class="progress mb-2" style="height: 22px; font-size: 12px; font-weight: bold;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="syncProgressBar" role="progressbar" style="width: 0%; min-width: 2em;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-muted small mb-2" id="syncProgressCounts">
                        <span id="syncCurrentItemBadge" class="badge bg-light text-dark border">Initializing...</span>
                        <span id="syncRateIndicator"><i class="fas fa-circle text-success me-1 small"></i>Live Streaming</span>
                    </div>
                    <div class="bg-dark text-light p-2 rounded small font-monospace" id="syncTerminalLog" style="height: 140px; overflow-y: auto; font-size: 11px;">
                        <div class="text-secondary">[<?= date('H:i:s') ?>] Preparing synchronization task...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="syncModuleCancelBtn">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold" id="syncModuleSubmitBtn">
                    <i class="fas fa-sync-alt me-1"></i> Start Module Sync
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Dedicated Universal Live Sync Progress Modal (for all buttons: Pull, Push, Full Sync) -->
<div class="modal fade" id="liveSyncModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="liveSyncModalTitle">
                    <i class="fas fa-sync-alt fa-spin text-primary me-2" id="liveSyncModalSpinner"></i><span id="liveSyncModalTitleText">Synchronizing with Zoho Books...</span>
                </h5>
                <button type="button" class="btn-close d-none" data-bs-dismiss="modal" id="liveSyncModalCloseBtn"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-bold text-dark text-truncate me-2" id="liveSyncStage">Connecting to Zoho Books...</span>
                    <span class="small fw-bold text-primary fs-6" id="liveSyncPct">0%</span>
                </div>
                <div class="progress mb-2" style="height: 24px; font-size: 13px; font-weight: bold;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="liveSyncBar" role="progressbar" style="width: 0%; min-width: 2.5em;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
                <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
                    <span id="liveSyncCurrentBadge" class="badge bg-light text-dark border">Initializing...</span>
                    <span class="badge bg-success-subtle text-success border"><i class="fas fa-signal me-1"></i>Real-time Progress</span>
                </div>
                <label class="form-label small fw-semibold text-muted mb-1"><i class="fas fa-terminal me-1"></i>Live Activity Stream</label>
                <div class="bg-dark text-light p-3 rounded font-monospace" id="liveSyncTerminal" style="height: 160px; overflow-y: auto; font-size: 11px; line-height: 1.5;">
                    <div class="text-secondary">[<?= date('H:i:s') ?>] Initialized synchronization task...</div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary d-none" data-bs-dismiss="modal" id="liveSyncDismissBtn">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Sync Selected Records -->
<div class="modal fade" id="syncRecordsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="<?= site_url('settings/integrations/zoho/sync-records') ?>" method="post" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-check-square text-primary me-2"></i>Sync Selected Records to Zoho Books</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">1. Choose Module</label>
                    <select name="module" id="pickerModuleSelect" class="form-select" required>
                        <option value="">-- Select Module --</option>
                        <?php foreach ($modulesList as $mKey => $mMeta): ?>
                            <option value="<?= esc($mKey) ?>"><?= esc($mMeta['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <label class="form-label fw-semibold mb-0">2. Select Records to Synchronize</label>
                    <div>
                        <button type="button" class="btn btn-xs btn-outline-secondary" id="selectAllRecordsBtn">Select All</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" id="clearAllRecordsBtn">Clear</button>
                    </div>
                </div>
                <div id="recordsPickerContainer" class="border rounded-3 p-3 bg-light" style="max-height: 320px; overflow-y: auto;">
                    <div class="text-muted text-center py-4">Select a module above to load its records.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="fas fa-cloud-upload-alt me-1"></i> Sync Checked Records
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modSelect = document.getElementById('pickerModuleSelect');
    const container = document.getElementById('recordsPickerContainer');
    const selAllBtn = document.getElementById('selectAllRecordsBtn');
    const clrAllBtn = document.getElementById('clearAllRecordsBtn');

    if (modSelect && container) {
        modSelect.addEventListener('change', function() {
            const mod = modSelect.value;
            if (!mod) {
                container.innerHTML = '<div class="text-muted text-center py-4">Select a module above to load its records.</div>';
                return;
            }
            container.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading records...</div>';
            fetch('<?= site_url('settings/integrations/zoho/module-records') ?>/' + encodeURIComponent(mod))
                .then(r => r.json())
                .then(data => {
                    if (!data.records || data.records.length === 0) {
                        container.innerHTML = '<div class="text-muted text-center py-4">No records found in this module.</div>';
                        return;
                    }
                    let html = '<div class="list-group list-group-flush">';
                    data.records.forEach(function(rec) {
                        const badgeClass = rec.sync_status === 'SYNCED' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary';
                        html += `
                            <label class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                <div>
                                    <input class="form-check-input me-2 record-picker-cb" type="checkbox" name="record_ids[]" value="${rec.id}">
                                    <strong>#${rec.id}</strong> &mdash; ${rec.display_label || ''}
                                </div>
                                <span class="badge ${badgeClass}">${rec.sync_status}</span>
                            </label>
                        `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                })
                .catch(() => {
                    container.innerHTML = '<div class="text-danger text-center py-4">Failed to load module records.</div>';
                });
        });
    }

    if (selAllBtn) {
        selAllBtn.addEventListener('click', function() {
            document.querySelectorAll('.record-picker-cb').forEach(cb => cb.checked = true);
        });
    }
    if (clrAllBtn) {
        clrAllBtn.addEventListener('click', function() {
            document.querySelectorAll('.record-picker-cb').forEach(cb => cb.checked = false);
        });
    }

    // Global Universal Sync Runner for all action buttons (Pull, Push, Full Sync)
    window.runLiveSyncAction = function(url, title, initialStage) {
        const modalEl = document.getElementById('liveSyncModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        const titleText  = document.getElementById('liveSyncModalTitleText');
        const spinner    = document.getElementById('liveSyncModalSpinner');
        const stage      = document.getElementById('liveSyncStage');
        const pctEl      = document.getElementById('liveSyncPct');
        const bar        = document.getElementById('liveSyncBar');
        const badge      = document.getElementById('liveSyncCurrentBadge');
        const terminal   = document.getElementById('liveSyncTerminal');
        const closeBtn   = document.getElementById('liveSyncModalCloseBtn');
        const dismissBtn = document.getElementById('liveSyncDismissBtn');

        titleText.textContent = title;
        spinner.className = 'fas fa-sync-alt fa-spin text-primary me-2';
        closeBtn.classList.add('d-none');
        dismissBtn.classList.add('d-none');
        bar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary';
        terminal.innerHTML = '';

        const syncKey = 'sync_' + Date.now() + '_' + Math.floor(Math.random() * 1000);

        function addLiveLog(msg, type = 'info') {
            const now = new Date().toLocaleTimeString();
            const div = document.createElement('div');
            div.className = type === 'success' ? 'text-success fw-bold' : (type === 'danger' ? 'text-danger fw-bold' : (type === 'warning' ? 'text-warning' : 'text-light'));
            div.innerHTML = `<span class="text-secondary me-2">[${now}]</span>${msg}`;
            terminal.appendChild(div);
            terminal.scrollTop = terminal.scrollHeight;
        }

        function setLivePct(val, text) {
            val = Math.min(100, Math.max(0, val));
            bar.style.width = val + '%';
            bar.textContent = val + '%';
            bar.setAttribute('aria-valuenow', val);
            pctEl.textContent = val + '%';
            if (text) {
                stage.textContent = text;
                badge.textContent = text;
            }
        }

        setLivePct(5, initialStage || 'Connecting to Zoho Books API...');
        addLiveLog(`Initiated: ${title}`, 'warning');

        // Poller
        const pollUrl = '<?= site_url('settings/integrations/zoho/sync-progress') ?>?sync_key=' + encodeURIComponent(syncKey);
        let lastMsg = '';
        const poller = setInterval(() => {
            fetch(pollUrl)
                .then(r => r.json())
                .then(pData => {
                    if (pData && typeof pData.pct !== 'undefined') {
                        setLivePct(pData.pct, pData.message);
                        if (pData.message && pData.message !== lastMsg) {
                            lastMsg = pData.message;
                            const logType = pData.status === 'failed' ? 'danger' : (pData.status === 'completed' ? 'success' : 'info');
                            addLiveLog(pData.message, logType);
                        }
                        if (pData.status === 'completed' || pData.status === 'failed') {
                            clearInterval(poller);
                        }
                    }
                })
                .catch(() => {});
        }, 800);

        // Send POST request
        const fd = new FormData();
        fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        fd.append('sync_key', syncKey);

        fetch(url, {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            clearInterval(poller);
            setLivePct(100, data.success ? 'Completed successfully!' : 'Failed');
            if (data.success) {
                bar.className = 'progress-bar bg-success';
                spinner.className = 'fas fa-check-circle text-success me-2';
                titleText.textContent = 'Sync Finished';
                addLiveLog(data.summary_message || 'Synchronization completed successfully!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 2200);
            } else {
                bar.className = 'progress-bar bg-danger';
                spinner.className = 'fas fa-exclamation-triangle text-danger me-2';
                titleText.textContent = 'Sync Encountered Errors';
                addLiveLog('Error: ' + (data.message || 'Operation failed'), 'danger');
                closeBtn.classList.remove('d-none');
                dismissBtn.classList.remove('d-none');
            }
        })
        .catch(err => {
            clearInterval(poller);
            setLivePct(100, 'Network/Server Error');
            bar.className = 'progress-bar bg-danger';
            spinner.className = 'fas fa-exclamation-triangle text-danger me-2';
            titleText.textContent = 'Sync Error';
            addLiveLog('Error: ' + err.message, 'danger');
            closeBtn.classList.remove('d-none');
            dismissBtn.classList.remove('d-none');
        });
    };

    // Attach to all manual action forms with data-live-sync
    document.querySelectorAll('form[data-live-sync]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const actionTitle = form.getAttribute('data-sync-title') || 'Zoho Synchronization';
            const actionUrl   = form.action;
            window.runLiveSyncAction(actionUrl, actionTitle, 'Starting task...');
        });
    });
    const syncForm = document.getElementById('syncModuleForm');
    if (syncForm) {
        syncForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn   = document.getElementById('syncModuleSubmitBtn');
            const cancelBtn   = document.getElementById('syncModuleCancelBtn');
            const closeBtn    = document.getElementById('syncModuleCloseBtn');
            const fieldsBox   = document.getElementById('syncModuleFields');
            const progressBox = document.getElementById('syncProgressContainer');
            const progressBar = document.getElementById('syncProgressBar');
            const progressPct = document.getElementById('syncProgressPct');
            const progressStg = document.getElementById('syncProgressStage');
            const terminal    = document.getElementById('syncTerminalLog');
            const modSelect   = document.getElementById('syncModalModule');
            const dirSelect   = document.getElementById('syncModalDirection');

            const modLabel = modSelect.options[modSelect.selectedIndex].text;
            const dirVal   = dirSelect.value;

            // Generate unique syncKey per session to poll accurately
            const syncKey = 'sync_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
            const formData = new FormData(syncForm);
            formData.append('sync_key', syncKey);

            // Lock modal UI
            submitBtn.disabled = true;
            cancelBtn.disabled = true;
            closeBtn.classList.add('d-none');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Synchronizing...';
            progressBox.classList.remove('d-none');

            let lastLoggedMessage = '';

            function addLog(msg, type = 'info') {
                if (!msg || msg === lastLoggedMessage) return;
                lastLoggedMessage = msg;
                const now = new Date().toLocaleTimeString();
                const div = document.createElement('div');
                div.className = type === 'success' ? 'text-success fw-bold' : (type === 'danger' ? 'text-danger fw-bold' : (type === 'warning' ? 'text-warning' : 'text-light'));
                div.innerHTML = `<span class="text-secondary me-2">[${now}]</span>${msg}`;
                terminal.appendChild(div);
                terminal.scrollTop = terminal.scrollHeight;
            }

            function setPct(pct, stage) {
                pct = Math.min(100, Math.max(0, pct));
                progressBar.style.width = pct + '%';
                progressBar.textContent = pct + '%';
                progressBar.setAttribute('aria-valuenow', pct);
                progressPct.textContent = pct + '%';
                if (stage) {
                    progressStg.textContent = stage;
                    const itemBadge = document.getElementById('syncCurrentItemBadge');
                    if (itemBadge) itemBadge.textContent = stage;
                }
            }

            addLog(`Initiating real-time sync for: ${modLabel} (${dirVal})...`, 'warning');
            setPct(5, 'Connecting to Zoho Books API...');

            // Realtime progress poller every 800ms
            const progressPollUrl = '<?= site_url('settings/integrations/zoho/sync-progress') ?>?sync_key=' + encodeURIComponent(syncKey);
            const poller = setInterval(() => {
                fetch(progressPollUrl)
                    .then(r => r.json())
                    .then(pData => {
                        if (pData && typeof pData.pct !== 'undefined') {
                            setPct(pData.pct, pData.message);
                            if (pData.message) {
                                const logType = pData.status === 'failed' ? 'danger' : (pData.status === 'completed' ? 'success' : 'info');
                                addLog(pData.message, logType);
                            }
                            if (pData.status === 'completed' || pData.status === 'failed') {
                                clearInterval(poller);
                            }
                        }
                    })
                    .catch(() => {});
            }, 800);

            fetch(syncForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                clearInterval(poller);
                progressBar.style.width = '100%';
                progressBar.textContent = '100%';
                progressPct.textContent = '100%';

                if (data.success) {
                    progressBar.className = 'progress-bar bg-success';
                    progressStg.textContent = 'Completed successfully!';
                    addLog(data.summary_message || 'Synchronization finished successfully!', 'success');
                    submitBtn.className = 'btn btn-success fw-bold';
                    submitBtn.innerHTML = '<i class="fas fa-check me-1"></i> Completed';
                    setTimeout(() => {
                        window.location.reload();
                    }, 2200);
                } else {
                    progressBar.className = 'progress-bar bg-danger';
                    progressStg.textContent = 'Sync failed';
                    addLog('Sync Error: ' + (data.message || 'Unknown failure'), 'danger');
                    submitBtn.disabled = false;
                    cancelBtn.disabled = false;
                    closeBtn.classList.remove('d-none');
                    submitBtn.className = 'btn btn-primary fw-bold';
                    submitBtn.innerHTML = '<i class="fas fa-redo me-1"></i> Retry';
                }
            })
            .catch(err => {
                clearInterval(poller);
                progressBar.className = 'progress-bar bg-danger';
                progressStg.textContent = 'Connection error';
                addLog('Request error: ' + err.message, 'danger');
                submitBtn.disabled = false;
                cancelBtn.disabled = false;
                closeBtn.classList.remove('d-none');
                submitBtn.className = 'btn btn-primary fw-bold';
                submitBtn.innerHTML = '<i class="fas fa-redo me-1"></i> Retry';
            });
        });
    }
});
</script>
<?= $this->endSection() ?>

