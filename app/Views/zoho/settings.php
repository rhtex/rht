<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Zoho Books Integration<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="bt-hero">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <span class="bt-eyebrow"><i class="fas fa-plug"></i> Settings &rarr; Integrations &rarr; Zoho Books</span>
            <h1><i class="fas fa-book-open"></i> Zoho Books API Configuration</h1>
            <p>Two-way OAuth 2.0 integration between CI4 ERP and your Zoho Books Organization.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= site_url('settings/integrations/zoho') ?>" class="btn btn-light fw-bold shadow-sm">
                <i class="fas fa-cog text-primary me-1"></i> Configuration
            </a>
            <a href="<?= site_url('settings/integrations/zoho/sync') ?>" class="btn btn-warning fw-bold shadow-sm">
                <i class="fas fa-sync-alt me-1"></i> Sync Dashboard
            </a>
            <a href="<?= site_url('settings/integrations/zoho/conflicts') ?>" class="btn btn-outline-light fw-bold">
                <i class="fas fa-code-branch me-1"></i> Conflicts
                <?php if (!empty($openConflicts)): ?>
                    <span class="badge bg-danger ms-1"><?= (int) $openConflicts ?></span>
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

<!-- Connection & Status KPI Strip -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="bt-stat <?= !empty($config['is_enabled']) ? 'bt-stat--emerald' : 'bt-stat--slate' ?>">
            <div class="bt-stat__icon"><i class="fas fa-power-off"></i></div>
            <div>
                <div class="bt-stat__label">Integration Status</div>
                <div class="bt-stat__val"><?= !empty($config['is_enabled']) ? 'Enabled' : 'Disabled' ?></div>
                <div class="bt-stat__sub">Auto-Sync: <?= !empty($config['auto_sync_enabled']) ? 'Active' : 'Manual Only' ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bt-stat <?= !empty($config['has_refresh_token']) ? 'bt-stat--blue' : 'bt-stat--amber' ?>">
            <div class="bt-stat__icon"><i class="fas fa-key"></i></div>
            <div>
                <div class="bt-stat__label">OAuth 2.0 Status</div>
                <div class="bt-stat__val"><?= !empty($config['has_refresh_token']) ? 'Connected' : 'Not Connected' ?></div>
                <div class="bt-stat__sub">DC: <?= strtoupper(esc($config['data_center'] ?? 'in')) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bt-stat bt-stat--purple">
            <div class="bt-stat__icon"><i class="fas fa-clock"></i></div>
            <div>
                <div class="bt-stat__label">Last Successful Connection</div>
                <div class="bt-stat__val" style="font-size:1rem;">
                    <?= !empty($config['last_connected_at']) ? date('d M Y, h:i A', strtotime($config['last_connected_at'])) : 'Never' ?>
                </div>
                <div class="bt-stat__sub">Org ID: <?= esc($config['organization_id'] ?: 'Not set') ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bt-stat bt-stat--emerald">
            <div class="bt-stat__icon"><i class="fas fa-exchange-alt"></i></div>
            <div>
                <div class="bt-stat__label">Last Synchronization</div>
                <div class="bt-stat__val" style="font-size:1rem;">
                    <?= !empty($config['last_sync_at']) ? date('d M Y, h:i A', strtotime($config['last_sync_at'])) : 'Never' ?>
                </div>
                <div class="bt-stat__sub"><?= esc($config['organization_name'] ?: 'Zoho Books') ?></div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($config['last_error'])): ?>
