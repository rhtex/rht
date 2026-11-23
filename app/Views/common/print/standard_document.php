<?php
/**
 * Unified Standard Print Template
 * Matches standard format:
 * - Clean solid outer borders around every section and cell
 * - Header with logo circle and company details, right-aligned doc heading
 * - 3-column boxed grid (Meta, Bill To, Ship To) with vertical dividers
 * - Boxed Place of Supply bar
 * - Complete boxed Line Items table with vertical and horizontal cell borders
 * - Boxed Totals & Items in total
 * - Boxed Total in words
 * - Boxed Bottom section: Configurable Bank Account Details (left), Sole Footer message (Terms & Conditions), and Authorized Signature (right)
 * - Powered by footer
 */

$docTitle       = $document_title ?? 'TAX INVOICE';
$docNumberLabel = $document_number_label ?? 'Invoice#';
$docNumber      = $document_number ?? '';

$companyName    = trim((string) get_setting('company_print_name', get_setting('org_name', get_setting('app_name', 'RASI HANDLOOM TEXTILES'))));
$companyAddress = (string) get_setting('company_print_address', '');
if (trim($companyAddress) === '') {
    $lines = array_filter([
        get_setting('company_address_1', ''),
        get_setting('company_address_2', ''),
        trim(get_setting('company_city', '') . ' ' . get_setting('company_pincode', ''))
    ]);
    $companyAddress = implode(",\n", $lines);
}
// Normalize literal '\n' if stored by forms into real newlines
$companyAddress = str_replace(['\r\n', '\r', '\n'], "\n", $companyAddress);

$companyPhone   = trim((string) get_setting('company_print_phone', get_setting('company_phone', get_setting('company_mobile', ''))));
$companyEmail   = trim((string) get_setting('company_print_email', get_setting('company_email', '')));
$companyGstin   = trim((string) get_setting('company_print_gstin', get_setting('company_gstin', '')));
$companyLogoUrl = trim((string) get_setting('company_logo_url', ''));
$headerFont     = get_setting('print_header_font', "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif");
$printFontSize  = get_setting('print_font_size', '13px');

// Bank accounts: resolve selected bank accounts from configuration
$bankAccounts = $bank_accounts ?? [];
if (empty($bankAccounts)) {
    try {
        $bankModel = new \App\Models\BankAccountModel();
        $selectedIdsJson = get_setting('print_selected_bank_ids', '');
        $selectedIds = !empty($selectedIdsJson) ? json_decode($selectedIdsJson, true) : [];
        if (!empty($selectedIds) && is_array($selectedIds)) {
            $bankAccounts = $bankModel->whereIn('id', $selectedIds)->where('status', 'active')->limit(2)->findAll();
        }
        if (empty($bankAccounts)) {
            $bankAccounts = $bankModel->where('account_type', 'Bank')->where('status', 'active')->limit(2)->findAll();
        }
    } catch (\Throwable $e) {}
}

// Terms & Conditions (Sole footer message)
$termsList = $terms ?? [];
if (is_string($termsList)) {
    $termsList = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $termsList)));
}
if (empty($termsList)) {
    $defaultTermsSetting = get_setting('print_default_terms', "1. All disputes will be resolved under Rasipuram jurisdiction.\n2. Please ensure payment is made before the due date to avoid an interest charge of 24% on the outstanding amount.\n3. We are not liable for any loss or damage occurring during transit.\n4. Goods once sold are non-returnable under any circumstances.");
    $termsList = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $defaultTermsSetting)));
}

// Number in words
$totalAmountFloat = (float) ($total_amount ?? 0);
$totalInWords = !empty($total_in_words) ? $total_in_words : (function_exists('number_to_indian_currency_words') ? number_to_indian_currency_words($totalAmountFloat) : '');

