<?php

namespace App\Controllers;

use App\Models\InvoicePaymentModel;
use App\Models\InvoiceModel;
use App\Models\CustomerModel;
use App\Models\BankAccountModel;

class InvoicePaymentController extends BaseController
{
    protected $paymentModel;
    protected $invoiceModel;
    protected $customerModel;
    protected $bankAccountModel;

    public function __construct()
    {
        $this->paymentModel = new InvoicePaymentModel();
        $this->invoiceModel = new InvoiceModel();
        $this->customerModel = new CustomerModel();
        $this->bankAccountModel = new BankAccountModel();
    }

    public function index()
    {
        $filters = [
            'customer_id' => $this->request->getGet('customer_id'),
            'payment_mode' => $this->request->getGet('payment_mode'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
        ];

        $data['payments'] = $this->paymentModel->getPaymentsWithFilters($filters);
        $data['customers'] = $this->customerModel->where('status', 'active')->orderBy('name', 'ASC')->findAll();
        $data['title'] = 'Customer Receipts';
        $data['filters'] = $filters;

        return view('invoice_payments/index', $data);
    }

    public function view($id)
    {
        $data['payment'] = $this->paymentModel->select('invoice_payments.*, invoices.invoice_number, customers.name as customer_name, bank_accounts.bank_name, bank_accounts.account_number')
            ->join('invoices', 'invoices.id = invoice_payments.invoice_id')
            ->join('customers', 'customers.id = invoice_payments.customer_id')
            ->join('bank_accounts', 'bank_accounts.id = invoice_payments.bank_account_id', 'left')
            ->find($id);

        if (!$data['payment']) {
            return redirect()->to('invoice_payments')->with('error', 'Receipt not found.');
        }

        $data['title'] = 'Receipt Details - ' . $data['payment']['payment_number'];
        return view('invoice_payments/view', $data);
    }

    public function edit($id)
    {
        $data['payment'] = $this->paymentModel->find($id);
        if (!$data['payment']) {
            return redirect()->to('invoice_payments')->with('error', 'Receipt not found.');
        }

        $data['invoice'] = $this->invoiceModel->getInvoiceById($data['payment']['invoice_id']);
        $data['bank_accounts'] = $this->bankAccountModel->findAll();
        $data['title'] = 'Edit Receipt - ' . $data['payment']['payment_number'];

        return view('invoice_payments/edit', $data);
    }

    public function update($id)
    {
        $payment = $this->paymentModel->find($id);
        if (!$payment) {
            return redirect()->to('invoice_payments')->with('error', 'Receipt not found.');
        }

        $invoice = $this->invoiceModel->find($payment['invoice_id']);

        $grossSettlement = (float) $this->request->getPost('gross_settlement');
        $amount = (float) $this->request->getPost('amount');
        $discount = (float) $this->request->getPost('discount_amount') ?? 0;
        $mahimai = (float) $this->request->getPost('mahimai_amount') ?? 0;
        $postal = (float) $this->request->getPost('postal_charges') ?? 0;

        $oldSettlement = $payment['amount'] + $payment['discount_amount'] + $payment['mahimai_amount'] + $payment['postal_charges'];
        $currentInvoiceBalance = $invoice['balance'] + $oldSettlement;

        if ($grossSettlement > $currentInvoiceBalance + 0.01) {
            return redirect()->back()->withInput()->with('error', 'Gross settlement cannot exceed invoice balance.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $paymentData = [
            'payment_date' => $this->request->getPost('payment_date'),
            'payment_mode' => $this->request->getPost('payment_mode'),
            'amount' => $amount,
            'discount_amount' => $discount,
            'mahimai_amount' => $mahimai,
            'postal_charges' => $postal,
            'reference_number' => $this->request->getPost('reference_number'),
            'bank_account_id' => $this->request->getPost('bank_account_id') ?: null,
            'notes' => $this->request->getPost('notes'),
            'updated_by' => session('user_id'),
        ];

        $this->paymentModel->update($id, $paymentData);

        $newPaidAmount = ($invoice['paid_amount'] - $oldSettlement) + $grossSettlement;
        $this->invoiceModel->update($invoice['id'], ['paid_amount' => $newPaidAmount]);
        $this->invoiceModel->updateBalance($invoice['id']);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update receipt.');
        }

        return redirect()->to('invoice_payments/view/' . $id)->with('success', 'Receipt updated successfully.');
    }

    public function delete($id)
    {
        $payment = $this->paymentModel->find($id);
        if (!$payment) {
            return redirect()->to('invoice_payments')->with('error', 'Receipt not found.');
        }

        $invoice = $this->invoiceModel->find($payment['invoice_id']);
        $settlement = $payment['amount'] + $payment['discount_amount'] + $payment['mahimai_amount'] + $payment['postal_charges'];

        $db = \Config\Database::connect();
        $db->transStart();

        $newPaidAmount = $invoice['paid_amount'] - $settlement;
        $this->invoiceModel->update($invoice['id'], ['paid_amount' => $newPaidAmount]);
        $this->invoiceModel->updateBalance($invoice['id']);

        $this->paymentModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to delete receipt.');
        }

        return redirect()->to('invoice_payments')->with('success', 'Receipt deleted successfully.');
    }
}
