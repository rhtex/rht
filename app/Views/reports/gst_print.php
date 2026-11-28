<?php
$customerName = '';
if (!empty($filters['party_id'])) {
    foreach ($customers as $c) {
        if ((int) $c['id'] === (int) $filters['party_id']) { $customerName = $c['name']; break; }
    }
}
$periodLabel = ($filters['date_from'] ? date('d M Y', strtotime($filters['date_from'])) : 'Beginning')
    . ' to ' . ($filters['date_to'] ? date('d M Y', strtotime($filters['date_to'])) : 'Today');
$n = static fn($v) => number_format((float) $v, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GST Report - <?= esc($periodLabel) ?></title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 16px; }
        .head { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 10px; }
        .head h1 { font-size: 18px; margin: 0 0 2px; }
        .head .company { font-size: 13px; font-weight: bold; }
        .meta { font-size: 11px; color: #555; text-align: right; line-height: 1.5; }
        .kpis { display: flex; gap: 8px; margin-bottom: 10px; }
        .kpi { flex: 1; border: 1px solid #ccc; border-radius: 4px; padding: 6px 8px; }
        .kpi .l { font-size: 9px; text-transform: uppercase; color: #666; }
        .kpi .v { font-size: 13px; font-weight: bold; }
        h2 { font-size: 12px; margin: 12px 0 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #bbb; padding: 3px 5px; }
        th { background: #eee; text-align: left; font-size: 10px; }
        td.r, th.r { text-align: right; }
        tfoot td { font-weight: bold; background: #f5f5f5; }
        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
        .void td { color: #999; text-decoration: line-through; }
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
            <h1>GST Report (Sales Invoices)</h1>
        </div>
        <div class="meta">
            <div><strong>Period:</strong> <?= esc($periodLabel) ?></div>
            <?php if ($customerName): ?><div><strong>Customer:</strong> <?= esc($customerName) ?></div><?php endif; ?>
            <?php if ($gstType): ?><div><strong>Type:</strong> <?= strtoupper(esc($gstType)) ?></div><?php endif; ?>
            <?php if ($filters['status'] !== ''): ?><div><strong>Status:</strong> <?= esc($filters['status']) ?></div><?php endif; ?>
            <div><strong>Generated:</strong> <?= date('d M Y h:i A') ?></div>
        </div>
    </div>

    <div class="kpis">
        <div class="kpi"><div class="l">Invoices</div><div class="v"><?= $totals['count'] ?> <small>(B2B <?= $totals['b2b'] ?> / B2C <?= $totals['b2c'] ?>)</small></div></div>
        <div class="kpi"><div class="l">Taxable Value</div><div class="v">₹<?= $n($totals['taxable']) ?></div></div>
        <div class="kpi"><div class="l">CGST</div><div class="v">₹<?= $n($totals['cgst']) ?></div></div>
        <div class="kpi"><div class="l">SGST</div><div class="v">₹<?= $n($totals['sgst']) ?></div></div>
        <div class="kpi"><div class="l">IGST</div><div class="v">₹<?= $n($totals['igst']) ?></div></div>
        <div class="kpi"><div class="l">Total Tax</div><div class="v">₹<?= $n($totals['tax']) ?></div></div>
        <div class="kpi"><div class="l">Invoice Value</div><div class="v">₹<?= $n($totals['total']) ?></div></div>
    </div>

    <?php if (!empty($rateSummary)): ?>
    <h2>Rate-wise Summary</h2>
    <table>
        <thead><tr><th>GST Rate</th><th class="r">Invoices</th><th class="r">Taxable</th><th class="r">CGST</th><th class="r">SGST</th><th class="r">IGST</th><th class="r">Total</th></tr></thead>
        <tbody>
        <?php foreach ($rateSummary as $rate => $s): ?>
            <tr><td><?= (int) $rate ?>%</td><td class="r"><?= $s['count'] ?></td><td class="r"><?= $n($s['taxable']) ?></td><td class="r"><?= $n($s['cgst']) ?></td><td class="r"><?= $n($s['sgst']) ?></td><td class="r"><?= $n($s['igst']) ?></td><td class="r"><?= $n($s['total']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <h2>Invoice Details</h2>
    <table>
        <thead>
            <tr>
                <th>#</th><th>Date</th><th>Invoice #</th><th>Customer</th><th>GSTIN</th><th>Type</th><th>Status</th>
                <th class="r">Taxable</th><th class="r">CGST</th><th class="r">SGST</th><th class="r">IGST</th><th class="r">Total</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="12" style="text-align:center;padding:12px;">No invoices found for the selected period.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $i => $r): ?>
            <tr class="<?= $r['is_void'] ? 'void' : '' ?>">
                <td><?= $i + 1 ?></td>
                <td><?= date('d/m/Y', strtotime($r['invoice_date'])) ?></td>
                <td><?= esc($r['invoice_number']) ?></td>
                <td><?= esc($r['customer_name'] ?? '-') ?></td>
                <td><?= esc($r['customer_gstin'] ?: '-') ?></td>
                <td><?= $r['is_b2b'] ? 'B2B' : 'B2C' ?></td>
                <td><?= esc($r['status']) ?></td>
                <td class="r"><?= $n($r['taxable']) ?></td>
                <td class="r"><?= $n($r['cgst']) ?></td>
                <td class="r"><?= $n($r['sgst']) ?></td>
                <td class="r"><?= $n($r['igst']) ?></td>
                <td class="r"><?= $n($r['total_amount']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <?php if (!empty($rows)): ?>
        <tfoot>
            <tr>
                <td colspan="7" class="r">Total (excl. Void)</td>
                <td class="r"><?= $n($totals['taxable']) ?></td>
                <td class="r"><?= $n($totals['cgst']) ?></td>
                <td class="r"><?= $n($totals['sgst']) ?></td>
                <td class="r"><?= $n($totals['igst']) ?></td>
                <td class="r"><?= $n($totals['total']) ?></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</body>
</html>
