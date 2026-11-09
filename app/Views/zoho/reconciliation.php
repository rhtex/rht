<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Zoho Books Reconciliation Report<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="bt-hero">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <span class="bt-eyebrow"><i class="fas fa-balance-scale"></i> Settings &rarr; Integrations &rarr; Zoho Books &rarr; Reconciliation</span>
            <h1><i class="fas fa-check-double"></i> CI4 ERP vs Zoho Books Reconciliation</h1>
            <p>Compare record counts, invoice/bill totals, and customer/supplier balances across both applications.</p>
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
            <a href="<?= site_url('settings/integrations/zoho/reconciliation') ?>" class="btn btn-light fw-bold shadow-sm">
                <i class="fas fa-balance-scale text-primary me-1"></i> Reconciliation
            </a>
            <a href="<?= site_url('settings/integrations/zoho/logs') ?>" class="btn btn-outline-light fw-bold">
                <i class="fas fa-history me-1"></i> Audit Logs
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-clipboard-check text-primary me-2"></i>Reconciliation Summary Matrix</h5>
            <small class="text-muted">Generated at <?= esc($report['generated_at'] ?? date('Y-m-d H:i:s')) ?> &bull; Live Zoho API Queried: <strong><?= !empty($report['live_zoho_queried']) ? 'Yes' : 'Local Mapping Snapshot' ?></strong></small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('settings/integrations/zoho/reconciliation?live=1') ?>" class="btn btn-sm btn-primary fw-bold">
                <i class="fas fa-sync me-1"></i> Re-Run Live Reconciliation
            </a>
            <form action="<?= site_url('settings/integrations/zoho/full-sync') ?>" method="post" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-success fw-bold">
                    <i class="fas fa-exchange-alt me-1"></i> Run Full Bidirectional Sync
                </button>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Module</th>
                        <th class="text-center">Local ERP Count</th>
                        <th class="text-center">Mapped / Zoho Count</th>
                        <th class="text-center">Unmapped</th>
                        <th class="text-end">Local Financial Total</th>
                        <th class="text-end">Zoho Financial Total</th>
                        <th class="text-end">Variance</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($report['rows'] ?? []) as $modKey => $r): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?= esc($r['label']) ?></div>
                                <div class="text-muted small"><?= esc($r['amount_label']) ?></div>
                            </td>
                            <td class="text-center fw-semibold"><?= number_format($r['local_count']) ?></td>
                            <td class="text-center fw-semibold text-primary"><?= number_format($r['zoho_count']) ?></td>
                            <td class="text-center">
                                <?php if ($r['unmapped_count'] > 0): ?>
                                    <span class="badge bg-warning-subtle text-warning border px-2 py-1"><?= number_format($r['unmapped_count']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-semibold">
                                <?= $r['amount_label'] !== 'N/A' ? '&#8377;' . number_format($r['local_total'], 2) : '&mdash;' ?>
                            </td>
                            <td class="text-end fw-semibold">
                                <?= $r['amount_label'] !== 'N/A' ? '&#8377;' . number_format($r['zoho_total'], 2) : '&mdash;' ?>
                            </td>
                            <td class="text-end">
                                <?php if ($r['amount_label'] === 'N/A'): ?>
                                    &mdash;
                                <?php elseif (abs($r['amount_diff']) < 1.00): ?>
                                    <span class="text-success fw-bold">&#8377;0.00</span>
                                <?php else: ?>
                                    <span class="text-danger fw-bold">&#8377;<?= number_format($r['amount_diff'], 2) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($r['is_matched'])): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="fas fa-check-circle me-1"></i>Reconciled
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>Needs Sync
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <form action="<?= site_url('settings/integrations/zoho/sync-module') ?>" method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="module" value="<?= esc($modKey) ?>">
                                    <input type="hidden" name="direction" value="bidirectional">
                                    <button type="submit" class="btn btn-xs btn-outline-primary">
                                        <i class="fas fa-sync-alt me-1"></i> Sync Module
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

