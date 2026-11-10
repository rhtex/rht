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
        $data['customers'] = $this->customerModel->where('status', 'active')->findAll();
        $data['products'] = $this->productModel->getProductsWithCategory();
        $data['title'] = 'Record Sales Return';
        $data['return_number'] = $this->returnModel->generateReturnNumber();
        return view('sales_returns/form', $data);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $returnDate = $this->request->getPost('return_date');
        $customerId = $this->request->getPost('customer_id');
        $invoiceId = $this->request->getPost('invoice_id') ?: null;

        $returnId = $this->returnModel->insert([
            'customer_id' => $customerId,
            'invoice_id' => $invoiceId,
            'return_number' => $this->returnModel->generateReturnNumber(),
            'return_date' => $returnDate,
            'reason' => $this->request->getPost('reason'),
            'status' => 'Open',
            'is_inter_state' => $this->request->getPost('is_inter_state') ?? 0,
            'created_by' => session('user_id'),
        ]);

        $items = $this->request->getPost('items');
        $subtotal = 0;
        $taxAmount = 0;

        if ($items) {
            foreach ($items as $item) {
                $lineAmount = $item['quantity'] * $item['rate'];
                $lineTax = ($lineAmount * ($item['tax_percentage'] ?? 0)) / 100;

                $this->itemModel->insert([
                    'sales_return_id' => $returnId,
                    'product_id' => $item['product_id'] ?: null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'rate' => $item['rate'],
                    'tax_percentage' => $item['tax_percentage'] ?? 0,
                    'amount' => $lineAmount + $lineTax
                ]);

                $subtotal += $lineAmount;
                $taxAmount += $lineTax;
            }
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


}
