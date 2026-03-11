<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\InvoiceItemModel;
use App\Models\InvoicePaymentModel;
use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\BankAccountModel;
use App\Models\AgentModel;
use App\Models\TransportModel;
use App\Services\ZohoBooksService;

class InvoiceController extends BaseController
{
    protected $invoiceModel;
    protected $itemModel;
    protected $paymentModel;
    protected $customerModel;
    protected $productModel;
    protected $bankAccountModel;
    protected $agentModel;
    protected $transportModel;
    protected $taxModel;
    protected $zohoService;
    protected $accountingModel;
    protected $historyModel;
    protected $expenseModel;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
        $this->itemModel = new InvoiceItemModel();
        $this->paymentModel = new InvoicePaymentModel();
        $this->customerModel = new CustomerModel();
        $this->productModel = new ProductModel();
        $this->bankAccountModel = new BankAccountModel();
        $this->agentModel = new AgentModel();
        $this->transportModel = new TransportModel();
        $this->taxModel = new \App\Models\TaxModel();
        $this->zohoService = new ZohoBooksService();
        $this->accountingModel = new \App\Models\AccountingModel();
        $this->historyModel = new \App\Models\InvoiceStatusHistoryModel();
        $this->expenseModel = new \App\Models\ExpenseModel();
    }

    public function index()
    {
        $filters = [
            'customer_id' => $this->request->getGet('customer_id'),
            'status' => $this->request->getGet('status'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
        ];

        $data['invoices'] = $this->invoiceModel->getInvoicesWithCustomer($filters);
        $data['customers'] = $this->customerModel->where('status', 'active')->findAll();
        $data['title'] = 'Invoices';

        return view('invoices/index', $data);
    }

    public function create()
    {
        $data['customers'] = $this->customerModel->where('status', 'active')->findAll();
        $data['agents'] = $this->agentModel->findAll();
        $data['transports'] = $this->transportModel->orderBy('transport_name', 'ASC')->findAll();
        $data['products'] = $this->productModel->getProductsWithCategory();
        $data['taxes'] = $this->taxModel->where('status', 'Active')->findAll();
        $data['invoice_number'] = $this->invoiceModel->generateInvoiceNumber();
        $data['title'] = 'Create New Invoice';
        $data['invoice'] = null;

        return view('invoices/form', $data);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $invoiceData = [
            'customer_id' => $this->request->getPost('customer_id'),
            'agent_id' => $this->request->getPost('agent_id') ?: null,
            'agent_commission_percent' => $this->request->getPost('agent_commission_percent') ?? 0,
            'invoice_number' => $this->request->getPost('invoice_number'),
            'invoice_date' => $this->request->getPost('invoice_date'),
            'due_date' => $this->request->getPost('due_date'),
            'reference_number' => $this->request->getPost('reference_number'),
            'po_date' => $this->request->getPost('po_date') ?: null,
            'transport_name' => $this->request->getPost('transport_name'),
            'waybill_number' => $this->request->getPost('waybill_number'),
            'waybill_date' => $this->request->getPost('waybill_date') ?: null,
            'ewaybill_number' => $this->request->getPost('ewaybill_number'),
            'packages_count' => $this->request->getPost('packages_count') ?: null,
            'notes' => $this->request->getPost('notes'),
            'terms' => $this->request->getPost('terms'),
            'discount_amount' => $this->request->getPost('discount_amount') ?? 0,
            'discount_type' => $this->request->getPost('discount_type') ?? 'Fixed',
            'shipping_charge' => $this->request->getPost('shipping_charge') ?? 0,
            'roundoff_amount' => $this->request->getPost('roundoff_amount') ?? 0,
            'is_inter_state' => $this->request->getPost('is_inter_state') ?? 0,
            'created_by' => session('user_id'),
            'status' => 'Draft'
        ];

        if (!$this->invoiceModel->insert($invoiceData)) {
            return redirect()->back()->withInput()->with('errors', $this->invoiceModel->errors());
        }

        $invoiceId = $this->invoiceModel->getInsertID();
        $items = $this->request->getPost('items');

        foreach ($items as $item) {
            $this->itemModel->insert([
                'invoice_id' => $invoiceId,
                'product_id' => $item['product_id'] ?: null,
                'description' => $item['description'],
                'hsn_code' => $item['hsn_code'],
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'tax_percentage' => $item['tax_percentage'],
                'amount' => $item['quantity'] * $item['rate']
            ]);
        }

        // Calculate Taxes and Totals
        $companyStateId = get_setting('company_state');

        // Get customer state
        $customerAddress = $db->table('addresses')
            ->where('owner_id', $invoiceData['customer_id'])
            ->where('owner_type', 'customer')
            ->where('address_type', 'billing')
            ->get()->getRowArray();

        $customerStateId = $customerAddress['state_id'] ?? null;

        // Use company state if customer state is missing (fallback to intra-state)
        if ($customerStateId === null) {
            $customerStateId = $companyStateId;
        }

        $this->invoiceModel->calculateGST($invoiceId, $customerStateId, $companyStateId);

        // Calculate Agent Commission
        if ($invoiceData['agent_id']) {
            $updatedInvoice = $this->invoiceModel->find($invoiceId);
            $commissionPercent = $this->request->getPost('agent_commission_percent') ?: 0;

            // If percent is 0 but an agent is selected, try to get their default percent
            if ($commissionPercent == 0) {
                $agent = $this->agentModel->find($invoiceData['agent_id']);
                $commissionPercent = $agent['commission_percentage'] ?? 0;
            }

            $taxableSubtotal = $updatedInvoice['subtotal'];
            $discountValue = ($updatedInvoice['discount_type'] == 'Percentage') ? ($taxableSubtotal * $updatedInvoice['discount_amount'] / 100) : $updatedInvoice['discount_amount'];
            $commissionAmount = (($taxableSubtotal - $discountValue) * $commissionPercent) / 100;

            $this->invoiceModel->update($invoiceId, [
                'agent_commission_percent' => $commissionPercent,
                'agent_commission_amount' => $commissionAmount,
                'agent_commission_status' => 'Unpaid'
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to create invoice.');
        }

        // --- ACCOUNTING LEDGER ---
        $finalInvoice = $this->invoiceModel->find($invoiceId);
        $invDate = $finalInvoice['invoice_date'];
        $invRef = "Sale Invoice: " . $finalInvoice['invoice_number'];

        // 1. Calculations for Ledger
        $totalDiscount = 0;
        if ($finalInvoice['discount_type'] == 'Percentage') {
            $totalDiscount = ($finalInvoice['subtotal'] * $finalInvoice['discount_amount']) / 100;
        } else {
            $totalDiscount = $finalInvoice['discount_amount'];
        }

        // 2. Dr Accounts Receivable (Total amount due)
        $this->accountingModel->postEntry('Accounts Receivable', $invDate, $finalInvoice['total_amount'], 0, $invRef, 'invoice', $invoiceId);

        // 3. Dr Customer Discount (Expense - if any)
        if ($totalDiscount > 0) {
            $this->accountingModel->postEntry('Customer Discount', $invDate, $totalDiscount, 0, "Discount Allowed on $invRef", 'invoice', $invoiceId);
        }

        // 4. Cr Sales Income (Gross Subtotal)
        $this->accountingModel->postEntry('Sales Income', $invDate, 0, $finalInvoice['subtotal'], $invRef, 'invoice', $invoiceId);

        // 5. Cr GST Payable (Tax)
        if ($finalInvoice['tax_amount'] > 0) {
            $this->accountingModel->postEntry('GST Payable', $invDate, 0, $finalInvoice['tax_amount'], "GST on $invRef", 'invoice', $invoiceId);
        }

        // 6. Cr Shipping Income (If any)
        if ($finalInvoice['shipping_charge'] > 0) {
            // Need a shipping income account. I'll add it or post to Sales Income.
            $this->accountingModel->postEntry('Sales Income', $invDate, 0, $finalInvoice['shipping_charge'], "Shipping handling for $invRef", 'invoice', $invoiceId);
        }
        // ------------------------

        // Push to Zoho
        $this->pushToZoho($invoiceId);

        return redirect()->to('invoices/view/' . $invoiceId)->with('success', 'Invoice created successfully.');
    }

    public function view($id)
    {
        $data['invoice'] = $this->invoiceModel->getInvoiceById($id);
        if (!$data['invoice']) {
            return redirect()->to('invoices')->with('error', 'Invoice not found.');
        }

        // Load agent information if agent is assigned
        if ($data['invoice']['agent_id']) {
            $data['agent'] = $this->agentModel->find($data['invoice']['agent_id']);
        }

        $data['history'] = $this->historyModel->getHistoryByInvoice($id);
        $data['title'] = 'Invoice #' . $data['invoice']['invoice_number'];
        return view('invoices/view', $data);
    }

    public function edit($id)
    {
        $data['invoice'] = $this->invoiceModel->getInvoiceById($id);
        if (!$data['invoice']) {
            return redirect()->to('invoices')->with('error', 'Invoice not found.');
        }

        $data['customers'] = $this->customerModel->where('status', 'active')->findAll();
        $data['agents'] = $this->agentModel->findAll();
        $data['transports'] = $this->transportModel->orderBy('transport_name', 'ASC')->findAll();
        $data['products'] = $this->productModel->getProductsWithCategory();
        $data['taxes'] = $this->taxModel->where('status', 'Active')->findAll();
        $data['title'] = 'Edit Invoice #' . $data['invoice']['invoice_number'];

        return view('invoices/form', $data);
    }


    public function update($id)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $invoiceData = [
            'customer_id' => $this->request->getPost('customer_id'),
            'agent_id' => $this->request->getPost('agent_id') ?: null,
            'agent_commission_percent' => $this->request->getPost('agent_commission_percent') ?? 0,
            'invoice_date' => $this->request->getPost('invoice_date'),
            'due_date' => $this->request->getPost('due_date'),
            'reference_number' => $this->request->getPost('reference_number'),
            'po_date' => $this->request->getPost('po_date') ?: null,
            'transport_name' => $this->request->getPost('transport_name'),
            'waybill_number' => $this->request->getPost('waybill_number'),
            'waybill_date' => $this->request->getPost('waybill_date') ?: null,
            'ewaybill_number' => $this->request->getPost('ewaybill_number'),
            'packages_count' => $this->request->getPost('packages_count') ?: null,
            'notes' => $this->request->getPost('notes'),
            'terms' => $this->request->getPost('terms'),
            'discount_amount' => $this->request->getPost('discount_amount') ?? 0,
            'discount_type' => $this->request->getPost('discount_type') ?? 'Fixed',
            'shipping_charge' => $this->request->getPost('shipping_charge') ?? 0,
            'roundoff_amount' => $this->request->getPost('roundoff_amount') ?? 0,
            'is_inter_state' => $this->request->getPost('is_inter_state') ?? 0,
            'updated_by' => session('user_id'),
        ];

        $this->invoiceModel->update($id, $invoiceData);

        // Update items
        $this->itemModel->where('invoice_id', $id)->delete();
        $items = $this->request->getPost('items');

        foreach ($items as $item) {
            $this->itemModel->insert([
                'invoice_id' => $id,
                'product_id' => $item['product_id'] ?: null,
                'description' => $item['description'],
                'hsn_code' => $item['hsn_code'],
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'tax_percentage' => $item['tax_percentage'],
                'amount' => $item['quantity'] * $item['rate']
            ]);
        }

        // Recalculate
        $companyStateId = get_setting('company_state');
        $customerAddress = $db->table('addresses')
            ->where('owner_id', $invoiceData['customer_id'])
            ->where('owner_type', 'customer')
            ->where('address_type', 'billing')
            ->get()->getRowArray();

        $customerStateId = $customerAddress['state_id'] ?? null;

        if ($customerStateId === null) {
            $customerStateId = $companyStateId;
        }

        $this->invoiceModel->calculateGST($id, $customerStateId, $companyStateId);

        // Calculate Agent Commission
        if ($invoiceData['agent_id']) {
            $updatedInvoice = $this->invoiceModel->find($id);
            $commissionPercent = $this->request->getPost('agent_commission_percent') ?: 0;

            // If percent is 0 but an agent is selected, try to get their default percent
            if ($commissionPercent == 0) {
                $agent = $this->agentModel->find($invoiceData['agent_id']);
                $commissionPercent = $agent['commission_percentage'] ?? 0;
            }

            $taxableSubtotal = $updatedInvoice['subtotal'];
            $discountValue = ($updatedInvoice['discount_type'] == 'Percentage') ? ($taxableSubtotal * $updatedInvoice['discount_amount'] / 100) : $updatedInvoice['discount_amount'];
            $commissionAmount = (($taxableSubtotal - $discountValue) * $commissionPercent) / 100;

            $this->invoiceModel->update($id, [
                'agent_commission_percent' => $commissionPercent,
                'agent_commission_amount' => $commissionAmount,
                'agent_commission_status' => $updatedInvoice['agent_commission_status'] ?: 'Unpaid'
            ]);
        } else {
            $this->invoiceModel->update($id, [
                'agent_commission_amount' => 0,
                'agent_commission_status' => null
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update invoice.');
        }

        // Push to Zoho
        $this->pushToZoho($id);

        return redirect()->to('invoices/view/' . $id)->with('success', 'Invoice updated successfully.');
    }

    public function delete($id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return redirect()->to('invoices')->with('error', 'Invoice not found.');
        }

        // Check if there are payments
        if ($invoice['paid_amount'] > 0) {
            return redirect()->to('invoices')->with('error', 'Cannot delete invoice with payments.');
        }

        $db = \Config\Database::connect();
        $db->transStart();
        $this->itemModel->where('invoice_id', $id)->delete();
        $this->invoiceModel->delete($id);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('invoices')->with('error', 'Failed to delete invoice.');
        }

        // Void in Zoho if synced
        if ($invoice['zoho_invoice_id']) {
            $this->zohoService->voidInvoice($invoice['zoho_invoice_id']);
        }

        return redirect()->to('invoices')->with('success', 'Invoice deleted successfully.');
    }

    public function recordPayment($invoiceId)
    {
        $data['invoice'] = $this->invoiceModel->getInvoiceById($invoiceId);
        if (!$data['invoice']) {
            return redirect()->to('invoices')->with('error', 'Invoice not found.');
        }

        $data['bank_accounts'] = $this->bankAccountModel->findAll();
        $data['title'] = 'Record Payment - Invoice #' . $data['invoice']['invoice_number'];

        return view('invoices/payment_form', $data);
    }

    public function storePayment($invoiceId)
    {
        $invoice = $this->invoiceModel->find($invoiceId);
        if (!$invoice) {
            return redirect()->to('invoices')->with('error', 'Invoice not found.');
        }

        $grossSettlement = (float) $this->request->getPost('gross_settlement');
        $amount = (float) $this->request->getPost('amount');
        $discount = (float) $this->request->getPost('discount_amount') ?? 0;
        $mahimai = (float) $this->request->getPost('mahimai_amount') ?? 0;
        $postal = (float) $this->request->getPost('postal_charges') ?? 0;

        if ($grossSettlement > $invoice['balance'] + 0.01) {
            return redirect()->back()->withInput()->with('error', 'Gross settlement cannot exceed invoice balance.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $paymentNumber = $this->paymentModel->generatePaymentNumber();

        $paymentData = [
            'invoice_id' => $invoiceId,
            'customer_id' => $invoice['customer_id'],
            'payment_number' => $paymentNumber,
            'payment_date' => $this->request->getPost('payment_date'),
            'payment_mode' => $this->request->getPost('payment_mode'),
            'amount' => $amount,
            'discount_amount' => $discount,
            'mahimai_amount' => $mahimai,
            'postal_charges' => $postal,
            'reference_number' => $this->request->getPost('reference_number'),
            'bank_account_id' => $this->request->getPost('bank_account_id') ?: null,
            'notes' => $this->request->getPost('notes'),
            'created_by' => session('user_id'),
            'updated_by' => session('user_id'),
            'zoho_sync_status' => 'Pending'
        ];

        $this->paymentModel->insert($paymentData);
        $paymentId = $this->paymentModel->getInsertID();

        // --- ACCOUNTING LEDGER ---
        $paymentDate = $paymentData['payment_date'];
        $paymentRef = "Payment: " . $paymentNumber . " (Ref: " . $invoice['invoice_number'] . ")";

        // 1. Dr Bank/Cash (Bank/Cash increases)
        $paymentAccount = ($paymentData['payment_mode'] == 'Cash') ? 'Cash' : 'Bank Account';
        $this->accountingModel->postEntry($paymentAccount, $paymentDate, $amount, 0, $paymentRef, 'invoice_payment', $paymentId);

        // 2. Dr Customer Discount (Expense increases)
        if ($discount > 0) {
            $this->accountingModel->postEntry('Customer Discount', $paymentDate, $discount, 0, "Discount allowed on $paymentNumber", 'invoice_payment', $paymentId);
        }

        // 3. Cr Accounts Receivable (Asset decreases by Gross Settlement)
        $this->accountingModel->postEntry('Accounts Receivable', $paymentDate, 0, $grossSettlement, "Gross settlement for $paymentNumber", 'invoice_payment', $paymentId);

        // 4. Cr Mahimai Income (If applicable)
        if ($mahimai > 0) {
            $this->accountingModel->postEntry('Mahimai Income', $paymentDate, 0, $mahimai, "Mahimai collected in $paymentNumber", 'invoice_payment', $paymentId);
        }

        // 5. Cr Postal Charges (If applicable)
        if ($postal > 0) {
            $this->accountingModel->postEntry('Postal Charges', $paymentDate, 0, $postal, "Postal charges collected in $paymentNumber", 'invoice_payment', $paymentId);
        }
        // ------------------------

        // Update invoice paid amount
        $newPaidAmount = $invoice['paid_amount'] + $grossSettlement;
        $this->invoiceModel->update($invoiceId, ['paid_amount' => $newPaidAmount]);
        $this->invoiceModel->updateBalance($invoiceId);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to record payment.');
        }

        // Push payment to Zoho
        $this->pushPaymentToZoho($paymentId);

        return redirect()->to('invoices/view/' . $invoiceId)->with('success', 'Payment recorded successfully.');
    }

    private function pushToZoho($invoiceId)
    {
        $invoice = $this->invoiceModel->getInvoiceById($invoiceId);
        $customer = $this->customerModel->find($invoice['customer_id']);

        if (!$customer['zoho_contact_id']) {
            return ['success' => false, 'message' => 'Customer is not synced with Zoho.'];
        }

        $zohoData = [
            'customer_id' => $customer['zoho_contact_id'],
            'invoice_number' => $invoice['invoice_number'],
            'date' => $invoice['invoice_date'],
            'due_date' => $invoice['due_date'],
            'reference_number' => $invoice['reference_number'],
            'discount' => $invoice['discount_amount'],
            'discount_type' => strtolower($invoice['discount_type']),
            'shipping_charge' => $invoice['shipping_charge'],
            'notes' => $invoice['notes'],
            'terms' => $invoice['terms'],
            'line_items' => []
        ];

        foreach ($invoice['items'] as $item) {
            $zohoData['line_items'][] = [
                'name' => $item['description'],
                'description' => $item['description'],
                'rate' => $item['rate'],
                'quantity' => $item['quantity'],
                'hsn_or_sac' => $item['hsn_code'],
                'tax_percentage' => $item['tax_percentage']
            ];
        }
        if ($invoice['zoho_invoice_id']) {
            $response = $this->zohoService->updateInvoice($invoice['zoho_invoice_id'], $zohoData);
        } else {
            $response = $this->zohoService->createInvoice($zohoData);
        }

        if ($response['success']) {
            $this->invoiceModel->update($invoiceId, [
                'zoho_invoice_id' => $response['data']['invoice']['invoice_id'],
                'zoho_sync_status' => 'Synced',
                'zoho_sync_at' => date('Y-m-d H:i:s')
            ]);
            return true;
        }

        $this->invoiceModel->update($invoiceId, ['zoho_sync_status' => 'Failed']);
        return false;
    }

    private function pushPaymentToZoho($paymentId)
    {
        $payment = $this->paymentModel->find($paymentId);
        $invoice = $this->invoiceModel->find($payment['invoice_id']);
        $customer = $this->customerModel->find($payment['customer_id']);

        if (!$invoice['zoho_invoice_id'] || !$customer['zoho_contact_id']) {
            return false;
        }

        $zohoData = [
            'customer_id' => $customer['zoho_contact_id'],
            'payment_mode' => $payment['payment_mode'],
            'amount' => $payment['amount'] + $payment['discount_amount'] + $payment['mahimai_amount'] + $payment['postal_charges'],
            'date' => $payment['payment_date'],
            'reference_number' => $payment['reference_number'],
            'description' => $payment['notes'],
            'invoices' => [
                [
                    'invoice_id' => $invoice['zoho_invoice_id'],
                    'amount_applied' => $payment['amount'] + $payment['discount_amount'] + $payment['mahimai_amount'] + $payment['postal_charges']
                ]
            ]
        ];

        $response = $this->zohoService->createCustomerPayment($zohoData);

        if ($response['success']) {
            $this->paymentModel->update($paymentId, [
                'zoho_payment_id' => $response['data']['payment']['payment_id'],
                'zoho_sync_status' => 'Synced',
                'zoho_sync_at' => date('Y-m-d H:i:s')
            ]);
            return true;
        }

        $this->paymentModel->update($paymentId, ['zoho_sync_status' => 'Failed']);
        return false;
    }

    public function print($id)
    {
        $data['invoice'] = $this->invoiceModel->getInvoiceById($id);
        if (!$data['invoice'])
            return 'Invoice not found';

        $data['company_name'] = get_setting('app_name', 'RasiDev');
        $data['company_address'] = get_setting('company_address', '');
        $data['company_gstin'] = get_setting('company_gstin', '');
        $data['title'] = 'Invoice ' . $data['invoice']['invoice_number'];

        return view('invoices/print', $data);
    }

    public function markAsSent($id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice not found.');
        }

        if ($invoice['status'] !== 'Draft') {
            return redirect()->back()->with('error', 'Only draft invoices can be marked as sent.');
        }

        $this->invoiceModel->update($id, ['status' => 'Open']);

        return redirect()->back()->with('success', 'Invoice marked as sent and is now open.');
    }

    public function tracking()
    {
        $limit = 25;
        $offset = intval($this->request->getGet('offset') ?? 0);

        $customer_id = $this->request->getGet('customer_id');
        $delivery_status = $this->request->getGet('delivery_status');
        $search = $this->request->getGet('search');

        $this->invoiceModel->select('invoices.*, customers.name as customer_name')
            ->join('customers', 'customers.id = invoices.customer_id', 'left');

        // Base Tracking Condition: (Incomplete Waybill OR Not Delivered) 
        // AND Not "HAND in Person"
        $this->invoiceModel->groupStart()
            ->groupStart()
            ->where('invoices.waybill_number', null)
            ->orWhere('invoices.waybill_number', '')
            ->orWhere('invoices.waybill_date', null)
            ->orWhere('invoices.waybill_image', null)
            ->orWhere('invoices.waybill_image', '')
            ->orWhere('invoices.delivery_status !=', 'Delivered')
            ->groupEnd()
            ->groupStart()
            ->where('invoices.transport_name !=', 'HAND in Person')
            ->orWhere('invoices.transport_name', null)
            ->groupEnd()
            ->groupEnd();

        // Dynamic Filters
        if ($customer_id !== null && $customer_id !== '') {
            $this->invoiceModel->where('invoices.customer_id', $customer_id);
        }
        if ($delivery_status !== null && $delivery_status !== '') {
            $this->invoiceModel->where('invoices.delivery_status', $delivery_status);
        }
        if ($search !== null && $search !== '') {
            $this->invoiceModel->like('invoices.invoice_number', $search);
        }

        // Get count before limit
        $totalResults = $this->invoiceModel->countAllResults(false);

        $invoices = $this->invoiceModel->orderBy('invoices.invoice_date', 'DESC')
            ->findAll($limit, $offset);

        $data = [
            'title' => 'Invoice Tracking',
            'invoices' => $invoices,
            'customers' => $this->customerModel->where('status', 'active')->findAll(),
            'total_count' => $totalResults,
            'filters' => [
                'customer_id' => $customer_id,
                'delivery_status' => $delivery_status,
                'search' => $search,
            ],
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $totalResults
        ];

        if ($this->request->isAJAX()) {
            return view('invoices/tracking_rows', $data);
        }

        return view('invoices/tracking', $data);
    }

    public function updateWaybill($id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice not found.');
        }

        $validationRules = [
            'waybill_number' => 'required',
            'waybill_date' => 'required|valid_date',
            'waybill_image' => 'permit_empty|max_size[waybill_image,2048]|is_image[waybill_image]',
            'transport_amount' => 'permit_empty|numeric',
            'waybill_shipping_charge' => 'permit_empty|numeric'
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->with('error', 'Validation failed: ' . implode(', ', $this->validator->getErrors()));
        }

        $transport_amount = $this->request->getPost('transport_amount') ?: 0;
        $transport_pay_type = $this->request->getPost('transport_pay_type') ?: 'To Pay';
        $waybill_shipping_charge = $this->request->getPost('waybill_shipping_charge') ?: 0;

        $updateData = [
            'waybill_number' => $this->request->getPost('waybill_number'),
            'waybill_date' => $this->request->getPost('waybill_date'),
            'transport_amount' => $transport_amount,
            'transport_pay_type' => $transport_pay_type,
            'waybill_shipping_charge' => $waybill_shipping_charge
        ];

        $img = $this->request->getFile('waybill_image');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            $img->move(ROOTPATH . 'public/uploads/waybills', $newName);
            $updateData['waybill_image'] = $newName;
        }

        if ($this->invoiceModel->update($id, $updateData)) {
            $waybill_number = $updateData['waybill_number'];
            $invoice_no = $invoice['invoice_number'];

            // 1. Handle Transport Expense (if Paid)
            if ($transport_pay_type === 'Paid' && $transport_amount > 0) {
                $desc = "Transport for INV: $invoice_no (Waybill: $waybill_number)";
                // Check if already recorded to avoid duplicates
                $existing = $this->expenseModel->where('description', $desc)->first();
                if (!$existing) {
                    $expenseData = [
                        'expense_date' => $updateData['waybill_date'],
                        'category_id' => 7, // SENDING PARCEL
                        'amount' => $transport_amount,
                        'description' => $desc,
                        'payment_mode' => 'Cash',
                        'reference_number' => $waybill_number
                    ];
                    if ($this->expenseModel->insert($expenseData)) {
                        $expId = $this->expenseModel->getInsertID();
                        // Post to Ledger
                        $this->accountingModel->postEntry('SENDING PARCEL', $expenseData['expense_date'], $transport_amount, 0, $desc, 'expense', $expId);
                        $this->accountingModel->postEntry('Cash', $expenseData['expense_date'], 0, $transport_amount, "Expense: $desc", 'expense', $expId);
                    }
                }
            }

            // 2. Handle Shipping Charge Expense
            if ($waybill_shipping_charge > 0) {
                $desc = "Shipping/Booking Charge for INV: $invoice_no (Waybill: $waybill_number)";
                // Check if already recorded
                $existing = $this->expenseModel->where('description', $desc)->first();
                if (!$existing) {
                    $expenseData = [
                        'expense_date' => $updateData['waybill_date'],
                        'category_id' => 7, // SENDING PARCEL
                        'amount' => $waybill_shipping_charge,
                        'description' => $desc,
                        'payment_mode' => 'Cash',
                        'reference_number' => $waybill_number
                    ];
                    if ($this->expenseModel->insert($expenseData)) {
                        $expId = $this->expenseModel->getInsertID();
                        // Post to Ledger
                        $this->accountingModel->postEntry('SENDING PARCEL', $expenseData['expense_date'], $waybill_shipping_charge, 0, $desc, 'expense', $expId);
                        $this->accountingModel->postEntry('Cash', $expenseData['expense_date'], 0, $waybill_shipping_charge, "Expense: $desc", 'expense', $expId);
                    }
                }
            }

            // Log history
            $this->historyModel->insert([
                'invoice_id' => $id,
                'status' => 'Booked',
                'description' => "Waybill updated: " . $waybill_number . " (Transport: $transport_pay_type)",
                'created_by' => session('user_id')
            ]);

            // Update delivery_status to Booked
            $this->invoiceModel->update($id, ['delivery_status' => 'Booked']);

            return redirect()->back()->with('success', 'Waybill information updated and expenses recorded successfully.');
        }

        return redirect()->back()->with('error', 'Failed to update waybill information.');
    }

    public function updateDeliveryStatus($id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice not found.');
        }

        $validationRules = [
            'delivery_status' => 'required|in_list[Pending,Booked,In Transit,Delivered,Cancelled]',
            'delivered_date' => 'permit_empty|valid_date'
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->with('error', 'Validation failed: ' . implode(', ', $this->validator->getErrors()));
        }

        $newStatus = $this->request->getPost('delivery_status');
        $deliveredDate = $this->request->getPost('delivered_date');

        $updateData = ['delivery_status' => $newStatus];
        if ($newStatus === 'Delivered' && !empty($deliveredDate)) {
            $updateData['delivered_date'] = $deliveredDate;
        }

        if ($this->invoiceModel->update($id, $updateData)) {
            // Log history
            $this->historyModel->insert([
                'invoice_id' => $id,
                'status' => $newStatus,
                'description' => "Status updated to " . $newStatus . ($newStatus === 'Delivered' ? " on " . $deliveredDate : ""),
                'created_by' => session('user_id')
            ]);

            return redirect()->back()->with('success', 'Delivery status updated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to update delivery status.');
    }

    public function getHistory($id)
    {
        $history = $this->historyModel->getHistoryByInvoice($id);
        return $this->response->setJSON($history);
    }

    public function docTracking()
    {
        $limit = 25;
        $offset = intval($this->request->getGet('offset') ?? 0);

        $customer_id = $this->request->getGet('customer_id');
        $doc_status = $this->request->getGet('doc_status');
        $search = $this->request->getGet('search');

        $this->invoiceModel->select('invoices.*, customers.name as customer_name')
            ->join('customers', 'customers.id = invoices.customer_id', 'left');

        // Document Tracking Condition: (Incomplete Doc Info OR Not Received)
        $this->invoiceModel->groupStart()
            ->where('invoices.doc_status !=', 'Delivered')
            ->orWhere('invoices.doc_tracking_number', null)
            ->orWhere('invoices.doc_tracking_number', '')
            ->groupEnd();

        // Dynamic Filters
        if ($customer_id !== null && $customer_id !== '') {
            $this->invoiceModel->where('invoices.customer_id', $customer_id);
        }
        if ($doc_status !== null && $doc_status !== '') {
            $this->invoiceModel->where('invoices.doc_status', $doc_status);
        }
        if ($search !== null && $search !== '') {
            $this->invoiceModel->like('invoices.invoice_number', $search);
        }

        // Get count before limit
        $totalResults = $this->invoiceModel->countAllResults(false);

        $invoices = $this->invoiceModel->orderBy('invoices.invoice_date', 'DESC')
            ->findAll($limit, $offset);

        $data = [
            'title' => 'Invoice Document Tracking',
            'invoices' => $invoices,
            'customers' => $this->customerModel->where('status', 'active')->findAll(),
            'total_count' => $totalResults,
            'filters' => [
                'customer_id' => $customer_id,
                'doc_status' => $doc_status,
                'search' => $search,
            ],
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $totalResults
        ];

        if ($this->request->isAJAX()) {
            return view('invoices/doc_tracking_rows', $data);
        }

        return view('invoices/doc_tracking', $data);
    }

    public function updateDocDetails($id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice not found.');
        }

        $validationRules = [
            'doc_courier_name' => 'required',
            'doc_tracking_number' => 'required',
            'doc_dispatched_date' => 'required|valid_date'
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->with('error', 'Validation failed: ' . implode(', ', $this->validator->getErrors()));
        }

        $updateData = [
            'doc_courier_name' => $this->request->getPost('doc_courier_name'),
            'doc_tracking_number' => $this->request->getPost('doc_tracking_number'),
            'doc_dispatched_date' => $this->request->getPost('doc_dispatched_date'),
            'doc_status' => 'Dispatched'
        ];

        if ($this->invoiceModel->update($id, $updateData)) {
            // Log history
            $this->historyModel->insert([
                'invoice_id' => $id,
                'status' => 'Dispatched',
                'description' => "Doc Dispatched via " . $updateData['doc_courier_name'] . " (Tracking: " . $updateData['doc_tracking_number'] . ")",
                'created_by' => session('user_id')
            ]);

            return redirect()->back()->with('success', 'Document tracking information updated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to update document tracking information.');
    }

    public function updateDocStatus($id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice not found.');
        }

        $validationRules = [
            'doc_status' => 'required|in_list[Pending,Dispatched,Delivered,Returned]',
            'doc_received_date' => 'permit_empty|valid_date'
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->with('error', 'Validation failed: ' . implode(', ', $this->validator->getErrors()));
        }

        $newStatus = $this->request->getPost('doc_status');
        $receivedDate = $this->request->getPost('doc_received_date');

        $updateData = ['doc_status' => $newStatus];
        if ($newStatus === 'Delivered' && !empty($receivedDate)) {
            $updateData['doc_received_date'] = $receivedDate;
        }

        if ($this->invoiceModel->update($id, $updateData)) {
            // Log history
            $this->historyModel->insert([
                'invoice_id' => $id,
                'status' => "Doc $newStatus",
                'description' => "Document status updated to " . $newStatus . ($newStatus === 'Delivered' ? " on " . $receivedDate : ""),
                'created_by' => session('user_id')
            ]);

            return redirect()->back()->with('success', 'Document status updated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to update document status.');
    }
}
