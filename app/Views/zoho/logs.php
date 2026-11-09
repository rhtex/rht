<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Zoho Audit Logs & Queue<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="bt-hero">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <span class="bt-eyebrow"><i class="fas fa-history"></i> Settings &rarr; Integrations &rarr; Zoho Books &rarr; Audit Logs</span>
            <h1><i class="fas fa-satellite-dish"></i> Zoho API Audit Logs &amp; Sync Queue</h1>
            <p>Inspect HTTP request/response logs (with redacted secrets) and manage queued synchronization items.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= site_url('settings/integrations/zoho') ?>" class="btn btn-outline-light fw-bold">
                <i class="fas fa-cog me-1"></i> Configuration
            </a>
            <a href="<?= site_url('settings/integrations/zoho/sync') ?>" class="btn btn-outline-light fw-bold">
                <i class="fas fa-sync-alt me-1"></i> Sync Dashboard
            </a>
            <a href="<?= site_url('settings/integrations/zoho/conflicts') ?>" class="btn btn-outline-light fw-bold">
                <i class="fas fa-code-branch me-1"></i> Conflicts
            </a>
            <a href="<?= site_url('settings/integrations/zoho/reconciliation') ?>" class="btn btn-outline-light fw-bold">
                <i class="fas fa-balance-scale me-1"></i> Reconciliation
            </a>
            <a href="<?= site_url('settings/integrations/zoho/logs') ?>" class="btn btn-light fw-bold shadow-sm">
                <i class="fas fa-history text-primary me-1"></i> Audit Logs
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="<?= site_url('settings/integrations/zoho/logs') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Module</label>
                <select name="module" class="form-select form-select-sm">
                    <option value="">All Modules</option>
                    <option value="oauth" <?= $moduleFilter === 'oauth' ? 'selected' : '' ?>>OAuth 2.0</option>
                    <option value="organizations" <?= $moduleFilter === 'organizations' ? 'selected' : '' ?>>Organizations</option>
                    <?php foreach ($modulesList as $mKey => $mMeta): ?>
                        <option value="<?= esc($mKey) ?>" <?= $moduleFilter === $mKey ? 'selected' : '' ?>><?= esc($mMeta['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Direction</label>
                <select name="direction" class="form-select form-select-sm">
                    <option value="">All Directions</option>
                    <option value="TO_ZOHO" <?= $directionFilter === 'TO_ZOHO' ? 'selected' : '' ?>>CI4 &rarr; Zoho (TO_ZOHO)</option>
                    <option value="FROM_ZOHO" <?= $directionFilter === 'FROM_ZOHO' ? 'selected' : '' ?>>Zoho &rarr; CI4 (FROM_ZOHO)</option>
                    <option value="OAUTH" <?= $directionFilter === 'OAUTH' ? 'selected' : '' ?>>OAuth</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="SUCCESS" <?= $statusFilter === 'SUCCESS' ? 'selected' : '' ?>>SUCCESS</option>
                    <option value="FAILED" <?= $statusFilter === 'FAILED' ? 'selected' : '' ?>>FAILED</option>
                    <option value="RATE_LIMITED" <?= $statusFilter === 'RATE_LIMITED' ? 'selected' : '' ?>>RATE_LIMITED</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary fw-bold px-3">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="<?= site_url('settings/integrations/zoho/logs') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Synchronization Queue Card -->
<?php if (!empty($queueItems)): ?>
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list-ol text-warning me-2"></i>Recent Synchronization Queue Items</h6>
        <form action="<?= site_url('settings/integrations/zoho/retry-failed') ?>" method="post" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-xs btn-warning fw-bold">
                <i class="fas fa-redo me-1"></i> Process / Retry Queue Now
            </button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Queue ID</th>
                        <th>Module</th>
                        <th>Local ID</th>
                        <th>Operation</th>
                        <th>Source</th>
                        <th>Attempts</th>
                        <th>Status</th>
                        <th>Error / Retry</th>
                        <th class="pe-3">Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($queueItems as $qi): ?>
                    <tr>
                        <td class="ps-3">#<?= (int) $qi['id'] ?></td>
                        <td><code><?= esc($qi['module']) ?></code></td>
                        <td>#<?= (int) $qi['local_record_id'] ?></td>
                        <td><span class="badge bg-light text-dark border"><?= esc($qi['operation']) ?></span></td>
                        <td><span class="badge bg-secondary-subtle text-secondary"><?= esc($qi['source']) ?></span></td>
                        <td><?= (int) $qi['attempts'] ?> / <?= (int) $qi['max_attempts'] ?></td>
                        <td>
                            <?php
                                $qSt = $qi['status'];
                                $qCls = match ($qSt) {
                                    'COMPLETED' => 'bg-success-subtle text-success',
                                    'FAILED'    => 'bg-danger-subtle text-danger',
                                    default     => 'bg-warning-subtle text-warning',
                                };
                            ?>
                            <span class="badge <?= $qCls ?>"><?= esc($qSt) ?></span>
                        </td>
                        <td class="small text-danger"><?= esc($qi['error_message'] ?? '') ?></td>
                        <td class="pe-3 small text-muted"><?= esc($qi['updated_at'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- HTTP Request & Response Audit Logs -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-terminal text-primary me-2"></i>API Request &amp; Response Logs (Latest 150)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Timestamp</th>
                        <th>Module</th>
                        <th>Direction</th>
                        <th>Operation</th>
                        <th>Method &amp; HTTP</th>
                        <th>Latency</th>
                        <th>Status</th>
                        <th>Message</th>
                        <th class="text-end pe-3">Payload</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="10" class="text-center text-muted py-4">No audit log entries match your filter.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="ps-3">#<?= (int) $log['id'] ?></td>
                                <td class="small text-muted"><?= esc($log['created_at']) ?></td>
                                <td>
                                    <code><?= esc($log['module']) ?></code>
                                    <?php if (!empty($log['local_record_id'])): ?>
                                        <span class="text-muted small">(#<?= (int) $log['local_record_id'] ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= esc($log['direction']) ?></span></td>
                                <td class="small fw-semibold"><?= esc($log['operation']) ?></td>
                                <td><code><?= esc($log['http_method']) ?> <?= (int) $log['response_code'] ?></code></td>
                                <td class="small text-muted"><?= (int) ($log['duration_ms'] ?? 0) ?> ms</td>
                                <td>
                                    <?php if ($log['status'] === 'SUCCESS'): ?>
                                        <span class="badge bg-success-subtle text-success">SUCCESS</span>
                                    <?php elseif ($log['status'] === 'RATE_LIMITED'): ?>
                                        <span class="badge bg-warning text-dark">RATE_LIMITED</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger"><?= esc($log['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-danger" style="max-width:260px;"><?= esc($log['error_message'] ?? '') ?></td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-xs btn-outline-secondary view-log-btn"
                                            data-endpoint="<?= esc($log['endpoint'] ?? '') ?>"
                                            data-req="<?= esc($log['request_payload'] ?? '') ?>"
                                            data-res="<?= esc($log['response_payload'] ?? '') ?>">
                                        <i class="fas fa-code"></i> Inspect
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Inspect Log Payload -->
<div class="modal fade" id="logPayloadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-code text-primary me-2"></i>API Request &amp; Response Inspector</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="fw-bold small">Endpoint URL:</label>
                    <code class="d-block p-2 bg-light border rounded small text-break" id="modalLogEndpoint"></code>
                </div>
                <div class="mb-3">
                    <label class="fw-bold small">Request Payload (Redacted):</label>
                    <pre class="p-2 bg-dark text-light rounded small" style="max-height:200px;overflow:auto;" id="modalLogReq"></pre>
                </div>
                <div>
                    <label class="fw-bold small">Response Payload (Redacted):</label>
                    <pre class="p-2 bg-dark text-info rounded small" style="max-height:260px;overflow:auto;" id="modalLogRes"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.view-log-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('modalLogEndpoint').textContent = btn.getAttribute('data-endpoint') || '';
            document.getElementById('modalLogReq').textContent      = btn.getAttribute('data-req') || '(empty)';
            document.getElementById('modalLogRes').textContent      = btn.getAttribute('data-res') || '(empty)';
            const modal = new bootstrap.Modal(document.getElementById('logPayloadModal'));
            modal.show();
        });
    });
});
</script>
<?= $this->endSection() ?>

