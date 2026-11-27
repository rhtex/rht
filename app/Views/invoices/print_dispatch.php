<?php
$items = $invoice['items'] ?? [];
$totalQty = 0.0;
$rates = [];
foreach ($items as $item) {
    $qty = (float) ($item['quantity'] ?? 0);
    $rate = (float) ($item['rate'] ?? 0);
    $totalQty += $qty;
    if ($rate > 0) {
        $rates[] = $rate;
    }
}

$qtyDisplay = rtrim(rtrim(number_format($totalQty, 2, '.', ''), '0'), '.');
if ($qtyDisplay === '') {
    $qtyDisplay = '0';
}

if (count($rates) > 0) {
    $minRate = min($rates);
    $maxRate = max($rates);
    $rateDisplay = abs($maxRate - $minRate) < 0.01
        ? '₹' . number_format($minRate, 2)
        : '₹' . number_format($minRate, 2) . ' – ₹' . number_format($maxRate, 2);
} else {
    $rateDisplay = '₹0.00';
}

$ewayBillNo      = trim((string) ($invoice['ewaybill_number'] ?? ($ewayBill['ewaybill_number'] ?? '')));
$ewayBillDateRaw = $ewayBill['ewaybill_date'] ?? null;
$ewayBillDate    = !empty($ewayBillDateRaw) ? date('d/m/Y', strtotime((string) $ewayBillDateRaw)) : '—';

$dispatchDateRaw = !empty($invoice['doc_dispatched_date'])
    ? $invoice['doc_dispatched_date']
    : (!empty($invoice['waybill_date']) ? $invoice['waybill_date'] : '');
$dispatchDate = $dispatchDateRaw !== '' ? date('d/m/Y', strtotime((string) $dispatchDateRaw)) : '—';

$transportCourier = trim((string) (!empty($invoice['doc_courier_name']) ? $invoice['doc_courier_name'] : ($invoice['transport_name'] ?? '')));
$trackingNo       = trim((string) (!empty($invoice['doc_tracking_number']) ? $invoice['doc_tracking_number'] : ($invoice['waybill_number'] ?? '')));

$poNumber = trim((string) ($invoice['reference_number'] ?? ''));
$poDateRaw = trim((string) ($invoice['po_date'] ?? ''));
$hasPo    = ($poNumber !== '' || $poDateRaw !== '');

$custAddrParts = array_filter([
    trim((string) ($invoice['customer_address_1'] ?? '')),
    trim((string) ($invoice['customer_address_2'] ?? '')),
    trim((string) ($invoice['customer_city'] ?? '')),
    trim((string) ($invoice['customer_state'] ?? '')),
    trim((string) ($invoice['customer_pincode'] ?? '')),
]);
$custAddress = implode(', ', $custAddrParts);
$custGstin   = trim((string) ($invoice['customer_gstin'] ?? ''));
$custContacts = array_unique(array_filter([
    trim((string) ($invoice['customer_phone'] ?? '')),
    trim((string) ($invoice['customer_whatsapp'] ?? '')),
]));
$custContactStr = implode(' / ', $custContacts);

$subtotal    = (float) ($invoice['subtotal'] ?? 0);
$gstAmount   = (float) ($invoice['tax_amount'] ?? 0);
$grandTotal  = (float) ($invoice['total_amount'] ?? 0);
$amountWords = function_exists('number_to_indian_currency_words')
    ? number_to_indian_currency_words($grandTotal)
    : number_format($grandTotal, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? ('Goods Dispatch Details - ' . $invoice['invoice_number'])) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
        body {
            background: #f3f4f6;
            color: #111827;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11.5px;
            line-height: 1.35;
        }
        .dispatch-sheet {
            width: 194mm;
            max-width: 100%;
            min-height: 132mm;
            max-height: 140mm;
            margin: 14px auto;
            background: #fff;
            border: 1px solid #9ca3af;
            border-radius: 4px;
            padding: 6mm 8mm;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .company-header {
            text-align: center;
            border-bottom: 1.5px solid #111827;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .company-name {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .company-meta {
            font-size: 10px;
            color: #374151;
            margin-top: 1px;
        }
        .dispatch-title {
            text-align: center;
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 0.8px;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 3px 8px;
            margin-bottom: 7px;
            text-transform: uppercase;
        }
        .customer-box {
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 5px 8px;
            margin-bottom: 7px;
            background: #fafafa;
        }
        .section-heading {
            font-size: 11.5px;
            font-weight: 700;
            margin-bottom: 3px;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 2px;
            text-transform: uppercase;
        }
        .kv-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .kv-table th, .kv-table td {
            padding: 1.5px 4px;
            vertical-align: top;
            font-size: 11.2px;
        }
        .kv-table th {
            width: 44%;
            font-weight: 600;
            color: #374151;
        }
        .amount-words-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 4px 7px;
            margin-top: 3px;
            font-size: 11px;
            font-weight: 600;
        }
        .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px dashed #d1d5db;
            padding-top: 6px;
            margin-top: 6px;
        }
        .ack-text {
            max-width: 65%;
            font-size: 10.5px;
            color: #374151;
        }
        .sig-right {
            text-align: right;
            font-size: 11.5px;
        }
        .sig-space {
            height: 22px;
        }
        @media print {
            body {
                background: #fff;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            .dispatch-sheet {
                margin: 0;
                box-shadow: none;
                border: 1px solid #9ca3af;
                width: 100%;
                max-height: 140mm;
            }
        }
    </style>