// Total Quantity
$totalQty = 0;
foreach (($items ?? []) as $it) {
    $totalQty += (float) ($it['quantity'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($docTitle . ' - ' . $docNumber) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 10mm 10mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: <?= $headerFont ?>;
            font-size: <?= $printFontSize ?>;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
            line-height: 1.35;
        }

        .print-container {
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            background: #fff;
            position: relative;
            /* Outermost enclosing border */
            border: 1.5px solid #000;
        }

        /* 1. Top Header Box */
        .top-header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #000;
        }

        .top-header-table td {
            padding: 8px 8px;
            vertical-align: top;
        }

        .logo-cell {
            width: 95px;
            text-align: center;
            vertical-align: middle !important;
            border-right: 1.5px solid #000;
        }

        .logo-circle {
            width: 78px;
            height: 78px;
            border-radius: 50%;
            border: 2px solid #003366;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: #fff;
            overflow: hidden;
            margin: 0 auto;
        }

        .logo-circle .monogram-r {
            font-family: 'Times New Roman', serif;
            font-size: 54px;
            font-weight: bold;
            font-style: italic;
            color: #e6b800;
            text-shadow: 2px 2px 0 #002244, -1px -1px 0 #002244;
            line-height: 1;
            margin-left: -3px;
        }

        .logo-circle img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .logo-subtext {
            font-size: 9px;
            font-weight: bold;
            color: #222;
            text-align: center;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }

        .company-details-cell {
            padding-left: 10px !important;
            border-right: 1.5px solid #000;
        }

        .company-title {
            font-size: 19px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #002244;
            margin: 0 0 3px 0;
            text-transform: uppercase;
        }

        .company-meta-line {
            font-size: 11px;
            line-height: 1.35;
            color: #111;
        }

        .company-meta-line strong {
            color: #000;
        }

        .doc-title-cell {
            text-align: right;
            vertical-align: middle !important;
            width: 240px;
            padding-right: 12px !important;
        }

        .doc-heading {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #117a38;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }

        .doc-number-line {
            font-size: 13.5px;
            font-weight: bold;
            color: #000;
        }

        /* 2. 3-Column Box (Meta | Bill To | Ship To) */
        .info-grid-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #000;
        }

        .info-grid-table td {
            vertical-align: top;
            padding: 6px 8px;
            font-size: 11px;
            border-right: 1.5px solid #000;
        }

        .info-grid-table td:last-child {
            border-right: none;
        }

        .meta-col {
            width: 33%;
        }

        .bill-col, .ship-col {
            width: 33.5%;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            line-height: 1.35;
        }

        .meta-label {
            font-weight: 600;
            color: #111;
            width: 88px;
        }

        .meta-sep {
            margin: 0 3px;
        }

        .meta-val {
            flex: 1;
            font-weight: bold;
            color: #000;
        }

        .party-heading {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            margin-bottom: 2px;
            text-decoration: underline;
        }

        .party-name {
            font-size: 12.5px;
            font-weight: 800;
            color: #002266;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .party-gstin-highlight {
            font-size: 12.5px;
            font-weight: 800;
            color: #002266;
            margin-bottom: 2px;
        }

        .party-address-text {
            font-size: 10.5px;
            line-height: 1.35;
            color: #222;
        }

        /* 3. Place of Supply Banner Box */
        .place-of-supply-banner {
            border-bottom: 1.5px solid #000;
            padding: 4px 8px;
            font-size: 11.5px;
            font-weight: bold;
            background: #fff;
        }

        /* 4. Complete Boxed Line Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #000;
        }

        .items-table th {
            border-bottom: 1.5px solid #000;
            border-right: 1px solid #000;
            padding: 5px 6px;
            font-size: 11px;
            font-weight: bold;
            background: #fff;
            text-align: left;
        }

        .items-table th:last-child {
            border-right: none;
        }

        .items-table td {
            border-bottom: 1px solid #000;
            border-right: 1px solid #000;
            padding: 4px 6px;
            font-size: 11px;
            vertical-align: top;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        .items-table td:last-child {
            border-right: none;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }

        /* 5. Summary Box (Items in Total on Left | Totals Table on Right) */
        .summary-wrapper-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #000;
        }

        .summary-wrapper-table td {
            vertical-align: top;
            padding: 6px 8px;
        }

        .notes-column {
            width: 62%;
            border-right: 1.5px solid #000;
            font-size: 11.5px;
            display: flex;
            align-items: center;
        }

        .totals-column {
            width: 38%;
            padding: 0 !important;
        }

        .totals-subtable {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-subtable td {
            padding: 3px 8px;
            font-size: 11.5px;
            border-bottom: 1px solid #e0e0e0;
        }

        .totals-subtable tr:last-child td {
            border-bottom: none;
        }

        .totals-subtable tr.grand-total td {
            font-size: 13.5px;
            font-weight: 800;
            border-top: 1.5px solid #000;
            border-bottom: none;
            padding: 5px 8px;
        }

        /* 6. Total In Words Box */
        .total-words-banner {
            border-bottom: 1.5px solid #000;
            padding: 5px 8px;
            font-size: 11.5px;
            font-weight: bold;
        }

        /* 7. Bottom Section: Bank Accounts + Terms + Signature Box */
        .bottom-section-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bottom-section-table td {
            vertical-align: top;
            padding: 6px 8px;
        }

        .bank-terms-cell {
            width: 62%;
            border-right: 1.5px solid #000;
            font-size: 10.5px;
            padding-right: 8px;
        }

        .signature-cell {
            width: 38%;
            text-align: center;
            vertical-align: bottom !important;
            padding-bottom: 16px;
            font-size: 12px;
            font-weight: bold;
        }

        .bank-details-title {
            font-size: 11px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 4px;
        }

        .bank-accounts-grid {
            display: flex;
            gap: 12px;
            margin-bottom: 6px;
            font-size: 10px;
            line-height: 1.3;
        }

        .bank-col {
            flex: 1;
            border-right: 1px dashed #bbb;
            padding-right: 6px;
        }

        .bank-col:last-child {
            border-right: none;
            padding-right: 0;
        }

        .terms-block {
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 4px;
            font-size: 10px;
            line-height: 1.35;
        }

        .terms-title {
            font-weight: bold;
        }

        /* 8. Outer Footer (Outside the box) */
        .page-footer {
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #444;
            letter-spacing: 0.5px;
        }

        .page-footer .powered-by {
            font-weight: bold;
            color: #333;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .print-container { max-width: 100%; }
        }

        .no-print-toolbar {
            background: #f1f3f5;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-print {
            background: #0d6efd;
            color: #fff;
            border: none;
            padding: 6px 14px;
            font-size: 13px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-print:hover {
            background: #0b5ed7;
        }
    </style>
</head>
<body onload="<?= !empty($auto_print) ? 'window.print()' : '' ?>">

    <!-- Floating toolbar when viewing in browser -->
    <div class="no-print no-print-toolbar">
        <div>
            <strong><?= esc($docTitle) ?> Preview:</strong> <?= esc($docNumber) ?>
        </div>
        <div>
            <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Print Document</button>
        </div>
    </div>

    <!-- MAIN ENCLOSED DOCUMENT BOX -->
    <div class="print-container">

        <!-- 1. HEADER SECTION BOX -->
        <table class="top-header-table">
            <tr>
                <td class="logo-cell">
                    <?php if (!empty($companyLogoUrl)): ?>
                        <div class="logo-circle">
                            <img src="<?= esc($companyLogoUrl) ?>" alt="Logo">
                        </div>
                    <?php else: ?>
                        <div class="logo-circle">
                            <span class="monogram-r">R</span>
                        </div>
                        <div class="logo-subtext">Since 1969</div>
                    <?php endif; ?>
                </td>
                <td class="company-details-cell">
                    <div class="company-title"><?= esc($companyName) ?></div>
                    <div class="company-meta-line"><?= nl2br(esc($companyAddress)) ?></div>
                    <?php if (!empty($companyPhone)): ?>
                        <div class="company-meta-line"><strong>Mobile:</strong> <?= esc($companyPhone) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($companyEmail)): ?>
                        <div class="company-meta-line"><strong>Email :</strong> <?= esc($companyEmail) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($companyGstin)): ?>
                        <div class="company-meta-line"><strong>GSTIN :</strong> <?= esc($companyGstin) ?></div>
                    <?php endif; ?>
                </td>
                <td class="doc-title-cell">
                    <div class="doc-heading"><?= esc($docTitle) ?></div>
                    <div class="doc-number-line"><?= esc($docNumberLabel) ?> <?= esc($docNumber) ?></div>
                </td>
            </tr>
        </table>

        <!-- 2. THREE-COLUMN INFO BOX (Meta | Bill To | Ship To) -->
        <table class="info-grid-table">
            <tr>
                <!-- Column 1: Document Meta -->
                <td class="meta-col">
                    <div class="meta-row">
                        <span class="meta-label"><?= esc($date_label ?? 'Invoice Date') ?></span>
                        <span class="meta-sep">:</span>
                        <span class="meta-val"><?= !empty($document_date) ? date('d-m-Y', strtotime($document_date)) : '-' ?></span>
                    </div>
                    <?php if (!empty($payment_terms)): ?>
                        <div class="meta-row">
                            <span class="meta-label">Terms</span>
                            <span class="meta-sep">:</span>
                            <span class="meta-val"><?= esc($payment_terms) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($due_date)): ?>
                        <div class="meta-row">
                            <span class="meta-label">Due Date</span>
                            <span class="meta-sep">:</span>
                            <span class="meta-val"><?= date('d-m-Y', strtotime($due_date)) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($ewaybill_number)): ?>
                        <div class="meta-row">
                            <span class="meta-label">E-Way Bill#</span>
                            <span class="meta-sep">:</span>
                            <span class="meta-val"><?= esc($ewaybill_number) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($transport_name)): ?>
                        <div class="meta-row">
                            <span class="meta-label">Transport</span>
                            <span class="meta-sep">:</span>
                            <span class="meta-val"><?= esc($transport_name) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($waybill_number)): ?>
                        <div class="meta-row">
                            <span class="meta-label">Waybill/LR#</span>
                            <span class="meta-sep">:</span>
                            <span class="meta-val"><?= esc($waybill_number) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($bales_count)): ?>
                        <div class="meta-row">
                            <span class="meta-label">No of Bales</span>
                            <span class="meta-sep">:</span>
                            <span class="meta-val"><?= esc($bales_count) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($reference_number)): ?>
                        <div class="meta-row">
                            <span class="meta-label">Ref / PO#</span>
                            <span class="meta-sep">:</span>
                            <span class="meta-val"><?= esc($reference_number) ?></span>
                        </div>
                    <?php endif; ?>
                </td>

                <!-- Column 2: Bill To -->
                <td class="bill-col">
                    <div class="party-heading"><?= esc($bill_to_heading ?? 'Bill To :') ?></div>
                    <div class="party-name"><?= esc($billing_name ?? '-') ?></div>
                    <?php if (!empty($billing_gstin)): ?>
                        <div class="party-gstin-highlight">GST: <?= esc($billing_gstin) ?></div>
                    <?php endif; ?>
                    <div class="party-address-text">
                        <?php if (!empty($billing_address)): ?>
                            <?= nl2br(esc($billing_address)) ?><br>
                        <?php endif; ?>
                        <?php if (!empty($billing_city) || !empty($billing_pincode) || !empty($billing_state)): ?>
                            <?= esc(implode(', ', array_filter([$billing_city ?? '', !empty($billing_pincode) ? $billing_pincode : '', $billing_state ?? '']))) ?><br>
                        <?php endif; ?>
                        <?php if (!empty($billing_phone)): ?>
                            <strong>Phone:</strong> <?= esc($billing_phone) ?>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- Column 3: Ship To -->
                <td class="ship-col">
                    <div class="party-heading"><?= esc($ship_to_heading ?? 'Ship To :') ?></div>
                    <div class="party-name"><?= esc($shipping_name ?? ($billing_name ?? '-')) ?></div>
                    <?php if (!empty($shipping_gstin) || !empty($billing_gstin)): ?>
                        <div class="party-gstin-highlight">GST: <?= esc($shipping_gstin ?? $billing_gstin) ?></div>
                    <?php endif; ?>
                    <div class="party-address-text">
                        <?php if (!empty($shipping_address)): ?>
                            <?= nl2br(esc($shipping_address)) ?><br>
                        <?php elseif (!empty($billing_address)): ?>
                            <?= nl2br(esc($billing_address)) ?><br>
                        <?php endif; ?>
                        <?php 
                        $sCity = $shipping_city ?? ($billing_city ?? '');
                        $sPin = $shipping_pincode ?? ($billing_pincode ?? '');
                        $sState = $shipping_state ?? ($billing_state ?? '');
                        ?>
                        <?php if (!empty($sCity) || !empty($sPin) || !empty($sState)): ?>
                            <?= esc(implode(', ', array_filter([$sCity, $sPin, $sState]))) ?><br>
                        <?php endif; ?>
                        <?php if (!empty($shipping_phone) || !empty($billing_phone)): ?>
                            <strong>Phone:</strong> <?= esc($shipping_phone ?? $billing_phone) ?>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>

        <!-- 3. PLACE OF SUPPLY BANNER BOX -->
        <?php if (!empty($place_of_supply)): ?>
            <div class="place-of-supply-banner">
                Place Of Supply : <?= esc($place_of_supply) ?>
            </div>
        <?php endif; ?>

        <!-- 4. FULLY BOXED LINE ITEMS TABLE -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">#</th>
                    <th style="width: 45%;">Item</th>
                    <th class="text-center" style="width: 10%;">Meters</th>
                    <th class="text-center" style="width: 12%;">HSN/SAC</th>
                    <th class="text-center" style="width: 8%;">Qty</th>
                    <th class="text-right" style="width: 10%;">Rate</th>
                    <th class="text-right" style="width: 10%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php $idx = 1; foreach ($items as $item): ?>
                        <tr>
                            <td class="text-center"><?= $idx++ ?></td>
                            <td>
                                <span class="fw-bold"><?= esc($item['description'] ?? ($item['name'] ?? '-')) ?></span>
                                <?php if (!empty($item['extra_notes'])): ?>
                                    <div class="text-muted" style="font-size: 10px;"><?= esc($item['extra_notes']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= esc($item['unit'] ?? ($item['meters'] ?? '6.20')) ?></td>
                            <td class="text-center"><?= esc($item['hsn_code'] ?? ($item['hsn_or_sac'] ?? '-')) ?></td>
                            <td class="text-center"><?= (float) ($item['quantity'] ?? 0) ?></td>
                            <td class="text-right"><?= number_format((float) ($item['rate'] ?? 0), 2) ?></td>
                            <td class="text-right fw-bold"><?= number_format((float) ($item['amount'] ?? 0), 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-3">No line items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 5. SUMMARY BOX (Items in Total on Left | Totals on Right) -->
        <table class="summary-wrapper-table">
            <tr>
                <td class="notes-column">
                    <span class="fw-bold">Items in Total <?= (int) $totalQty ?></span>
                </td>
                <td class="totals-column">
                    <table class="totals-subtable">
                        <tr>
                            <td class="fw-bold">Sub Total</td>
                            <td class="text-right fw-bold"><?= number_format((float) ($subtotal ?? 0), 2) ?></td>
                        </tr>
                        <?php if (!empty($discount_amount) && (float) $discount_amount > 0): ?>
                            <tr>
                                <td>Discount</td>
                                <td class="text-right">-<?= number_format((float) $discount_amount, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($cgst_amount) && (float) $cgst_amount > 0): ?>
                            <tr>
                                <td>CGST (<?= !empty($cgst_rate) ? esc($cgst_rate) : '2.5' ?>%)</td>
                                <td class="text-right"><?= number_format((float) $cgst_amount, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($sgst_amount) && (float) $sgst_amount > 0): ?>
                            <tr>
                                <td>SGST (<?= !empty($sgst_rate) ? esc($sgst_rate) : '2.5' ?>%)</td>
                                <td class="text-right"><?= number_format((float) $sgst_amount, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($igst_amount) && (float) $igst_amount > 0): ?>
                            <tr>
                                <td>IGST (<?= !empty($igst_rate) ? esc($igst_rate) : '5.0' ?>%)</td>
                                <td class="text-right"><?= number_format((float) $igst_amount, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($shipping_charge) && (float) $shipping_charge > 0): ?>
                            <tr>
                                <td>Shipping Charge</td>
                                <td class="text-right"><?= number_format((float) $shipping_charge, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($roundoff_amount) && abs((float) $roundoff_amount) > 0): ?>
                            <tr>
                                <td>Round Off</td>
                                <td class="text-right"><?= number_format((float) $roundoff_amount, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="grand-total">
                            <td>Total</td>
                            <td class="text-right">₹<?= number_format($totalAmountFloat, 2) ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- 6. TOTAL IN WORDS BOX -->
        <div class="total-words-banner">
            Total In Words : <?= esc($totalInWords) ?>
        </div>

        <!-- 7. BOTTOM SECTION: BANK DETAILS, TERMS (SOLE FOOTER MESSAGE) & SIGNATURE BOX -->
        <table class="bottom-section-table">
            <tr>
                <td class="bank-terms-cell">
                    <?php if (!empty($bankAccounts)): ?>
                        <div class="bank-details-title">Bank Account Details</div>
                        <div class="bank-accounts-grid">
                            <?php foreach ($bankAccounts as $bk): ?>
                                <div class="bank-col">
                                    <div><strong>Bank Name:</strong> <?= esc($bk['bank_name']) ?></div>
                                    <div><strong>Account Number:</strong> <?= esc($bk['account_number']) ?></div>
                                    <div><strong>Account Name:</strong> <?= esc($companyName) ?></div>
                                    <div><strong>Branch:</strong> <?= esc($bk['branch_name'] ?? 'Rasipuram') ?></div>
                                    <div><strong>IFSC Code:</strong> <?= esc($bk['ifsc_code']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($termsList)): ?>
                        <div class="terms-block">
                            <span class="terms-title">Terms & Conditions : </span>
                            <?php foreach ($termsList as $tIdx => $termLine): ?>
                                <span><?= esc($termLine) ?> </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="signature-cell">
                    <div>Authorized Signature</div>
                </td>
            </tr>
        </table>

    </div><!-- END .print-container -->

    <!-- 8. FOOTER BELOW BOX -->
    <div class="page-footer" style="max-width: 820px; margin: 8px auto 0 auto;">
        <div class="powered-by">POWERED BY <?= esc($companyName) ?></div>
        <div>1</div>
    </div>

</body>
</html>
