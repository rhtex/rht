<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 14px; margin: 0; padding: 40px; color: #333; }
        .box { max-width: 800px; margin: auto; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
        .company h1 { margin: 0; color: #007bff; }
        .details { text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f8f9fa; border: 1px solid #ddd; padding: 10px; text-align: left; }
        td { border: 1px solid #ddd; padding: 10px; }
        .text-right { text-align: right; }
        .totals { float: right; width: 250px; }
        .totals table td { border: none; padding: 5px 0; }
        .footer { margin-top: 50px; border-top: 1px solid #ddd; padding-top: 20px; font-size: 12px; text-align: center; color: #777; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="box">
        <div class="header">
            <div class="company">
                <h1><?= esc($company_name) ?></h1>
                <p><?= nl2br(esc($company_address)) ?></p>
                <?php if ($company_gstin): ?><p>GSTIN: <?= esc($company_gstin) ?></p><?php endif; ?>
            </div>
            <div class="details">
                <h2>QUOTATION</h2>
                <p><strong># :</strong> <?= esc($quotation['quotation_number']) ?></p>
                <p><strong>Date :</strong> <?= date('d/m/Y', strtotime($quotation['quotation_date'])) ?></p>
                <?php if ($quotation['expiry_date']): ?><p><strong>Valid Until :</strong> <?= date('d/m/Y', strtotime($quotation['expiry_date'])) ?></p><?php endif; ?>
                <?php if ($quotation['transport_name']): ?><p><strong>Transport :</strong> <?= esc($quotation['transport_name']) ?></p><?php endif; ?>
            </div>
        </div>

        <div style="margin-bottom: 30px;">
            <h3>For:</h3>
            <strong><?= esc($quotation['customer_name']) ?></strong>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>HSN</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quotation['items'] as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($item['description']) ?></td>
                        <td><?= esc($item['hsn_code']) ?></td>
                        <td><?= $item['quantity'] + 0 ?></td>
                        <td><?= number_format($item['rate'], 2) ?></td>
                        <td class="text-right"><?= number_format($item['amount'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr><td>Subtotal:</td><td class="text-right">₹<?= number_format($quotation['subtotal'], 2) ?></td></tr>
                <?php if ($quotation['discount_amount'] > 0): ?>
                    <?php $actualDisc = ($quotation['discount_type'] == 'Percentage') ? ($quotation['subtotal'] * $quotation['discount_amount'] / 100) : $quotation['discount_amount']; ?>
                    <tr><td>Discount (<?= $quotation['discount_type'] ?>):</td><td class="text-right">-₹<?= number_format($actualDisc, 2) ?></td></tr>
                <?php endif; ?>
                <tr><td>Tax:</td><td class="text-right">₹<?= number_format($quotation['tax_amount'], 2) ?></td></tr>
                <?php if ($quotation['shipping_charge'] > 0): ?>
                    <tr><td>Shipping:</td><td class="text-right">₹<?= number_format($quotation['shipping_charge'], 2) ?></td></tr>
                <?php endif; ?>
                <tr style="border-top: 2px solid #333; font-size: 16px; font-weight: bold;">
                    <td>Total:</td><td class="text-right">₹<?= number_format($quotation['total_amount'], 2) ?></td>
                </tr>
            </table>
        </div>

        <div style="clear: both; margin-top: 30px;">
            <?php if ($quotation['notes']): ?>
                <p><strong>Notes:</strong><br><?= nl2br(esc($quotation['notes'])) ?></p>
            <?php endif; ?>
            <?php if ($quotation['terms']): ?>
                <p><strong>Terms:</strong><br><?= nl2br(esc($quotation['terms'])) ?></p>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p>Thank you for choosing <?= esc($company_name) ?>!</p>
            <p>This is a computer-generated document.</p>
        </div>
    </div>
    <div class="no-print" style="position: fixed; top: 20px; right: 20px;">
        <button onclick="window.print()" style="padding: 10px; cursor: pointer;">Print</button>
        <button onclick="window.close()" style="padding: 10px; cursor: pointer;">Close</button>
    </div>
</body>
</html>
