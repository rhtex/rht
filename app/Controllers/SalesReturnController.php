<?php

namespace App\Controllers;

use App\Models\SalesReturnModel;
use App\Models\SalesReturnItemModel;
use App\Models\InvoiceModel;
use App\Models\CustomerModel;
use App\Models\ProductModel;

class SalesReturnController extends BaseController
{
    protected $returnModel;
    protected $itemModel;
    protected $invoiceModel;
    protected $customerModel;
    protected $productModel;

    public function __construct()
    {
        $this->returnModel = new SalesReturnModel();
        $this->itemModel = new SalesReturnItemModel();
        $this->invoiceModel = new InvoiceModel();
        $this->customerModel = new CustomerModel();
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        $filters = [
            'customer_id' => $this->request->getGet('customer_id'),
            'status' => $this->request->getGet('status'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
        ];

        // Overall aggregate stats
        $summaryBuilder = $this->returnModel->select('COUNT(*) as total_count, COALESCE(SUM(total_amount), 0) as total_amount');
        if (!empty($filters['customer_id'])) {
            $summaryBuilder->where('customer_id', $filters['customer_id']);
        }
        if (!empty($filters['status'])) {
            $summaryBuilder->where('status', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $summaryBuilder->where('return_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $summaryBuilder->where('return_date <=', $filters['date_to']);
        }
        $totals = $summaryBuilder->first();

        // If AJAX request asking for more rows (infinite scroll)
        if ($this->request->isAJAX() && $this->request->getGet('load_more') == 1) {
            $offset = max(0, (int) ($this->request->getGet('offset') ?? 0));
            $limit  = max(1, (int) ($this->request->getGet('limit') ?? 20));
            $filters['offset'] = $offset;
            $filters['limit']  = $limit;

            $moreReturns = $this->returnModel->getReturnsWithFilters($filters);
            return view('sales_returns/list_rows', ['returns' => $moreReturns]);
        }

        // Default initial load: 50 credit notes
        $filters['offset'] = 0;
        $filters['limit']  = 50;

        $data['returns'] = $this->returnModel->getReturnsWithFilters($filters);
        $data['customers'] = $this->customerModel->where('status', 'active')->orderBy('name', 'ASC')->findAll();
        $data['title'] = 'Sales Returns (Credit Notes)';
        $data['filters'] = $filters;

        $stats = [
            'total'         => (int) ($totals['total_count'] ?? count($data['returns'])),
            'total_amount'  => (float) ($totals['total_amount'] ?? 0),
            'open_count'    => 0,
            'closed_count'  => 0,
        ];
        foreach ($data['returns'] as $r) {
            $st = $r['status'] ?? '';
            if ($st === 'Open') {
                $stats['open_count']++;
            } elseif ($st === 'Closed') {
                $stats['closed_count']++;
            }
        }
        $data['stats'] = $stats;

        return view('sales_returns/index', $data);
    }

    public function create()
    {
        $data['customers'] = $this->customerModel->where('status', 'active')->orderBy('name', 'ASC')->findAll();
        $data['products'] = $this->productModel->getProductsWithCategory();
        $data['title'] = 'Record Sales Return';
        $data['return_number'] = $this->returnModel->generateReturnNumber();
        $data['preselected_invoice_id'] = (int) ($this->request->getGet('invoice_id') ?? 0);
        $data['preselected_customer_id'] = (int) ($this->request->getGet('customer_id') ?? 0);

        if ($data['preselected_invoice_id'] > 0 && $data['preselected_customer_id'] === 0) {
            $inv = $this->invoiceModel->find($data['preselected_invoice_id']);
            if ($inv) {
                $data['preselected_customer_id'] = (int) ($inv['customer_id'] ?? 0);
            }
        }

        return view('sales_returns/form', $data);
    }

    public function getCustomerInvoices($customerId)
    {
        $invoices = $this->invoiceModel
            ->select('id, invoice_number, invoice_date, total_amount, paid_amount, balance, status, is_inter_state')
            ->where('customer_id', (int) $customerId)
            ->where('status !=', 'Void')
            ->orderBy('invoice_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(100)
            ->findAll();

        return $this->response->setJSON([
            'status'   => 'success',
            'invoices' => $invoices,
        ]);
    }

    public function getInvoiceItems($invoiceId)
    {
        $invoice = $this->invoiceModel->getInvoiceById((int) $invoiceId);
        if (!$invoice) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Invoice not found.']);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'invoice' => [
                'id'             => $invoice['id'],
                'invoice_number' => $invoice['invoice_number'],
                'invoice_date'   => $invoice['invoice_date'],
                'status'         => $invoice['status'],
                'total_amount'   => $invoice['total_amount'],
                'paid_amount'    => $invoice['paid_amount'],
                'balance'        => $invoice['balance'],
                'is_inter_state' => !empty($invoice['is_inter_state']) ? 1 : 0,
            ],
            'items'   => $invoice['items'] ?? [],
        ]);
    }

    public function store()
    {
        $items = $this->request->getPost('items') ?? [];
        $validItems = [];
        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $hasProdOrDesc = !empty($item['product_id']) || !empty(trim((string) ($item['description'] ?? '')));
            if ($hasProdOrDesc && $qty > 0) {
                $validItems[] = $item;
            }
        }

        if (empty($validItems)) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one item with quantity greater than 0.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $returnDate = $this->request->getPost('return_date');
        $customerId = $this->request->getPost('customer_id');
        $invoiceId = $this->request->getPost('invoice_id') ?: null;
        $customReturnNo = trim((string) $this->request->getPost('return_number'));

        $returnId = $this->returnModel->insert([
            'customer_id' => $customerId,
            'invoice_id' => $invoiceId,
            'return_number' => $customReturnNo !== '' ? $customReturnNo : $this->returnModel->generateReturnNumber(),
            'return_date' => $returnDate,
            'reason' => $this->request->getPost('reason'),
            'status' => 'Open',
            'is_inter_state' => $this->request->getPost('is_inter_state') ?? 0,
            'created_by' => session('user_id'),
        ]);

        $subtotal = 0;
        $taxAmount = 0;

        foreach ($validItems as $item) {
            $qty  = (float) ($item['quantity'] ?? 0);
            $rate = (float) ($item['rate'] ?? 0);
            $taxPct = (float) ($item['tax_percentage'] ?? 0);
            $lineAmount = $qty * $rate;
            $lineTax = ($lineAmount * $taxPct) / 100;

            $desc = trim((string) ($item['description'] ?? ''));
            if ($desc === '' && !empty($item['product_id'])) {
                $prod = $this->productModel->find((int) $item['product_id']);
                $desc = $prod['product_name'] ?? '';
            }

            $this->itemModel->insert([
                'sales_return_id' => $returnId,
                'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                'description' => $desc,
                'quantity' => $qty,
                'rate' => $rate,
                'tax_percentage' => $taxPct,
                'amount' => $lineAmount + $lineTax
            ]);

            $subtotal += $lineAmount;
            $taxAmount += $lineTax;
        }

        $totalAmount = $subtotal + $taxAmount;
        $this->returnModel->update($returnId, [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to save return.');
        }

        if (function_exists('zoho_auto_sync')) {
            zoho_auto_sync('credit_notes', (int) $returnId, 'CREATE');
        }

        return redirect()->to('sales_returns')->with('success', 'Sales return recorded successfully.');
    }

    public function view($id)
    {
        $data['return'] = $this->returnModel->getReturnById($id);

        if (!$data['return']) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Return not found.']);
            }
            return redirect()->to('sales_returns')->with('error', 'Return not found.');
        }

        $data['items'] = $data['return']['items'] ?? [];
        $data['title'] = 'Sales Return Details - ' . $data['return']['return_number'];

        // AJAX request for side panel view
        if ($this->request->isAJAX() || $this->request->getGet('ajax') == 1) {
            return view('sales_returns/detail_pane', $data);
        }

        $data['is_single'] = ($this->request->getGet('mode') === 'single');

        return view('sales_returns/view', $data);
    }

    public function print($id)
    {
        $data['return'] = $this->returnModel->getReturnById($id);
        if (!$data['return']) {
            return redirect()->to('sales_returns')->with('error', 'Credit note not found.');
        }

        $data['items'] = $data['return']['items'] ?? [];
        $data['title'] = 'Credit Note ' . $data['return']['return_number'];

        if ($this->request->getGet('format') !== 'html') {
            try {
                $html = view('sales_returns/print', array_merge($data, ['auto_print' => false]));
                return \App\Libraries\PdfRenderer::streamDocument('credit_note', $html, 'CreditNote_' . $data['return']['return_number']);
            } catch (\Throwable $e) {
                log_message('error', 'Credit Note PDF generation error: ' . $e->getMessage());
            }
        }

        return view('sales_returns/print', $data);
    }
}
