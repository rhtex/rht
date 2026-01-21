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
        $data['accounts'] = $this->bankAccountModel->findAll();
        $data['title'] = 'Bank Accounts';
        return view('bank_accounts/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Add New Bank Account';
        return view('bank_accounts/form', $data);
    }

    public function store()
    {
        if (!$this->validate($this->bankAccountModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->bankAccountModel->save($this->request->getPost());
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
        if (!$this->validate($this->bankAccountModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->bankAccountModel->update($id, $this->request->getPost());
        return redirect()->to('bank_accounts')->with('success', 'Bank account updated successfully.');
    }

    public function delete($id)
    {
        $this->bankAccountModel->delete($id);
        return redirect()->to('bank_accounts')->with('success', 'Bank account deleted successfully.');
    }
}
