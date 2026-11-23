<?php
/**
 * Standard Boxed Document Print Layout (Dynamically Configured via Print Settings)
 *
 * Reads field visibility, labels, ordering, bold, alignment, font size, column widths,
 * and paper orientation from PrintConfig (saved in print_configurations table).
 * Default configuration matches the original standard invoice layout 1:1.
 *
 * All styles live in common/print/print_styles.php (central print stylesheet + border spec).
 */

use App\Libraries\PrintConfig;

$docType     = $document_type ?? 'invoice';
$printConfig = PrintConfig::getForDocument($docType, $print_config_override ?? null);
$cfgFields   = $printConfig['fields'] ?? [];

$f = fn (string $key): array => $cfgFields[$key] ?? ['visible' => false, 'label' => '', 'bold' => true, 'align' => 'left', 'font_size' => '10.5px', 'width' => 10];
$isVis = fn (string $key): bool => !empty($cfgFields[$key]['visible']);
$fldStyle = function (string $key, bool $withAlign = false) use ($f): string {
    $c = $f($key);
    $s = 'font-weight:' . (!empty($c['bold']) ? 'bold' : 'normal') . ';';
    if (!empty($c['font_size'])) {
        $s .= 'font-size:' . esc($c['font_size']) . ';';
    }
    if ($withAlign && !empty($c['align'])) {
        $s .= 'text-align:' . esc($c['align']) . ';';
    }
    return $s;
};

$docTitle       = $isVis('doc_title') && !empty($f('doc_title')['label']) ? $f('doc_title')['label'] : ($document_title ?? 'TAX INVOICE');
$docNumberLabel = $f('doc_number')['label'] ?: ($document_number_label ?? 'Invoice#');
$docNumber      = $document_number ?? '';

// Company details
$companyName    = trim((string) get_setting('company_print_name', get_setting('org_name', get_setting('app_name', 'RASI HANDLOOM TEXTILES'))));
$companyAddress = (string) get_setting('company_print_address', '');
if (trim($companyAddress) === '') {
    $lines = array_filter([
        get_setting('company_address_1', ''),
        get_setting('company_address_2', ''),
        trim(get_setting('company_city', '') . ' ' . get_setting('company_pincode', '')),
    ]);
    $companyAddress = implode(",\n", $lines);
}
$companyAddress = str_replace(['\r\n', '\r', '\n'], "\n", $companyAddress);
$companyPhone   = trim((string) get_setting('company_print_phone', get_setting('company_phone', get_setting('company_mobile', ''))));
$companyEmail   = trim((string) get_setting('company_print_email', get_setting('company_email', '')));
$companyGstin   = trim((string) get_setting('company_print_gstin', get_setting('company_gstin', '')));
$companyWebsite = trim((string) get_setting('company_website', get_setting('app_url', '')));
$companyUpi     = trim((string) get_setting('company_upi_id', ''));
$companyUpiName = trim((string) get_setting('company_upi_name', $companyName));

$companyLogoUrl      = trim((string) get_setting('company_logo_url', ''));
$defaultMonogramPath = FCPATH . 'assets/images/default_monogram_r.png';
if (empty($companyLogoUrl) && file_exists($defaultMonogramPath)) {
    $companyLogoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($defaultMonogramPath));
} elseif (!empty($companyLogoUrl) && file_exists(str_replace(base_url(), FCPATH, $companyLogoUrl))) {
    $localPath = str_replace(base_url(), FCPATH, $companyLogoUrl);
    $companyLogoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($localPath));
} else {
    $companyLogoBase64 = !empty($companyLogoUrl) ? $companyLogoUrl : '';
}

// Bank accounts
$bankAccounts = $bank_accounts ?? [];
if (empty($bankAccounts) && $isVis('bank_details')) {
    try {
        $bankModel       = new \App\Models\BankAccountModel();
        $selectedIdsJson = get_setting('print_selected_bank_ids', '');
        $selectedIds     = !empty($selectedIdsJson) ? json_decode($selectedIdsJson, true) : [];
        if (!empty($selectedIds) && is_array($selectedIds)) {
            $bankAccounts = $bankModel->whereIn('id', $selectedIds)->where('status', 'active')->limit(2)->findAll();
        }
        if (empty($bankAccounts)) {
            $bankAccounts = $bankModel->where('account_type', 'Bank')->where('status', 'active')->limit(2)->findAll();
        }
    } catch (\Throwable $e) {
    }
}

