<?php

namespace App\Controllers;

use App\Libraries\PdfRenderer;
use App\Models\InvoiceModel;

/**
 * Reports module:
 *  - Document reports (Invoices, Bills, Credit Notes, Debit Notes)
 *  - GST report with bulk/merged invoice PDF generation
 */
class ReportController extends BaseController
{
    /** Maximum invoices that can be merged into one PDF in a single request. */
    private const MAX_PDF_INVOICES = 300;

    /**
     * Report type definitions (table, party, columns, view links).
     */
    private function definitions(): array
    {
        return [
            'invoices' => [
                'title'       => 'Invoices Report',
                'icon'        => 'fa-file-invoice-dollar',
                'color'       => 'primary',
                'permission'  => 'invoice.view',
                'table'       => 'invoices',
                'date_col'    => 'invoice_date',
                'number_col'  => 'invoice_number',
                'party_table' => 'customers',
                'party_col'   => 'customer_id',
                'party_label' => 'Customer',
                'view_url'    => 'invoices/view/',
                'print_url'   => 'invoices/print/',
            ],
            'bills' => [
                'title'       => 'Bills Report',
                'icon'        => 'fa-file-invoice',
                'color'       => 'danger',
                'permission'  => 'bill.view',
                'table'       => 'bills',
                'date_col'    => 'bill_date',
                'number_col'  => 'bill_number',
                'party_table' => 'vendors',
                'party_col'   => 'vendor_id',
                'party_label' => 'Vendor',
                'view_url'    => 'bills/view/',
                'print_url'   => 'bills/print/',
            ],
            'credit-notes' => [
                'title'       => 'Credit Notes Report',
                'icon'        => 'fa-undo-alt',
                'color'       => 'success',
                'permission'  => 'invoice.view',
                'table'       => 'sales_returns',
                'date_col'    => 'return_date',
                'number_col'  => 'return_number',
                'party_table' => 'customers',
                'party_col'   => 'customer_id',
                'party_label' => 'Customer',
                'view_url'    => 'sales_returns/view/',
                'print_url'   => 'sales_returns/print/',
            ],
            'debit-notes' => [
                'title'       => 'Debit Notes Report',
                'icon'        => 'fa-file-signature',
                'color'       => 'warning',
                'permission'  => 'vendor_credit.view',
                'table'       => 'vendor_credits',
                'date_col'    => 'credit_note_date',
                'number_col'  => 'reference_no',
                'party_table' => 'vendors',
                'party_col'   => 'vendor_id',
                'party_label' => 'Vendor',
                'view_url'    => 'vendor_credits/view/',
                'print_url'   => 'vendor_credits/print/',
            ],
        ];
    }

    /**
     * Quick period presets used by all report filters.
     */
    public static function periodOptions(): array
    {
        return [
            'this_month'     => 'This Month',
            'last_month'     => 'Last Month',
            'this_quarter'   => 'This Quarter',
            'last_quarter'   => 'Last Quarter',
            'this_fy'        => 'This Financial Year',
            'last_fy'        => 'Last Financial Year',
            'last_7_days'    => 'Last 7 Days',
            'last_30_days'   => 'Last 30 Days',
            'all'            => 'All Time',
            'custom'         => 'Custom Range',
        ];
    }

    /**
     * Resolve [from, to] (Y-m-d or null) for a period preset.
     * Financial year runs April -> March (India).
     */
    private function resolvePeriod(string $period, ?string $from, ?string $to): array
    {
        $today = new \DateTimeImmutable('today');
        $m = (int) $today->format('n');
        $y = (int) $today->format('Y');

        switch ($period) {
            case 'this_month':
                return [$today->format('Y-m-01'), $today->format('Y-m-t')];
            case 'last_month':
                $lm = $today->modify('first day of last month');
                return [$lm->format('Y-m-01'), $lm->format('Y-m-t')];
            case 'this_quarter':
            case 'last_quarter':
                // Indian FY quarters: Apr-Jun, Jul-Sep, Oct-Dec, Jan-Mar
                $qStartMonth = (int) (floor(($m - 1) / 3) * 3) + 1;
                $start = new \DateTimeImmutable(sprintf('%04d-%02d-01', $y, $qStartMonth));
                if ($period === 'last_quarter') {
                    $start = $start->modify('-3 months');
                }
                $end = $start->modify('+2 months');
                return [$start->format('Y-m-01'), $end->format('Y-m-t')];
            case 'this_fy':
            case 'last_fy':
                $fyStartYear = $m >= 4 ? $y : $y - 1;
                if ($period === 'last_fy') {
                    $fyStartYear--;
                }
                return [sprintf('%04d-04-01', $fyStartYear), sprintf('%04d-03-31', $fyStartYear + 1)];
            case 'last_7_days':
                return [$today->modify('-6 days')->format('Y-m-d'), $today->format('Y-m-d')];
            case 'last_30_days':
                return [$today->modify('-29 days')->format('Y-m-d'), $today->format('Y-m-d')];
            case 'all':
                return [null, null];
            case 'custom':
            default:
                $valid = static fn($d) => ($d && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) ? $d : null;
                return [$valid($from), $valid($to)];
        }
    }

