<?php

namespace App\Controllers;

use App\Models\ExpenseModel;
use App\Models\BankAccountModel;
use App\Models\ExpenseCategoryModel;

class ExpenseController extends BaseController
{
    protected $expenseModel;
    protected $bankAccountModel;

    public function __construct()
    {
        $this->expenseModel = new ExpenseModel();
        $this->bankAccountModel = new BankAccountModel();
    }

    public function index()
    {
        $data['expenses'] = $this->expenseModel
            ->select('expenses.*, bank_accounts.bank_name, bank_accounts.account_number, expense_categories.category_name')
            ->join('bank_accounts', 'bank_accounts.id = expenses.bank_account_id', 'left')
            ->join('expense_categories', 'expense_categories.id = expenses.category_id', 'left')
            ->orderBy('expense_date', 'DESC')
            ->findAll();
        $data['title'] = 'Expense Management';
        return view('expenses/index', $data);
    }

    public function create()
    {
        $categoryModel = new ExpenseCategoryModel();
        $data['categories'] = $categoryModel->where('status', 'active')->findAll();
        $data['bank_accounts'] = $this->bankAccountModel->where('status', 'active')->findAll();
        $data['title'] = 'Add New Expense';
        return view('expenses/form', $data);
    }

    public function store()
    {
        if (!$this->expenseModel->insert($this->request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $this->expenseModel->errors());
        }

        $data = $this->request->getPost();

        // Optional: Deduct from bank balance if bank_account_id is provided
        if (!empty($data['bank_account_id'])) {
            $this->bankAccountModel->where('id', $data['bank_account_id'])
                ->set('current_balance', 'current_balance - ' . $data['amount'], false)
                ->update();
        }

        return redirect()->to('expenses')->with('success', 'Expense recorded successfully.');
    }

    public function edit($id)
    {
        $data['expense'] = $this->expenseModel->find($id);
        if (!$data['expense']) {
            return redirect()->to('expenses')->with('error', 'Expense not found.');
        }

        $categoryModel = new ExpenseCategoryModel();
        $data['categories'] = $categoryModel->where('status', 'active')->findAll();
        $data['bank_accounts'] = $this->bankAccountModel->where('status', 'active')->findAll();
        $data['title'] = 'Edit Expense';
        return view('expenses/form', $data);
    }

    public function update($id)
    {
        $oldExpense = $this->expenseModel->find($id);
        if (!$oldExpense) {
            return redirect()->to('expenses')->with('error', 'Expense not found.');
        }

        if (!$this->expenseModel->update($id, $this->request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $this->expenseModel->errors());
        }

        $data = $this->request->getPost();
        
        // Adjust bank balances if changed
        if (!empty($oldExpense['bank_account_id'])) {
            $this->bankAccountModel->where('id', $oldExpense['bank_account_id'])
                ->set('current_balance', 'current_balance + ' . $oldExpense['amount'], false)
                ->update();
        }

        if (!empty($data['bank_account_id'])) {
            $this->bankAccountModel->where('id', $data['bank_account_id'])
                ->set('current_balance', 'current_balance - ' . $data['amount'], false)
                ->update();
        }

        return redirect()->to('expenses')->with('success', 'Expense updated successfully.');
    }

    public function delete($id)
    {
        $expense = $this->expenseModel->find($id);
        if ($expense) {
            // Revert bank balance
            if (!empty($expense['bank_account_id'])) {
                $this->bankAccountModel->where('id', $expense['bank_account_id'])
                    ->set('current_balance', 'current_balance + ' . $expense['amount'], false)
                    ->update();
            }
            $this->expenseModel->delete($id);
        }
        return redirect()->to('expenses')->with('success', 'Expense deleted successfully.');
    }
}
