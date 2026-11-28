<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>GST Report<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-percent text-success me-2"></i>GST Report</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('reports') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-chart-bar me-1"></i> Document Reports</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$fmt = static fn($n) => 'â‚¹' . number_format((float) $n, 2);
$currentQuery = $_SERVER['QUERY_STRING'] ?? '';
$pdfAllUrl = site_url('reports/gst/invoices-pdf') . ($currentQuery ? '?' . $currentQuery : '');
$printQuery = $_GET;
$printQuery['print'] = '1';
$printReportUrl = site_url('reports/gst') . '?' . http_build_query($printQuery);

ob_start(); ?>
<div class="col-lg-1 col-md-4">
    <label class="form-label small fw-semibold mb-1">Type</label>
    <select name="gst_type" class="form-select form-select-sm">
        <option value="">All</option>
        <option value="b2b" <?= $gstType === 'b2b' ? 'selected' : '' ?>>B2B</option>
        <option value="b2c" <?= $gstType === 'b2c' ? 'selected' : '' ?>>B2C</option>
    </select>
</div>
<div class="col-lg-auto col-md-4">
    <div class="form-check mb-1">
        <input class="form-check-input" type="checkbox" name="include_void" value="1" id="incVoid" <?= $includeVoid ? 'checked' : '' ?>>
        <label class="form-check-label small" for="incVoid">Include Void/Draft</label>
    </div>
</div>
<?php $extra = ob_get_clean(); ?>

<?= view('reports/_filters', [
    'action'        => site_url('reports/gst'),
    'filters'       => $filters,
    'periodOptions' => $periodOptions,
    'parties'       => $customers,
    'partyLabel'    => 'Customer',
    'statuses'      => $statuses,
    'extra'         => $extra,
]) ?>

<!-- KPIs -->
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="bt-stat bt-g-blue">
            <i class="fas fa-file-invoice bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totals['count']) ?></div>
            <div class="bt-stat-label">Qty Sold <?= number_format($totals['qty'], 2) ?> &middot; B2B <?= $totals['b2b'] ?> / B2C <?= $totals['b2c'] ?></div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="bt-stat bt-g-purple">
            <i class="fas fa-calculator bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $fmt($totals['taxable']) ?></div>
            <div class="bt-stat-label">Taxable Value</div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="bt-stat bt-g-amber">
            <i class="fas fa-balance-scale bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $fmt($totals['cgst'] + $totals['sgst']) ?></div>
            <div class="bt-stat-label">CGST <?= number_format($totals['cgst'], 2) ?> + SGST <?= number_format($totals['sgst'], 2) ?></div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="bt-stat bt-g-red">
            <i class="fas fa-globe-asia bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $fmt($totals['igst']) ?></div>
            <div class="bt-stat-label">IGST</div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="bt-stat bt-g-green">
            <i class="fas fa-rupee-sign bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $fmt($totals['total']) ?></div>
            <div class="bt-stat-label">Invoice Value (Tax <?= number_format($totals['tax'], 2) ?>)</div>
        </div>
    </div>
</div>

