<?php

namespace App\Controllers;

use App\Models\BankAccountModel;

class BankAccountController extends BaseController
{
    protected $bankAccountModel;

    public function __construct()
    {
        $this->bankAccountModel = new BankAccountModel();
    }

    public function index()
    {
        $filters = [
            'search'       => $this->request->getGet('search'),
            'status'       => $this->request->getGet('status'),
            'account_type' => $this->request->getGet('account_type'),
        ];

        $data['accounts'] = $this->bankAccountModel->getAccountsWithFilters($filters);
        $data['title'] = 'Bank Accounts';
        $data['filters'] = $filters;

        $stats = [
            'total'         => count($data['accounts']),
            'total_balance' => 0.0,
            'active_count'  => 0,
            'bank_count'    => 0,
            'cash_count'    => 0,
        ];
        foreach ($data['accounts'] as $acc) {
            $stats['total_balance'] += (float) ($acc['current_balance'] ?? 0);
            if (($acc['status'] ?? '') === 'active') {
                $stats['active_count']++;
            }
            if (($acc['account_type'] ?? 'Bank') === 'Cash') {
                $stats['cash_count']++;
            } else {
                $stats['bank_count']++;
            }
        }
        $data['stats'] = $stats;

        return view('bank_accounts/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Add New Bank Account';
        return view('bank_accounts/form', $data);
    }

    public function store()
    {
        $data = $this->request->getPost();
        if (empty($data['account_type'])) {
            $data['account_type'] = 'Bank';
        }
        if (!isset($data['current_balance']) || $data['current_balance'] === '') {
            $data['current_balance'] = 0.00;
        }

        $rules = $this->bankAccountModel->getValidationRules();

        if ($data['account_type'] === 'Bank') {
            $rules['ifsc_code'] = 'required|exact_length[11]|alpha_numeric';
            if (!empty($data['ifsc_code'])) {
                $data['ifsc_code'] = strtoupper(trim($data['ifsc_code']));
            }
        } else {
            $rules['ifsc_code'] = 'permit_empty';
            $data['ifsc_code'] = !empty($data['ifsc_code']) ? trim($data['ifsc_code']) : 'N/A';
        }

        if (!$this->validateData($data, $rules, $this->bankAccountModel->getValidationMessages())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->bankAccountModel->save($data);
        return redirect()->to('bank_accounts')->with('success', 'Bank account added successfully.');
    }

    public function edit($id)
    {
        $data['account'] = $this->bankAccountModel->find($id);
        if (!$data['account']) {
            return redirect()->to('bank_accounts')->with('error', 'Bank account not found.');
        }

        $data['title'] = 'Edit Bank Account';
        return view('bank_accounts/form', $data);
    }

    public function update($id)
    {
        $data = $this->request->getPost();
        if (empty($data['account_type'])) {
            $data['account_type'] = 'Bank';
        }
        if (!isset($data['current_balance']) || $data['current_balance'] === '') {
            $data['current_balance'] = 0.00;
        }

        $rules = $this->bankAccountModel->getValidationRules();
        if (isset($rules['account_number'])) {
            $rules['account_number'] = str_replace('{id}', (string) $id, $rules['account_number']);
        }

        if ($data['account_type'] === 'Bank') {
            $rules['ifsc_code'] = 'required|exact_length[11]|alpha_numeric';
            if (!empty($data['ifsc_code'])) {
                $data['ifsc_code'] = strtoupper(trim($data['ifsc_code']));
            }
        } else {
            $rules['ifsc_code'] = 'permit_empty';
            $data['ifsc_code'] = !empty($data['ifsc_code']) ? trim($data['ifsc_code']) : 'N/A';
        }

        if (!$this->validateData($data, $rules, $this->bankAccountModel->getValidationMessages())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->bankAccountModel->update($id, $data);
        return redirect()->to('bank_accounts')->with('success', 'Bank account updated successfully.');
    }

    public function delete($id)
    {
        try {
            $this->bankAccountModel->delete($id);
            return redirect()->to('bank_accounts')->with('success', 'Bank account deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->to('bank_accounts')->with('error', 'Unable to delete bank account: ' . $e->getMessage());
        }
    }
}
