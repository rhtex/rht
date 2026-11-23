<?php
/**
 * CENTRAL PRINT STYLESHEET – shared by every printable ERP document.
 * Used by both the Dompdf PDF output and browser (Chrome / Edge) printing.
 *
 * BORDER SPECIFICATION
 * --------------------
 *  - One line weight: 1px solid #000. Nothing else.
 *  - Outer edges of every page are drawn ONLY by the page frame:
 *      PDF  -> PdfRenderer draws a rectangle on each page canvas, 8mm from every edge
 *      HTML -> .page-frame (position: fixed), repeated by Chrome/Edge on every page
 *  - Single-owner rule for shared edges:
 *      * between stacked sections  -> the upper section's border-bottom
 *      * between side-by-side cols -> the left column's border-right (last col: none)
 *      * no content element draws an outer left / right / top border
 *  - Content bottom margin is 10mm vs. frame at 8mm, so a row line at a page break
 *    never lands on (and thickens) the frame's bottom line.
 *  - No page numbers.
 *
 * TYPOGRAPHY
 * ----------
 *  - Helvetica / Arial, weights normal + bold only (Dompdf has no in-between weights).
 */
$printFontSize = $printFontSize ?? '10.5px';
$paperSize     = $paperSize ?? 'A4';
$orientation   = ($orientation ?? 'portrait') === 'landscape' ? 'landscape' : 'portrait';
$fontFamily    = $fontFamily ?? 'Helvetica, Arial, sans-serif';
$screenWidth   = $orientation === 'landscape' ? '297mm' : '210mm';
$screenHeight  = $orientation === 'landscape' ? '210mm' : '297mm';
?>
<style>
    @page {
        size: <?= esc($paperSize) ?> <?= esc($orientation) ?>;
        margin: 8mm 8mm 10mm 8mm;
    }

    * {
        box-sizing: border-box;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* NOTE: never set margin on <html> – Dompdf maps the @page margins onto the
       html element, so an html margin reset silently removes the page margins
       (content then runs to the paper edge and over the bottom frame line). */
    body {
        margin: 0;
        padding: 0;
    }

    body {
        font-family: <?= $fontFamily ?>;
        font-size: <?= esc($printFontSize) ?>;
        font-weight: bold;
        line-height: 1.35;
        color: #000;
        background: #fff;
    }

    table {
        border-collapse: collapse;
        border-spacing: 0;
    }

    td, th {
        font-family: <?= $fontFamily ?>;
        font-weight: bold;
    }

    .text-center { text-align: center; }
    .text-right  { text-align: right; }
    .fw-bold     { font-weight: bold; }

    /* ---------- OUTER PAGE FRAME ----------
       PDF (default): drawn per page by App\Libraries\PdfRenderer on the canvas,
                      8mm from every page edge; this element is not output.
       Browser print (?format=html): fixed element, which Chrome/Edge repeat on
                      every printed page. Kept inside the page area (bottom: 0)
                      because anything outside the page area is clipped. */
    .page-frame {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border: 1px solid #000;
        z-index: -1;
    }

    .document-wrapper {
        width: 100%;
        margin: 0;
        padding: 0;
        border: none;
    }

    /* ---------- 1. HEADER ---------- */
    .header-table {
        width: 100%;
        border-bottom: 1px solid #000;
    }

    .header-table td {
        vertical-align: top;
        padding: 5px 7px;
        border: none;
    }

    .header-logo-cell {
        width: 80px;
        text-align: center;
        vertical-align: middle !important;
    }

    .logo-circle {
        width: 68px;
        height: 68px;
        margin: 0 auto;
        text-align: center;
    }

    .logo-circle img {
        width: 68px;
        height: 68px;
    }

    .logo-circle .monogram-r {
        font-family: 'Times New Roman', Times, serif;
        font-size: 48px;
        font-weight: bold;
        font-style: italic;
        color: #c99a00;
        line-height: 68px;
    }

    .logo-subtext {
        font-family: 'Times New Roman', Times, serif;
        font-size: 9px;
        font-weight: bold;
        text-align: center;
        margin-top: 1px;
        letter-spacing: 0.4px;
    }

    .header-company-cell {
        padding-left: 8px !important;
    }

    .company-name {
        font-size: 18px;
        font-weight: bold;
        letter-spacing: 0.4px;
        color: #002244;
        margin: 0 0 2px 0;
        text-transform: uppercase;
    }

    .company-text-line {
        font-size: 10.5px;
        line-height: 1.35;
    }

    .header-title-cell {
        width: 185px;
        text-align: right;
        vertical-align: middle !important;
    }

    .header-qr-cell {
        width: 102px;
        text-align: center;
        vertical-align: middle !important;
        padding: 3px 5px !important;
    }

    .header-qr-cell img {
        width: 86px;
        height: 86px;
        display: block;
        margin: 0 auto;
    }

    .header-qr-label {
        font-size: 8px;
        margin-top: 1px;
        line-height: 1.1;
    }

    .doc-title-main {
        font-size: 18px;
        font-weight: bold;
        letter-spacing: 0.8px;
        color: #117a38;
        margin: 0 0 3px 0;
        text-transform: uppercase;
    }

    .doc-number-main {
        font-size: 12.5px;
    }

    /* ---------- 2. DETAILS (meta | bill to | ship to) ---------- */
    .invoice-details-box {
        width: 100%;
        table-layout: fixed;
        border-bottom: 1px solid #000;
    }

    .invoice-details-box > tbody > tr > td,
    .invoice-details-box > tr > td {
        vertical-align: top;
        padding: 5px 7px;
        font-size: 10.5px;
        border: none;
        border-right: 1px solid #000;
    }

    .invoice-details-box > tbody > tr > td:last-child,
    .invoice-details-box > tr > td:last-child {
        border-right: none;
    }

    .meta-col { width: 32%; }
    .bill-col, .ship-col { width: 34%; }

    .meta-table {
        width: 100%;
    }

    .meta-table td {
        padding: 1px 0 !important;
        border: none !important;
        font-size: 10.5px;
        vertical-align: top !important;
    }

    .meta-table td.lbl {
        width: 80px;
        white-space: nowrap;
    }

    .meta-table td.sep {
        width: 9px;
        text-align: center;
    }

    .party-lbl {
        font-size: 11px;
        margin-bottom: 3px;
    }

    .party-name-bold,
    .party-gst-bold {
        font-size: 12px;
        color: #002266;
        margin-bottom: 2px;
    }

    .party-name-bold { text-transform: uppercase; }

    .party-addr-text {
        font-size: 10.5px;
        line-height: 1.4;
        word-wrap: break-word;
    }

    /* ---------- 3. PLACE OF SUPPLY ---------- */
    .pos-bar-table {
        width: 100%;
        border-bottom: 1px solid #000;
    }

    .pos-bar-table td {
        padding: 5px 7px;
        font-size: 11px;
        border: none;
    }

    /* ---------- 4. LINE ITEMS ---------- */
    .items-grid-table {
        width: 100%;
        table-layout: fixed;
        border: none;
    }

    .items-grid-table thead {
        display: table-header-group;
    }

    .items-grid-table th {
        padding: 5px 5px;
        font-size: 10.5px;
        text-align: left;
        background: #fff;
        border: none;
        border-bottom: 1px solid #000;
        border-right: 1px solid #000;
    }

    .items-grid-table td {
        padding: 3px 5px;
        font-size: 10px;
        vertical-align: top;
        height: 18px;
        border: none;
        border-bottom: 1px solid #000;
        border-right: 1px solid #000;
        word-wrap: break-word;
    }

    .items-grid-table th:last-child,
    .items-grid-table td:last-child {
        border-right: none;
    }

    .items-grid-table tr {
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .items-grid-table tr.blank-filler-row td {
        padding: 0 5px;
        color: transparent;
    }

    .item-extra-notes {
        font-size: 9.5px;
        font-weight: normal;
    }

    /* Summary row: notes (left) | totals (right). Owns the bottom divider. */
    .items-grid-table td.summary-notes-td {
        padding: 7px !important;
        font-size: 11px;
        vertical-align: top !important;
        border-right: 1px solid #000 !important;
        border-bottom: 1px solid #000 !important;
    }

    .items-grid-table td.summary-totals-td {
        padding: 0 !important;
        vertical-align: top !important;
        border-right: none !important;
        border-bottom: 1px solid #000 !important;
    }

    .totals-inner-table {
        width: 100%;
    }

    .totals-inner-table td {
        border: none !important;
        padding: 4px 8px !important;
        font-size: 11px;
        height: auto !important;
        white-space: nowrap;
    }

    .totals-inner-table td.total-value { text-align: right; }

    .totals-inner-table tr.total-row td {
        border-top: 1px solid #000 !important;
        font-size: 13px;
        padding: 6px 8px !important;
    }

    /* ---------- 5. TOTAL IN WORDS ---------- */
    .total-words-table {
        width: 100%;
        border-bottom: 1px solid #000;
    }

    .total-words-table td {
        padding: 6px 7px;
        font-size: 11px;
        border: none;
    }

    /* ---------- 6. BANK / TERMS | SIGNATURE ---------- */
    .bottom-details-box {
        width: 100%;
        table-layout: fixed;
        border-bottom: 1px solid #000;
    }

    .bottom-details-box > tbody > tr > td,
    .bottom-details-box > tr > td {
        vertical-align: top;
        padding: 5px 7px;
        border: none;
    }

    .bottom-details-box td.bank-terms-left {
        width: 63%;
        font-size: 9.5px;
        border-right: 1px solid #000;
    }

    .bottom-details-box td.signature-right {
        width: 37%;
        text-align: center;
        vertical-align: bottom !important;
        padding-bottom: 10px;
        font-size: 11px;
    }

    .bank-sec-title {
        font-size: 10.5px;
        text-decoration: underline;
        margin-bottom: 3px;
    }

    .bank-accounts-table {
        width: 100%;
        table-layout: fixed;
        margin-bottom: 4px;
    }

    .bank-accounts-table td.bank-entry-cell {
        vertical-align: top;
        width: 50%;
        padding: 0 6px;
        font-size: 9.5px;
        line-height: 1.35;
        border: none;
    }

    .bank-accounts-table td.bank-entry-cell:first-child {
        padding-left: 0;
        border-right: 1px solid #000;
    }

    .bank-accounts-table td.bank-entry-cell:last-child {
        border-right: none;
    }

    /* Terms divider spans the full left cell (cancels the cell padding) */
    .terms-wrap {
        border-top: 1px solid #000;
        margin: 3px -7px 0 -7px;
        padding: 3px 7px 0 7px;
        font-size: 9.5px;
        line-height: 1.35;
    }

    /* ---------- PAGE-BREAK INTEGRITY ---------- */
    .keep-together,
    .invoice-details-box,
    .pos-bar-table,
    .total-words-table,
    .bottom-details-box {
        page-break-inside: avoid;
        break-inside: avoid;
    }

    /* ---------- BROWSER SCREEN PREVIEW (?format=html) ---------- */
    @media screen {
        html { background: #e9ecef; }
        body {
            width: <?= $screenWidth ?>;
            min-height: <?= $screenHeight ?>;
            margin: 10px auto;
            padding: 8mm 8mm 10mm 8mm;
            background: #fff;
            position: relative;
        }
        .page-frame {
            position: absolute;
            top: 8mm; left: 8mm; right: 8mm; bottom: 8mm;
        }
    }

    @media print {
        html, body { background: #fff; }
        body { width: auto; min-height: 0; margin: 0; padding: 0; }
    }
</style>