// Terms & Conditions (configured in Print Settings per document type, fallback to global setting / document terms)
$termsSetting = trim((string) ($printConfig['default_terms'] ?? get_setting('print_default_terms', '')));
if ($termsSetting === '') {
    $termsSetting = !empty($terms)
        ? (is_array($terms) ? implode("\n", $terms) : $terms)
        : "1. All disputes will be resolved under Rasipuram jurisdiction.\n2. Please ensure payment is made before the due date to avoid an interest charge of 24% on the outstanding amount.\n3. We are not liable for any loss or damage occurring during transit.\n4. Goods once sold are non-returnable under any circumstances.";
}
$termsSetting = str_replace(['\r\n', '\r', '\n'], "\n", $termsSetting);
$termsList    = array_filter(array_map('trim', explode("\n", $termsSetting)));

// Notes: use document's own modified notes if non-empty, otherwise fall back to Print Settings default_notes
$notes = trim((string) ($notes ?? ''));
if ($notes === '') {
    $notes = trim((string) ($printConfig['default_notes'] ?? ''));
}

// Totals & Tax Type (Intra-state vs Inter-state)
$totalAmountFloat = (float) ($total_amount ?? 0);
$totalInWords     = !empty($total_in_words) ? $total_in_words : (function_exists('number_to_indian_currency_words') ? number_to_indian_currency_words($totalAmountFloat) : '');

$cgstAmt = (float) ($cgst_amount ?? 0);
$sgstAmt = (float) ($sgst_amount ?? 0);
$igstAmt = (float) ($igst_amount ?? 0);
if (isset($is_inter_state)) {
    $isInterState = (bool) $is_inter_state;
} elseif ($igstAmt > 0) {
    $isInterState = true;
} else {
    $isInterState = false;
}

$totalQty = 0;
foreach (($items ?? []) as $it) {
    $totalQty += (float) ($it['quantity'] ?? 0);
}

$itemCount     = count($items ?? []);
$minRowsNeeded = ($itemCount > 0 && $itemCount < 14 && empty($is_preview)) ? (14 - $itemCount) : 0;

// Build visible item table columns (respecting drag-and-drop order + GST applicability rule)
$visibleItemCols = [];
$separateDescCol = $isVis('item_description');
foreach (PrintConfig::getSectionFields($printConfig, 'items', true) as $colKey => $colDef) {
    if ($isInterState && in_array($colKey, ['item_cgst_pct', 'item_cgst_amt', 'item_sgst_pct', 'item_sgst_amt'], true)) {
        continue;
    }
    if (!$isInterState && in_array($colKey, ['item_igst_pct', 'item_igst_amt'], true)) {
        continue;
    }
    $visibleItemCols[$colKey] = $colDef;
}
if (empty($visibleItemCols)) {
    $visibleItemCols['item_name'] = $f('item_name');
}

// Normalize column widths proportionally to 100%
$totalColWidth = array_sum(array_map(fn ($c) => (float) ($c['width'] ?? 10), $visibleItemCols));
$totalColWidth = $totalColWidth > 0 ? $totalColWidth : 100;
foreach ($visibleItemCols as $colKey => &$colDef) {
    $colDef['norm_width'] = round(((float) ($colDef['width'] ?? 10) / $totalColWidth) * 100, 2);
}
unset($colDef);
$colCount = count($visibleItemCols);
$leftSpan = max(1, (int) floor($colCount / 2) + ($colCount > 3 ? 1 : 0));
if ($leftSpan >= $colCount) {
    $leftSpan = max(1, $colCount - 1);
}
$rightSpan = max(1, $colCount - $leftSpan);

// Build Section B / F metadata rows dynamically in configured order
$metaFieldKeys = ['doc_date', 'payment_terms', 'due_date', 'ewaybill_number', 'transport_name', 'vehicle_number', 'waybill_number', 'bales_count', 'reference_number', 'order_number', 'reverse_charge', 'delivery_details'];
$metaItems = [];
foreach ($cfgFields as $k => $def) {
    if (!in_array($k, $metaFieldKeys, true) || empty($def['visible'])) {
        continue;
    }
    $val = null;
    switch ($k) {
        case 'doc_date':
            $val = !empty($document_date) ? date('d-m-Y', strtotime($document_date)) : '-';
            break;
        case 'payment_terms':
            $val = $payment_terms ?? null;
            break;
        case 'due_date':
            $val = !empty($due_date) ? date('d-m-Y', strtotime($due_date)) : null;
            break;
        case 'ewaybill_number':
            $val = $ewaybill_number ?? null;
            break;
        case 'transport_name':
            $val = $transport_name ?? null;
            break;
        case 'vehicle_number':
            $val = $vehicle_number ?? null;
            break;
        case 'waybill_number':
            $val = $waybill_number ?? null;
            break;
        case 'bales_count':
            $val = $bales_count ?? null;
            break;
        case 'reference_number':
            $val = $reference_number ?? null;
            break;
        case 'order_number':
            $val = $order_number ?? null;
            break;
        case 'reverse_charge':
            $val = $reverse_charge ?? 'No';
            break;
        case 'delivery_details':
            $val = $delivery_details ?? null;
            break;
    }
    if ($val !== null && trim((string) $val) !== '') {
        $metaItems[] = ['key' => $k, 'label' => $def['label'], 'value' => $val];
    }
}

