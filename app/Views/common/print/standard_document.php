<?php
/**
 * Exact Standard Boxed Document Print Layout
 * Perfectly replicated from the original standard invoice layout:
 * 
 * 1. Top Header: Borderless layout with circular logo on left, company details in middle, Document heading & Number on right.
 * 2. Invoice Details Box: Solid outer border with 3 columns:
 *    - Col 1: Invoice Date, Terms, Due Date, E-Way Bill#, Transport, No of Bales (with vertical right border)
 *    - Col 2: Bill To party details (with vertical right border)
 *    - Col 3: Ship To party details
 * 3. Place of Supply bar: Full width bordered bar.
 * 4. Line Items Table:
 *    - Boxed table with outer border.
 *    - Bold table header with vertical column separators (#, Item, Meters, HSN/SAC, Qty, Rate, Amount).
 *    - Items rows with vertical dividers between all columns.
 *    - Bottom row of table: Left cell has Items in Total + Notes; Right cell has Sub Total, CGST, SGST, IGST, Total.
 * 5. Total In Words bar: Enclosed box below items table.
 * 6. Bottom Box: Enclosed box containing:
 *    - Left: Bank Account Details (2 columns separated by vertical line), followed by horizontal divider and Terms & Conditions.
 *    - Right: Authorized Signature area separated by vertical line.
 * 7. Page footer: POWERED BY on left, page number on right.
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
$companyAddress = str_replace(['\r\n', '\r', '\n'], "\n", $companyAddress);

$companyPhone   = trim((string) get_setting('company_print_phone', get_setting('company_phone', get_setting('company_mobile', ''))));
$companyEmail   = trim((string) get_setting('company_print_email', get_setting('company_email', '')));
$companyGstin   = trim((string) get_setting('company_print_gstin', get_setting('company_gstin', '')));
$companyLogoUrl = trim((string) get_setting('company_logo_url', ''));
$headerFont     = get_setting('print_header_font', "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif");
$printFontSize  = get_setting('print_font_size', '12px');

// Bank accounts
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

// Terms & Conditions (Always fetched directly from Settings as requested by user)
$termsSetting = trim((string) get_setting('print_default_terms', ''));
if (empty($termsSetting)) {
    // If not set in settings, fallback to document terms or default 4 terms
    $termsSetting = !empty($terms) ? (is_array($terms) ? implode("\n", $terms) : $terms) : "1. All disputes will be resolved under Rasipuram jurisdiction.\n2. Please ensure payment is made before the due date to avoid an interest charge of 24% on the outstanding amount.\n3. We are not liable for any loss or damage occurring during transit.\n4. Goods once sold are non-returnable under any circumstances.";
}

// Convert literal \r\n, \n to real newlines and split into clean lines
$termsSetting = str_replace(['\r\n', '\r', '\n'], "\n", $termsSetting);
$termsList = array_filter(array_map('trim', explode("\n", $termsSetting)));

// Number in words
$totalAmountFloat = (float) ($total_amount ?? 0);
$totalInWords = !empty($total_in_words) ? $total_in_words : (function_exists('number_to_indian_currency_words') ? number_to_indian_currency_words($totalAmountFloat) : '');

// Total Quantity
$totalQty = 0;
foreach (($items ?? []) as $it) {
    $totalQty += (float) ($it['quantity'] ?? 0);
}

// Calculate minimum rows to cleanly fill the page and prevent empty whitespace
$itemCount = count($items ?? []);
$minRowsNeeded = 0;
if ($itemCount > 0 && $itemCount < 14) {
    $minRowsNeeded = 14 - $itemCount;
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
            margin: 8mm 8mm 8mm 8mm;
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
            padding: 0;
            line-height: 1.35;
            font-weight: 500;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        .document-wrapper {
            width: 100%;
            margin: 0;
            background: #fff;
            color: #000;
            border: 1.2px solid #000;
            box-sizing: border-box;
        }

        /* ----------------------------------------------------
           1. TOP HEADER (BORDERLESS WITH MONOGRAM / LOGO)
           ---------------------------------------------------- */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.2px solid #000;
            margin-bottom: 0px;
            padding: 0;
        }

        .header-table td {
            vertical-align: top;
            padding: 6px 8px;
            border: none;
        }

        .header-logo-cell {
            width: 80px;
            text-align: left;
            vertical-align: middle !important;
        }

        .logo-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 2px solid #003366;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            overflow: hidden;
        }

        .logo-circle .monogram-r {
            font-family: 'Times New Roman', serif;
            font-size: 50px;
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
            font-size: 9.5px;
            font-weight: 800;
            color: #000;
            text-align: center;
            margin-top: 2px;
            width: 70px;
            letter-spacing: 0.5px;
        }

        .header-company-cell {
            padding-left: 10px !important;
        }

        .company-name {
            font-size: 19px;
            font-weight: 900;
            letter-spacing: 0.5px;
            color: #002244;
            margin: 0 0 2px 0;
            text-transform: uppercase;
        }

        .company-text-line {
            font-size: 11px;
            line-height: 1.35;
            color: #000;
            font-weight: 550;
        }

        .header-title-cell {
            text-align: right;
            vertical-align: middle !important;
            width: 240px;
        }

        .doc-title-main {
            font-size: 19px;
            font-weight: 900;
            letter-spacing: 1px;
            color: #117a38;
            margin: 0 0 3px 0;
            text-transform: uppercase;
        }

        .doc-number-main {
            font-size: 13.5px;
            font-weight: 800;
            color: #000;
        }

        /* ----------------------------------------------------
           2. INVOICE DETAILS BOX (OUTER BORDER + 3 COLUMNS)
           ---------------------------------------------------- */
        .invoice-details-box {
            width: 100%;
            border-collapse: collapse;
            border-top: none;
            border-left: none;
            border-right: none;
            border-bottom: 1.2px solid #000;
            margin-bottom: 0px;
        }

        .invoice-details-box td {
            vertical-align: top;
            padding: 5px 7px;
            font-size: 11px;
            border-right: 1.2px solid #000;
            border-top: none;
            border-bottom: none;
            border-left: none;
            color: #000;
        }

        .invoice-details-box td:last-child {
            border-right: none;
        }

        .meta-col {
            width: 32%;
        }

        .bill-col, .ship-col {
            width: 34%;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 1px 0 !important;
            border: none !important;
            font-size: 11px;
            color: #000;
            vertical-align: top !important;
        }

        .meta-table td.lbl {
            width: 82px;
            font-weight: 700;
            white-space: nowrap;
        }

        .meta-table td.sep {
            width: 10px;
            text-align: center;
            font-weight: 700;
        }

        .meta-table td.val {
            font-weight: 700;
        }

        .party-lbl {
            font-size: 11.5px;
            font-weight: 800;
            color: #000;
            margin-bottom: 3px;
        }

        .party-name-bold {
            font-size: 13px;
            font-weight: 900;
            color: #002266;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .party-gst-bold {
            font-size: 13px;
            font-weight: 900;
            color: #002266;
            margin-bottom: 3px;
        }

        .party-addr-text {
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            font-weight: 550;
        }

        /* ----------------------------------------------------
           3. PLACE OF SUPPLY BAR (ENCLOSED BOX)
           ---------------------------------------------------- */
        .pos-bar-box {
            width: 100%;
            border-left: none;
            border-right: none;
            border-top: none;
            border-bottom: 1.2px solid #000;
            padding: 5px 8px;
            font-size: 12px;
            font-weight: 800;
            background: #fff;
            color: #000;
        }

        /* ----------------------------------------------------
           4. LINE ITEMS TABLE (FULLY BOXED GRID)
           ---------------------------------------------------- */
        .items-grid-table {
            width: 100%;
            border-collapse: collapse;
            border-left: none;
            border-right: none;
            border-top: none;
            border-bottom: 1.2px solid #000;
            color: #000;
        }

        .items-grid-table th {
            border-top: none;
            border-bottom: 1.2px solid #000;
            border-left: none;
            border-right: 1.2px solid #000;
            padding: 6px 6px;
            font-size: 11.5px;
            font-weight: 800;
            background: #fff;
            color: #000;
            text-align: left;
        }

        .items-grid-table th:last-child {
            border-right: none;
        }

        .items-grid-table td {
            border-top: none;
            border-bottom: 1.2px solid #000;
            border-left: none;
            border-right: 1.2px solid #000;
            padding: 4px 6px;
            font-size: 11px;
            font-weight: 550;
            color: #000;
            vertical-align: top;
            height: 22px;
        }

        .items-grid-table tr.blank-filler-row td {
            height: 22px;
            padding: 0 6px;
            border-top: none;
            border-bottom: 1.2px solid #000;
            border-left: none;
            border-right: 1.2px solid #000;
            color: transparent;
        }

        .items-grid-table td:last-child {
            border-right: none;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: 800; }

        /* Summary row inside the table */
        .summary-notes-td {
            border-right: 1.2px solid #000 !important;
            border-bottom: none !important;
            padding: 8px !important;
            vertical-align: top !important;
            font-weight: 600;
            font-size: 12px;
        }

        .summary-totals-td {
            padding: 0 !important;
            border-right: none !important;
            border-bottom: none !important;
            vertical-align: top !important;
            min-width: 260px;
            width: 35%;
        }

        .totals-inner-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-inner-table td {
            border: none !important;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 600;
            color: #000;
            white-space: nowrap;
        }

        .totals-inner-table td.total-label {
            text-align: left;
            white-space: nowrap;
        }

        .totals-inner-table td.total-value {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .totals-inner-table tr.total-row td {
            border-top: 1.2px solid #000 !important;
            font-size: 14px;
            font-weight: 900;
            padding: 6px 8px;
            white-space: nowrap;
        }

        /* ----------------------------------------------------
           5. TOTAL IN WORDS BAR (BOXED)
           ---------------------------------------------------- */
        .total-words-box {
            width: 100%;
            border-left: none;
            border-right: none;
            border-top: none;
            border-bottom: 1.2px solid #000;
            padding: 6px 8px;
            font-size: 12px;
            font-weight: 800;
            color: #000;
        }

        /* ----------------------------------------------------
           6. BOTTOM BANK ACCOUNTS & TERMS BOX
           ---------------------------------------------------- */
        .bottom-details-box {
            width: 100%;
            border-collapse: collapse;
            border-left: none;
            border-right: none;
            border-top: none;
            border-bottom: 1.2px solid #000;
            color: #000;
        }

        .bottom-details-box td {
            vertical-align: top;
            padding: 6px 8px;
            border-top: none;
            border-bottom: none;
        }

        .bank-terms-left {
            width: 63%;
            border-right: 1.2px solid #000 !important;
            border-left: none !important;
            font-size: 11px;
            padding-right: 8px;
        }

        .signature-right {
            width: 37%;
            border-right: none !important;
            border-left: none !important;
            text-align: center;
            vertical-align: bottom !important;
            padding-bottom: 18px;
            font-size: 13px;
            font-weight: 800;
            color: #000;
        }

        .bank-sec-title {
            font-size: 11.5px;
            font-weight: 800;
            text-decoration: underline;
            margin-bottom: 4px;
            color: #000;
        }

        .bank-accounts-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .bank-entry-cell {
            vertical-align: top;
            width: 50%;
            padding: 0 8px 0 0;
            font-size: 10.5px;
            line-height: 1.38;
            font-weight: 550;
            color: #000;
        }

        .bank-entry-cell:first-child {
            border-right: 1.2px solid #000;
            padding-right: 8px;
        }

        .bank-entry-cell:last-child {
            padding-left: 8px;
        }

        .terms-wrap {
            border-top: 1.2px solid #000;
            padding-top: 5px;
            margin-top: 4px;
            font-size: 10.5px;
            line-height: 1.4;
            font-weight: 600;
            color: #000;
        }

        /* ----------------------------------------------------
           PRINT PAGE-BREAK AND BOX INTEGRITY
           ---------------------------------------------------- */
        .items-grid-table {
            page-break-inside: auto;
        }

        .items-grid-table thead {
            display: table-header-group;
        }

        .items-grid-table thead th {
            border-top: none;
            border-bottom: 1.2px solid #000;
        }

        .items-grid-table tfoot {
            display: table-footer-group;
        }

        .items-grid-table tfoot td {
            height: 0px !important;
            padding: 0 !important;
            margin: 0 !important;
            border-top: 1.2px solid #000 !important;
            border-bottom: none !important;
            border-left: none !important;
            border-right: none !important;
            line-height: 0 !important;
            font-size: 0 !important;
        }

        .items-grid-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .items-grid-table th,
        .items-grid-table td {
            box-sizing: border-box;
        }

        .invoice-details-box,
        .pos-bar-box,
        .total-words-box,
        .bottom-details-box,
        .summary-totals-td,
        .summary-notes-td,
        .totals-inner-table tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 5mm 6mm 4mm 6mm;
            }
            body { 
                padding: 0;
                margin: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .document-wrapper {
                width: 100%;
                max-width: 100%;
                margin: 0;
                border: 1.2px solid #000 !important;
                box-sizing: border-box;
                page-break-after: avoid;
            }
            .items-grid-table {
                border-collapse: collapse !important;
            }
            .items-grid-table thead {
                display: table-header-group !important;
            }
            .items-grid-table thead th {
                border-top: none !important;
                border-bottom: 1.2px solid #000 !important;
            }
            .items-grid-table tfoot {
                display: table-footer-group !important;
            }
            .items-grid-table tfoot td {
                border-top: 1.2px solid #000 !important;
                height: 0px !important;
                padding: 0 !important;
            }
            .items-grid-table tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .invoice-details-box,
            .pos-bar-box,
            .total-words-box,
            .bottom-details-box {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body onload="<?= !empty($auto_print) ? 'window.print()' : '' ?>">

    <div class="document-wrapper">

        <!-- 1. TOP HEADER (BORDERLESS WITH MONOGRAM / LOGO) -->
        <table class="header-table">
            <tr>
                <td class="header-logo-cell">
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
                <td class="header-company-cell">
                    <div class="company-name"><?= esc($companyName) ?></div>
                    <div class="company-text-line"><?= nl2br(esc($companyAddress)) ?></div>
                    <?php if (!empty($companyPhone)): ?>
                        <div class="company-text-line"><strong>Mobile:</strong> <?= esc($companyPhone) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($companyEmail)): ?>
                        <div class="company-text-line"><strong>Email :</strong> <?= esc($companyEmail) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($companyGstin)): ?>
                        <div class="company-text-line"><strong>GSTIN :</strong> <?= esc($companyGstin) ?></div>
                    <?php endif; ?>
                </td>
                <td class="header-title-cell">
                    <div class="doc-title-main"><?= esc($docTitle) ?></div>
                    <div class="doc-number-main"><?= esc($docNumberLabel) ?> <?= esc($docNumber) ?></div>
                </td>
            </tr>
        </table>

        <!-- 2. INVOICE DETAILS BOX (OUTER BORDER + 3 VERTICAL SECTIONS) -->
        <table class="invoice-details-box">
            <tr>
                <!-- Col 1: Metadata -->
                <td class="meta-col">
                    <table class="meta-table">
                        <tr>
                            <td class="lbl"><?= esc($date_label ?? 'Invoice Date') ?></td>
                            <td class="sep">:</td>
                            <td class="val"><?= !empty($document_date) ? date('d-m-Y', strtotime($document_date)) : '-' ?></td>
                        </tr>
                        <?php if (!empty($payment_terms)): ?>
                            <tr>
                                <td class="lbl">Terms</td>
                                <td class="sep">:</td>
                                <td class="val"><?= esc($payment_terms) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($due_date)): ?>
                            <tr>
                                <td class="lbl">Due Date</td>
                                <td class="sep">:</td>
                                <td class="val"><?= date('d-m-Y', strtotime($due_date)) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($ewaybill_number)): ?>
                            <tr>
                                <td class="lbl">E-Way Bill#</td>
                                <td class="sep">:</td>
                                <td class="val"><?= esc($ewaybill_number) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($transport_name)): ?>
                            <tr>
                                <td class="lbl">Transport</td>
                                <td class="sep">:</td>
                                <td class="val"><?= esc($transport_name) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($waybill_number)): ?>
                            <tr>
                                <td class="lbl">Waybill/LR#</td>
                                <td class="sep">:</td>
                                <td class="val"><?= esc($waybill_number) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($bales_count)): ?>
                            <tr>
                                <td class="lbl">No of Bales</td>
                                <td class="sep">:</td>
                                <td class="val"><?= esc($bales_count) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($reference_number)): ?>
                            <tr>
                                <td class="lbl">Ref / PO#</td>
                                <td class="sep">:</td>
                                <td class="val"><?= esc($reference_number) ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </td>

                <!-- Col 2: Bill To -->
                <td class="bill-col">
                    <div class="party-lbl"><?= esc($bill_to_heading ?? 'Bill To :') ?></div>
                    <div class="party-name-bold"><?= esc($billing_name ?? '-') ?></div>
                    <?php if (!empty($billing_gstin)): ?>
                        <div class="party-gst-bold">GST: <?= esc($billing_gstin) ?></div>
                    <?php endif; ?>
                    <div class="party-addr-text">
                        <?php if (!empty($billing_address)): ?>
                            <?= nl2br(esc($billing_address)) ?><br>
                        <?php endif; ?>
                        <?php if (!empty($billing_city) || !empty($billing_pincode) || !empty($billing_state)): ?>
                            <?= esc(implode(', ', array_filter([$billing_city ?? '', !empty($billing_pincode) ? $billing_pincode : '', $billing_state ?? '']))) ?><br>
                        <?php endif; ?>
                        <?php if (!empty($billing_phone)): ?>
                            Phone: <?= esc($billing_phone) ?>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- Col 3: Ship To -->
                <td class="ship-col">
                    <div class="party-lbl"><?= esc($ship_to_heading ?? 'Ship To :') ?></div>
                    <div class="party-name-bold"><?= esc($shipping_name ?? ($billing_name ?? '-')) ?></div>
                    <?php if (!empty($shipping_gstin) || !empty($billing_gstin)): ?>
                        <div class="party-gst-bold">GST: <?= esc($shipping_gstin ?? $billing_gstin) ?></div>
                    <?php endif; ?>
                    <div class="party-addr-text">
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
                            Phone: <?= esc($shipping_phone ?? $billing_phone) ?>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>

        <!-- 3. PLACE OF SUPPLY BAR (BOXED) -->
        <?php if (!empty($place_of_supply)): ?>
            <div class="pos-bar-box">
                Place Of Supply : <?= esc($place_of_supply) ?>
            </div>
        <?php endif; ?>

        <!-- 4. FULLY BOXED LINE ITEMS TABLE -->
        <table class="items-grid-table">
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
            <tfoot>
                <tr>
                    <td colspan="7"></td>
                </tr>
            </tfoot>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php $idx = 1; foreach ($items as $item): ?>
                        <tr>
                            <td class="text-center"><?= $idx++ ?></td>
                            <td>
                                <span><?= esc($item['description'] ?? ($item['name'] ?? '-')) ?></span>
                                <?php if (!empty($item['extra_notes'])): ?>
                                    <div class="text-muted" style="font-size: 10px;"><?= esc($item['extra_notes']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= esc($item['unit'] ?? ($item['meters'] ?? '6.20')) ?></td>
                            <td class="text-center"><?= esc($item['hsn_code'] ?? ($item['hsn_or_sac'] ?? '-')) ?></td>
                            <td class="text-center"><?= (float) ($item['quantity'] ?? 0) ?></td>
                            <td class="text-right"><?= number_format((float) ($item['rate'] ?? 0), 2) ?></td>
                            <td class="text-right"><?= number_format((float) ($item['amount'] ?? 0), 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php for ($bi = 0; $bi < $minRowsNeeded; $bi++): ?>
                        <tr class="blank-filler-row">
                            <td class="text-center">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class="text-center">&nbsp;</td>
                            <td class="text-center">&nbsp;</td>
                            <td class="text-center">&nbsp;</td>
                            <td class="text-right">&nbsp;</td>
                            <td class="text-right">&nbsp;</td>
                        </tr>
                    <?php endfor; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-3">No line items found.</td>
                    </tr>
                <?php endif; ?>

                <!-- Table Summary Row (Items in Total & Notes on left | Financial Totals on right) -->
                <tr>
                    <td colspan="4" class="summary-notes-td">
                        <div class="fw-bold mb-2">Items in Total <?= (int) $totalQty ?></div>
                        <?php if (!empty($notes)): ?>
                            <div><strong>Notes :</strong> <?= nl2br(esc($notes)) ?></div>
                        <?php endif; ?>
                    </td>
                    <td colspan="3" class="summary-totals-td">
                        <table class="totals-inner-table">
                            <tr>
                                <td class="total-label">Sub Total</td>
                                <td class="total-value"><?= number_format((float) ($subtotal ?? 0), 2) ?></td>
                            </tr>
                            <?php if (!empty($discount_amount) && (float) $discount_amount > 0): ?>
                                <tr>
                                    <td class="total-label">Discount</td>
                                    <td class="total-value">-<?= number_format((float) $discount_amount, 2) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($cgst_amount) && (float) $cgst_amount > 0): ?>
                                <tr>
                                    <td class="total-label">CGST (<?= !empty($cgst_rate) ? esc($cgst_rate) : '2.5' ?>%)</td>
                                    <td class="total-value"><?= number_format((float) $cgst_amount, 2) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($sgst_amount) && (float) $sgst_amount > 0): ?>
                                <tr>
                                    <td class="total-label">SGST (<?= !empty($sgst_rate) ? esc($sgst_rate) : '2.5' ?>%)</td>
                                    <td class="total-value"><?= number_format((float) $sgst_amount, 2) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($igst_amount) && (float) $igst_amount > 0): ?>
                                <tr>
                                    <td class="total-label">IGST (<?= !empty($igst_rate) ? esc($igst_rate) : '5.0' ?>%)</td>
                                    <td class="total-value"><?= number_format((float) $igst_amount, 2) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($shipping_charge) && (float) $shipping_charge > 0): ?>
                                <tr>
                                    <td class="total-label">Shipping Charge</td>
                                    <td class="total-value"><?= number_format((float) $shipping_charge, 2) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($roundoff_amount) && abs((float) $roundoff_amount) > 0): ?>
                                <tr>
                                    <td class="total-label">Round Off</td>
                                    <td class="total-value"><?= number_format((float) $roundoff_amount, 2) ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr class="total-row">
                                <td class="total-label">Total</td>
                                <td class="total-value"><?= number_format($totalAmountFloat, 2) ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- 5. TOTAL IN WORDS BAR (BOXED) -->
        <div class="total-words-box">
            Total In Words : <?= esc($totalInWords) ?>
        </div>

        <!-- 6. BOTTOM BANK ACCOUNTS & TERMS BOX -->
        <table class="bottom-details-box">
            <tr>
                <td class="bank-terms-left">
                    <?php if (!empty($bankAccounts)): ?>
                        <div class="bank-sec-title">Bank Account Details</div>
                        <table class="bank-accounts-table">
                            <tr>
                                <?php foreach ($bankAccounts as $bk): ?>
                                    <td class="bank-entry-cell">
                                        <div><strong>Bank Name:</strong> <?= esc($bk['bank_name']) ?></div>
                                        <div><strong>Account Number:</strong> <?= esc($bk['account_number']) ?></div>
                                        <div><strong>Account Name:</strong><br><?= esc($companyName) ?></div>
                                        <div><strong>Branch:</strong> <?= esc($bk['branch_name'] ?? 'Rasipuram') ?></div>
                                        <div><strong>IFSC Code:</strong> <?= esc($bk['ifsc_code']) ?></div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </table>
                    <?php endif; ?>

                    <?php if (!empty($termsList)): ?>
                        <div class="terms-wrap">
                            <strong>Terms & Conditions : </strong>
                            <?php foreach ($termsList as $tIdx => $termLine): ?>
                                <span><?= esc($termLine) ?> </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="signature-right">
                    <div>Authorized Signature</div>
                </td>
            </tr>
        </table>

    </div><!-- END .document-wrapper -->

</body>
</html>