    /**
     * Read common filters from the query string.
     */
    private function readFilters(string $defaultPeriod = 'this_month'): array
    {
        $req    = $this->request;
        $period = (string) ($req->getGet('period') ?: $defaultPeriod);
        if (!array_key_exists($period, self::periodOptions())) {
            $period = $defaultPeriod;
        }
        [$from, $to] = $this->resolvePeriod($period, $req->getGet('date_from'), $req->getGet('date_to'));

        return [
            'period'    => $period,
            'date_from' => $from,
            'date_to'   => $to,
            'party_id'  => (int) ($req->getGet('party_id') ?? 0),
            'status'    => trim((string) ($req->getGet('status') ?? '')),
        ];
    }

    /**
     * Reports hub: redirects to the invoices report.
     */
    public function index()
    {
        foreach ($this->definitions() as $type => $def) {
            if (can($def['permission'])) {
                return redirect()->to(site_url('reports/' . $type));
            }
        }
        return redirect()->to(site_url('dashboard'))->with('error', 'You do not have access to any reports.');
    }

    /**
     * Document report (invoices | bills | credit-notes | debit-notes).
     */
    public function documents(string $type = 'invoices')
    {
        $defs = $this->definitions();
        if (!isset($defs[$type])) {
            return redirect()->to(site_url('reports/invoices'));
        }
        $def = $defs[$type];
        if (!can($def['permission'])) {
            return redirect()->to(site_url('dashboard'))->with('error', 'You do not have permission to view this report.');
        }

        $db      = \Config\Database::connect();
        $filters = $this->readFilters();
        $t       = $def['table'];

        $builder = $db->table($t . ' d')
            ->select('d.*, p.name AS party_name')
            ->join($def['party_table'] . ' p', 'p.id = d.' . $def['party_col'], 'left');

        if ($type === 'credit-notes') {
            $builder->select('inv.invoice_number AS ref_doc_number')
                ->join('invoices inv', 'inv.id = d.invoice_id', 'left');
        } elseif ($type === 'debit-notes') {
            $builder->select('b.bill_number AS ref_doc_number')
                ->join('bills b', 'b.id = d.bill_id', 'left');
        }

        if ($db->fieldExists('deleted_at', $t)) {
            $builder->where('d.deleted_at', null);
        }
        if ($filters['date_from']) {
            $builder->where('d.' . $def['date_col'] . ' >=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $builder->where('d.' . $def['date_col'] . ' <=', $filters['date_to']);
        }
        if ($filters['party_id'] > 0) {
            $builder->where('d.' . $def['party_col'], $filters['party_id']);
        }
        if ($filters['status'] !== '') {
            $builder->where('d.status', $filters['status']);
        }

        $rows = $builder->orderBy('d.' . $def['date_col'], 'DESC')->orderBy('d.id', 'DESC')->get()->getResultArray();

        // Normalise to common columns + build totals
        $totals = ['count' => 0, 'subtotal' => 0, 'tax' => 0, 'total' => 0, 'paid' => 0, 'balance' => 0];
        $items  = [];
        foreach ($rows as $r) {
            $isVoid = strcasecmp((string) ($r['status'] ?? ''), 'Void') === 0;
            if ($type === 'debit-notes') {
                $subtotal = (float) ($r['amount'] ?? 0);
                $tax      = 0.0;
                $total    = (float) ($r['amount'] ?? 0);
                $paid     = (float) ($r['used_amount'] ?? 0);
                $balance  = (float) ($r['balance'] ?? 0);
            } elseif ($type === 'credit-notes') {
                $subtotal = (float) ($r['subtotal'] ?? 0);
                $tax      = (float) ($r['tax_amount'] ?? 0);
                $total    = (float) ($r['total_amount'] ?? 0);
                $paid     = $total; // credit notes are applied against the invoice
                $balance  = 0.0;
            } else {
                $subtotal = (float) ($r['subtotal'] ?? 0);
                $tax      = (float) ($r['tax_amount'] ?? 0);
                $total    = (float) ($r['total_amount'] ?? 0);
                $paid     = (float) ($r['paid_amount'] ?? 0);
                $balance  = (float) ($r['balance'] ?? 0);
            }

            $items[] = [
                'id'          => $r['id'],
                'number'      => $r[$def['number_col']] ?? ('#' . $r['id']),
                'date'        => $r[$def['date_col']] ?? null,
                'due_date'    => $r['due_date'] ?? null,
                'party_name'  => $r['party_name'] ?? '-',
                'ref_doc'     => $r['ref_doc_number'] ?? ($r['reference_number'] ?? ''),
                'status'      => $r['status'] ?? '',
                'subtotal'    => $subtotal,
                'tax'         => $tax,
                'total'       => $total,
                'paid'        => $paid,
                'balance'     => $balance,
                'is_void'     => $isVoid,
            ];

            if (!$isVoid) {
                $totals['count']++;
                $totals['subtotal'] += $subtotal;
                $totals['tax']      += $tax;
                $totals['total']    += $total;
                $totals['paid']     += $paid;
                $totals['balance']  += $balance;
            }
        }

        $statuses = array_column(
            $db->table($t)->select('status')->distinct()->where('status IS NOT NULL')->orderBy('status')->get()->getResultArray(),
            'status'
        );
        $parties = $db->table($def['party_table'])->select('id, name')->orderBy('name')->get()->getResultArray();

        $viewName = $this->request->getGet('print') === '1' ? 'reports/documents_print' : 'reports/documents';

        return view($viewName, [
            'title'         => $def['title'],
            'type'          => $type,
            'def'           => $def,
            'definitions'   => $defs,
            'items'         => $items,
            'totals'        => $totals,
            'filters'       => $filters,
            'statuses'      => $statuses,
            'parties'       => $parties,
            'periodOptions' => self::periodOptions(),
        ]);
    }

    /**
     * Build the invoice query used by the GST report and bulk PDF.
     */
    private function gstInvoiceQuery(array $filters, string $gstType = '', bool $includeVoid = false)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('invoices i')
            ->select('i.id, i.invoice_number, i.invoice_date, i.status, i.subtotal, i.discount_amount, i.discount_type, i.tax_amount,
                      i.cgst_amount, i.sgst_amount, i.igst_amount, i.shipping_charge, i.roundoff_amount, i.total_amount, i.balance,
                      c.name AS customer_name, c.gstin AS customer_gstin, c.gst_type AS customer_gst_type,
                      (SELECT COALESCE(SUM(ii.quantity), 0) FROM invoice_items ii WHERE ii.invoice_id = i.id) AS qty_sold,
                      (SELECT MAX(ii2.tax_percentage) FROM invoice_items ii2 WHERE ii2.invoice_id = i.id) AS item_tax_rate')
            ->join('customers c', 'c.id = i.customer_id', 'left');

        if ($db->fieldExists('deleted_at', 'invoices')) {
            $builder->where('i.deleted_at', null);
        }
        if (!$includeVoid) {
            $builder->whereNotIn('i.status', ['Void', 'Draft']);
        }
        if ($filters['date_from']) {
            $builder->where('i.invoice_date >=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $builder->where('i.invoice_date <=', $filters['date_to']);
        }
        if ($filters['party_id'] > 0) {
            $builder->where('i.customer_id', $filters['party_id']);
        }
        if ($filters['status'] !== '') {
            $builder->where('i.status', $filters['status']);
        }
        if ($gstType === 'b2b') {
            $builder->where('c.gstin IS NOT NULL')->where("c.gstin <> ''");
        } elseif ($gstType === 'b2c') {
            $builder->groupStart()->where('c.gstin IS NULL')->orWhere('c.gstin', '')->groupEnd();
        }

        return $builder->orderBy('i.invoice_date', 'ASC')->orderBy('i.invoice_number', 'ASC');
    }

    /**
     * Summarize a list of GST tax-bearing rows (invoices, bills, sales returns) using stored columns only.
     */
    private static function summarizeGstRows(array &$rows, bool $splitInterStateTax = false): array
    {
        $totals = ['count' => 0, 'b2b' => 0, 'b2c' => 0, 'qty' => 0, 'taxable' => 0, 'cgst' => 0, 'sgst' => 0, 'igst' => 0, 'tax' => 0, 'total' => 0];
        foreach ($rows as &$r) {
            $r['qty']     = (float) ($r['qty_sold'] ?? 0);
            $r['taxable'] = (float) ($r['subtotal'] ?? 0);
            $r['tax']     = (float) ($r['tax_amount'] ?? 0);
            if ($splitInterStateTax) {
                $inter = !empty($r['is_inter_state']);
                $r['cgst'] = $inter ? 0.0 : round($r['tax'] / 2, 2);
                $r['sgst'] = $inter ? 0.0 : round($r['tax'] - $r['cgst'], 2);
                $r['igst'] = $inter ? $r['tax'] : 0.0;
            } else {
                $r['cgst'] = (float) ($r['cgst_amount'] ?? 0);
                $r['sgst'] = (float) ($r['sgst_amount'] ?? 0);
                $r['igst'] = (float) ($r['igst_amount'] ?? 0);
            }
            $r['is_b2b']  = !empty($r['party_gstin']);
            $r['is_void'] = strcasecmp((string) ($r['status'] ?? ''), 'Void') === 0;
            if ($r['is_void']) {
                continue;
            }
            $totals['count']++;
            $totals[$r['is_b2b'] ? 'b2b' : 'b2c']++;
            $totals['qty']     += $r['qty'];
            $totals['taxable'] += $r['taxable'];
            $totals['cgst']    += $r['cgst'];
            $totals['sgst']    += $r['sgst'];
            $totals['igst']    += $r['igst'];
            $totals['tax']     += $r['tax'];
            $totals['total']   += (float) ($r['total_amount'] ?? 0);
        }
        unset($r);
        return $totals;
    }

    /**
     * Build common date/status/gstType filters on a query builder.
     */
    private static function applyGstFilters($builder, string $table, string $alias, string $dateCol, string $partyAlias, array $filters, string $gstType, bool $includeVoid): void
    {
        $db = \Config\Database::connect();
        if ($db->fieldExists('deleted_at', $table)) {
            $builder->where($alias . '.deleted_at', null);
        }
        if (!$includeVoid) {
            $builder->whereNotIn($alias . '.status', ['Void', 'Draft']);
        }
        if ($filters['date_from']) {
            $builder->where($alias . '.' . $dateCol . ' >=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $builder->where($alias . '.' . $dateCol . ' <=', $filters['date_to']);
        }
        if ($filters['status'] !== '') {
            $builder->where($alias . '.status', $filters['status']);
        }
        if ($gstType === 'b2b') {
            $builder->where($partyAlias . '.gstin IS NOT NULL')->where($partyAlias . ".gstin <> ''");
        } elseif ($gstType === 'b2c') {
            $builder->groupStart()->where($partyAlias . '.gstin IS NULL')->orWhere($partyAlias . '.gstin', '')->groupEnd();
        }
    }

    /**
     * GST report: Invoices, Bills, Sales Returns, and Purchase Returns on one page,
     * with bulk invoice PDF printing.
     */
    public function gst()
    {
        $db          = \Config\Database::connect();
        $filters     = $this->readFilters('last_month');
        $gstType     = (string) ($this->request->getGet('gst_type') ?? '');
        $includeVoid = $this->request->getGet('include_void') === '1';

        // 1. Invoices (Sales)
        $rows = $this->gstInvoiceQuery($filters, $gstType, $includeVoid)->get()->getResultArray();
        foreach ($rows as &$r) {
            $r['party_gstin'] = $r['customer_gstin'] ?? '';
        }
        unset($r);
        $totals = self::summarizeGstRows($rows, false);

        $rateSummary = [];
        foreach ($rows as $r) {
            if ($r['is_void']) {
                continue;
            }
            $key = (int) round((float) ($r['item_tax_rate'] ?? 0));
            if (!isset($rateSummary[$key])) {
                $rateSummary[$key] = ['count' => 0, 'qty' => 0, 'taxable' => 0, 'cgst' => 0, 'sgst' => 0, 'igst' => 0, 'total' => 0];
            }
            $rateSummary[$key]['count']++;
            $rateSummary[$key]['qty']     += $r['qty'];
            $rateSummary[$key]['taxable'] += $r['taxable'];
            $rateSummary[$key]['cgst']    += $r['cgst'];
            $rateSummary[$key]['sgst']    += $r['sgst'];
            $rateSummary[$key]['igst']    += $r['igst'];
            $rateSummary[$key]['total']   += (float) $r['total_amount'];
        }
        ksort($rateSummary);

        // 2. Bills (Purchases)
        $billRows = [];
        if (can('bill.view')) {
            $bb = $db->table('bills b')
                ->select('b.id, b.bill_number, b.bill_date, b.status, b.subtotal, b.tax_amount,
                          b.cgst_amount, b.sgst_amount, b.igst_amount, b.total_amount,
                          v.name AS vendor_name, v.gstin AS party_gstin,
                          (SELECT COALESCE(SUM(bi.quantity), 0) FROM bill_items bi WHERE bi.bill_id = b.id) AS qty_sold')
                ->join('vendors v', 'v.id = b.vendor_id', 'left');
            self::applyGstFilters($bb, 'bills', 'b', 'bill_date', 'v', $filters, $gstType, $includeVoid);
            $billRows = $bb->orderBy('b.bill_date', 'ASC')->orderBy('b.bill_number', 'ASC')->get()->getResultArray();
        }
        $billTotals = self::summarizeGstRows($billRows, false);

        // 3. Sales Returns (Credit Notes)
        $srBuilder = $db->table('sales_returns sr')
            ->select('sr.id, sr.return_number, sr.return_date, sr.status, sr.subtotal, sr.tax_amount, sr.total_amount, sr.is_inter_state,
                      inv.invoice_number, c.name AS customer_name, c.gstin AS party_gstin,
                      (SELECT COALESCE(SUM(sri.quantity), 0) FROM sales_return_items sri WHERE sri.sales_return_id = sr.id) AS qty_sold')
            ->join('customers c', 'c.id = sr.customer_id', 'left')
            ->join('invoices inv', 'inv.id = sr.invoice_id', 'left');
        if ($filters['party_id'] > 0) {
            $srBuilder->where('sr.customer_id', $filters['party_id']);
        }
        self::applyGstFilters($srBuilder, 'sales_returns', 'sr', 'return_date', 'c', $filters, $gstType, $includeVoid);
        $salesReturnRows   = $srBuilder->orderBy('sr.return_date', 'ASC')->orderBy('sr.return_number', 'ASC')->get()->getResultArray();
        $salesReturnTotals = self::summarizeGstRows($salesReturnRows, true);

        // 4. Purchase Returns (Debit Notes / Vendor Credits)
        $purchaseReturnRows = [];
        $purchaseReturnTotals = ['count' => 0, 'amount' => 0, 'used' => 0, 'balance' => 0];
        if (can('vendor_credit.view')) {
            $prb = $db->table('vendor_credits vc')
                ->select('vc.id, vc.credit_note_date, vc.reference_no, vc.status, vc.amount, vc.used_amount, vc.balance,
                          b.bill_number, v.name AS vendor_name, v.gstin AS party_gstin')
                ->join('vendors v', 'v.id = vc.vendor_id', 'left')
                ->join('bills b', 'b.id = vc.bill_id', 'left');
            self::applyGstFilters($prb, 'vendor_credits', 'vc', 'credit_note_date', 'v', $filters, $gstType, $includeVoid);
            $purchaseReturnRows = $prb->orderBy('vc.credit_note_date', 'ASC')->orderBy('vc.id', 'ASC')->get()->getResultArray();
            foreach ($purchaseReturnRows as &$pr) {
                $pr['is_void'] = strcasecmp((string) ($pr['status'] ?? ''), 'Void') === 0;
                $pr['is_b2b']  = !empty($pr['party_gstin']);
                if ($pr['is_void']) {
                    continue;
                }
                $purchaseReturnTotals['count']++;
                $purchaseReturnTotals['amount']  += (float) ($pr['amount'] ?? 0);
                $purchaseReturnTotals['used']    += (float) ($pr['used_amount'] ?? 0);
                $purchaseReturnTotals['balance'] += (float) ($pr['balance'] ?? 0);
            }
            unset($pr);
        }

        $statuses = array_column(
            $db->table('invoices')->select('status')->distinct()->where('status IS NOT NULL')->orderBy('status')->get()->getResultArray(),
            'status'
        );
        $customers = $db->table('customers')->select('id, name')->orderBy('name')->get()->getResultArray();

        // Printable report (standalone page, auto-opens print dialog)
        $viewName = $this->request->getGet('print') === '1' ? 'reports/gst_print' : 'reports/gst';

        return view($viewName, [
            'title'                => 'GST Report',
            'rows'                 => $rows,
            'totals'               => $totals,
            'billRows'             => $billRows,
            'billTotals'           => $billTotals,
            'salesReturnRows'      => $salesReturnRows,
            'salesReturnTotals'    => $salesReturnTotals,
            'purchaseReturnRows'   => $purchaseReturnRows,
            'purchaseReturnTotals' => $purchaseReturnTotals,
            'rateSummary'          => $rateSummary,
            'filters'              => $filters,
            'gstType'              => $gstType,
            'includeVoid'          => $includeVoid,
            'statuses'             => $statuses,
            'customers'            => $customers,
            'periodOptions'        => self::periodOptions(),
            'maxPdf'               => self::MAX_PDF_INVOICES,
        ]);
    }

    /**
     * Generate ONE merged PDF containing many invoices (one invoice per page set).
     * POST ids[] = selected invoices; if none posted, all invoices matching the GET filters are used.
     */
    public function gstInvoicesPdf()
    {
        $ids = array_values(array_filter(array_map('intval', (array) ($this->request->getPost('ids') ?? []))));

        if (empty($ids)) {
            $filters = $this->readFilters('last_month');
            $gstType = (string) ($this->request->getGet('gst_type') ?? '');
            $includeVoid = $this->request->getGet('include_void') === '1';
            $ids = array_map('intval', array_column($this->gstInvoiceQuery($filters, $gstType, $includeVoid)->get()->getResultArray(), 'id'));
        }

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No invoices found for the selected filters.');
        }
        if (count($ids) > self::MAX_PDF_INVOICES) {
            return redirect()->back()->with('error', 'Too many invoices (' . count($ids) . '). Please narrow the filter to at most ' . self::MAX_PDF_INVOICES . ' invoices per PDF.');
        }

        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $invoiceModel = new InvoiceModel();
        $common = [
            'company_name'    => get_setting('app_name', 'RasiDev'),
            'company_address' => get_setting('company_address', ''),
            'company_gstin'   => get_setting('company_gstin', ''),
            'auto_print'      => false,
        ];

        // Keep chronological order
        $order = \Config\Database::connect()->table('invoices')->select('id')->whereIn('id', $ids)
            ->orderBy('invoice_date', 'ASC')->orderBy('invoice_number', 'ASC')->get()->getResultArray();

        $head   = '';
        $bodies = [];
        foreach ($order as $o) {
            $invoice = $invoiceModel->getInvoiceById((int) $o['id']);
            if (!$invoice) {
                continue;
            }
            $html = view('invoices/print', array_merge($common, ['invoice' => $invoice, 'title' => 'Invoice ' . $invoice['invoice_number']]));

            if ($head === '' && preg_match('#<head[^>]*>(.*?)</head>#is', $html, $hm)) {
                $head = $hm[1];
            }
            if (preg_match('#<body[^>]*>(.*)</body>#is', $html, $bm)) {
                $bodies[] = $bm[1];
            }
        }

        if (empty($bodies)) {
            return redirect()->back()->with('error', 'Unable to render the selected invoices.');
        }

        $merged = '<!DOCTYPE html><html lang="en"><head>' . $head
            . '<style>.bulk-page-break{page-break-after:always;break-after:page;}</style></head><body>';
        $last = count($bodies) - 1;
        foreach ($bodies as $i => $b) {
            $merged .= '<div class="' . ($i < $last ? 'bulk-page-break' : '') . '">' . $b . '</div>';
        }
        $merged .= '</body></html>';

        $label = 'GST_Invoices_' . date('Ymd_His') . '_' . count($bodies);
        return PdfRenderer::streamDocument('invoice', $merged, $label);
    }
}
