<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>GST Report<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-percent text-success me-2"></i>GST Report</h1>
    </div>
    <div class="col-sm-6 text-end d-flex gap-2 justify-content-end">
        <?php
        $printQuery = $_GET;
        $printQuery['print'] = '1';
        $printReportUrl = site_url('reports/gst') . '?' . http_build_query($printQuery);
        ?>
        <a href="<?= esc($printReportUrl) ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="fas fa-print me-1"></i> Print Report</a>
        <a href="<?= site_url('reports') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-chart-bar me-1"></i> Document Reports</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$fmt = static fn($n) => '₹' . number_format((float) $n, 2);
$currentQuery = $_SERVER['QUERY_STRING'] ?? '';
$pdfAllUrl = site_url('reports/gst/invoices-pdf') . ($currentQuery ? '?' . $currentQuery : '');
$netGst = ($totals['tax'] - $salesReturnTotals['tax']) - $billTotals['tax'];
$secPrintUrl = static fn(string $sec) => site_url('reports/gst') . '?' . http_build_query(array_merge($_GET, ['print' => '1', 'section' => $sec]));

ob_start(); ?>
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

<!-- Overall Summary KPIs (Invoices, Bills, Sales Returns, Purchase Returns, Net GST) -->
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <a href="#sec-invoices" class="bt-stat bt-g-blue text-decoration-none">
            <i class="fas fa-file-invoice-dollar bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $fmt($totals['total']) ?></div>
            <div class="bt-stat-label">Invoices (<?= $totals['count'] ?>) &middot; Tax <?= number_format($totals['tax'], 2) ?></div>
        </a>
    </div>
    <div class="col-xl col-sm-6">
        <a href="#sec-bills" class="bt-stat bt-g-purple text-decoration-none">
            <i class="fas fa-file-invoice bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $fmt($billTotals['total']) ?></div>
            <div class="bt-stat-label">Bills (<?= $billTotals['count'] ?>) &middot; Tax <?= number_format($billTotals['tax'], 2) ?></div>
        </a>
    </div>
    <div class="col-xl col-sm-6">
        <a href="#sec-sales-returns" class="bt-stat bt-g-amber text-decoration-none">
            <i class="fas fa-undo-alt bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $fmt($salesReturnTotals['total']) ?></div>
            <div class="bt-stat-label">Sales Returns (<?= $salesReturnTotals['count'] ?>) &middot; Tax <?= number_format($salesReturnTotals['tax'], 2) ?></div>
        </a>
    </div>
    <div class="col-xl col-sm-6">
        <a href="#sec-purchase-returns" class="bt-stat bt-g-red text-decoration-none">
            <i class="fas fa-file-signature bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $fmt($purchaseReturnTotals['amount']) ?></div>
            <div class="bt-stat-label">Purchase Returns (<?= $purchaseReturnTotals['count'] ?>)</div>
        </a>
    </div>
    <div class="col-xl col-sm-6">
        <div class="bt-stat bt-g-green">
            <i class="fas fa-balance-scale bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $fmt($netGst) ?></div>
            <div class="bt-stat-label">Net GST (Out <?= number_format($totals['tax'] - $salesReturnTotals['tax'], 2) ?> &minus; In <?= number_format($billTotals['tax'], 2) ?>)</div>
        </div>
    </div>
</div>

<!-- Quick section links -->
<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
    <span class="small text-muted fw-semibold">Jump to:</span>
    <a href="#sec-invoices" class="btn btn-xs btn-outline-primary">Invoices (<?= count($rows) ?>)</a>
    <a href="#sec-bills" class="btn btn-xs btn-outline-secondary">Bills (<?= count($billRows) ?>)</a>
    <a href="#sec-sales-returns" class="btn btn-xs btn-outline-warning text-dark">Sales Returns (<?= count($salesReturnRows) ?>)</a>
    <a href="#sec-purchase-returns" class="btn btn-xs btn-outline-danger">Purchase Returns (<?= count($purchaseReturnRows) ?>)</a>
</div>

