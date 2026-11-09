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
            Print Purchase Bill
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
                <h2>PURCHASE BILL</h2>
                <p><strong>Bill #:</strong> <?= esc($bill['bill_number']) ?></p>
                <p><strong>Bill Date:</strong> <?= date('d/m/Y', strtotime($bill['bill_date'])) ?></p>
                <p><strong>Due Date:</strong> <?= date('d/m/Y', strtotime($bill['due_date'])) ?></p>
                <?php if (!empty($bill['reference_number'])): ?>
                    <p><strong>Vendor Inv / Ref #:</strong> <?= esc($bill['reference_number']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="billing-info">
            <div>
                <h3>Vendor Details</h3>
                <p><strong><?= esc($bill['vendor_name']) ?></strong></p>
                <?php if (!empty($bill['vendor_address1'])): ?>
                    <p><?= esc($bill['vendor_address1']) ?><?= !empty($bill['vendor_address2']) ? ', ' . esc($bill['vendor_address2']) : '' ?></p>
                    <p><?= esc($bill['vendor_city'] ?? '') ?><?= !empty($bill['vendor_pincode']) ? ' - ' . esc($bill['vendor_pincode']) : '' ?></p>
                <?php endif; ?>
                <?php if (!empty($bill['vendor_state'])): ?>
                    <p>State: <?= esc($bill['vendor_state']) ?></p>
                <?php endif; ?>
                <?php if (!empty($bill['vendor_gstin'])): ?>
                    <p>GSTIN: <?= esc($bill['vendor_gstin']) ?></p>
                <?php endif; ?>
                <?php if (!empty($bill['vendor_phone'])): ?>
                    <p>Phone: <?= esc($bill['vendor_phone']) ?></p>
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
                <?php foreach ($bill['items'] as $index => $item): ?>
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
                    <td>₹<?= number_format($bill['subtotal'], 2) ?></td>
                </tr>
                <?php if (!empty($bill['discount_amount']) && $bill['discount_amount'] > 0): ?>
                    <?php
                    $subtotal = (float) ($bill['subtotal'] ?? 0);
                    $actualDiscount = (($bill['discount_type'] ?? '') == 'Percentage') ? ($subtotal * $bill['discount_amount'] / 100) : $bill['discount_amount'];
                    ?>
                    <tr>
                        <td>Discount (<?= ($bill['discount_type'] ?? 'Amount') == 'Percentage' ? ($bill['discount_amount'] + 0) . '%' : 'Fixed' ?>):</td>
                        <td style="color: red;">-₹<?= number_format($actualDiscount, 2) ?></td>
                    </tr>
                <?php endif; ?>

                <?php
                $subtotal = (float) ($bill['subtotal'] ?? 0);
                $totalDiscount = (($bill['discount_type'] ?? '') == 'Percentage') ? ($subtotal * ($bill['discount_amount'] ?? 0) / 100) : (float) ($bill['discount_amount'] ?? 0);
                $isInterState = !empty($bill['is_inter_state']);

                $cgstGroups = [];
                $sgstGroups = [];
                $igstGroups = [];

                foreach ($bill['items'] as $item) {
                    $itemAmt = (float) $item['quantity'] * (float) $item['rate'];
                    $itemDiscount = ($subtotal > 0) ? ($itemAmt / $subtotal * $totalDiscount) : 0;
                    $taxableValue = max(0, $itemAmt - $itemDiscount);

                    $cr = (float) ($item['cgst_rate'] ?? 0);
                    $sr = (float) ($item['sgst_rate'] ?? 0);
                    $ir = (float) ($item['igst_rate'] ?? 0);

                    if ($cr > 0) {
                        if (!isset($cgstGroups["$cr"])) $cgstGroups["$cr"] = 0;
                        $cgstGroups["$cr"] += ($taxableValue * $cr) / 100;
                    }
                    if ($sr > 0) {
                        if (!isset($sgstGroups["$sr"])) $sgstGroups["$sr"] = 0;
                        $sgstGroups["$sr"] += ($taxableValue * $sr) / 100;
                    }
                    if ($ir > 0) {
                        if (!isset($igstGroups["$ir"])) $igstGroups["$ir"] = 0;
                        $igstGroups["$ir"] += ($taxableValue * $ir) / 100;
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

                <?php if ($bill['shipping_charge'] > 0): ?>
                    <tr>
                        <td>Shipping / Freight:</td>
                        <td>₹<?= number_format($bill['shipping_charge'], 2) ?></td>
                    </tr>
                <?php endif; ?>

                <?php if (!empty($bill['roundoff_amount']) && $bill['roundoff_amount'] != 0): ?>
                    <tr>
                        <td>Round Off:</td>
                        <td><?= $bill['roundoff_amount'] > 0 ? '+' : '' ?>₹<?= number_format($bill['roundoff_amount'], 2) ?></td>
                    </tr>
                <?php endif; ?>

                <tr style="border-top: 2px solid #333; font-size: 15px;">
                    <td>Total Amount:</td>
                    <td>₹<?= number_format($bill['total_amount'], 2) ?></td>
                </tr>
                <tr>
                    <td>Amount Paid:</td>
                    <td>₹<?= number_format($bill['paid_amount'], 2) ?></td>
                </tr>
                <tr style="border-top: 1px solid #ddd; font-size: 15px; color: #d9534f;">
                    <td>Balance Due:</td>
                    <td>₹<?= number_format($bill['balance'], 2) ?></td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        <?php if (!empty($bill['notes'])): ?>
            <div style="margin-top: 20px;">
                <strong>Notes:</strong><br>
                <?= nl2br(esc($bill['notes'])) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($bill['terms'])): ?>
            <div style="margin-top: 15px;">
                <strong>Terms & Conditions:</strong><br>
                <?= nl2br(esc($bill['terms'])) ?>
            </div>
        <?php endif; ?>

        <div class="signature-block">
            <div class="signature-box">
                Vendor Signature
            </div>
            <div class="signature-box">
                Authorized Signatory<br>
                <strong><?= esc($company_name) ?></strong>
            </div>
        </div>

        <div class="footer">
            <p style="text-align: center; margin: 0;">This is a computer-generated purchase bill. Thank you for your business!</p>
        </div>
    </div>
</body>

</html>
