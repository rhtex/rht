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
 * - Complete outer border (all four sides) drawn on every page at identical coordinates
 * - No page numbers
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

        // Outer page frame: drawn directly on EVERY page canvas at fixed page coordinates,
        // independent of flowing content (a fixed/positioned HTML element gets clipped
        // by Dompdf at the content-area edge, which is what hid the bottom border).
        // Frame = 8mm from all four page edges, 0.75pt (= 1px) black.
        // Content area (@page margin in print_styles.php) is 8mm top/left/right and
        // 10mm bottom, so the last row line on a page always sits 2mm above the frame's
        // bottom edge and can never overlap or thicken it.
        $mm    = 72 / 25.4;
        $inset = self::FRAME_INSET_MM * $mm;
        $dompdf->getCanvas()->page_script(function ($pageNumber, $pageCount, $canvas) use ($inset) {
            $w = $canvas->get_width();
            $h = $canvas->get_height();
            $canvas->rectangle($inset, $inset, $w - 2 * $inset, $h - 2 * $inset, [0, 0, 0], 0.75);
        });

        return $dompdf->output();
    }

    /** Distance of the outer frame from every page edge (must match print_styles.php). */
    public const FRAME_INSET_MM = 8;

    /**
     * Return an inline PDF response.
     */
    public static function stream(string $html, string $filename, string $paper = 'A4', string $orientation = 'portrait'): ResponseInterface
    {
        $filename = preg_replace('/[^A-Za-z0-9_.-]/', '_', $filename);
        if (!str_ends_with(strtolower($filename), '.pdf')) {
            $filename .= '.pdf';
        }

        return service('response')
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
            ->setBody(self::render($html, $paper, $orientation));
    }

    /**
     * Stream a document PDF using its saved PrintConfig paper size and orientation.
     */
    public static function streamDocument(string $docType, string $html, string $filename): ResponseInterface
    {
        $cfg = PrintConfig::getForDocument($docType);
        return self::stream(
            $html,
            $filename,
            $cfg['paper_size'] ?? 'A4',
            $cfg['orientation'] ?? 'portrait'
        );
    }
}
