<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Unsynced Zoho Records<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-hourglass-half text-primary me-2"></i>Unsynced ERP Records</h1>
        <small class="text-muted">Local records that are not yet synced (or failed sync) with Zoho Books.</small>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex flex-wrap gap-2 align-items-center">
            <a href="<?= site_url('settings/integrations/zoho') ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-cog me-1"></i> Configuration
            </a>
            <a href="<?= site_url('settings/integrations/zoho/sync') ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-sync-alt me-1"></i> Sync Dashboard
            </a>
            <a href="<?= site_url('settings/integrations/zoho/unsynced') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-hourglass-half me-1"></i> Unsynced (<?= number_format($totalUnsynced) ?>)
            </a>
            <a href="<?= site_url('settings/integrations/zoho/conflicts') ?>" class="btn btn-sm btn-outline-secondary">
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

<!-- Module Filter & Summary Pills -->
<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-body p-3">
        <form action="<?= site_url('settings/integrations/zoho/unsynced') ?>" method="get" class="row g-2 align-items-end mb-2">
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">Filter by Module</label>
                <select name="module" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Modules with Unsynced Records</option>
                    <?php foreach ($modulesList as $mKey => $mMeta): ?>
                        <?php $cnt = (int) ($moduleStats[$mKey]['unsynced_count'] ?? 0); ?>
                        <option value="<?= esc($mKey) ?>" <?= $moduleFilter === $mKey ? 'selected' : '' ?>>
                            <?= esc($mMeta['label']) ?> (<?= number_format($cnt) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary fw-bold px-3">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <?php if ($moduleFilter !== ''): ?>
                    <a href="<?= site_url('settings/integrations/zoho/unsynced') ?>" class="btn btn-sm btn-outline-secondary">
                        Show All Modules
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <?php
        $nonZeroMods = array_filter($moduleStats, fn($ms) => (int) ($ms['unsynced_count'] ?? 0) > 0);
        ?>
        <?php if (!empty($nonZeroMods)): ?>
            <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                <a href="<?= site_url('settings/integrations/zoho/unsynced') ?>"
                   class="badge text-decoration-none px-2 py-1 <?= $moduleFilter === '' ? 'bg-primary text-white' : 'bg-light text-dark border' ?>">
                    All (<?= number_format($totalUnsynced) ?>)
                </a>
                <?php foreach ($nonZeroMods as $mKey => $ms): ?>
                    <a href="<?= site_url('settings/integrations/zoho/unsynced?module=' . urlencode($mKey)) ?>"
                       class="badge text-decoration-none px-2 py-1 <?= $moduleFilter === $mKey ? 'bg-primary text-white' : 'bg-light text-dark border' ?>">
                        <?= esc($ms['label']) ?>: <strong><?= number_format($ms['unsynced_count']) ?></strong>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Unsynced Records Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h3 class="card-title fw-bold m-0 text-dark">
            <i class="fas fa-list text-primary me-2"></i>
            <?= $moduleFilter !== '' ? esc($modulesList[$moduleFilter]['label']) . ' — ' : '' ?>Unsynced Records (<?= count($records) ?> shown)
        </h3>
        <?php if ($moduleFilter !== '' && !empty($records) && can('setting.edit')): ?>
            <button type="submit" form="bulkSyncUnsyncedForm" class="btn btn-sm btn-success fw-bold" id="bulkPushBtn" disabled>
                <i class="fas fa-cloud-upload-alt me-1"></i> Push Selected to Zoho
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <?php if ($moduleFilter !== ''): ?>
        <form action="<?= site_url('settings/integrations/zoho/sync-records') ?>" method="post" id="bulkSyncUnsyncedForm">
            <?= csrf_field() ?>
            <input type="hidden" name="module" value="<?= esc($moduleFilter) ?>">
            <input type="hidden" name="direction" value="push">
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <?php if ($moduleFilter !== '' && can('setting.edit')): ?>
                            <th width="4%" class="text-center ps-3">
                                <input type="checkbox" class="form-check-input" id="selectAllUnsynced" title="Select All">
                            </th>
                        <?php endif; ?>
                        <th class="ps-3">Module</th>
                        <th>ID</th>
                        <th>Document / Name</th>
                        <th>Date</th>
                        <th class="text-end">Amount (₹)</th>
                        <th class="text-center">ERP Status</th>
                        <th class="text-center">Zoho Status</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="<?= ($moduleFilter !== '' && can('setting.edit')) ? 9 : 8 ?>" class="text-center py-5 text-muted">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <div>All records are synced with Zoho Books.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $rec): ?>
                            <?php
                            $st = strtoupper((string) ($rec['sync_status'] ?? 'NOT_SYNCED'));
                            $stBadge = match ($st) {
                                'FAILED'   => 'bg-danger-subtle text-danger border-danger-subtle',
                                'PENDING'  => 'bg-warning-subtle text-warning border-warning-subtle',
                                'CONFLICT' => 'bg-warning text-dark',
                                'VOIDED'   => 'bg-dark-subtle text-dark',
                                default    => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                            };
                            ?>
                            <tr>
                                <?php if ($moduleFilter !== '' && can('setting.edit')): ?>
                                    <td class="text-center ps-3">
                                        <input type="checkbox" class="form-check-input unsynced-row-cb" name="record_ids[]" value="<?= (int) $rec['id'] ?>">
                                    </td>
                                <?php endif; ?>
                                <td class="ps-3">
                                    <span class="badge bg-light text-dark border"><?= esc($rec['module_label']) ?></span>
                                </td>
                                <td class="text-muted fw-semibold">#<?= (int) $rec['id'] ?></td>
                                <td class="fw-bold">
                                    <?php if (!empty($rec['view_url'])): ?>
                                        <a href="<?= esc($rec['view_url']) ?>" class="text-decoration-none text-primary">
                                            <?= esc($rec['doc_label'] ?: ('#' . $rec['id'])) ?>
                                            <i class="fas fa-external-link-alt small ms-1" style="font-size: 0.7rem;"></i>
                                        </a>
                                    <?php else: ?>
                                        <?= esc($rec['doc_label'] ?: ('#' . $rec['id'])) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    <?= !empty($rec['doc_date']) ? date('d/m/Y', strtotime((string) $rec['doc_date'])) : '&mdash;' ?>
                                </td>
                                <td class="text-end fw-semibold">
                                    <?= $rec['doc_amount'] !== null ? '&#8377;' . number_format((float) $rec['doc_amount'], 2) : '&mdash;' ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($rec['local_status'])): ?>
                                        <span class="badge bg-light text-dark border"><?= esc($rec['local_status']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge border <?= $stBadge ?>"
                                          <?php if (!empty($rec['last_error'])): ?>title="<?= esc($rec['last_error']) ?>"<?php endif; ?>>
                                        <?= esc($st) ?>
                                    </span>
                                    <?php if (!empty($rec['last_error'])): ?>
                                        <div class="small text-danger text-truncate" style="max-width: 200px;" title="<?= esc($rec['last_error']) ?>">
                                            <?= esc($rec['last_error']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3 text-nowrap">
                                    <?php if (!empty($rec['view_url'])): ?>
                                        <a href="<?= esc($rec['view_url']) ?>" class="btn btn-xs btn-outline-secondary" title="Open Record">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (can('setting.edit')): ?>
                                        <a href="<?= site_url('settings/integrations/zoho/sync-record/' . urlencode($rec['module']) . '/' . (int) $rec['id']) ?>"
                                           class="btn btn-xs btn-outline-primary ms-1" title="Sync this record to Zoho Books">
                                            <i class="fas fa-sync-alt me-1"></i> Sync Now
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($moduleFilter !== ''): ?>
        </form>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selAll = document.getElementById('selectAllUnsynced');
    const cbs = document.querySelectorAll('.unsynced-row-cb');
    const bulkBtn = document.getElementById('bulkPushBtn');

    function updateBulkBtn() {
        if (!bulkBtn) return;
        const checked = document.querySelectorAll('.unsynced-row-cb:checked').length;
        bulkBtn.disabled = checked === 0;
        bulkBtn.innerHTML = checked > 0
            ? `<i class="fas fa-cloud-upload-alt me-1"></i> Push Selected (${checked}) to Zoho`
            : `<i class="fas fa-cloud-upload-alt me-1"></i> Push Selected to Zoho`;
    }

    if (selAll) {
        selAll.addEventListener('change', function() {
            cbs.forEach(cb => cb.checked = selAll.checked);
            updateBulkBtn();
        });
    }
    cbs.forEach(cb => cb.addEventListener('change', updateBulkBtn));
});
</script>
<?= $this->endSection() ?>
