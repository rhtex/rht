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

$ewayBillNo   = trim((string) ($invoice['ewaybill_number'] ?? ($ewayBill['ewaybill_number'] ?? '')));
$ewayBillDateRaw = $ewayBill['ewaybill_date'] ?? null;
$ewayBillDate = !empty($ewayBillDateRaw) ? date('d/m/Y', strtotime((string) $ewayBillDateRaw)) : '_________________';

$dispatchDateRaw = !empty($invoice['doc_dispatched_date'])
    ? $invoice['doc_dispatched_date']
    : (!empty($invoice['waybill_date']) ? $invoice['waybill_date'] : '');
$dispatchDate = $dispatchDateRaw !== '' ? date('d/m/Y', strtotime((string) $dispatchDateRaw)) : '_________________';

$transportCourier = trim((string) (!empty($invoice['doc_courier_name']) ? $invoice['doc_courier_name'] : ($invoice['transport_name'] ?? '')));
$trackingNo       = trim((string) (!empty($invoice['doc_tracking_number']) ? $invoice['doc_tracking_number'] : ($invoice['waybill_number'] ?? '')));

$subtotal   = (float) ($invoice['subtotal'] ?? 0);
$gstAmount  = (float) ($invoice['tax_amount'] ?? 0);
$grandTotal = (float) ($invoice['total_amount'] ?? 0);
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
        body {
            background: #f3f4f6;
            color: #111827;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.55;
        }
        .dispatch-sheet {
            max-width: 780px;
            margin: 24px auto;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 32px 36px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .dispatch-title {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.6px;
            border-bottom: 2px solid #111827;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .section-heading {
            font-size: 15px;
            font-weight: 700;
            margin-top: 18px;
            margin-bottom: 8px;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }
        .detail-list {
            list-style: disc;
            padding-left: 22px;
            margin-bottom: 12px;
        }
        .detail-list li {
            margin-bottom: 4px;
        }
        .detail-label {
            display: inline-block;
            min-width: 175px;
            font-weight: 500;
        }
        .amount-words-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 14px;
            margin-top: 8px;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .signature-block {
            margin-top: 36px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .sig-right {
            text-align: right;
        }
        .sig-space {
            height: 46px;
        }
        @media print {
            body {
                background: #fff;
            }
            .no-print {
                display: none !important;
            }
            .dispatch-sheet {
                margin: 0;
                border: none;
                box-shadow: none;
                padding: 12px 16px;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container py-3 no-print text-center">
        <button type="button" class="btn btn-primary btn-sm px-3" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print Goods Dispatch Details
        </button>
        <button type="button" class="btn btn-secondary btn-sm px-3 ms-2" onclick="window.close()">
            Close
        </button>
    </div>

    <div class="dispatch-sheet">
        <div class="dispatch-title">GOODS DISPATCH DETAILS</div>

        <p class="mb-3">
            <strong>To:</strong> Customer Name: <span class="fw-semibold"><?= esc($invoice['customer_name'] ?? '______________________') ?></span>
        </p>

        <div class="section-heading">Invoice &amp; E-Way Bill Details</div>
        <ul class="detail-list">
            <li><span class="detail-label">Invoice No.:</span> <strong><?= esc($invoice['invoice_number']) ?></strong></li>
            <li><span class="detail-label">Invoice Date:</span> <?= !empty($invoice['invoice_date']) ? date('d/m/Y', strtotime($invoice['invoice_date'])) : '_________________' ?></li>
            <li><span class="detail-label">E-Way Bill No.:</span> <?= $ewayBillNo !== '' ? esc($ewayBillNo) : '________________' ?></li>
            <li><span class="detail-label">E-Way Bill Date:</span> <?= esc($ewayBillDate) ?></li>
        </ul>

        <div class="section-heading">Goods Details</div>
        <ul class="detail-list">
            <li><span class="detail-label">Product Description:</span> Sarees</li>
            <li><span class="detail-label">Total Quantity:</span> <?= esc($qtyDisplay) ?> Pieces</li>
            <li><span class="detail-label">Rate per Piece:</span> <?= esc($rateDisplay) ?></li>
            <li><span class="detail-label">Total Amount:</span> ₹<?= number_format($subtotal, 2) ?></li>
            <li><span class="detail-label">GST Amount:</span> ₹<?= number_format($gstAmount, 2) ?></li>
            <li><span class="detail-label"><strong>Grand Total:</strong></span> <strong>₹<?= number_format($grandTotal, 2) ?></strong></li>
        </ul>

        <div class="mt-2">
            <strong>Amount in Words:</strong>
            <div class="amount-words-box"><?= esc($amountWords) ?></div>
        </div>

        <div class="section-heading">Dispatch Details</div>
        <ul class="detail-list">
            <li><span class="detail-label">Dispatch Date:</span> <?= esc($dispatchDate) ?></li>
            <li><span class="detail-label">Transport / Courier:</span> <?= $transportCourier !== '' ? esc($transportCourier) : '____________' ?></li>
            <li><span class="detail-label">LR / Tracking No.:</span> <?= $trackingNo !== '' ? esc($trackingNo) : '______________' ?></li>
        </ul>

        <p class="mt-4 mb-0">
            The above goods have been dispatched against the mentioned invoice. Kindly acknowledge receipt of the goods.
        </p>

        <div class="signature-block">
            <div class="text-muted small">
                Receiver's Signature
                <div class="sig-space"></div>
                <span>_______________________</span>
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
