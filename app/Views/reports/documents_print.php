<?php
$isDebit  = $type === 'debit-notes';
$isCredit = $type === 'credit-notes';
$partyName = '';
if (!empty($filters['party_id'])) {
    foreach ($parties as $p) {
        if ((int) $p['id'] === (int) $filters['party_id']) { $partyName = $p['name']; break; }
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
    <title><?= esc($def['title']) ?> - <?= esc($periodLabel) ?></title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12.5px; font-weight: 500; color: #111; margin: 0; padding: 14px; }
        .head { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 10px; }
        .head h1 { font-size: 19px; margin: 0 0 2px; font-weight: 700; }
        .head .company { font-size: 14px; font-weight: 700; }
        .meta { font-size: 12px; color: #333; text-align: right; line-height: 1.5; }
        .kpis { display: flex; gap: 8px; margin-bottom: 10px; }
        .kpi { flex: 1; border: 1px solid #999; border-radius: 4px; padding: 6px 8px; }
        .kpi .l { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #444; }
        .kpi .v { font-size: 14px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #888; padding: 4px 6px; font-size: 12px; }
        th { background: #eaeaea; text-align: left; font-size: 11.5px; font-weight: 700; color: #000; }
        td.r, th.r { text-align: right; }
        .total-row td { font-weight: 700; font-size: 12.5px; background: #f0f0f0; border-top: 2px solid #333; }
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
            <h1><?= esc($def['title']) ?></h1>
        </div>
        <div class="meta">
            <div><strong>Period:</strong> <?= esc($periodLabel) ?></div>
            <?php if ($partyName): ?><div><strong><?= esc($def['party_label']) ?>:</strong> <?= esc($partyName) ?></div><?php endif; ?>
            <div><strong>Generated:</strong> <?= date('d M Y h:i A') ?></div>
        </div>
    </div>

    <div class="kpis">
        <div class="kpi"><div class="l">Documents</div><div class="v"><?= number_format($totals['count']) ?></div></div>
        <?php if (!$isDebit): ?>
            <div class="kpi"><div class="l">Subtotal</div><div class="v">₹<?= $n($totals['subtotal']) ?></div></div>
            <div class="kpi"><div class="l">GST / Tax</div><div class="v">₹<?= $n($totals['tax']) ?></div></div>
        <?php endif; ?>
        <div class="kpi"><div class="l">Total Amount</div><div class="v">₹<?= $n($totals['total']) ?></div></div>
        <?php if (!$isCredit): ?>
            <div class="kpi"><div class="l"><?= $isDebit ? 'Used' : 'Paid' ?></div><div class="v">₹<?= $n($totals['paid']) ?></div></div>
            <div class="kpi"><div class="l"><?= $isDebit ? 'Unused Balance' : 'Balance Due' ?></div><div class="v">₹<?= $n($totals['balance']) ?></div></div>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th><?= $isDebit ? 'Reference #' : 'Number' ?></th>
                <th><?= esc($def['party_label']) ?></th>
                <?php if ($isCredit || $isDebit): ?><th><?= $isCredit ? 'Invoice #' : 'Bill #' ?></th><?php endif; ?>
                <?php if (!$isDebit): ?>
                    <th class="r">Subtotal</th>
                    <th class="r">Tax</th>
                <?php endif; ?>
                <th class="r">Total</th>
                <?php if (!$isCredit): ?>
                    <th class="r"><?= $isDebit ? 'Used' : 'Paid' ?></th>
                    <th class="r">Balance</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="10" style="text-align:center;padding:12px;">No records found for the selected filters.</td></tr>
            <?php endif; ?>
            <?php foreach ($items as $i => $it): ?>
                <tr class="<?= $it['is_void'] ? 'void' : '' ?>">
                    <td><?= $i + 1 ?></td>
                    <td><?= $it['date'] ? date('d/m/Y', strtotime($it['date'])) : '-' ?></td>
                    <td><?= esc($it['number'] ?: ('#' . $it['id'])) ?></td>
                    <td><?= esc($it['party_name']) ?></td>
                    <?php if ($isCredit || $isDebit): ?><td><?= esc($it['ref_doc'] ?: '-') ?></td><?php endif; ?>
                    <?php if (!$isDebit): ?>
                        <td class="r"><?= $n($it['subtotal']) ?></td>
                        <td class="r"><?= $n($it['tax']) ?></td>
                    <?php endif; ?>
                    <td class="r"><?= $n($it['total']) ?></td>
                    <?php if (!$isCredit): ?>
                        <td class="r"><?= $n($it['paid']) ?></td>
                        <td class="r"><?= $n($it['balance']) ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (!empty($items)): ?>
            <tr class="total-row">
                <td colspan="<?= ($isCredit || $isDebit) ? 5 : 4 ?>" class="r">Total (excl. Void)</td>
                <?php if (!$isDebit): ?>
                    <td class="r"><?= $n($totals['subtotal']) ?></td>
                    <td class="r"><?= $n($totals['tax']) ?></td>
                <?php endif; ?>
                <td class="r"><?= $n($totals['total']) ?></td>
                <?php if (!$isCredit): ?>
                    <td class="r"><?= $n($totals['paid']) ?></td>
                    <td class="r"><?= $n($totals['balance']) ?></td>
                <?php endif; ?>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