<?php if (!empty($rateSummary)): ?>
<div class="card shadow-sm mb-3">
    <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-layer-group text-info me-2"></i>Invoice Rate-wise Summary</h3></div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>GST Rate</th><th class="text-end">Invoices</th><th class="text-end">Qty Sold</th><th class="text-end">Subtotal</th>
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

<!-- 1. INVOICES -->
<form method="post" action="<?= site_url('reports/gst/invoices-pdf') ?>" target="_blank" id="gstPdfForm">
    <?= csrf_field() ?>
    <div class="card shadow-sm mb-4" id="sec-invoices">
        <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-file-invoice-dollar text-primary me-2"></i>1. Invoices
                <small class="text-muted fw-normal ms-2">(<?= count($rows) ?> &middot; Qty <?= number_format($totals['qty'], 2) ?>)</small>
            </h3>
            <div class="d-flex flex-wrap gap-2 ms-auto">
                <input type="search" class="form-control form-control-sm gst-search" data-target="gstTable" style="width:190px" placeholder="Search invoices...">
                <button type="button" class="btn btn-sm btn-outline-secondary gst-csv" data-target="gstTable" data-name="gst_invoices"><i class="fas fa-file-csv me-1"></i> CSV</button>
                <a href="<?= esc($secPrintUrl('invoices')) ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="fas fa-print me-1"></i> Print Report</a>
                <button type="submit" id="btnPrintSelected" class="btn btn-sm btn-primary" disabled>
                    <i class="fas fa-print me-1"></i> Print Selected PDF (<span id="selCount">0</span>)
                </button>
                <a href="<?= esc($pdfAllUrl) ?>" target="_blank" id="btnPrintAll"
                   class="btn btn-sm btn-success <?= empty($rows) ? 'disabled' : '' ?>"
                   data-count="<?= count($rows) ?>" data-max="<?= (int) $maxPdf ?>">
                    <i class="fas fa-file-pdf me-1"></i> Print All PDF (<?= count($rows) ?>)
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height:55vh;">
                <table class="table table-hover table-sm align-middle mb-0" id="gstTable">
                    <thead class="table-light" style="position:sticky;top:0;z-index:1;">
                        <tr>
                            <th style="width:32px"><input type="checkbox" class="form-check-input" id="chkAll"></th>
                            <th>Date</th><th>Invoice #</th><th>Customer</th><th>GSTIN</th>
                            <th class="text-end">Qty Sold</th><th class="text-end">Subtotal</th><th class="text-end">CGST</th><th class="text-end">SGST</th><th class="text-end">IGST</th><th class="text-end">Total</th>
                            <th class="text-center">PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="12" class="text-center text-muted py-3">No invoices found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $r): ?>
                        <tr class="<?= $r['is_void'] ? 'text-muted text-decoration-line-through' : '' ?>">
                            <td><input type="checkbox" class="form-check-input row-chk" name="ids[]" value="<?= (int) $r['id'] ?>"></td>
                            <td><?= date('d/m/Y', strtotime($r['invoice_date'])) ?></td>
                            <td><a href="<?= site_url('invoices/view/' . $r['id']) ?>" class="fw-semibold"><?= esc($r['invoice_number']) ?></a></td>
                            <td><?= esc($r['customer_name'] ?? '-') ?></td>
                            <td class="small"><?= esc($r['customer_gstin'] ?: '-') ?></td>
                            <td class="text-end"><?= number_format($r['qty'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['taxable'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['cgst'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['sgst'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['igst'], 2) ?></td>
                            <td class="text-end fw-semibold"><?= number_format((float) $r['total_amount'], 2) ?></td>
                            <td class="text-center"><a href="<?= site_url('invoices/print/' . $r['id']) ?>" target="_blank" class="text-danger"><i class="fas fa-file-pdf"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php if (!empty($rows)): ?>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end">Total (excl. Void)</td>
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
    </div>
</form>

<!-- 2. BILLS (PURCHASES) -->
<div class="card shadow-sm mb-4" id="sec-bills">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-file-invoice text-danger me-2"></i>2. Bills (Purchases) <small class="text-muted fw-normal ms-2">(<?= count($billRows) ?>)</small></h3>
        <div class="d-flex gap-2 ms-auto">
            <input type="search" class="form-control form-control-sm gst-search" data-target="billsTable" style="width:190px" placeholder="Search bills...">
            <button type="button" class="btn btn-sm btn-outline-secondary gst-csv" data-target="billsTable" data-name="gst_bills"><i class="fas fa-file-csv me-1"></i> CSV</button>
            <a href="<?= esc($secPrintUrl('bills')) ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="fas fa-print me-1"></i> Print Report</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height:50vh;">
            <table class="table table-hover table-sm align-middle mb-0" id="billsTable">
                <thead class="table-light" style="position:sticky;top:0;z-index:1;">
                    <tr>
                        <th>Date</th><th>Bill #</th><th>Vendor</th><th>GSTIN</th>
                        <th class="text-end">Qty</th><th class="text-end">Subtotal</th><th class="text-end">CGST</th><th class="text-end">SGST</th><th class="text-end">IGST</th><th class="text-end">Total</th>
                        <th class="text-center">PDF</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($billRows)): ?>
                        <tr><td colspan="11" class="text-center text-muted py-3">No bills found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($billRows as $r): ?>
                    <tr class="<?= $r['is_void'] ? 'text-muted text-decoration-line-through' : '' ?>">
                        <td><?= date('d/m/Y', strtotime($r['bill_date'])) ?></td>
                        <td><a href="<?= site_url('bills/view/' . $r['id']) ?>" class="fw-semibold"><?= esc($r['bill_number']) ?></a></td>
                        <td><?= esc($r['vendor_name'] ?? '-') ?></td>
                        <td class="small"><?= esc($r['party_gstin'] ?: '-') ?></td>
                        <td class="text-end"><?= number_format($r['qty'], 2) ?></td>
                        <td class="text-end"><?= number_format($r['taxable'], 2) ?></td>
                        <td class="text-end"><?= number_format($r['cgst'], 2) ?></td>
                        <td class="text-end"><?= number_format($r['sgst'], 2) ?></td>
                        <td class="text-end"><?= number_format($r['igst'], 2) ?></td>
                        <td class="text-end fw-semibold"><?= number_format((float) $r['total_amount'], 2) ?></td>
                        <td class="text-center"><a href="<?= site_url('bills/print/' . $r['id']) ?>" target="_blank" class="text-danger"><i class="fas fa-file-pdf"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if (!empty($billRows)): ?>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="4" class="text-end">Total (excl. Void)</td>
                        <td class="text-end"><?= number_format($billTotals['qty'], 2) ?></td>
                        <td class="text-end"><?= number_format($billTotals['taxable'], 2) ?></td>
                        <td class="text-end"><?= number_format($billTotals['cgst'], 2) ?></td>
                        <td class="text-end"><?= number_format($billTotals['sgst'], 2) ?></td>
                        <td class="text-end"><?= number_format($billTotals['igst'], 2) ?></td>
                        <td class="text-end"><?= number_format($billTotals['total'], 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- 3. SALES RETURNS (CREDIT NOTES) -->
<div class="card shadow-sm mb-4" id="sec-sales-returns">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-undo-alt text-warning me-2"></i>3. Sales Returns (Credit Notes) <small class="text-muted fw-normal ms-2">(<?= count($salesReturnRows) ?>)</small></h3>
        <div class="d-flex gap-2 ms-auto">
            <input type="search" class="form-control form-control-sm gst-search" data-target="srTable" style="width:190px" placeholder="Search sales returns...">
            <button type="button" class="btn btn-sm btn-outline-secondary gst-csv" data-target="srTable" data-name="gst_sales_returns"><i class="fas fa-file-csv me-1"></i> CSV</button>
            <a href="<?= esc($secPrintUrl('sales_returns')) ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="fas fa-print me-1"></i> Print Report</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height:50vh;">
            <table class="table table-hover table-sm align-middle mb-0" id="srTable">
                <thead class="table-light" style="position:sticky;top:0;z-index:1;">
                    <tr>
                        <th>Date</th><th>Return #</th><th>Customer</th><th>GSTIN</th><th>Invoice #</th>
                        <th class="text-end">Qty</th><th class="text-end">Subtotal</th><th class="text-end">Tax</th><th class="text-end">Total</th>
                        <th class="text-center">PDF</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($salesReturnRows)): ?>
                        <tr><td colspan="10" class="text-center text-muted py-3">No sales returns found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($salesReturnRows as $r): ?>
                    <tr class="<?= $r['is_void'] ? 'text-muted text-decoration-line-through' : '' ?>">
                        <td><?= date('d/m/Y', strtotime($r['return_date'])) ?></td>
                        <td><a href="<?= site_url('sales_returns/view/' . $r['id']) ?>" class="fw-semibold"><?= esc($r['return_number']) ?></a></td>
                        <td><?= esc($r['customer_name'] ?? '-') ?></td>
                        <td class="small"><?= esc($r['party_gstin'] ?: '-') ?></td>
                        <td><?= esc($r['invoice_number'] ?: '-') ?></td>
                        <td class="text-end"><?= number_format($r['qty'], 2) ?></td>
                        <td class="text-end"><?= number_format($r['taxable'], 2) ?></td>
                        <td class="text-end"><?= number_format($r['tax'], 2) ?></td>
                        <td class="text-end fw-semibold"><?= number_format((float) $r['total_amount'], 2) ?></td>
                        <td class="text-center"><a href="<?= site_url('sales_returns/print/' . $r['id']) ?>" target="_blank" class="text-danger"><i class="fas fa-file-pdf"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if (!empty($salesReturnRows)): ?>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="5" class="text-end">Total (excl. Void)</td>
                        <td class="text-end"><?= number_format($salesReturnTotals['qty'], 2) ?></td>
                        <td class="text-end"><?= number_format($salesReturnTotals['taxable'], 2) ?></td>
                        <td class="text-end"><?= number_format($salesReturnTotals['tax'], 2) ?></td>
                        <td class="text-end"><?= number_format($salesReturnTotals['total'], 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- 4. PURCHASE RETURNS (DEBIT NOTES) -->
<div class="card shadow-sm mb-4" id="sec-purchase-returns">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-file-signature text-info me-2"></i>4. Purchase Returns (Debit Notes) <small class="text-muted fw-normal ms-2">(<?= count($purchaseReturnRows) ?>)</small></h3>
        <div class="d-flex gap-2 ms-auto">
            <input type="search" class="form-control form-control-sm gst-search" data-target="prTable" style="width:190px" placeholder="Search purchase returns...">
            <button type="button" class="btn btn-sm btn-outline-secondary gst-csv" data-target="prTable" data-name="gst_purchase_returns"><i class="fas fa-file-csv me-1"></i> CSV</button>
            <a href="<?= esc($secPrintUrl('purchase_returns')) ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="fas fa-print me-1"></i> Print Report</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height:50vh;">
            <table class="table table-hover table-sm align-middle mb-0" id="prTable">
                <thead class="table-light" style="position:sticky;top:0;z-index:1;">
                    <tr>
                        <th>Date</th><th>Reference #</th><th>Vendor</th><th>GSTIN</th><th>Bill #</th>
                        <th class="text-end">Amount</th><th class="text-end">Used</th><th class="text-end">Balance</th>
                        <th class="text-center">PDF</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($purchaseReturnRows)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-3">No purchase returns found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($purchaseReturnRows as $r): ?>
                    <tr class="<?= $r['is_void'] ? 'text-muted text-decoration-line-through' : '' ?>">
                        <td><?= $r['credit_note_date'] ? date('d/m/Y', strtotime($r['credit_note_date'])) : '-' ?></td>
                        <td><a href="<?= site_url('vendor_credits/view/' . $r['id']) ?>" class="fw-semibold"><?= esc($r['reference_no'] ?: ('#' . $r['id'])) ?></a></td>
                        <td><?= esc($r['vendor_name'] ?? '-') ?></td>
                        <td class="small"><?= esc($r['party_gstin'] ?: '-') ?></td>
                        <td><?= esc($r['bill_number'] ?: '-') ?></td>
                        <td class="text-end fw-semibold"><?= number_format((float) $r['amount'], 2) ?></td>
                        <td class="text-end text-success"><?= number_format((float) $r['used_amount'], 2) ?></td>
                        <td class="text-end"><?= number_format((float) $r['balance'], 2) ?></td>
                        <td class="text-center"><a href="<?= site_url('vendor_credits/print/' . $r['id']) ?>" target="_blank" class="text-danger"><i class="fas fa-file-pdf"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if (!empty($purchaseReturnRows)): ?>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="5" class="text-end">Total (excl. Void)</td>
                        <td class="text-end"><?= number_format($purchaseReturnTotals['amount'], 2) ?></td>
                        <td class="text-end"><?= number_format($purchaseReturnTotals['used'], 2) ?></td>
                        <td class="text-end"><?= number_format($purchaseReturnTotals['balance'], 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    var table = document.getElementById('gstTable');
    var chkAll = document.getElementById('chkAll');
    var btnSel = document.getElementById('btnPrintSelected');
    var selCount = document.getElementById('selCount');
    var maxPdf = <?= (int) $maxPdf ?>;

    if (table) {
        var visibleChecks = function () {
            return Array.prototype.filter.call(table.querySelectorAll('.row-chk'), function (c) {
                return c.closest('tr').style.display !== 'none';
            });
        };
        var refresh = function () {
            var n = table.querySelectorAll('.row-chk:checked').length;
            selCount.textContent = n;
            btnSel.disabled = n === 0;
        };
        chkAll && chkAll.addEventListener('change', function () {
            visibleChecks().forEach(function (c) { c.checked = chkAll.checked; });
            refresh();
        });
        table.addEventListener('change', function (e) { if (e.target.classList.contains('row-chk')) refresh(); });
        document.getElementById('gstPdfForm').addEventListener('submit', function (e) {
            if (table.querySelectorAll('.row-chk:checked').length > maxPdf) {
                e.preventDefault(); alert('Please select at most ' + maxPdf + ' invoices per PDF.');
            }
        });
    }
    var btnAll = document.getElementById('btnPrintAll');
    btnAll && btnAll.addEventListener('click', function (e) {
        var n = parseInt(btnAll.dataset.count, 10) || 0;
        if (n > maxPdf) { e.preventDefault(); alert('This filter has ' + n + ' invoices. Please narrow it to at most ' + maxPdf + ' per PDF.'); }
    });

    // Per-section instant search
    document.querySelectorAll('.gst-search').forEach(function (inp) {
        inp.addEventListener('input', function () {
            var q = inp.value.trim().toLowerCase();
            var t = document.getElementById(inp.dataset.target);
            if (!t || !t.tBodies.length) return;
            Array.prototype.forEach.call(t.tBodies[0].rows, function (tr) {
                if (tr.cells.length <= 1) return;
                tr.style.display = !q || tr.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
            });
        });
    });

    // Per-section CSV export
    document.querySelectorAll('.gst-csv').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var t = document.getElementById(btn.dataset.target);
            if (!t) return;
            var skipFirst = btn.dataset.target === 'gstTable' ? 1 : 0;
            var esc = function (s) { s = (s || '').replace(/\s+/g, ' ').trim(); return '"' + s.replace(/"/g, '""') + '"'; };
            var lines = [];
            lines.push(Array.prototype.slice.call(t.tHead.rows[0].cells, skipFirst, -1).map(function (c) { return esc(c.textContent); }).join(','));
            Array.prototype.forEach.call(t.tBodies[0].rows, function (tr) {
                if (tr.cells.length <= 1 || tr.style.display === 'none') return;
                lines.push(Array.prototype.slice.call(tr.cells, skipFirst, -1).map(function (c) { return esc(c.textContent); }).join(','));
            });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(new Blob(['\ufeff' + lines.join('\n')], { type: 'text/csv;charset=utf-8;' }));
            a.download = (btn.dataset.name || 'gst_report') + '_<?= esc($filters['date_from'] ?? 'all') ?>_<?= esc($filters['date_to'] ?? 'all') ?>.csv';
            document.body.appendChild(a); a.click(); a.remove();
        });
    });
})();
</script>
<?= $this->endSection() ?>
