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
    }

    public function index()
    {
        $filters = [
            'customer_id' => $this->request->getGet('customer_id'),
            'status'      => $this->request->getGet('status'),
            'date_from'   => $this->request->getGet('date_from'),
            'date_to'     => $this->request->getGet('date_to'),
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
            'customer_id'      => $this->request->getPost('customer_id'),
            'agent_id'         => $this->request->getPost('agent_id') ?: null,
            'agent_commission_percent' => $this->request->getPost('agent_commission_percent') ?? 0,
            'invoice_number'   => $this->request->getPost('invoice_number'),
            'invoice_date'     => $this->request->getPost('invoice_date'),
            'due_date'         => $this->request->getPost('due_date'),
            'reference_number' => $this->request->getPost('reference_number'),
            'transport_name'   => $this->request->getPost('transport_name'),
            'waybill_number'   => $this->request->getPost('waybill_number'),
            'waybill_date'     => $this->request->getPost('waybill_date') ?: null,
            'ewaybill_number'  => $this->request->getPost('ewaybill_number'),
            'packages_count'   => $this->request->getPost('packages_count') ?: null,
            'notes'            => $this->request->getPost('notes'),
            'terms'            => $this->request->getPost('terms'),
            'discount_amount'  => $this->request->getPost('discount_amount') ?? 0,
            'discount_type'    => $this->request->getPost('discount_type') ?? 'Fixed',
            'shipping_charge'  => $this->request->getPost('shipping_charge') ?? 0,
            'roundoff_amount'  => $this->request->getPost('roundoff_amount') ?? 0,
            'created_by'       => session('user_id'),
            'status'           => 'Draft'
        ];

        if (!$this->invoiceModel->insert($invoiceData)) {
            return redirect()->back()->withInput()->with('errors', $this->invoiceModel->errors());
        }

        $invoiceId = $this->invoiceModel->getInsertID();
        $items = $this->request->getPost('items');

        foreach ($items as $item) {
            $this->itemModel->insert([
                'invoice_id'     => $invoiceId,
                'product_id'     => $item['product_id'] ?: null,
                'description'    => $item['description'],
                'hsn_code'       => $item['hsn_code'],
                'quantity'       => $item['quantity'],
                'rate'           => $item['rate'],
                'tax_percentage' => $item['tax_percentage'],
                'amount'         => $item['quantity'] * $item['rate']
            ]);
        }

        // Calculate Taxes and Totals
        $customer = $this->customerModel->find($invoiceData['customer_id']);
        $companyStateId = get_setting('company_state_id');
        
        // Get customer state
        $customerAddress = $db->table('addresses')
                             ->where('owner_id', $invoiceData['customer_id'])
                             ->where('owner_type', 'customer')
                             ->where('address_type', 'billing')
                             ->get()->getRowArray();
        
        $customerStateId = $customerAddress['state_id'] ?? $companyStateId;

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
                'agent_commission_amount'  => $commissionAmount,
                'agent_commission_status'  => 'Unpaid'
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to create invoice.');
        }

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

    public function getCustomerState($customerId)
    {
        $db = \Config\Database::connect();
        $address = $db->table('addresses')
                      ->where('owner_id', $customerId)
                      ->where('owner_type', 'customer')
                      ->where('address_type', 'billing')
                      ->get()->getRowArray();

        $companyStateId = get_setting('company_state_id');
        $customerStateId = $address['state_id'] ?? $companyStateId;

        return $this->response->setJSON([
            'customer_state_id' => $customerStateId,
            'company_state_id'  => $companyStateId,
            'is_inter_state'    => ($customerStateId != $companyStateId)
        ]);
    }

    public function update($id)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $invoiceData = [
            'customer_id'      => $this->request->getPost('customer_id'),
            'agent_id'         => $this->request->getPost('agent_id') ?: null,
            'agent_commission_percent' => $this->request->getPost('agent_commission_percent') ?? 0,
            'invoice_date'     => $this->request->getPost('invoice_date'),
            'due_date'         => $this->request->getPost('due_date'),
            'reference_number' => $this->request->getPost('reference_number'),
            'transport_name'   => $this->request->getPost('transport_name'),
            'waybill_number'   => $this->request->getPost('waybill_number'),
            'waybill_date'     => $this->request->getPost('waybill_date') ?: null,
            'ewaybill_number'  => $this->request->getPost('ewaybill_number'),
            'packages_count'   => $this->request->getPost('packages_count') ?: null,
            'notes'            => $this->request->getPost('notes'),
            'terms'            => $this->request->getPost('terms'),
            'discount_amount'  => $this->request->getPost('discount_amount') ?? 0,
            'discount_type'    => $this->request->getPost('discount_type') ?? 'Fixed',
            'shipping_charge'  => $this->request->getPost('shipping_charge') ?? 0,
            'roundoff_amount'  => $this->request->getPost('roundoff_amount') ?? 0,
            'updated_by'       => session('user_id'),
        ];

        $this->invoiceModel->update($id, $invoiceData);

        // Update items
        $this->itemModel->where('invoice_id', $id)->delete();
        $items = $this->request->getPost('items');

        foreach ($items as $item) {
            $this->itemModel->insert([
                'invoice_id'     => $id,
                'product_id'     => $item['product_id'] ?: null,
                'description'    => $item['description'],
                'hsn_code'       => $item['hsn_code'],
                'quantity'       => $item['quantity'],
                'rate'           => $item['rate'],
                'tax_percentage' => $item['tax_percentage'],
                'amount'         => $item['quantity'] * $item['rate']
            ]);
        }

        // Recalculate
        $customer = $this->customerModel->find($invoiceData['customer_id']);
        $companyStateId = get_setting('company_state_id');
        $customerAddress = $db->table('addresses')
                             ->where('owner_id', $invoiceData['customer_id'])
                             ->where('owner_type', 'customer')
                             ->where('address_type', 'billing')
                             ->get()->getRowArray();
        
        $customerStateId = $customerAddress['state_id'] ?? $companyStateId;

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
                'agent_commission_amount'  => $commissionAmount,
                'agent_commission_status'  => $updatedInvoice['agent_commission_status'] ?: 'Unpaid'
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

        $grossSettlement = (float)$this->request->getPost('gross_settlement');
        $amount = (float)$this->request->getPost('amount');
        $discount = (float)$this->request->getPost('discount_amount') ?? 0;
        $mahimai = (float)$this->request->getPost('mahimai_amount') ?? 0;
        $postal = (float)$this->request->getPost('postal_charges') ?? 0;

        if ($grossSettlement > $invoice['balance'] + 0.01) {
            return redirect()->back()->withInput()->with('error', 'Gross settlement cannot exceed invoice balance.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $paymentNumber = $this->paymentModel->generatePaymentNumber();

        $paymentData = [
            'invoice_id'         => $invoiceId,
            'customer_id'        => $invoice['customer_id'],
            'payment_number'     => $paymentNumber,
            'payment_date'       => $this->request->getPost('payment_date'),
            'payment_mode'       => $this->request->getPost('payment_mode'),
            'amount'             => $amount,
            'discount_amount'    => $discount,
            'mahimai_amount'     => $mahimai,
            'postal_charges'     => $postal,
            'reference_number'   => $this->request->getPost('reference_number'),
            'bank_account_id'    => $this->request->getPost('bank_account_id'),
            'notes'              => $this->request->getPost('notes'),
            'created_by'         => session('user_id'),
            'updated_by'         => session('user_id'),
            'zoho_sync_status'   => 'Pending'
        ];

        $this->paymentModel->insert($paymentData);
        $paymentId = $this->paymentModel->getInsertID();

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
            'customer_id'    => $customer['zoho_contact_id'],
            'invoice_number' => $invoice['invoice_number'],
            'date'           => $invoice['invoice_date'],
            'due_date'       => $invoice['due_date'],
            'reference_number'=> $invoice['reference_number'],
            'discount'       => $invoice['discount_amount'],
            'discount_type'  => strtolower($invoice['discount_type']),
            'shipping_charge'=> $invoice['shipping_charge'],
            'notes'          => $invoice['notes'],
            'terms'          => $invoice['terms'],
            'line_items'     => []
        ];

        foreach ($invoice['items'] as $item) {
            $zohoData['line_items'][] = [
                'name'        => $item['description'],
                'description' => $item['description'],
                'rate'        => $item['rate'],
                'quantity'    => $item['quantity'],
                'hsn_or_sac'  => $item['hsn_code'],
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
                'zoho_sync_status'=> 'Synced',
                'zoho_sync_at'    => date('Y-m-d H:i:s')
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
            'payment_mode'=> $payment['payment_mode'],
            'amount'      => $payment['amount'] + $payment['discount_amount'] + $payment['mahimai_amount'] + $payment['postal_charges'],
            'date'        => $payment['payment_date'],
            'reference_number' => $payment['reference_number'],
            'description' => $payment['notes'],
            'invoices'    => [
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
                'zoho_sync_status'=> 'Synced',
                'zoho_sync_at'    => date('Y-m-d H:i:s')
            ]);
            return true;
        }

        $this->paymentModel->update($paymentId, ['zoho_sync_status' => 'Failed']);
        return false;
    }

    public function print($id)
    {
        $data['invoice'] = $this->invoiceModel->getInvoiceById($id);
        if (!$data['invoice']) return 'Invoice not found';
        
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
}
