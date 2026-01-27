<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 14px; color: #333; line-height: 1.6; margin: 0; padding: 40px; }
        .invoice-box { max-width: 800px; margin: auto; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
        .company-info h1 { margin: 0; color: #007bff; font-size: 28px; }
        .invoice-details { text-align: right; }
        .invoice-details h2 { margin: 0; color: #333; }
        .billing-info { display: flex; box-shadow: none; justify-content: space-between; margin-bottom: 30px; }
        .billing-info div { width: 45%; }
        .billing-info h3 { border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f8f9fa; border: 1px solid #ddd; padding: 10px; text-align: left; }
        td { border: 1px solid #ddd; padding: 10px; }
        .text-right { text-align: right; }
        .totals { float: right; width: 300px; }
        .totals table tr td { border: none; padding: 5px 0; }
        .totals table tr td:last-child { text-align: right; font-weight: bold; }
        .footer { margin-top: 50px; border-top: 1px solid #ddd; padding-top: 20px; font-size: 12px; color: #777; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="invoice-box">
        <div class="header">
            <div class="company-info">
                <h1><?= esc($settings['org_name'] ?? 'RasiDev Solutions') ?></h1>
                <p><?= nl2br(esc($settings['org_address'] ?? '')) ?></p>
                <p>Contact: <?= esc($settings['org_contact'] ?? '') ?></p>
                <?php if (!empty($settings['org_gstin'])): ?>
                    <p>GSTIN: <?= esc($settings['org_gstin']) ?></p>
                <?php endif; ?>
            </div>
            <div class="invoice-details">
                <h2>BILL</h2>
                <p><strong>Bill #:</strong> <?= esc($bill['bill_number']) ?></p>
                <p><strong>Date:</strong> <?= date('d/m/Y', strtotime($bill['bill_date'])) ?></p>
                <p><strong>Due Date:</strong> <?= date('d/m/Y', strtotime($bill['due_date'])) ?></p>
                <?php if ($bill['reference_number']): ?>
                    <p><strong>Vendor Inv #:</strong> <?= esc($bill['reference_number']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="billing-info">
            <div>
                <h3>Vendor Details</h3>
                <p><strong><?= esc($bill['vendor_name']) ?></strong></p>
                <p><?= nl2br(esc($bill['vendor_address'] ?? '')) ?></p>
                <?php if (!empty($bill['vendor_gstin'])): ?>
                    <p>GSTIN: <?= esc($bill['vendor_gstin']) ?></p>
                <?php endif; ?>
                <?php if (!empty($bill['vendor_state'])): ?>
                    <p>State: <?= esc($bill['vendor_state']) ?></p>
                <?php endif; ?>
            </div>
            <div>
                <!-- Could add shipping address here if applicable -->
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="45%">Description</th>
                    <th width="10%">HSN</th>
                    <th width="10%">Qty</th>
                    <th width="15%">Rate</th>
                    <th width="15%">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bill['items'] as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= esc($item['description']) ?></td>
                        <td><?= esc($item['hsn_code']) ?></td>
                        <td><?= $item['quantity'] + 0 ?></td>
                        <td><?= number_format($item['rate'], 2) ?></td>
                        <td class="text-right"><?= number_format($item['quantity'] * $item['rate'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td>₹<?= number_format($bill['subtotal'], 2) ?></td>
                </tr>
                <?php if ($bill['discount_amount'] > 0): ?>
                    <tr>
                        <td>Discount (<?= $bill['discount_type'] == 'Percentage' ? ($bill['discount_amount'] + 0) . '%' : 'Fixed' ?>):</td>
                        <td style="color: red;">-₹<?= number_format($bill['discount_type'] == 'Percentage' ? ($bill['subtotal'] * $bill['discount_amount'] / 100) : $bill['discount_amount'], 2) ?></td>
                    </tr>
                <?php endif; ?>

                <?php
                // Tax breakdown for print
                $taxGroups = [];
                $subtotal = $bill['subtotal'];
                $totalDiscount = ($bill['discount_type'] == 'Percentage') ? ($subtotal * $bill['discount_amount'] / 100) : $bill['discount_amount'];

                foreach ($bill['items'] as $item) {
                    $rate = (float)$item['tax_percentage'];
                    if ($rate > 0) {
                        $itemAmount = $item['quantity'] * $item['rate'];
                        $itemDiscount = ($subtotal > 0) ? ($itemAmount / $subtotal * $totalDiscount) : 0;
                        $taxableValue = $itemAmount - $itemDiscount;
                        $taxAmount = ($taxableValue * $rate) / 100;
                        
                        if (!isset($taxGroups[$rate])) $taxGroups[$rate] = 0;
                        $taxGroups[$rate] += $taxAmount;
                    }
                }
                ksort($taxGroups);
                $isInterState = ($bill['igst_amount'] > 0);
                ?>

                <?php foreach ($taxGroups as $rate => $totalTax): ?>
                    <?php if ($isInterState): ?>
                        <tr>
                            <td>IGST (<?= $rate + 0 ?>%):</td>
                            <td>₹<?= number_format($totalTax, 2) ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td>CGST (<?= ($rate / 2) + 0 ?>%):</td>
                            <td>₹<?= number_format($totalTax / 2, 2) ?></td>
                        </tr>
                        <tr>
                            <td>SGST (<?= ($rate / 2) + 0 ?>%):</td>
                            <td>₹<?= number_format($totalTax / 2, 2) ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($bill['shipping_charge'] > 0): ?>
                    <tr>
                        <td>Shipping:</td>
                        <td>₹<?= number_format($bill['shipping_charge'], 2) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ($bill['roundoff_amount'] != 0): ?>
                    <tr>
                        <td>Roundoff:</td>
                        <td>₹<?= number_format($bill['roundoff_amount'], 2) ?></td>
                    </tr>
                <?php endif; ?>
                <tr style="border-top: 2px solid #333; font-size: 18px;">
                    <td style="padding-top: 10px;">Total:</td>
                    <td style="padding-top: 10px;">₹<?= number_format($bill['total_amount'], 2) ?></td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        <?php if ($bill['notes']): ?>
            <div style="margin-top: 20px;">
                <strong>Notes:</strong><br>
                <?= nl2br(esc($bill['notes'])) ?>
            </div>
        <?php endif; ?>

        <?php if ($bill['terms']): ?>
            <div style="margin-top: 20px;">
                <strong>Terms & Conditions:</strong><br>
                <?= nl2br(esc($bill['terms'])) ?>
            </div>
        <?php endif; ?>

        <div class="footer">
            <p style="text-align: center;">This is a computer-generated document. No signature is required.</p>
        </div>
    </div>

    <div class="no-print" style="position: fixed; top: 20px; right: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #007bff; color: #fff; border: none; border-radius: 4px;">Reprint</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; background: #6c757d; color: #fff; border: none; border-radius: 4px; margin-left: 10px;">Close</button>
    </div>
</body>
</html>
