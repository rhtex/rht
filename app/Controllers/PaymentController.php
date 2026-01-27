<?php

namespace App\Controllers;

use App\Models\PaymentModel;
use App\Models\BillModel;
use App\Models\VendorModel;
use App\Models\BankAccountModel;

class PaymentController extends BaseController
{
    protected $paymentModel;
    protected $billModel;
    protected $vendorModel;
    protected $bankAccountModel;

    public function __construct()
    {
        $this->paymentModel = new PaymentModel();
        $this->billModel = new BillModel();
        $this->vendorModel = new VendorModel();
        $this->bankAccountModel = new BankAccountModel();
    }

    public function index()
    {
        $data['payments'] = $this->paymentModel->select('payments.*, bills.bill_number, vendors.name as vendor_name')
                                              ->join('bills', 'bills.id = payments.bill_id')
                                              ->join('vendors', 'vendors.id = payments.vendor_id')
                                              ->orderBy('payments.payment_date', 'DESC')
                                              ->findAll();
        $data['title'] = 'Payments History';
        return view('payments/index', $data);
    }

    public function view($id)
    {
        $data['payment'] = $this->paymentModel->select('payments.*, bills.bill_number, vendors.name as vendor_name, bank_accounts.bank_name, bank_accounts.account_number')
                                             ->join('bills', 'bills.id = payments.bill_id')
                                             ->join('vendors', 'vendors.id = payments.vendor_id')
                                             ->join('bank_accounts', 'bank_accounts.id = payments.bank_account_id', 'left')
                                             ->find($id);

        if (!$data['payment']) {
            return redirect()->to('payments')->with('error', 'Payment not found.');
        }

        $data['title'] = 'Payment Details - ' . $data['payment']['payment_number'];
        return view('payments/view', $data);
    }

    public function edit($id)
    {
        $data['payment'] = $this->paymentModel->find($id);
        if (!$data['payment']) {
            return redirect()->to('payments')->with('error', 'Payment not found.');
        }

        $data['bill'] = $this->billModel->getBillById($data['payment']['bill_id']);
        $data['bank_accounts'] = $this->bankAccountModel->findAll();
        $data['title'] = 'Edit Payment - ' . $data['payment']['payment_number'];

        return view('payments/edit', $data);
    }

    public function update($id)
    {
        $payment = $this->paymentModel->find($id);
        if (!$payment) {
            return redirect()->to('payments')->with('error', 'Payment not found.');
        }

        $bill = $this->billModel->find($payment['bill_id']);
        
        $grossSettlement = (float)$this->request->getPost('gross_settlement');
        $amount = (float)$this->request->getPost('amount');
        $discount = (float)$this->request->getPost('discount_amount') ?? 0;
        $mahimai = (float)$this->request->getPost('mahimai_amount') ?? 0;
        $postal = (float)$this->request->getPost('postal_charges') ?? 0;

        // Current settlement in DB for this payment
        $oldSettlement = $payment['amount'] + $payment['discount_amount'] + $payment['mahimai_amount'] + $payment['postal_charges'];
        
        // Validation: New balance if we revert old and apply new
        $currentBillBalance = $bill['balance'] + $oldSettlement;
        if ($grossSettlement > $currentBillBalance + 0.01) {
            return redirect()->back()->withInput()->with('error', 'Gross settlement cannot exceed bill balance.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $paymentData = [
            'payment_date'   => $this->request->getPost('payment_date'),
            'payment_mode'   => $this->request->getPost('payment_mode'),
            'amount'         => $amount,
            'discount_amount'=> $discount,
            'mahimai_amount' => $mahimai,
            'postal_charges' => $postal,
            'reference_number' => $this->request->getPost('reference_number'),
            'bank_account_id'  => $this->request->getPost('bank_account_id'),
            'notes'            => $this->request->getPost('notes'),
            'updated_by'       => session('user_id'),
        ];

        $this->paymentModel->update($id, $paymentData);

        // Update bill paid amount: Subtract old, Add new
        $newPaidAmount = ($bill['paid_amount'] - $oldSettlement) + $grossSettlement;
        $this->billModel->update($bill['id'], ['paid_amount' => $newPaidAmount]);
        $this->billModel->updateBalance($bill['id']);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update payment.');
        }

        return redirect()->to('payments/view/' . $id)->with('success', 'Payment updated successfully.');
    }

    public function delete($id)
    {
        $payment = $this->paymentModel->find($id);
        if (!$payment) {
            return redirect()->to('payments')->with('error', 'Payment not found.');
        }

        $bill = $this->billModel->find($payment['bill_id']);
        $settlement = $payment['amount'] + $payment['discount_amount'] + $payment['mahimai_amount'] + $payment['postal_charges'];

        $db = \Config\Database::connect();
        $db->transStart();

        // Revert bill totals
        $newPaidAmount = $bill['paid_amount'] - $settlement;
        $this->billModel->update($bill['id'], ['paid_amount' => $newPaidAmount]);
        $this->billModel->updateBalance($bill['id']);

        // Delete payment
        $this->paymentModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to delete payment.');
        }

        return redirect()->to('payments')->with('success', 'Payment deleted successfully.');
    }
}
