<?php
$customerName = '';
if (!empty($filters['party_id'])) {
    foreach ($customers as $c) {
        if ((int) $c['id'] === (int) $filters['party_id']) { $customerName = $c['name']; break; }
    }
}
$df = $filters['date_from'] ?? '';
$dt = $filters['date_to'] ?? '';
$periodLabel = ($df ? date('d M Y', strtotime($df)) : 'Beginning')
    . ' to ' . ($dt ? date('d M Y', strtotime($dt)) : 'Today');
if ($df && $dt && date('Y-m-01', strtotime($df)) === $df && date('Y-m-t', strtotime($df)) === $dt) {
    $forPeriod = 'for the Month of ' . date('F Y', strtotime($df));
} elseif ($df && $dt) {
    $forPeriod = 'from ' . date('d M Y', strtotime($df)) . ' to ' . date('d M Y', strtotime($dt));
} elseif ($df) {
    $forPeriod = 'from ' . date('d M Y', strtotime($df));
} elseif ($dt) {
    $forPeriod = 'up to ' . date('d M Y', strtotime($dt));
} else {
    $forPeriod = '(All Time)';
}
$n = static fn($v) => number_format((float) $v, 2);
$netGst = ($totals['tax'] - $salesReturnTotals['tax']) - $billTotals['tax'];
$sec = $section ?? '';
$showAll = !in_array($sec, ['invoices', 'bills', 'sales_returns', 'purchase_returns'], true);
$secTitles = [
    'invoices'         => 'Sales Invoices ' . $forPeriod,
    'bills'            => 'Purchase Bills ' . $forPeriod,
    'sales_returns'    => 'Sales Returns ' . $forPeriod,
    'purchase_returns' => 'Purchase Returns ' . $forPeriod,
];
$reportHeading = $secTitles[$sec] ?? ('GST Report ' . $forPeriod);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($reportHeading) ?></title>
    <?= view('layouts/_favicon') ?>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 10mm 14mm 10mm;
            @bottom-right {
                content: "Page " counter(page) " of " counter(pages);
                font-family: Helvetica, Arial, sans-serif;
                font-size: 11px;
                font-weight: 600;
                color: #333;
            }
        }
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: 400; color: #111; margin: 0; padding: 14px; }
        .head { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #222; padding-bottom: 8px; margin-bottom: 10px; }
        .head h1 { font-size: 19px; margin: 0 0 2px; font-weight: 700; }
        .head .company { font-size: 14px; font-weight: 700; }
        .meta { font-size: 12.5px; color: #333; text-align: right; line-height: 1.5; font-weight: 400; }
        .kpis { display: flex; gap: 8px; margin-bottom: 10px; }
        .kpi { flex: 1; border: 1px solid #999; border-radius: 4px; padding: 6px 8px; }
        .kpi .l { font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #444; }
        .kpi .v { font-size: 14px; font-weight: 700; }
        h2 { font-size: 14px; font-weight: 700; margin: 14px 0 5px; border-bottom: 1px solid #bbb; padding-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #888; padding: 4px 6px; font-size: 12.5px; font-weight: 400; }
        th { background: #eaeaea; text-align: left; font-size: 12px; font-weight: 700; color: #000; }
        td.r, th.r { text-align: right; }
        .total-row td { font-weight: 700; font-size: 13px; background: #f0f0f0; border-top: 2px solid #222; }
        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
        .void td { color: #999; text-decoration: line-through; }
        .gstin { font-size: 11px; color: #444; font-weight: 400; }
        .toolbar { margin-bottom: 12px; }
        .toolbar button { padding: 6px 14px; font-size: 12px; cursor: pointer; }
        @media print { .toolbar { display: none; } body { padding: 0; } }
    </style>
</head>
<body onload="window.print()">
    <div class="toolbar">
        <button onclick="window.print()">&#128424; Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="head">
        <div>
            <div class="company"><?= esc(get_setting('app_name', 'RasiDev')) ?></div>
            <?php if ($g = get_setting('company_gstin', '')): ?><div>GSTIN: <?= esc($g) ?></div><?php endif; ?>
            <h1><?= esc($reportHeading) ?></h1>
        </div>
        <div class="meta">
            <div><strong>Period:</strong> <?= esc($periodLabel) ?></div>
            <?php if ($customerName): ?><div><strong>Customer:</strong> <?= esc($customerName) ?></div><?php endif; ?>
            <div><strong>Generated:</strong> <?= date('d M Y h:i A') ?></div>
        </div>
    </div>

    <?php if ($showAll): ?>
    <div class="kpis">
        <div class="kpi"><div class="l">Invoices (<?= $totals['count'] ?>)</div><div class="v">₹<?= $n($totals['total']) ?> <small>(Tax ₹<?= $n($totals['tax']) ?>)</small></div></div>
        <div class="kpi"><div class="l">Bills (<?= $billTotals['count'] ?>)</div><div class="v">₹<?= $n($billTotals['total']) ?> <small>(Tax ₹<?= $n($billTotals['tax']) ?>)</small></div></div>
        <div class="kpi"><div class="l">Sales Returns (<?= $salesReturnTotals['count'] ?>)</div><div class="v">₹<?= $n($salesReturnTotals['total']) ?> <small>(Tax ₹<?= $n($salesReturnTotals['tax']) ?>)</small></div></div>
        <div class="kpi"><div class="l">Purchase Returns (<?= $purchaseReturnTotals['count'] ?>)</div><div class="v">₹<?= $n($purchaseReturnTotals['amount']) ?></div></div>
        <div class="kpi"><div class="l">Net GST Payable</div><div class="v">₹<?= $n($netGst) ?></div></div>
    </div>
    <?php endif; ?>

    <?php if (($showAll || $sec === 'invoices') && !empty($rateSummary)): ?>
    <h2>Invoice Rate-wise Summary</h2>
    <table>
        <thead><tr><th>GST Rate</th><th class="r">Invoices</th><th class="r">Qty Sold</th><th class="r">Subtotal</th><th class="r">CGST</th><th class="r">SGST</th><th class="r">IGST</th><th class="r">Total</th></tr></thead>
        <tbody>
        <?php foreach ($rateSummary as $rate => $s): ?>
            <tr><td><?= (int) $rate ?>%</td><td class="r"><?= $s['count'] ?></td><td class="r"><?= $n($s['qty']) ?></td><td class="r"><?= $n($s['taxable']) ?></td><td class="r"><?= $n($s['cgst']) ?></td><td class="r"><?= $n($s['sgst']) ?></td><td class="r"><?= $n($s['igst']) ?></td><td class="r"><?= $n($s['total']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php if ($showAll || $sec === 'invoices'): ?>
    <h2>1. <?= esc($secTitles['invoices']) ?> (<?= count($rows) ?>)</h2>
    <table>
        <thead><tr><th>#</th><th>Date</th><th>Invoice #</th><th>Customer / GSTIN</th><th class="r">Qty Sold</th><th class="r">Subtotal</th><th class="r">CGST</th><th class="r">SGST</th><th class="r">IGST</th><th class="r">Total</th></tr></thead>
        <tbody>
        <?php if (empty($rows)): ?><tr><td colspan="10" style="text-align:center;">No invoices found.</td></tr><?php endif; ?>
        <?php foreach ($rows as $i => $r): ?>
            <tr class="<?= $r['is_void'] ? 'void' : '' ?>">
                <td><?= $i + 1 ?></td><td><?= date('d/m/Y', strtotime($r['invoice_date'])) ?></td><td><?= esc($r['invoice_number']) ?></td>
                <td><?= esc($r['customer_name'] ?? '-') ?><?= !empty($r['customer_gstin']) ? ' <span class="gstin">(' . esc($r['customer_gstin']) . ')</span>' : '' ?></td>
                <td class="r"><?= $n($r['qty']) ?></td><td class="r"><?= $n($r['taxable']) ?></td><td class="r"><?= $n($r['cgst']) ?></td><td class="r"><?= $n($r['sgst']) ?></td><td class="r"><?= $n($r['igst']) ?></td><td class="r"><?= $n($r['total_amount']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!empty($rows)): ?>
        <tr class="total-row"><td colspan="4" class="r">Total (excl. Void)</td><td class="r"><?= $n($totals['qty']) ?></td><td class="r"><?= $n($totals['taxable']) ?></td><td class="r"><?= $n($totals['cgst']) ?></td><td class="r"><?= $n($totals['sgst']) ?></td><td class="r"><?= $n($totals['igst']) ?></td><td class="r"><?= $n($totals['total']) ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php endif; ?>

    <?php if ($showAll || $sec === 'bills'): ?>
    <h2>2. <?= esc($secTitles['bills']) ?> (<?= count($billRows) ?>)</h2>
    <table>
        <thead><tr><th>#</th><th>Date</th><th>Bill #</th><th>Vendor / GSTIN</th><th class="r">Qty</th><th class="r">Subtotal</th><th class="r">CGST</th><th class="r">SGST</th><th class="r">IGST</th><th class="r">Total</th></tr></thead>
        <tbody>
        <?php if (empty($billRows)): ?><tr><td colspan="10" style="text-align:center;">No bills found.</td></tr><?php endif; ?>
        <?php foreach ($billRows as $i => $r): ?>
            <tr class="<?= $r['is_void'] ? 'void' : '' ?>">
                <td><?= $i + 1 ?></td><td><?= date('d/m/Y', strtotime($r['bill_date'])) ?></td><td><?= esc($r['bill_number']) ?></td>
                <td><?= esc($r['vendor_name'] ?? '-') ?><?= !empty($r['party_gstin']) ? ' <span class="gstin">(' . esc($r['party_gstin']) . ')</span>' : '' ?></td>
                <td class="r"><?= $n($r['qty']) ?></td><td class="r"><?= $n($r['taxable']) ?></td><td class="r"><?= $n($r['cgst']) ?></td><td class="r"><?= $n($r['sgst']) ?></td><td class="r"><?= $n($r['igst']) ?></td><td class="r"><?= $n($r['total_amount']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!empty($billRows)): ?>
        <tr class="total-row"><td colspan="4" class="r">Total (excl. Void)</td><td class="r"><?= $n($billTotals['qty']) ?></td><td class="r"><?= $n($billTotals['taxable']) ?></td><td class="r"><?= $n($billTotals['cgst']) ?></td><td class="r"><?= $n($billTotals['sgst']) ?></td><td class="r"><?= $n($billTotals['igst']) ?></td><td class="r"><?= $n($billTotals['total']) ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php endif; ?>

    <?php if ($showAll || $sec === 'sales_returns'): ?>
    <h2>3. <?= esc($secTitles['sales_returns']) ?> (<?= count($salesReturnRows) ?>)</h2>
    <table>
        <thead><tr><th>#</th><th>Date</th><th>Return #</th><th>Customer / GSTIN</th><th>Invoice #</th><th class="r">Qty</th><th class="r">Subtotal</th><th class="r">Tax</th><th class="r">Total</th></tr></thead>
        <tbody>
        <?php if (empty($salesReturnRows)): ?><tr><td colspan="9" style="text-align:center;">No sales returns found.</td></tr><?php endif; ?>
        <?php foreach ($salesReturnRows as $i => $r): ?>
            <tr class="<?= $r['is_void'] ? 'void' : '' ?>">
                <td><?= $i + 1 ?></td><td><?= date('d/m/Y', strtotime($r['return_date'])) ?></td><td><?= esc($r['return_number']) ?></td>
                <td><?= esc($r['customer_name'] ?? '-') ?><?= !empty($r['party_gstin']) ? ' <span class="gstin">(' . esc($r['party_gstin']) . ')</span>' : '' ?></td><td><?= esc($r['invoice_number'] ?: '-') ?></td>
                <td class="r"><?= $n($r['qty']) ?></td><td class="r"><?= $n($r['taxable']) ?></td><td class="r"><?= $n($r['tax']) ?></td><td class="r"><?= $n($r['total_amount']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!empty($salesReturnRows)): ?>
        <tr class="total-row"><td colspan="5" class="r">Total (excl. Void)</td><td class="r"><?= $n($salesReturnTotals['qty']) ?></td><td class="r"><?= $n($salesReturnTotals['taxable']) ?></td><td class="r"><?= $n($salesReturnTotals['tax']) ?></td><td class="r"><?= $n($salesReturnTotals['total']) ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php endif; ?>

    <?php if ($showAll || $sec === 'purchase_returns'): ?>
    <h2>4. <?= esc($secTitles['purchase_returns']) ?> (<?= count($purchaseReturnRows) ?>)</h2>
    <table>
        <thead><tr><th>#</th><th>Date</th><th>Reference #</th><th>Vendor / GSTIN</th><th>Bill #</th><th class="r">Amount</th><th class="r">Used</th><th class="r">Balance</th></tr></thead>
        <tbody>
        <?php if (empty($purchaseReturnRows)): ?><tr><td colspan="8" style="text-align:center;">No purchase returns found.</td></tr><?php endif; ?>
        <?php foreach ($purchaseReturnRows as $i => $r): ?>
            <tr class="<?= $r['is_void'] ? 'void' : '' ?>">
                <td><?= $i + 1 ?></td><td><?= $r['credit_note_date'] ? date('d/m/Y', strtotime($r['credit_note_date'])) : '-' ?></td>
                <td><?= esc($r['reference_no'] ?: ('#' . $r['id'])) ?></td>
                <td><?= esc($r['vendor_name'] ?? '-') ?><?= !empty($r['party_gstin']) ? ' <span class="gstin">(' . esc($r['party_gstin']) . ')</span>' : '' ?></td>
                <td><?= esc($r['bill_number'] ?: '-') ?></td>
                <td class="r"><?= $n($r['amount']) ?></td><td class="r"><?= $n($r['used_amount']) ?></td><td class="r"><?= $n($r['balance']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!empty($purchaseReturnRows)): ?>
        <tr class="total-row"><td colspan="5" class="r">Total (excl. Void)</td><td class="r"><?= $n($purchaseReturnTotals['amount']) ?></td><td class="r"><?= $n($purchaseReturnTotals['used']) ?></td><td class="r"><?= $n($purchaseReturnTotals['balance']) ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php endif; ?>
</body>
</html>
