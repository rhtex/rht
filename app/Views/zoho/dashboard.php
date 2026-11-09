<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Zoho Books Synchronization Dashboard<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="bt-hero">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <span class="bt-eyebrow"><i class="fas fa-sync-alt"></i> Settings &rarr; Integrations &rarr; Zoho Books &rarr; Synchronization</span>
            <h1><i class="fas fa-exchange-alt"></i> Zoho Books Synchronization Center</h1>
            <p>Manage manual &amp; automated bidirectional CRUD synchronization across all Sales, Purchase, Inventory, and Finance modules.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= site_url('settings/integrations/zoho') ?>" class="btn btn-outline-light fw-bold">
                <i class="fas fa-cog me-1"></i> Configuration
            </a>
            <a href="<?= site_url('settings/integrations/zoho/sync') ?>" class="btn btn-light fw-bold shadow-sm">
                <i class="fas fa-sync-alt text-primary me-1"></i> Sync Dashboard
            </a>
            <a href="<?= site_url('settings/integrations/zoho/conflicts') ?>" class="btn btn-outline-light fw-bold">
                <i class="fas fa-code-branch me-1"></i> Conflicts
                <?php if (!empty($totalConflicts)): ?>
                    <span class="badge bg-danger ms-1"><?= (int) $totalConflicts ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= site_url('settings/integrations/zoho/reconciliation') ?>" class="btn btn-outline-light fw-bold">
                <i class="fas fa-balance-scale me-1"></i> Reconciliation
            </a>
            <a href="<?= site_url('settings/integrations/zoho/logs') ?>" class="btn btn-outline-light fw-bold">
                <i class="fas fa-history me-1"></i> Audit Logs
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- 7 Required Manual Synchronization Action Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
            <div>
                <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-play-circle text-primary me-2"></i>Manual Synchronization Controls</h5>
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
                <form action="<?= site_url('settings/integrations/zoho/sync-from-zoho') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary fw-bold shadow-sm">
                        <i class="fas fa-cloud-download-alt me-1"></i> Sync from Zoho Books
                    </button>
                </form>

                <!-- 2. Sync to Zoho Books -->
                <form action="<?= site_url('settings/integrations/zoho/sync-to-zoho') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success fw-bold shadow-sm">
                        <i class="fas fa-cloud-upload-alt me-1"></i> Sync to Zoho Books
                    </button>
                </form>

                <!-- 3. Full Bidirectional Sync -->
                <form action="<?= site_url('settings/integrations/zoho/full-sync') ?>" method="post" class="d-inline">
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
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="bt-stat bt-stat--emerald">
            <div class="bt-stat__icon"><i class="fas fa-check-double"></i></div>
            <div>
                <div class="bt-stat__label">Synced &amp; Mapped</div>
                <div class="bt-stat__val"><?= number_format($totalMapped) ?></div>
                <div class="bt-stat__sub">Linked Local &harr; Zoho IDs</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bt-stat bt-stat--blue">
            <div class="bt-stat__icon"><i class="fas fa-hourglass-half"></i></div>
            <div>
                <div class="bt-stat__label">Unsynced Local Records</div>
                <div class="bt-stat__val"><?= number_format($totalUnsynced) ?></div>
                <div class="bt-stat__sub">Queue Pending: <?= number_format($pendingQueue) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bt-stat <?= $totalConflicts > 0 ? 'bt-stat--amber' : 'bt-stat--purple' ?>">
            <div class="bt-stat__icon"><i class="fas fa-code-branch"></i></div>
            <div>
                <div class="bt-stat__label">Open Conflicts</div>
                <div class="bt-stat__val"><?= number_format($totalConflicts) ?></div>
                <div class="bt-stat__sub">
                    <a href="<?= site_url('settings/integrations/zoho/conflicts') ?>" class="text-decoration-none">Review Conflicts &rarr;</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bt-stat <?= $totalFailed > 0 ? 'bt-stat--rose' : 'bt-stat--slate' ?>">
            <div class="bt-stat__icon"><i class="fas fa-exclamation-circle"></i></div>
            <div>
                <div class="bt-stat__label">Failed Synchronizations</div>
                <div class="bt-stat__val"><?= number_format($totalFailed) ?></div>
                <div class="bt-stat__sub">
                    <a href="<?= site_url('settings/integrations/zoho/logs?status=FAILED') ?>" class="text-decoration-none">View Error Logs &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Module-by-Module Synchronization Status Matrix -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-cubes text-primary me-2"></i>Module Synchronization Matrix (13 Modules)</h5>
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
                                <form action="<?= site_url('settings/integrations/zoho/sync-module') ?>" method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="module" value="<?= esc($modKey) ?>">
                                    <input type="hidden" name="direction" value="from_zoho">
                                    <button type="submit" class="btn btn-outline-primary" title="Pull <?= esc($ms['label']) ?> from Zoho Books">
                                        <i class="fas fa-download me-1"></i> Pull
                                    </button>
                                </form>
                                <form action="<?= site_url('settings/integrations/zoho/sync-module') ?>" method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="module" value="<?= esc($modKey) ?>">
                                    <input type="hidden" name="direction" value="to_zoho">
                                    <button type="submit" class="btn btn-outline-success" title="Push <?= esc($ms['label']) ?> to Zoho Books">
                                        <i class="fas fa-upload me-1"></i> Push
                                    </button>
                                </form>
                                <form action="<?= site_url('settings/integrations/zoho/sync-module') ?>" method="post" class="d-inline">
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
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-tasks text-info me-2"></i>Recent Synchronization Runs</h6>
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
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-satellite-dish text-primary me-2"></i>Recent API Calls</h6>
                <a href="<?= site_url('settings/integrations/zoho/logs') ?>" class="btn btn-xs btn-outline-primary">View All Logs</a>
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
<div class="modal fade" id="syncModuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= site_url('settings/integrations/zoho/sync-module') ?>" method="post" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-layer-group text-primary me-2"></i>Sync Selected Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Module</label>
                    <select name="module" class="form-select" required>
                        <?php foreach ($modulesList as $mKey => $mMeta): ?>
                            <option value="<?= esc($mKey) ?>"><?= esc($mMeta['label']) ?> (<?= esc($mKey) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Synchronization Direction</label>
                    <select name="direction" class="form-select">
                        <option value="bidirectional">Full Bidirectional (Zoho &harr; CI4 ERP)</option>
                        <option value="from_zoho">Pull from Zoho Books (Zoho &rarr; CI4 ERP)</option>
                        <option value="to_zoho">Push to Zoho Books (CI4 ERP &rarr; Zoho)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="fas fa-sync-alt me-1"></i> Start Module Sync
                </button>
            </div>
        </form>
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
});
</script>
<?= $this->endSection() ?>