$showBank        = $isVis('bank_details') && !empty($bankAccounts);
$showUpi         = $isVis('upi_details') && !empty($companyUpi);
$showUpiQr       = in_array($docType, ['invoice', 'quotation'], true) && $isVis('upi_qr_code') && $companyUpi !== '' && $totalAmountFloat > 0;
$upiQrDataUri    = '';
if ($showUpiQr) {
    try {
        $qrDesc       = trim($docNumber . ' - ' . ($billing_name ?? ''));
        $upiUri       = \App\Libraries\QrCodeGenerator::buildUpiUri($companyUpi, $companyUpiName ?: $companyName, $totalAmountFloat, $qrDesc);
        $upiQrDataUri = \App\Libraries\QrCodeGenerator::toDataUri($upiUri, 3, 2);
    } catch (\Throwable $e) {
        $showUpiQr = false;
    }
}
$showTerms       = $isVis('terms_conditions') && !empty($termsList);
$showSig         = $isVis('authorised_signature');
$showPrepared    = $isVis('prepared_by');
$showSeal        = $isVis('company_seal');
$showBottomLeft  = $showBank || $showUpi || $showTerms;
$showBottomRight = $showSig || $showPrepared || $showSeal;
$showBottomBox   = $showBottomLeft || $showBottomRight;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($docTitle . ' - ' . $docNumber) ?></title>
    <?= view('common/print/print_styles', [
        'printFontSize' => $printConfig['base_font_size'] ?? '10.5px',
        'paperSize'     => $printConfig['paper_size'] ?? 'A4',
        'orientation'   => $printConfig['orientation'] ?? 'portrait',
        'fontFamily'    => $printConfig['font_family'] ?? 'Helvetica, Arial, sans-serif',
    ]) ?>
