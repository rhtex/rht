<?php

namespace App\Libraries;

use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Central PDF renderer for all printable ERP documents
 * (Invoice, Bill, Sales Order, Quotation, Credit Note, Debit Note ...).
 *
 * - A4 portrait
 * - "Page X of N" stamped on every page, bottom-right, inside the bottom margin
 * - Inline Content-Disposition so the browser opens its PDF viewer (print-ready)
 */
class PdfRenderer
{
    /**
     * Render HTML into PDF binary.
     */
    public static function render(string $html, string $paper = 'A4', string $orientation = 'portrait'): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('defaultFont', 'Helvetica');
        $options->set('dpi', 96);
        $options->set('defaultMediaType', 'print');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        // Page X of N, bottom-right inside the 12mm bottom margin (below the page frame)
        $canvas  = $dompdf->getCanvas();
        $metrics = $dompdf->getFontMetrics();
        $font    = $metrics->getFont('Helvetica', 'bold');
        $size    = 7.5;
        $text    = 'Page {PAGE_NUM} of {PAGE_COUNT}';
        $w       = $canvas->get_width();
        $h       = $canvas->get_height();
        $mm      = 72 / 25.4;
        $textW   = $metrics->getTextWidth('Page 00 of 00', $font, $size);
        $canvas->page_text($w - (8 * $mm) - $textW, $h - (6.5 * $mm), $text, $font, $size, [0, 0, 0]);

        return $dompdf->output();
    }

    /**
     * Return an inline PDF response.
     */
    public static function stream(string $html, string $filename): ResponseInterface
    {
        $filename = preg_replace('/[^A-Za-z0-9_.-]/', '_', $filename);
        if (!str_ends_with(strtolower($filename), '.pdf')) {
            $filename .= '.pdf';
        }

        return service('response')
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
            ->setBody(self::render($html));
    }
}
