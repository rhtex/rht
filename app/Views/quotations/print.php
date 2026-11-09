<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 40px;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .company-info h1 {
            margin: 0;
            color: #007bff;
            font-size: 28px;
        }

        .invoice-details {
            text-align: right;
        }

        .invoice-details h2 {
            margin: 0;
            color: #333;
            letter-spacing: 1px;
        }

        .billing-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .billing-info div {
            width: 48%;
        }

        .billing-info h3 {
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
            font-size: 15px;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 9px;
            text-align: left;
            font-size: 13px;
        }

        td {
            border: 1px solid #ddd;
            padding: 8px 9px;
            font-size: 13px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals {
            float: right;
            width: 320px;
        }

        .totals table tr td {
            border: none;
            padding: 4px 0;
        }

        .totals table tr td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            font-size: 12px;
            color: #777;
        }

        .signature-block {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 200px;
            border-top: 1px solid #777;
            padding-top: 5px;
            font-size: 12px;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
            Print Quotation / Estimate
        </button>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div class="company-info">
                <h1><?= esc($company_name) ?></h1>
                <p><?= nl2br(esc($company_address)) ?></p>
                <?php if (!empty($company_contact)): ?>
                    <p>Phone: <?= esc($company_contact) ?></p>
                <?php endif; ?>
                <?php if (!empty($company_gstin)): ?>
                    <p>GSTIN: <?= esc($company_gstin) ?></p>
                <?php endif; ?>
            </div>
            <div class="invoice-details">
                <h2>QUOTATION / ESTIMATE</h2>
                <p><strong>Quotation #:</strong> <?= esc($quotation['quotation_number']) ?></p>
                <p><strong>Date:</strong> <?= date('d/m/Y', strtotime($quotation['quotation_date'])) ?></p>
                <?php if (!empty($quotation['expiry_date'])): ?>
                    <p><strong>Valid Until:</strong> <?= date('d/m/Y', strtotime($quotation['expiry_date'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($quotation['reference_number'])): ?>
                    <p><strong>Ref / P.O #:</strong> <?= esc($quotation['reference_number']) ?></p>
                <?php endif; ?>
                <?php if (!empty($quotation['transport_name'])): ?>
                    <p><strong>Transport:</strong> <?= esc($quotation['transport_name']) ?></p>
                <?php endif; ?>
                <?php if (!empty($quotation['waybill_number'])): ?>
                    <p><strong>Waybill / LR:</strong> <?= esc($quotation['waybill_number']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="billing-info">
            <div>
                <h3>Quotation For</h3>
                <p><strong><?= esc($quotation['customer_name']) ?></strong></p>
                <?php if (!empty($quotation['customer_address1'])): ?>
                    <p><?= esc($quotation['customer_address1']) ?><?= !empty($quotation['customer_address2']) ? ', ' . esc($quotation['customer_address2']) : '' ?></p>
                    <p><?= esc($quotation['customer_city'] ?? '') ?><?= !empty($quotation['customer_pincode']) ? ' - ' . esc($quotation['customer_pincode']) : '' ?></p>
                <?php endif; ?>
                <?php if (!empty($quotation['customer_state'])): ?>
                    <p>State: <?= esc($quotation['customer_state']) ?></p>
                <?php endif; ?>
                <?php if (!empty($quotation['customer_gstin'])): ?>
                    <p>GSTIN: <?= esc($quotation['customer_gstin']) ?></p>
                <?php endif; ?>
                <?php if (!empty($quotation['customer_phone'])): ?>
                    <p>Phone: <?= esc($quotation['customer_phone']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="45%">Description</th>
                    <th width="12%" class="text-center">HSN</th>
                    <th width="10%" class="text-center">Qty</th>
                    <th width="14%" class="text-right">Rate (₹)</th>
                    <th width="14%" class="text-right">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quotation['items'] as $index => $item): ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td><?= esc($item['description']) ?></td>
                        <td class="text-center"><?= esc($item['hsn_code']) ?: '-' ?></td>
                        <td class="text-center"><?= $item['quantity'] + 0 ?></td>
                        <td class="text-right"><?= number_format($item['rate'], 2) ?></td>
                        <td class="text-right"><?= number_format($item['quantity'] * $item['rate'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td>₹<?= number_format($quotation['subtotal'], 2) ?></td>
                </tr>
                <?php if (!empty($quotation['discount_amount']) && $quotation['discount_amount'] > 0): ?>
                    <?php
                    $subtotal = (float) ($quotation['subtotal'] ?? 0);
                    $actualDiscount = (($quotation['discount_type'] ?? '') == 'Percentage') ? ($subtotal * $quotation['discount_amount'] / 100) : $quotation['discount_amount'];
                    ?>
                    <tr>
                        <td>Discount (<?= ($quotation['discount_type'] ?? '') == 'Percentage' ? ($quotation['discount_amount'] + 0) . '%' : 'Fixed' ?>):</td>
                        <td style="color: red;">-₹<?= number_format($actualDiscount, 2) ?></td>
                    </tr>
                <?php endif; ?>

                <?php
                // Tax slab breakdown
                $subtotal = (float) ($quotation['subtotal'] ?? 0);
                $totalDiscount = (($quotation['discount_type'] ?? '') == 'Percentage') ? ($subtotal * ($quotation['discount_amount'] ?? 0) / 100) : (float) ($quotation['discount_amount'] ?? 0);
                $isInterState = !empty($quotation['is_inter_state']);

                $cgstGroups = [];
                $sgstGroups = [];
                $igstGroups = [];

                foreach ($quotation['items'] as $item) {
                    $itemAmt = (float) $item['quantity'] * (float) $item['rate'];
                    $itemDiscount = ($subtotal > 0) ? ($itemAmt / $subtotal * $totalDiscount) : 0;
                    $taxableValue = max(0, $itemAmt - $itemDiscount);
                    $rate = (float) ($item['tax_percentage'] ?? 0);

                    if ($rate > 0) {
                        if ($isInterState) {
                            if (!isset($igstGroups["$rate"])) $igstGroups["$rate"] = 0;
                            $igstGroups["$rate"] += ($taxableValue * $rate) / 100;
                        } else {
                            $half = $rate / 2;
                            if (!isset($cgstGroups["$half"])) $cgstGroups["$half"] = 0;
                            if (!isset($sgstGroups["$half"])) $sgstGroups["$half"] = 0;
                            $cgstGroups["$half"] += ($taxableValue * $half) / 100;
                            $sgstGroups["$half"] += ($taxableValue * $half) / 100;
                        }
                    }
                }
                ?>

                <?php if (!$isInterState): ?>
                    <?php foreach ($cgstGroups as $r => $amt): ?>
                        <tr>
                            <td>CGST (<?= $r ?>%):</td>
                            <td>₹<?= number_format($amt, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php foreach ($sgstGroups as $r => $amt): ?>
                        <tr>
                            <td>SGST (<?= $r ?>%):</td>
                            <td>₹<?= number_format($amt, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ($igstGroups as $r => $amt): ?>
                        <tr>
                            <td>IGST (<?= $r ?>%):</td>
                            <td>₹<?= number_format($amt, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($quotation['shipping_charge'] > 0): ?>
                    <tr>
                        <td>Shipping / Freight:</td>
                        <td>₹<?= number_format($quotation['shipping_charge'], 2) ?></td>
                    </tr>
                <?php endif; ?>

                <?php if (!empty($quotation['roundoff_amount']) && $quotation['roundoff_amount'] != 0): ?>
                    <tr>
                        <td>Round Off:</td>
                        <td><?= $quotation['roundoff_amount'] > 0 ? '+' : '' ?>₹<?= number_format($quotation['roundoff_amount'], 2) ?></td>
                    </tr>
                <?php endif; ?>

                <tr style="border-top: 2px solid #333; font-size: 16px;">
                    <td>Total Amount:</td>
                    <td>₹<?= number_format($quotation['total_amount'], 2) ?></td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        <?php if (!empty($quotation['notes'])): ?>
            <div style="margin-top: 20px;">
                <strong>Notes:</strong><br>
                <?= nl2br(esc($quotation['notes'])) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($quotation['terms'])): ?>
            <div style="margin-top: 15px;">
                <strong>Terms & Conditions:</strong><br>
                <?= nl2br(esc($quotation['terms'])) ?>
            </div>
        <?php endif; ?>

        <div class="signature-block">
            <div class="signature-box">
                Customer Signature
            </div>
            <div class="signature-box">
                Authorized Signatory<br>
                <strong><?= esc($company_name) ?></strong>
            </div>
        </div>

        <div class="footer">
            <p style="text-align: center; margin: 0;">This is a computer-generated quotation / estimate. Thank you for your business!</p>
        </div>
    </div>
</body>

</html>
