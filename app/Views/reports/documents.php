<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-chart-bar text-primary me-2"></i>Reports</h1>
    </div>
    <div class="col-sm-6 text-end">
        <?php if (can('invoice.view')): ?>
        <a href="<?= site_url('reports/gst') ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-percent me-1"></i> GST Report</a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$fmt = static fn($n) => 'â‚¹' . number_format((float) $n, 2);
$isDebit  = $type === 'debit-notes';
$isCredit = $type === 'credit-notes';
$qs = http_build_query(array_filter([
    'period'    => $filters['period'],
    'date_from' => $filters['period'] === 'custom' ? $filters['date_from'] : null,
    'date_to'   => $filters['period'] === 'custom' ? $filters['date_to'] : null,
]));
?>

<!-- Report type tabs -->
<ul class="nav nav-pills mb-3 gap-1">
    <?php foreach ($definitions as $k => $d): if (!can($d['permission'])) continue; ?>
        <li class="nav-item">
            <a class="nav-link <?= $k === $type ? 'active' : 'bg-body-secondary' ?>" href="<?= site_url('reports/' . $k) . ($qs ? '?' . $qs : '') ?>">
                <i class="fas <?= $d['icon'] ?> me-1"></i><?= esc(str_replace(' Report', '', $d['title'])) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<?= view('reports/_filters', [
    'action'        => site_url('reports/' . $type),
    'filters'       => $filters,
    'periodOptions' => $periodOptions,
    'parties'       => $parties,
    'partyLabel'    => $def['party_label'],
    'statuses'      => $statuses,
]) ?>

<!-- KPIs -->
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="bt-stat bt-g-blue">
            <i class="fas fa-hashtag bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totals['count']) ?></div>
            <div class="bt-stat-label">Documents</div>
        </div>
    </div>
    <?php if (!$isDebit): ?>
    <div class="col-xl col-sm-6">
        <div class="bt-stat bt-g-purple">
            <i class="fas fa-calculator bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $fmt($totals['subtotal']) ?></div>
            <div class="bt-stat-label">Taxable Value</div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="bt-stat bt-g-amber">
            <i class="fas fa-percent bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $fmt($totals['tax']) ?></div>
            <div class="bt-stat-label">GST / Tax</div>
        </div>
    </div>
    <?php endif; ?>
    <div class="col-xl col-sm-6">
        <div class="bt-stat bt-g-green">
            <i class="fas fa-rupee-sign bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $fmt($totals['total']) ?></div>
            <div class="bt-stat-label">Total Amount</div>
        </div>
    </div>
    <?php if (!$isCredit): ?>
    <div class="col-xl col-sm-6">
        <div class="bt-stat bt-g-red">
            <i class="fas fa-hourglass-half bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $fmt($totals['balance']) ?></div>
            <div class="bt-stat-label"><?= $isDebit ? 'Unused Balance' : 'Balance Due' ?></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas <?= $def['icon'] ?> text-<?= $def['color'] ?> me-2"></i><?= esc($def['title']) ?></h3>
        <small class="text-muted ms-auto">
            <?= $filters['date_from'] ? date('d M Y', strtotime($filters['date_from'])) : 'Beginning' ?>
            &ndash;
            <?= $filters['date_to'] ? date('d M Y', strtotime($filters['date_to'])) : 'Today' ?>
        </small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th><?= $isDebit ? 'Reference #' : 'Number' ?></th>
                        <th><?= esc($def['party_label']) ?></th>
                        <?php if ($isCredit || $isDebit): ?><th><?= $isCredit ? 'Invoice #' : 'Bill #' ?></th><?php endif; ?>
                        <th>Status</th>
                        <?php if (!$isDebit): ?>
                            <th class="text-end">Taxable</th>
                            <th class="text-end">Tax</th>
                        <?php endif; ?>
                        <th class="text-end">Total</th>
                        <?php if (!$isCredit): ?>
                            <th class="text-end"><?= $isDebit ? 'Used' : 'Paid' ?></th>
                            <th class="text-end">Balance</th>
                        <?php endif; ?>
                        <th class="text-center no-export">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="11" class="text-center text-muted py-4">No records found for the selected filters.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($items as $it): ?>
                        <tr class="<?= $it['is_void'] ? 'text-muted text-decoration-line-through' : '' ?>">
                            <td><?= $it['date'] ? date('d/m/Y', strtotime($it['date'])) : '-' ?></td>
                            <td><a href="<?= site_url($def['view_url'] . $it['id']) ?>" class="fw-semibold"><?= esc($it['number'] ?: ('#' . $it['id'])) ?></a></td>
                            <td><?= esc($it['party_name']) ?></td>
                            <?php if ($isCredit || $isDebit): ?><td><?= esc($it['ref_doc'] ?: '-') ?></td><?php endif; ?>
                            <td><span class="badge bg-secondary-subtle text-secondary-emphasis"><?= esc($it['status']) ?></span></td>
                            <?php if (!$isDebit): ?>
                                <td class="text-end"><?= number_format($it['subtotal'], 2) ?></td>
                                <td class="text-end"><?= number_format($it['tax'], 2) ?></td>
                            <?php endif; ?>
                            <td class="text-end fw-semibold"><?= number_format($it['total'], 2) ?></td>
                            <?php if (!$isCredit): ?>
                                <td class="text-end text-success"><?= number_format($it['paid'], 2) ?></td>
                                <td class="text-end <?= $it['balance'] > 0 ? 'text-danger' : '' ?>"><?= number_format($it['balance'], 2) ?></td>
                            <?php endif; ?>
                            <td class="text-center text-nowrap">
                                <a href="<?= site_url($def['view_url'] . $it['id']) ?>" class="btn btn-xs btn-outline-primary py-0 px-1" title="View"><i class="fas fa-eye"></i></a>
                                <a href="<?= site_url($def['print_url'] . $it['id']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary py-0 px-1" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if (!empty($items)): ?>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="<?= ($isCredit || $isDebit) ? 5 : 4 ?>" class="text-end">Total (excl. Void)</td>
                        <?php if (!$isDebit): ?>
                            <td class="text-end"><?= number_format($totals['subtotal'], 2) ?></td>
                            <td class="text-end"><?= number_format($totals['tax'], 2) ?></td>
                        <?php endif; ?>
                        <td class="text-end"><?= number_format($totals['total'], 2) ?></td>
                        <?php if (!$isCredit): ?>
                            <td class="text-end"><?= number_format($totals['paid'], 2) ?></td>
                            <td class="text-end"><?= number_format($totals['balance'], 2) ?></td>
                        <?php endif; ?>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