<div class="alert alert-warning border-start border-4 border-warning shadow-sm mb-4">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle fa-lg text-warning me-3"></i>
        <div>
            <strong>Last Reported Zoho API Notice:</strong>
            <div class="small"><?= esc($config['last_error']) ?></div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Column: Main Configuration Form -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-sliders-h text-primary me-2"></i>Zoho Books API Credentials & Settings</h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                    <i class="fas fa-shield-alt me-1"></i>AES-256 Encrypted Storage
                </span>
            </div>

            <form action="<?= site_url('settings/integrations/zoho/save') ?>" method="post" autocomplete="off">
                <?= csrf_field() ?>
                <div class="card-body p-4">

                    <!-- Enable / Auto Sync Toggles -->
                    <div class="row g-3 mb-4 p-3 rounded-3 bg-light border">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_enabled" name="is_enabled" value="1" <?= !empty($config['is_enabled']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold text-dark" for="is_enabled">
                                    Enable Zoho Books Integration
                                </label>
                                <div class="text-muted small">Master switch for Zoho Books API communication.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="auto_sync_enabled" name="auto_sync_enabled" value="1" <?= !empty($config['auto_sync_enabled']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold text-dark" for="auto_sync_enabled">
                                    Enable Automatic Synchronization (CI4 &rarr; Zoho)
                                </label>
                                <div class="text-muted small">Automatically sync records when created, edited or deleted locally.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Centre & Endpoints -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Zoho Data Centre <span class="text-danger">*</span></label>
                            <select name="data_center" id="dataCenterSelect" class="form-select" required>
                                <?php foreach ($dataCenters as $dcCode => $dc): ?>
                                    <option value="<?= esc($dcCode) ?>"
                                            data-api="<?= esc($dc['api_base_url']) ?>"
                                            <?= ($config['data_center'] ?? 'in') === $dcCode ? 'selected' : '' ?>>
                                        <?= esc($dc['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">For Indian GST organizations, select <strong>India (.zoho.in)</strong>.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">API Base URL <span class="text-danger">*</span></label>
                            <input type="url" name="api_base_url" id="apiBaseUrlInput" class="form-control"
                                   value="<?= esc($config['api_base_url'] ?? 'https://www.zohoapis.in/books/v3') ?>" required>
                            <small class="text-muted">Automatically matched to the selected Data Centre.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">OAuth Redirect URL (Authorized Redirect URI in Zoho API Console)</label>
                        <div class="input-group">
                            <input type="url" name="oauth_redirect_url" id="oauthRedirectInput" class="form-control bg-light"
                                   value="<?= esc($config['oauth_redirect_url'] ?? site_url('settings/integrations/zoho/callback')) ?>">
                            <button type="button" class="btn btn-outline-secondary" id="copyRedirectBtn" title="Copy Redirect URL">
                                <i class="fas fa-copy me-1"></i> Copy
                            </button>
                        </div>
                        <small class="text-muted">Add this exact URL in <a href="https://api-console.zoho.in/" target="_blank" rel="noopener">Zoho API Console</a> (Server-based Applications).</small>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-lock me-2"></i>OAuth 2.0 Credentials (Encrypted at Rest)</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Zoho Client ID
                                <?php if (!empty($config['has_client_id'])): ?>
                                    <span class="badge bg-success-subtle text-success ms-1">Saved (<?= esc($config['client_id_masked']) ?>)</span>
                                <?php endif; ?>
                            </label>
                            <input type="password" name="client_id" class="form-control"
                                   placeholder="<?= !empty($config['has_client_id']) ? 'Leave blank to keep existing Client ID' : '1000.XXXXXXXXXXXXXXXXXXXXXXXX' ?>"
                                   autocomplete="new-password">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Zoho Client Secret
                                <?php if (!empty($config['has_client_secret'])): ?>
                                    <span class="badge bg-success-subtle text-success ms-1">Saved (<?= esc($config['client_secret_masked']) ?>)</span>
                                <?php endif; ?>
                            </label>
                            <input type="password" name="client_secret" class="form-control"
                                   placeholder="<?= !empty($config['has_client_secret']) ? 'Leave blank to keep existing Client Secret' : 'Enter Zoho Client Secret' ?>"
                                   autocomplete="new-password">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Zoho Refresh Token
                                <?php if (!empty($config['has_refresh_token'])): ?>
                                    <span class="badge bg-success-subtle text-success ms-1">Saved (<?= esc($config['refresh_token_masked']) ?>)</span>
                                <?php endif; ?>
                            </label>
                            <input type="password" name="refresh_token" class="form-control"
                                   placeholder="<?= !empty($config['has_refresh_token']) ? 'Auto-managed via OAuth or enter new Refresh Token' : '1000.xxxx (Or click Connect to Zoho Books)' ?>"
                                   autocomplete="new-password">
                            <small class="text-muted">Populated automatically when you click <strong>Connect to Zoho Books</strong>, or paste a self-client refresh token.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Zoho Organization ID</label>
                            <input type="text" name="organization_id" class="form-control"
                                   value="<?= esc($config['organization_id'] ?? '') ?>"
                                   placeholder="e.g. 60012345678">
                            <input type="hidden" name="organization_name" value="<?= esc($config['organization_name'] ?? '') ?>">
                            <small class="text-muted">Can also be selected from retrieved organizations on the right panel after connecting.</small>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-cogs me-2"></i>Synchronization & Conflict Policies</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Conflict Resolution Rule</label>
                            <select name="conflict_strategy" class="form-select">
                                <option value="manual_review" <?= ($config['conflict_strategy'] ?? 'manual_review') === 'manual_review' ? 'selected' : '' ?>>Flag for Manual Review (Recommended)</option>
                                <option value="latest_timestamp" <?= ($config['conflict_strategy'] ?? '') === 'latest_timestamp' ? 'selected' : '' ?>>Latest Modified Timestamp Wins (Non-Accounting Fields)</option>
                                <option value="prefer_zoho" <?= ($config['conflict_strategy'] ?? '') === 'prefer_zoho' ? 'selected' : '' ?>>Prefer Zoho Books Version</option>
                                <option value="prefer_local" <?= ($config['conflict_strategy'] ?? '') === 'prefer_local' ? 'selected' : '' ?>>Prefer CI4 ERP Version</option>
                            </select>
                            <small class="text-muted">Conflicting accounting totals are always flagged for safe review.</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Outgoing Sync Dispatch Mode</label>
                            <select name="sync_mode" class="form-select">
                                <option value="immediate" <?= ($config['sync_mode'] ?? 'immediate') === 'immediate' ? 'selected' : '' ?>>Immediate + Fallback Queue (Default)</option>
                                <option value="queue_cron" <?= ($config['sync_mode'] ?? '') === 'queue_cron' ? 'selected' : '' ?>>Background Queue Only (cPanel Cron)</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Document Numbering</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="preserve_document_numbers" name="preserve_document_numbers" value="1" <?= !empty($config['preserve_document_numbers']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="preserve_document_numbers">
                                    Preserve exact ERP Invoice / Bill / SO numbers in Zoho Books
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-tachometer-alt me-2"></i>Zoho Books v3 API Rate Limit, Daily Quota &amp; Concurrency Controls</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Zoho Books Subscription Plan</label>
                            <select name="subscription_plan" id="zohoPlanSelect" class="form-select">
                                <?php foreach (($planPresets ?? []) as $pKey => $preset): ?>
                                    <option value="<?= esc($pKey) ?>"
                                            data-daily="<?= (int) $preset['daily_limit'] ?>"
                                            data-minute="<?= (int) $preset['per_minute'] ?>"
                                            data-concurrent="<?= (int) $preset['max_concurrent'] ?>"
                                            <?= ($config['subscription_plan'] ?? 'free') === $pKey ? 'selected' : '' ?>>
                                        <?= esc($preset['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Free Plan allows <strong>1,000 API calls/day</strong> and <strong>5 concurrent connections</strong>.</small>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Daily API Limit</label>
                            <input type="number" name="daily_api_limit" id="dailyApiLimitInput" class="form-control"
                                   min="100" max="100000" value="<?= (int) ($config['daily_api_limit'] ?? 1000) ?>">
                            <small class="text-muted">Calls / 24h day</small>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Per-Minute Cap</label>
                            <input type="number" name="per_minute_limit" id="perMinuteLimitInput" class="form-control"
                                   min="10" max="100" value="<?= (int) ($config['per_minute_limit'] ?? 90) ?>">
                            <small class="text-muted">Max 100/min (~1.6/s)</small>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Max Concurrent</label>
                            <input type="number" name="max_concurrent_connections" id="maxConcurrentInput" class="form-control"
                                   min="1" max="10" value="<?= (int) ($config['max_concurrent_connections'] ?? 5) ?>">
                            <small class="text-muted">Free Plan max: 5</small>
                        </div>
                    </div>

                </div>

                <div class="card-footer bg-white py-3 px-4 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="fas fa-save me-1"></i> Save Configuration
                    </button>
                    <a href="<?= site_url('settings/integrations/zoho/sync') ?>" class="btn btn-outline-primary fw-semibold">
                        Go to Synchronization Dashboard <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Connection Actions & Organization Selector -->
    <div class="col-lg-4">
        <!-- Connection Actions Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-bolt text-warning me-2"></i>Connection Actions</h5>
            </div>
            <div class="card-body p-4 d-grid gap-2">
                <a href="<?= site_url('settings/integrations/zoho/connect') ?>" class="btn btn-success fw-bold py-2">
                    <i class="fas fa-plug me-2"></i> Connect to Zoho Books (OAuth 2.0)
                </a>

                <form action="<?= site_url('settings/integrations/zoho/test') ?>" method="post" class="d-grid">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-primary fw-semibold py-2">
                        <i class="fas fa-vial me-2"></i> Test Connection & Fetch Orgs
                    </button>
                </form>

                <form action="<?= site_url('settings/integrations/zoho/refresh-token') ?>" method="post" class="d-grid">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-info fw-semibold py-2">
                        <i class="fas fa-sync me-2"></i> Refresh Access Token
                    </button>
                </form>

                <form action="<?= site_url('settings/integrations/zoho/disconnect') ?>" method="post" class="d-grid"
                      onsubmit="return confirm('Are you sure you want to disconnect Zoho Books and revoke the active OAuth token?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger fw-semibold py-2">
                        <i class="fas fa-unlink me-2"></i> Disconnect Zoho Books
                    </button>
                </form>
            </div>
        </div>

        <!-- Organization Selector Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-building text-info me-2"></i>Select Zoho Organization</h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($config['organizations'])): ?>
                    <form action="<?= site_url('settings/integrations/zoho/select-organization') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Available Organizations (<?= count($config['organizations']) ?>)</label>
                            <select name="organization_id" class="form-select" required>
                                <?php foreach ($config['organizations'] as $org): ?>
                                    <?php $oId = (string) ($org['organization_id'] ?? ''); ?>
                                    <option value="<?= esc($oId) ?>" <?= ($config['organization_id'] ?? '') === $oId ? 'selected' : '' ?>>
                                        <?= esc($org['name'] ?? $oId) ?> (ID: <?= esc($oId) ?><?= !empty($org['state_code']) ? ' | ' . esc($org['state_code']) : '' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">
                            <i class="fas fa-check-circle me-1"></i> Use Selected Organization
                        </button>
                    </form>
                <?php else: ?>
                    <p class="text-muted small mb-0">
                        Connect to Zoho Books or click <strong>Test Connection & Fetch Orgs</strong> above to retrieve your available Zoho Books organizations.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Live API Rate Limit & Quota Monitor -->
        <?php if (!empty($rateUsage)): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-gauge-high text-primary me-2"></i>API Frequency &amp; Daily Quota</h6>
                <span class="badge bg-info-subtle text-info border border-info-subtle text-uppercase"><?= esc($rateUsage['subscription_plan']) ?> Plan</span>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-semibold mb-1">
                        <span>Daily Allowance (24h)</span>
                        <span><?= number_format($rateUsage['daily_used']) ?> / <?= number_format($rateUsage['daily_limit']) ?> calls (<?= $rateUsage['daily_percent'] ?>%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar <?= $rateUsage['daily_percent'] >= 90 ? 'bg-danger' : ($rateUsage['daily_percent'] >= 70 ? 'bg-warning' : 'bg-success') ?>"
                             style="width: <?= min(100, (float) $rateUsage['daily_percent']) ?>%"></div>
                    </div>
                    <div class="text-muted small mt-1"><?= number_format($rateUsage['daily_remaining']) ?> calls remaining today</div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-semibold mb-1">
                        <span>Per-Minute Frequency (Rolling 60s)</span>
                        <span><?= (int) $rateUsage['minute_used'] ?> / <?= (int) $rateUsage['minute_limit'] ?> req/min (Hard Cap: 100/min)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: <?= min(100, (float) $rateUsage['minute_percent']) ?>%"></div>
                    </div>
                    <div class="text-muted small mt-1">Paced at &le; 1.6 req/sec (<?= (int) $rateUsage['min_interval_ms'] ?>ms min spacing) to prevent HTTP 429</div>
                </div>

                <div class="d-flex justify-content-between align-items-center small pt-2 border-top">
                    <span class="fw-semibold text-dark"><i class="fas fa-network-wired me-1 text-secondary"></i>Concurrent Connections:</span>
                    <span class="badge bg-secondary-subtle text-dark border">
                        <?= (int) $rateUsage['active_concurrent'] ?> / <?= (int) $rateUsage['max_concurrent_connections'] ?> Active Slots
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- cPanel Cron Command Reference -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-terminal text-secondary me-2"></i>Shared cPanel Cron Job</h6>
            </div>
            <div class="card-body p-3">
                <p class="text-muted small mb-2">To process the background queue and incremental sync automatically on cPanel, schedule:</p>
                <code class="d-block p-2 bg-dark text-light rounded small">php <?= esc(ROOTPATH) ?>spark zoho:sync --direction=both</code>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dcSelect = document.getElementById('dataCenterSelect');
    const apiInput = document.getElementById('apiBaseUrlInput');
    const copyBtn  = document.getElementById('copyRedirectBtn');
    const redirInp = document.getElementById('oauthRedirectInput');

    if (dcSelect && apiInput) {
        dcSelect.addEventListener('change', function() {
            const opt = dcSelect.options[dcSelect.selectedIndex];
            const url = opt ? opt.getAttribute('data-api') : '';
            if (url) {
                apiInput.value = url;
            }
        });
    }

    if (copyBtn && redirInp) {
        copyBtn.addEventListener('click', function() {
            navigator.clipboard.writeText(redirInp.value).then(function() {
                copyBtn.innerHTML = '<i class="fas fa-check text-success me-1"></i> Copied';
                setTimeout(function() {
                    copyBtn.innerHTML = '<i class="fas fa-copy me-1"></i> Copy';
                }, 2000);
            });
        });
    }

    const planSelect = document.getElementById('zohoPlanSelect');
    const dailyInp   = document.getElementById('dailyApiLimitInput');
    const minInp     = document.getElementById('perMinuteLimitInput');
    const concInp    = document.getElementById('maxConcurrentInput');

    if (planSelect && dailyInp && minInp && concInp) {
        planSelect.addEventListener('change', function() {
            const opt = planSelect.options[planSelect.selectedIndex];
            if (!opt || opt.value === 'custom') return;
            dailyInp.value = opt.getAttribute('data-daily') || '1000';
            minInp.value   = opt.getAttribute('data-minute') || '90';
            concInp.value  = opt.getAttribute('data-concurrent') || '5';
        });
    }
});
</script>
<?= $this->endSection() ?>