<?php if (!empty($rateSummary)): ?>
<div class="card shadow-sm mb-3">
    <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-layer-group text-info me-2"></i>Rate-wise Summary</h3></div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>GST Rate</th><th class="text-end">Invoices</th><th class="text-end">Qty Sold</th><th class="text-end">Taxable</th>
                    <th class="text-end">CGST</th><th class="text-end">SGST</th><th class="text-end">IGST</th><th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rateSummary as $rate => $s): ?>
                <tr>
                    <td><?= (int) $rate ?>%</td>
                    <td class="text-end"><?= $s['count'] ?></td>
                    <td class="text-end"><?= number_format($s['qty'], 2) ?></td>
                    <td class="text-end"><?= number_format($s['taxable'], 2) ?></td>
                    <td class="text-end"><?= number_format($s['cgst'], 2) ?></td>
                    <td class="text-end"><?= number_format($s['sgst'], 2) ?></td>
                    <td class="text-end"><?= number_format($s['igst'], 2) ?></td>
                    <td class="text-end fw-semibold"><?= number_format($s['total'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<form method="post" action="<?= site_url('reports/gst/invoices-pdf') ?>" target="_blank" id="gstPdfForm">
    <?= csrf_field() ?>
    <div class="card shadow-sm">
        <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-list text-primary me-2"></i>Invoices
                <small class="text-muted fw-normal ms-2">
                    <?= $filters['date_from'] ? date('d M Y', strtotime($filters['date_from'])) : 'Beginning' ?> &ndash;
                    <?= $filters['date_to'] ? date('d M Y', strtotime($filters['date_to'])) : 'Today' ?>
                </small>
            </h3>
            <div class="d-flex flex-wrap gap-2 ms-auto">
                <input type="search" id="gstSearch" class="form-control form-control-sm" style="width:200px" placeholder="Search invoice / customer / GSTIN">
                <button type="button" id="gstCsv" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-csv me-1"></i> CSV</button>
                <a href="<?= esc($printReportUrl) ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="fas fa-print me-1"></i> Print Report</a>
                <button type="submit" id="btnPrintSelected" class="btn btn-sm btn-primary" disabled>
                    <i class="fas fa-print me-1"></i> Print Selected PDF (<span id="selCount">0</span>)
                </button>
                <a href="<?= esc($pdfAllUrl) ?>" target="_blank" id="btnPrintAll"
                   class="btn btn-sm btn-success <?= ($totals['count'] === 0 && !$includeVoid) || empty($rows) ? 'disabled' : '' ?>"
                   data-count="<?= count($rows) ?>" data-max="<?= (int) $maxPdf ?>">
                    <i class="fas fa-file-pdf me-1"></i> Print All PDF (<?= count($rows) ?>)
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height:65vh;">
                <table class="table table-hover table-sm align-middle mb-0" id="gstTable">
                    <thead class="table-light" style="position:sticky;top:0;z-index:1;">
                        <tr>
                            <th style="width:32px"><input type="checkbox" class="form-check-input" id="chkAll"></th>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>GSTIN</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-end">Qty Sold</th>
                            <th class="text-end">Taxable</th>
                            <th class="text-end">CGST</th>
                            <th class="text-end">SGST</th>
                            <th class="text-end">IGST</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="14" class="text-center text-muted py-4">No invoices found for the selected period.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $r): ?>
                        <tr class="<?= $r['is_void'] ? 'text-muted text-decoration-line-through' : '' ?>">
                            <td><input type="checkbox" class="form-check-input row-chk" name="ids[]" value="<?= (int) $r['id'] ?>"></td>
                            <td><?= date('d/m/Y', strtotime($r['invoice_date'])) ?></td>
                            <td><a href="<?= site_url('invoices/view/' . $r['id']) ?>" class="fw-semibold"><?= esc($r['invoice_number']) ?></a></td>
                            <td><?= esc($r['customer_name'] ?? '-') ?></td>
                            <td class="small"><?= esc($r['customer_gstin'] ?: '-') ?></td>
                            <td><span class="badge <?= $r['is_b2b'] ? 'bg-primary' : 'bg-secondary' ?>"><?= $r['is_b2b'] ? 'B2B' : 'B2C' ?></span></td>
                            <td><?= esc($r['status']) ?></td>
                            <td class="text-end"><?= number_format($r['qty'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['taxable'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['cgst'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['sgst'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['igst'], 2) ?></td>
                            <td class="text-end fw-semibold"><?= number_format((float) $r['total_amount'], 2) ?></td>
                            <td class="text-center"><a href="<?= site_url('invoices/print/' . $r['id']) ?>" target="_blank" class="text-danger" title="Open PDF"><i class="fas fa-file-pdf"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php if (!empty($rows)): ?>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="7" class="text-end">Total (excl. Void)</td>
                            <td class="text-end"><?= number_format($totals['qty'], 2) ?></td>
                            <td class="text-end"><?= number_format($totals['taxable'], 2) ?></td>
                            <td class="text-end"><?= number_format($totals['cgst'], 2) ?></td>
                            <td class="text-end"><?= number_format($totals['sgst'], 2) ?></td>
                            <td class="text-end"><?= number_format($totals['igst'], 2) ?></td>
                            <td class="text-end"><?= number_format($totals['total'], 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        <div class="card-footer small text-muted">
            <i class="fas fa-info-circle me-1"></i>
            Select invoices and click <strong>Print Selected PDF</strong>, or use <strong>Print All PDF</strong> to merge every invoice in the current filter into one PDF (max <?= (int) $maxPdf ?> per PDF). Large batches may take a minute to generate.
        </div>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    var table = document.getElementById('gstTable');
    if (!table) return;
    var chkAll = document.getElementById('chkAll');
    var btnSel = document.getElementById('btnPrintSelected');
    var selCount = document.getElementById('selCount');
    var maxPdf = <?= (int) $maxPdf ?>;

    function visibleChecks() {
        return Array.prototype.filter.call(table.querySelectorAll('.row-chk'), function (c) {
            return c.closest('tr').style.display !== 'none';
        });
    }
    function refresh() {
        var n = table.querySelectorAll('.row-chk:checked').length;
        selCount.textContent = n;
        btnSel.disabled = n === 0;
    }
    chkAll && chkAll.addEventListener('change', function () {
        visibleChecks().forEach(function (c) { c.checked = chkAll.checked; });
        refresh();
    });
    table.addEventListener('change', function (e) { if (e.target.classList.contains('row-chk')) refresh(); });

    document.getElementById('gstPdfForm').addEventListener('submit', function (e) {
        var n = table.querySelectorAll('.row-chk:checked').length;
        if (n > maxPdf) { e.preventDefault(); alert('Please select at most ' + maxPdf + ' invoices per PDF.'); }
    });
    var btnAll = document.getElementById('btnPrintAll');
    btnAll && btnAll.addEventListener('click', function (e) {
        var n = parseInt(btnAll.dataset.count, 10) || 0;
        if (n > maxPdf) { e.preventDefault(); alert('This filter has ' + n + ' invoices. Please narrow it to at most ' + maxPdf + ' per PDF.'); }
    });

    // Instant search
    document.getElementById('gstSearch').addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        Array.prototype.forEach.call(table.tBodies[0].rows, function (tr) {
            if (!tr.querySelector('.row-chk')) return;
            tr.style.display = !q || tr.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
        });
    });

    // CSV export (visible rows)
    document.getElementById('gstCsv').addEventListener('click', function () {
        var esc = function (s) { s = (s || '').replace(/\s+/g, ' ').trim(); return '"' + s.replace(/"/g, '""') + '"'; };
        var lines = [];
        var heads = Array.prototype.slice.call(table.tHead.rows[0].cells, 1, -1).map(function (c) { return esc(c.textContent); });
        lines.push(heads.join(','));
        Array.prototype.forEach.call(table.tBodies[0].rows, function (tr) {
            if (!tr.querySelector('.row-chk') || tr.style.display === 'none') return;
            lines.push(Array.prototype.slice.call(tr.cells, 1, -1).map(function (c) { return esc(c.textContent); }).join(','));
        });
        var blob = new Blob(['\ufeff' + lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'gst_report_<?= esc($filters['date_from'] ?? 'all') ?>_<?= esc($filters['date_to'] ?? 'all') ?>.csv';
        document.body.appendChild(a); a.click(); a.remove();
    });
})();
</script>
<?= $this->endSection() ?>