</head>
<body onload="<?= !empty($auto_print) ? 'window.print()' : '' ?>">

    <?php if (!empty($auto_print) || !empty($is_preview)): ?>
        <div class="page-frame"></div>
    <?php endif; ?>

    <div class="document-wrapper">

        <!-- 1. TOP HEADER -->
        <?php
        $showLogo     = $isVis('company_logo');
        $showCompInfo = $isVis('company_name') || $isVis('company_address') || $isVis('company_phone') || $isVis('company_email') || $isVis('company_gstin') || $isVis('company_website');
        $showDocHead  = $isVis('doc_title') || $isVis('doc_number');
        ?>
        <?php if ($showLogo || $showCompInfo || $showDocHead): ?>
        <table class="header-table">
            <tr>
                <?php if ($showLogo): ?>
                <td class="header-logo-cell">
                    <?php if (!empty($companyLogoBase64)): ?>
                        <div class="logo-circle">
                            <img src="<?= $companyLogoBase64 ?>" alt="Logo">
                        </div>
                        <?php if (empty($companyLogoUrl)): ?>
                            <div class="logo-subtext">Since 1969</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="logo-circle">
                            <span class="monogram-r">R</span>
                        </div>
                        <div class="logo-subtext">Since 1969</div>
                    <?php endif; ?>
                </td>
                <?php endif; ?>

                <?php if ($showCompInfo): ?>
                <td class="header-company-cell">
                    <?php foreach (PrintConfig::getSectionFields($printConfig, 'company', true) as $ck => $cf): ?>
                        <?php if ($ck === 'company_logo') continue; ?>
                        <?php if ($ck === 'company_name'): ?>
                            <div class="company-name" style="<?= $fldStyle('company_name') ?>"><?= esc($companyName) ?></div>
                        <?php elseif ($ck === 'company_address' && !empty($companyAddress)): ?>
                            <div class="company-text-line" style="<?= $fldStyle('company_address') ?>"><?= nl2br(esc($companyAddress)) ?></div>
                        <?php elseif ($ck === 'company_phone' && !empty($companyPhone)): ?>
                            <div class="company-text-line" style="<?= $fldStyle('company_phone') ?>"><?= esc($cf['label']) ?> <?= esc($companyPhone) ?></div>
                        <?php elseif ($ck === 'company_email' && !empty($companyEmail)): ?>
                            <div class="company-text-line" style="<?= $fldStyle('company_email') ?>"><?= esc($cf['label']) ?> <?= esc($companyEmail) ?></div>
                        <?php elseif ($ck === 'company_gstin' && !empty($companyGstin)): ?>
                            <div class="company-text-line" style="<?= $fldStyle('company_gstin') ?>"><?= esc($cf['label']) ?> <?= esc($companyGstin) ?></div>
                        <?php elseif ($ck === 'company_website' && !empty($companyWebsite)): ?>
                            <div class="company-text-line" style="<?= $fldStyle('company_website') ?>"><?= esc($cf['label']) ?> <?= esc($companyWebsite) ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </td>
                <?php endif; ?>

                <?php if ($showUpiQr && $upiQrDataUri !== ''): ?>
                <td class="header-qr-cell">
                    <img src="<?= $upiQrDataUri ?>" alt="UPI QR">
                    <div class="header-qr-label" style="<?= $fldStyle('upi_qr_code') ?>"><?= esc($f('upi_qr_code')['label']) ?></div>
                </td>
                <?php endif; ?>

                <?php if ($showDocHead): ?>
                <td class="header-title-cell">
                    <?php if ($isVis('doc_title')): ?>
                        <div class="doc-title-main" style="<?= $fldStyle('doc_title', true) ?>"><?= esc($docTitle) ?></div>
                    <?php endif; ?>
                    <?php if ($isVis('doc_number')): ?>
                        <div class="doc-number-main" style="<?= $fldStyle('doc_number', true) ?>"><?= esc($docNumberLabel) ?> <?= esc($docNumber) ?></div>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
        </table>
        <?php endif; ?>

        <!-- 2. DOCUMENT META + BILL TO + SHIP TO -->
        <?php
        $showBillCol = $isVis('billing_address') || $isVis('party_name') || $isVis('party_gstin');
        $showShipCol = $isVis('shipping_address');
        $showMetaCol = !empty($metaItems);
        ?>
        <?php if ($showMetaCol || $showBillCol || $showShipCol): ?>
        <table class="invoice-details-box">
            <tr>
                <?php if ($showMetaCol): ?>
                <td class="meta-col">
                    <table class="meta-table">
                        <?php foreach ($metaItems as $mi): ?>
                        <tr>
                            <td class="lbl" style="<?= $fldStyle($mi['key']) ?>"><?= esc($mi['label']) ?></td>
                            <td class="sep">:</td>
                            <td class="val" style="<?= $fldStyle($mi['key']) ?>"><?= esc($mi['value']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </td>
                <?php endif; ?>

                <?php if ($showBillCol): ?>
                <td class="bill-col">
                    <?php if ($isVis('billing_address')): ?>
                        <div class="party-lbl" style="<?= $fldStyle('billing_address') ?>"><?= esc($f('billing_address')['label'] ?: ($bill_to_heading ?? 'Bill To :')) ?></div>
                    <?php endif; ?>
                    <?php if ($isVis('party_name')): ?>
                        <div class="party-name-bold" style="<?= $fldStyle('party_name') ?>"><?= esc($billing_name ?? '-') ?></div>
                    <?php endif; ?>
                    <?php if ($isVis('party_gstin') && !empty($billing_gstin)): ?>
                        <div class="party-gst-bold" style="<?= $fldStyle('party_gstin') ?>"><?= esc($f('party_gstin')['label']) ?> <?= esc($billing_gstin) ?></div>
                    <?php endif; ?>
                    <div class="party-addr-text">
                        <?php if ($isVis('billing_address') && !empty($billing_address)): ?>
                            <span style="<?= $fldStyle('billing_address') ?>"><?= nl2br(esc($billing_address)) ?></span><br>
                        <?php endif; ?>
                        <?php
                        $bCity  = $isVis('billing_address') ? ($billing_city ?? '') : '';
                        $bPin   = $isVis('billing_address') ? ($billing_pincode ?? '') : '';
                        $bState = $isVis('party_state') ? ($billing_state ?? '') : '';
                        $bLine  = implode(', ', array_filter([$bCity, $bPin, $bState]));
                        ?>
                        <?php if ($bLine !== ''): ?>
                            <span><?= esc($bLine) ?></span>
                            <?php if ($isVis('party_state_code') && !empty($billing_state_code)): ?>
                                (<?= esc($billing_state_code) ?>)
                            <?php endif; ?>
                            <br>
                        <?php elseif ($isVis('party_state_code') && !empty($billing_state_code)): ?>
                            <span><?= esc($f('party_state_code')['label']) ?> <?= esc($billing_state_code) ?></span><br>
                        <?php endif; ?>
                        <?php if ($isVis('party_phone') && !empty($billing_phone)): ?>
                            <span style="<?= $fldStyle('party_phone') ?>"><?= esc($f('party_phone')['label']) ?> <?= esc($billing_phone) ?></span><br>
                        <?php endif; ?>
                        <?php if ($isVis('party_email') && !empty($billing_email)): ?>
                            <span style="<?= $fldStyle('party_email') ?>"><?= esc($f('party_email')['label']) ?> <?= esc($billing_email) ?></span>
                        <?php endif; ?>
                    </div>
                </td>
                <?php endif; ?>

                <?php if ($showShipCol): ?>
                <td class="ship-col">
                    <div class="party-lbl" style="<?= $fldStyle('shipping_address') ?>"><?= esc($f('shipping_address')['label'] ?: ($ship_to_heading ?? 'Ship To :')) ?></div>
                    <?php if ($isVis('party_name')): ?>
                        <div class="party-name-bold" style="<?= $fldStyle('party_name') ?>"><?= esc($shipping_name ?? ($billing_name ?? '-')) ?></div>
                    <?php endif; ?>
                    <?php if ($isVis('party_gstin') && (!empty($shipping_gstin) || !empty($billing_gstin))): ?>
                        <div class="party-gst-bold" style="<?= $fldStyle('party_gstin') ?>"><?= esc($f('party_gstin')['label']) ?> <?= esc($shipping_gstin ?? $billing_gstin) ?></div>
                    <?php endif; ?>
                    <div class="party-addr-text" style="<?= $fldStyle('shipping_address') ?>">
                        <?php if (!empty($shipping_address)): ?>
                            <?= nl2br(esc($shipping_address)) ?><br>
                        <?php elseif (!empty($billing_address)): ?>
                            <?= nl2br(esc($billing_address)) ?><br>
                        <?php endif; ?>
                        <?php
                        $sCity  = $shipping_city ?? ($billing_city ?? '');
                        $sPin   = $shipping_pincode ?? ($billing_pincode ?? '');
                        $sState = $isVis('party_state') ? ($shipping_state ?? ($billing_state ?? '')) : '';
                        $sLine  = implode(', ', array_filter([$sCity, $sPin, $sState]));
                        ?>
                        <?php if ($sLine !== ''): ?>
                            <?= esc($sLine) ?>
                            <?php if ($isVis('party_state_code') && !empty($shipping_state_code ?? ($billing_state_code ?? ''))): ?>
                                (<?= esc($shipping_state_code ?? $billing_state_code) ?>)
                            <?php endif; ?>
                            <br>
                        <?php endif; ?>
                        <?php if ($isVis('party_phone') && (!empty($shipping_phone) || !empty($billing_phone))): ?>
                            <span style="<?= $fldStyle('party_phone') ?>"><?= esc($f('party_phone')['label']) ?> <?= esc($shipping_phone ?? $billing_phone) ?></span>
                        <?php endif; ?>
                    </div>
                </td>
                <?php endif; ?>
            </tr>
        </table>
        <?php endif; ?>

        <!-- 3. PLACE OF SUPPLY BAR -->
        <?php if ($isVis('place_of_supply') && !empty($place_of_supply)): ?>
        <table class="pos-bar-table">
            <tr>
                <td style="<?= $fldStyle('place_of_supply', true) ?>"><?= esc($f('place_of_supply')['label']) ?> <?= esc($place_of_supply) ?></td>
            </tr>
        </table>
        <?php endif; ?>

        <!-- 4. LINE ITEMS TABLE -->
        <table class="items-grid-table">
            <thead>
                <tr>
                    <?php foreach ($visibleItemCols as $colKey => $colDef): ?>
                        <th style="width:<?= $colDef['norm_width'] ?>%;text-align:<?= esc($colDef['align'] ?? 'left') ?>;<?= !empty($colDef['font_size']) ? 'font-size:' . esc($colDef['font_size']) . ';' : '' ?>"><?= esc($colDef['label']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php $idx = 1; foreach ($items as $item): ?>
                        <?php
                        $qty     = (float) ($item['quantity'] ?? 0);
                        $rate    = (float) ($item['rate'] ?? 0);
                        $disc    = (float) ($item['discount'] ?? 0);
                        $amount  = (float) ($item['amount'] ?? 0);
                        $taxable = isset($item['taxable']) ? (float) $item['taxable'] : max(0, ($qty * $rate) - $disc);
                        ?>
                        <tr>
                            <?php foreach ($visibleItemCols as $colKey => $colDef): ?>
                                <?php
                                $cellStyle = 'text-align:' . esc($colDef['align'] ?? 'left') . ';font-weight:' . (!empty($colDef['bold']) ? 'bold' : 'normal') . ';' . (!empty($colDef['font_size']) ? 'font-size:' . esc($colDef['font_size']) . ';' : '');
                                ?>
                                <td style="<?= $cellStyle ?>">
                                    <?php switch ($colKey):
                                        case 'serial_no':
                                            echo $idx++;
                                            break;
                                        case 'item_name':
                                            $lbl = $separateDescCol
                                                ? trim((string) ($item['name'] ?? ($item['description'] ?? '')))
                                                : trim((string) ($item['description'] ?? ($item['name'] ?? '')));
                                            echo esc($lbl !== '' ? $lbl : '-');
                                            if (!empty($item['extra_notes'])) {
                                                echo '<div class="item-extra-notes">' . esc($item['extra_notes']) . '</div>';
                                            }
                                            break;
                                        case 'item_sku':
                                            echo esc($item['sku'] ?? ($item['barcode'] ?? '-'));
                                            break;
                                        case 'item_description':
                                            echo esc($item['description'] ?? '-');
                                            break;
                                        case 'item_unit':
                                            echo esc($item['unit'] ?? ($item['meters'] ?? '6.20'));
                                            break;
                                        case 'item_hsn':
                                            echo esc($item['hsn_code'] ?? ($item['hsn_or_sac'] ?? '-'));
                                            break;
                                        case 'item_qty':
                                            echo $qty;
                                            break;
                                        case 'item_rate':
                                            echo number_format($rate, 2);
                                            break;
                                        case 'item_discount':
                                            echo number_format($disc, 2);
                                            break;
                                        case 'item_taxable':
                                            echo number_format($taxable, 2);
                                            break;
                                        case 'item_cgst_pct':
                                            echo number_format((float) ($item['cgst_rate'] ?? 0), 2) . '%';
                                            break;
                                        case 'item_cgst_amt':
                                            $cAmt = isset($item['cgst_amount']) ? (float) $item['cgst_amount'] : ($taxable * ((float) ($item['cgst_rate'] ?? 0) / 100));
                                            echo number_format($cAmt, 2);
                                            break;
                                        case 'item_sgst_pct':
                                            echo number_format((float) ($item['sgst_rate'] ?? 0), 2) . '%';
                                            break;
                                        case 'item_sgst_amt':
                                            $sAmt = isset($item['sgst_amount']) ? (float) $item['sgst_amount'] : ($taxable * ((float) ($item['sgst_rate'] ?? 0) / 100));
                                            echo number_format($sAmt, 2);
                                            break;
                                        case 'item_igst_pct':
                                            echo number_format((float) ($item['igst_rate'] ?? 0), 2) . '%';
                                            break;
                                        case 'item_igst_amt':
                                            $iAmt = isset($item['igst_amount']) ? (float) $item['igst_amount'] : ($taxable * ((float) ($item['igst_rate'] ?? 0) / 100));
                                            echo number_format($iAmt, 2);
                                            break;
                                        case 'item_cess':
                                            echo number_format((float) ($item['cess_amount'] ?? 0), 2);
                                            break;
                                        case 'item_amount':
                                            echo number_format($amount, 2);
                                            break;
                                    endswitch; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    <?php for ($bi = 0; $bi < $minRowsNeeded; $bi++): ?>
                        <tr class="blank-filler-row">
                            <?php foreach ($visibleItemCols as $colDef): ?>
                                <td>&nbsp;</td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endfor; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $colCount ?>" class="text-center">No line items found.</td>
                    </tr>
                <?php endif; ?>

                <!-- Summary Row -->
                <?php
                $showNotesLeft = $isVis('total_items_qty') || ($isVis('notes') && !empty($notes));
                $summaryRows = [];
                foreach (PrintConfig::getSectionFields($printConfig, 'summary', true) as $sk => $sf) {
                    if (in_array($sk, ['total_items_qty', 'amount_in_words'], true)) {
                        continue;
                    }
                    if ($isInterState && in_array($sk, ['cgst_total', 'sgst_total'], true)) {
                        continue;
                    }
                    if (!$isInterState && $sk === 'igst_total') {
                        continue;
                    }
                    switch ($sk) {
                        case 'subtotal':
                            $summaryRows[] = ['key' => $sk, 'label' => $sf['label'], 'value' => number_format((float) ($subtotal ?? 0), 2), 'is_total' => false];
                            break;
                        case 'total_discount':
                            if (!empty($discount_amount) && (float) $discount_amount > 0) {
                                $summaryRows[] = ['key' => $sk, 'label' => $sf['label'], 'value' => '-' . number_format((float) $discount_amount, 2), 'is_total' => false];
                            }
                            break;
                        case 'packing_charge':
                            if (!empty($packing_charge) && (float) $packing_charge > 0) {
                                $summaryRows[] = ['key' => $sk, 'label' => $sf['label'], 'value' => number_format((float) $packing_charge, 2), 'is_total' => false];
                            }
                            break;
                        case 'shipping_charge':
                            if (!empty($shipping_charge) && (float) $shipping_charge > 0) {
                                $summaryRows[] = ['key' => $sk, 'label' => $sf['label'], 'value' => number_format((float) $shipping_charge, 2), 'is_total' => false];
                            }
                            break;
                        case 'other_charges':
                            if (!empty($other_charges) && (float) $other_charges > 0) {
                                $summaryRows[] = ['key' => $sk, 'label' => $sf['label'], 'value' => number_format((float) $other_charges, 2), 'is_total' => false];
                            }
                            break;
                        case 'taxable_value':
                            $tv = isset($taxable_value) ? (float) $taxable_value : max(0, (float) ($subtotal ?? 0) - (float) ($discount_amount ?? 0));
                            $summaryRows[] = ['key' => $sk, 'label' => $sf['label'], 'value' => number_format($tv, 2), 'is_total' => false];
                            break;
                        case 'cgst_total':
                            if ($cgstAmt > 0) {
                                $summaryRows[] = ['key' => $sk, 'label' => $sf['label'] . ' (' . (!empty($cgst_rate) ? esc($cgst_rate) : '2.5') . '%)', 'value' => number_format($cgstAmt, 2), 'is_total' => false];
                            }
                            break;
                        case 'sgst_total':
                            if ($sgstAmt > 0) {
                                $summaryRows[] = ['key' => $sk, 'label' => $sf['label'] . ' (' . (!empty($sgst_rate) ? esc($sgst_rate) : '2.5') . '%)', 'value' => number_format($sgstAmt, 2), 'is_total' => false];
                            }
                            break;
                        case 'igst_total':
                            if ($igstAmt > 0) {
                                $summaryRows[] = ['key' => $sk, 'label' => $sf['label'] . ' (' . (!empty($igst_rate) ? esc($igst_rate) : '5.0') . '%)', 'value' => number_format($igstAmt, 2), 'is_total' => false];
                            }
                            break;
                        case 'roundoff_amount':
                            if (!empty($roundoff_amount) && abs((float) $roundoff_amount) > 0) {
                                $summaryRows[] = ['key' => $sk, 'label' => $sf['label'], 'value' => number_format((float) $roundoff_amount, 2), 'is_total' => false];
                            }
                            break;
                        case 'grand_total':
                            $summaryRows[] = ['key' => $sk, 'label' => $sf['label'], 'value' => number_format($totalAmountFloat, 2), 'is_total' => true];
                            break;
                    }
                }
                ?>
                <?php if ($showNotesLeft || !empty($summaryRows)): ?>
                <tr>
                    <?php if (!empty($summaryRows) && $colCount > 1): ?>
                    <td colspan="<?= $leftSpan ?>" class="summary-notes-td">
                        <?php if ($isVis('total_items_qty')): ?>
                            <div style="<?= $fldStyle('total_items_qty') ?>"><?= esc($f('total_items_qty')['label']) ?> <?= (int) $totalQty ?></div>
                        <?php endif; ?>
                        <?php if ($isVis('notes') && !empty($notes)): ?>
                            <div style="<?= $fldStyle('notes') ?>margin-top:4px;"><?= esc($f('notes')['label']) ?> <?= nl2br(esc($notes)) ?></div>
                        <?php endif; ?>
                    </td>
                    <td colspan="<?= $rightSpan ?>" class="summary-totals-td">
                        <table class="totals-inner-table">
                            <?php foreach ($summaryRows as $sr): ?>
                                <tr class="<?= $sr['is_total'] ? 'total-row' : '' ?>">
                                    <td class="total-label" style="<?= $fldStyle($sr['key']) ?>"><?= esc($sr['label']) ?></td>
                                    <td class="total-value" style="<?= $fldStyle($sr['key']) ?>"><?= esc($sr['value']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                    <?php else: ?>
                    <td colspan="<?= $colCount ?>" class="summary-totals-td">
                        <?php if ($isVis('total_items_qty')): ?>
                            <div style="padding:5px 7px;<?= $fldStyle('total_items_qty') ?>"><?= esc($f('total_items_qty')['label']) ?> <?= (int) $totalQty ?></div>
                        <?php endif; ?>
                        <?php if ($isVis('notes') && !empty($notes)): ?>
                            <div style="padding:2px 7px;<?= $fldStyle('notes') ?>"><?= esc($f('notes')['label']) ?> <?= nl2br(esc($notes)) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($summaryRows)): ?>
                        <table class="totals-inner-table">
                            <?php foreach ($summaryRows as $sr): ?>
                                <tr class="<?= $sr['is_total'] ? 'total-row' : '' ?>">
                                    <td class="total-label" style="<?= $fldStyle($sr['key']) ?>"><?= esc($sr['label']) ?></td>
                                    <td class="total-value" style="<?= $fldStyle($sr['key']) ?>"><?= esc($sr['value']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 5 + 6 kept together on the last page -->
        <div class="keep-together">
            <?php if ($isVis('amount_in_words') && !empty($totalInWords)): ?>
            <table class="total-words-table">
                <tr>
                    <td style="<?= $fldStyle('amount_in_words', true) ?>"><?= esc($f('amount_in_words')['label']) ?> <?= esc($totalInWords) ?></td>
                </tr>
            </table>
            <?php endif; ?>

            <?php if ($showBottomBox): ?>
            <table class="bottom-details-box">
                <tr>
                    <?php if ($showBottomLeft): ?>
                    <td class="bank-terms-left" <?= !$showBottomRight ? 'style="width:100%;border-right:none;"' : '' ?>>
                        <?php if ($showBank): ?>
                            <div class="bank-sec-title" style="<?= $fldStyle('bank_details') ?>"><?= esc($f('bank_details')['label']) ?></div>
                            <table class="bank-accounts-table">
                                <tr>
                                    <?php foreach ($bankAccounts as $bk): ?>
                                        <td class="bank-entry-cell" style="<?= $fldStyle('bank_details') ?>">
                                            <div>Bank Name: <?= esc($bk['bank_name']) ?></div>
                                            <div>Account Number: <?= esc($bk['account_number']) ?></div>
                                            <div>Account Name:<br><?= esc($companyName) ?></div>
                                            <div>Branch: <?= esc($bk['branch_name'] ?? 'Rasipuram') ?></div>
                                            <div>IFSC Code: <?= esc($bk['ifsc_code']) ?></div>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            </table>
                        <?php endif; ?>

                        <?php if ($showUpi): ?>
                            <div style="<?= $fldStyle('upi_details') ?>margin-bottom:3px;"><?= esc($f('upi_details')['label']) ?> <?= esc($companyUpi) ?></div>
                        <?php endif; ?>

                        <?php if ($showTerms): ?>
                            <div class="terms-wrap" style="<?= $fldStyle('terms_conditions') ?><?= (!$showBank && !$showUpi) ? 'border-top:none;margin-top:0;padding-top:0;' : '' ?>">
                                <strong><?= esc($f('terms_conditions')['label']) ?> </strong>
                                <?php foreach ($termsList as $termLine): ?>
                                    <span><?= esc($termLine) ?> </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>

                    <?php if ($showBottomRight): ?>
                    <td class="signature-right" <?= !$showBottomLeft ? 'style="width:100%;"' : '' ?>>
                        <?php if ($showPrepared): ?>
                            <div style="<?= $fldStyle('prepared_by', true) ?>margin-bottom:18px;"><?= esc($f('prepared_by')['label']) ?><?= !empty($prepared_by) ? ': ' . esc($prepared_by) : '' ?></div>
                        <?php endif; ?>
                        <?php if ($showSeal): ?>
                            <div style="<?= $fldStyle('company_seal', true) ?>margin-bottom:18px;"><?= esc($f('company_seal')['label']) ?></div>
                        <?php endif; ?>
                        <?php if ($showSig): ?>
                            <div style="<?= $fldStyle('authorised_signature', true) ?>"><?= esc($f('authorised_signature')['label']) ?></div>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
            </table>
            <?php endif; ?>
        </div><!-- END .keep-together -->

    </div><!-- END .document-wrapper -->

</body>
</html>
