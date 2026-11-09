<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Zoho Synchronization Conflicts<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-exclamation-triangle text-primary me-2"></i>Synchronization Conflicts</h1>
        <small class="text-muted">Review and resolve records modified independently in both CI4 ERP and Zoho Books.</small>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex flex-wrap gap-2 align-items-center">
            <a href="<?= site_url('settings/integrations/zoho') ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-cog me-1"></i> Configuration
            </a>
            <a href="<?= site_url('settings/integrations/zoho/sync') ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-sync-alt me-1"></i> Sync Dashboard
            </a>
            <a href="<?= site_url('settings/integrations/zoho/conflicts') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-code-branch me-1"></i> Conflicts
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

<!-- Filter Bar -->
<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header bg-white py-2">
        <h3 class="card-title fw-bold m-0 text-dark"><i class="fas fa-filter text-primary me-2"></i>Filter Conflicts</h3>
    </div>
    <div class="card-body p-3">
        <form action="<?= site_url('settings/integrations/zoho/conflicts') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">Filter by Module</label>
                <select name="module" class="form-select form-select-sm">
                    <option value="">All Modules</option>
                    <?php foreach ($modulesList as $mKey => $mMeta): ?>
                        <option value="<?= esc($mKey) ?>" <?= $moduleFilter === $mKey ? 'selected' : '' ?>>
                            <?= esc($mMeta['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">Filter by Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="OPEN" <?= $statusFilter === 'OPEN' ? 'selected' : '' ?>>Open Conflicts</option>
                    <option value="RESOLVED_LOCAL" <?= $statusFilter === 'RESOLVED_LOCAL' ? 'selected' : '' ?>>Resolved (Chose Local)</option>
                    <option value="RESOLVED_ZOHO" <?= $statusFilter === 'RESOLVED_ZOHO' ? 'selected' : '' ?>>Resolved (Chose Zoho)</option>
                    <option value="IGNORED" <?= $statusFilter === 'IGNORED' ? 'selected' : '' ?>>Ignored</option>
                    <option value="ALL" <?= $statusFilter === 'ALL' ? 'selected' : '' ?>>All Statuses</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-sm btn-primary fw-bold px-3">
                    <i class="fas fa-filter me-1"></i> Apply Filters
                </button>
                <a href="<?= site_url('settings/integrations/zoho/conflicts') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<?php if (empty($conflicts)): ?>
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <h5 class="fw-bold text-dark">No Synchronization Conflicts Found</h5>
            <p class="text-muted mb-0">All synchronized records between CI4 ERP and Zoho Books are aligned for the selected filter.</p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($conflicts as $c): ?>
        <?php
            $diffs     = json_decode($c['diff_fields_json'] ?? '[]', true) ?: [];
            $localVals = json_decode($c['local_payload_json'] ?? '{}', true) ?: [];
            $zohoVals  = json_decode($c['zoho_payload_json'] ?? '{}', true) ?: [];
            $modLabel  = $modulesList[$c['module']]['label'] ?? ucfirst($c['module']);
        ?>
        <div class="card card-outline card-warning shadow-sm mb-3">
            <div class="card-header bg-white py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger px-2 py-1"><i class="fas fa-exclamation-triangle me-1"></i>Conflict #<?= (int) $c['id'] ?></span>
                    <span class="badge bg-primary-subtle text-primary border px-2 py-1"><?= esc($modLabel) ?></span>
                    <strong class="text-dark">Record: <?= esc($c['record_number'] ?: ('#' . $c['local_record_id'])) ?></strong>
                    <span class="text-muted small">(Local ID: #<?= (int) $c['local_record_id'] ?> &bull; Zoho ID: <code><?= esc($c['zoho_record_id']) ?></code>)</span>
                </div>
                <div>
                    <span class="badge <?= $c['status'] === 'OPEN' ? 'bg-warning text-dark' : 'bg-success-subtle text-success' ?> px-2 py-1">
                        <?= esc($c['status']) ?>
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="alert alert-light border mb-3 py-2">
                    <div class="row small">
                        <div class="col-md-6">
                            <strong><i class="fas fa-info-circle text-danger me-1"></i>Conflict Reason:</strong>
                            <?= esc($c['conflict_reason']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>CI4 Modified Time:</strong>
                            <?= !empty($c['local_modified_at']) ? date('d M Y, h:i:s A', strtotime($c['local_modified_at'])) : 'N/A' ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Zoho Modified Time:</strong>
                            <?= !empty($c['zoho_modified_at']) ? date('d M Y, h:i:s A', strtotime($c['zoho_modified_at'])) : 'N/A' ?>
                        </div>
                    </div>
                </div>

                <!-- Side-by-Side Diff Table -->
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:25%;">Field</th>
                                <th style="width:37.5%;" class="text-primary"><i class="fas fa-database me-1"></i>Local CI4 ERP Value</th>
                                <th style="width:37.5%;" class="text-success"><i class="fas fa-cloud me-1"></i>Zoho Books Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($diffs)): ?>
                                <?php foreach ($diffs as $d): ?>
                                    <tr>
                                        <td class="fw-semibold">
                                            <?= esc($d['label'] ?? $d['field']) ?>
                                            <?php if (!empty($d['is_accounting_critical'])): ?>
                                                <span class="badge bg-danger-subtle text-danger ms-1">Accounting</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="bg-primary-subtle bg-opacity-10">
                                            <code><?= esc(is_array($d['local_value']) ? json_encode($d['local_value']) : (string) ($d['local_value'] ?? '')) ?></code>
                                        </td>
                                        <td class="bg-success-subtle bg-opacity-10">
                                            <code><?= esc(is_array($d['zoho_value']) ? json_encode($d['zoho_value']) : (string) ($d['zoho_value'] ?? '')) ?></code>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td class="fw-semibold">Full Normalized Snapshot</td>
                                    <td><pre class="small mb-0"><?= esc(json_encode($localVals, JSON_PRETTY_PRINT)) ?></pre></td>
                                    <td><pre class="small mb-0"><?= esc(json_encode($zohoVals, JSON_PRETTY_PRINT)) ?></pre></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($c['status'] === 'OPEN'): ?>
                <form action="<?= site_url('settings/integrations/zoho/conflicts/resolve/' . (int) $c['id']) ?>" method="post" class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top">
                    <?= csrf_field() ?>
                    <div class="flex-grow-1" style="max-width:420px;">
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Optional resolution note...">
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" name="choice" value="LOCAL" class="btn btn-sm btn-primary fw-bold">
                            <i class="fas fa-upload me-1"></i> Choose Local Version (Push CI4 &rarr; Zoho)
                        </button>
                        <button type="submit" name="choice" value="ZOHO" class="btn btn-sm btn-success fw-bold">
                            <i class="fas fa-download me-1"></i> Choose Zoho Version (Pull Zoho &rarr; CI4)
                        </button>
                        <button type="submit" name="choice" value="IGNORE" class="btn btn-sm btn-outline-secondary">
                            Ignore
                        </button>
                    </div>
                </form>
                <?php else: ?>
                    <div class="small text-muted">
                        Resolved at <?= esc($c['resolved_at'] ?? '') ?> &bull; Notes: <?= esc($c['resolution_notes'] ?? 'N/A') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>

