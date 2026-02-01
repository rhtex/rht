<?php

namespace App\Controllers;

use App\Models\SalesReturnModel;
use App\Models\SalesReturnItemModel;
use App\Models\InvoiceModel;
use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Services\ZohoBooksService;

class SalesReturnController extends BaseController
{
    protected $returnModel;
    protected $itemModel;
    protected $invoiceModel;
    protected $customerModel;
    protected $productModel;
    protected $zohoService;

    public function __construct()
    {
        $this->returnModel = new SalesReturnModel();
        $this->itemModel = new SalesReturnItemModel();
        $this->invoiceModel = new InvoiceModel();
        $this->customerModel = new CustomerModel();
        $this->productModel = new ProductModel();
        $this->zohoService = new ZohoBooksService();
    }

    public function index()
    {
        $filters = [
            'customer_id' => $this->request->getGet('customer_id'),
            'status' => $this->request->getGet('status'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
        ];

        $data['returns'] = $this->returnModel->getReturnsWithFilters($filters);
        $data['customers'] = $this->customerModel->where('status', 'active')->orderBy('name', 'ASC')->findAll();
        $data['title'] = 'Sales Returns (Credit Notes)';
        $data['filters'] = $filters;

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

        // Push to Zoho
        $this->pushToZoho($returnId);

        return redirect()->to('sales_returns')->with('success', 'Sales return recorded successfully.');
    }

    public function view($id)
    {
        $data['return'] = $this->returnModel->select('sales_returns.*, customers.name as customer_name, invoices.invoice_number')
            ->join('customers', 'customers.id = sales_returns.customer_id')
            ->join('invoices', 'invoices.id = sales_returns.invoice_id', 'left')
            ->find($id);

        if (!$data['return']) {
            return redirect()->to('sales_returns')->with('error', 'Return not found.');
        }

        $data['items'] = $this->itemModel->where('sales_return_id', $id)->findAll();
        $data['title'] = 'Sales Return Details - ' . $data['return']['return_number'];

        return view('sales_returns/view', $data);
    }

    public function pushToZoho($id)
    {
        $return = $this->returnModel->find($id);
        if (!$return)
            return;

        $customer = $this->customerModel->find($return['customer_id']);
        if (!$customer || !$customer['zoho_contact_id'])
            return;

        $items = $this->itemModel->where('sales_return_id', $id)->findAll();

        $lineItems = [];
        foreach ($items as $item) {
            $lineItems[] = [
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                // Add tax info if needed, simplified for now
            ];
        }

        $data = [
            'customer_id' => $customer['zoho_contact_id'],
            'creditnote_number' => $return['return_number'],
            'date' => $return['return_date'],
            'line_items' => $lineItems,
            'reason' => $return['reason']
        ];

        $response = $this->zohoService->createCreditNote($data);

        if ($response['success']) {
            $zohoId = $response['data']['creditnote']['creditnote_id'];
            $this->returnModel->update($id, [
                'zoho_credit_note_id' => $zohoId,
                'zoho_sync_status' => 'Synced',
                'zoho_sync_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            log_message('error', 'Zoho Push Error for Credit Note ' . $id . ': ' . $response['message']);
        }
    }

    public function syncFromZoho()
    {
        $page = 1;
        $syncedCount = 0;

        while (true) {
            $response = $this->zohoService->getCreditNotes($page);
            if (!$response['success'] || empty($response['data']['creditnotes']))
                break;

            foreach ($response['data']['creditnotes'] as $cn) {
                $existing = $this->returnModel->where('zoho_credit_note_id', $cn['creditnote_id'])->first();

                $customer = $this->customerModel->where('zoho_contact_id', $cn['customer_id'])->first();
                if (!$customer)
                    continue;

                $returnId = null;
                $data = [
                    'customer_id' => $customer['id'],
                    'return_number' => $cn['creditnote_number'],
                    'return_date' => $cn['date'],
                    'total_amount' => $cn['total'],
                    'status' => $cn['status'],
                    'zoho_credit_note_id' => $cn['creditnote_id'],
                    'zoho_sync_status' => 'Synced',
                    'zoho_sync_at' => date('Y-m-d H:i:s')
                ];

                if ($existing) {
                    $this->returnModel->update($existing['id'], $data);
                    $returnId = $existing['id'];
                } else {
                    $this->returnModel->insert($data);
                    $returnId = $this->returnModel->getInsertID();
                }
                $syncedCount++;
            }
            if (!$response['data']['page_context']['has_more_page'])
                break;
            $page++;
        }

        return redirect()->to('sales_returns')->with('success', "Synced $syncedCount credit notes from Zoho.");
    }

}