</head>
<body>
    <div class="container py-2 no-print text-center">
        <button type="button" class="btn btn-primary btn-sm px-3" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Goods Dispatch Details
        </button>
        <button type="button" class="btn btn-secondary btn-sm px-3 ms-2" onclick="window.close()">
            Close
        </button>
    </div>

    <div class="dispatch-sheet">
        <div>
            <div class="company-header">
                <div class="company-name"><?= esc($company_name) ?></div>
                <?php
                $metaParts = array_filter([
                    trim((string) ($company_address ?? '')),
                    !empty($company_gstin) ? 'GSTIN: ' . trim((string) $company_gstin) : '',
                    !empty($company_phone) ? 'Ph: ' . trim((string) $company_phone) : '',
                ]);
                ?>
                <?php if (!empty($metaParts)): ?>
                    <div class="company-meta"><?= esc(implode(' | ', $metaParts)) ?></div>
                <?php endif; ?>
            </div>

            <div class="dispatch-title">GOODS DISPATCH DETAILS</div>

            <div class="customer-box">
                <div><strong>To:</strong> <strong><?= esc($invoice['customer_name'] ?? 'N/A') ?></strong></div>
                <?php if ($custAddress !== ''): ?>
                    <div><?= esc($custAddress) ?></div>
                <?php endif; ?>
                <div class="d-flex flex-wrap gap-3 mt-1">
                    <span><strong>GSTIN:</strong> <?= $custGstin !== '' ? esc($custGstin) : 'Unregistered / N/A' ?></span>
                    <span><strong>Contact:</strong> <?= $custContactStr !== '' ? esc($custContactStr) : '—' ?></span>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-6">
                    <div class="section-heading">Invoice &amp; E-Way Bill Details</div>
                    <table class="kv-table">
                        <tr><th>Invoice No.:</th><td><strong><?= esc($invoice['invoice_number']) ?></strong></td></tr>
                        <tr><th>Invoice Date:</th><td><?= !empty($invoice['invoice_date']) ? date('d/m/Y', strtotime($invoice['invoice_date'])) : '—' ?></td></tr>
                        <?php if ($hasPo): ?>
                            <?php if ($poNumber !== ''): ?>
                                <tr><th>PO No.:</th><td><?= esc($poNumber) ?></td></tr>
                            <?php endif; ?>
                            <?php if ($poDateRaw !== ''): ?>
                                <tr><th>PO Date:</th><td><?= date('d/m/Y', strtotime($poDateRaw)) ?></td></tr>
                            <?php endif; ?>
                        <?php endif; ?>
                        <tr><th>E-Way Bill No.:</th><td><?= $ewayBillNo !== '' ? esc($ewayBillNo) : '—' ?></td></tr>
                        <tr><th>E-Way Bill Date:</th><td><?= esc($ewayBillDate) ?></td></tr>
                    </table>

                    <div class="section-heading">Dispatch Details</div>
                    <table class="kv-table mb-0">
                        <tr><th>Dispatch Date:</th><td><?= esc($dispatchDate) ?></td></tr>
                        <tr><th>Transport / Courier:</th><td><?= $transportCourier !== '' ? esc($transportCourier) : '—' ?></td></tr>
                        <tr><th>LR / Tracking No.:</th><td><?= $trackingNo !== '' ? esc($trackingNo) : '—' ?></td></tr>
                    </table>
                </div>

                <div class="col-6">
                    <div class="section-heading">Goods Details</div>
                    <table class="kv-table">
                        <tr><th>Product Description:</th><td>Sarees</td></tr>
                        <tr><th>Total Quantity:</th><td><?= esc($qtyDisplay) ?> Pieces</td></tr>
                        <tr><th>Rate per Piece:</th><td><?= esc($rateDisplay) ?></td></tr>
                        <tr><th>Total Amount:</th><td>₹<?= number_format($subtotal, 2) ?></td></tr>
                        <tr><th>GST Amount:</th><td>₹<?= number_format($gstAmount, 2) ?></td></tr>
                        <tr><th><strong>Grand Total:</strong></th><td><strong>₹<?= number_format($grandTotal, 2) ?></strong></td></tr>
                    </table>

                    <div>
                        <strong style="font-size:11px;">Amount in Words:</strong>
                        <div class="amount-words-box"><?= esc($amountWords) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-row">
            <div class="ack-text">
                The above goods have been dispatched against the mentioned invoice. Kindly acknowledge receipt of the goods.
            </div>
            <div class="sig-right">
                <strong>For <?= esc($company_name) ?></strong>
                <div class="sig-space"></div>
                <div>Authorized Signature</div>
            </div>
        </div>
    </div>
</body>
</html>
