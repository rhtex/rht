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


}
