<?php

namespace App\Controllers;

use App\Models\ExpenseModel;
use App\Models\BankAccountModel;
use App\Models\ExpenseCategoryModel;

class ExpenseController extends BaseController
{
    protected $expenseModel;
    protected $bankAccountModel;

    protected $accountingModel;

    public function __construct()
    {
        $this->expenseModel = new ExpenseModel();
        $this->bankAccountModel = new BankAccountModel();
        $this->accountingModel = new \App\Models\AccountingModel();
    }

    public function index()
    {
        $filters = [
            'category_id'     => $this->request->getGet('category_id'),
            'bank_account_id' => $this->request->getGet('bank_account_id'),
            'date_from'       => $this->request->getGet('date_from'),
            'date_to'         => $this->request->getGet('date_to'),
        ];

        $data['expenses'] = $this->expenseModel->getExpensesWithFilters($filters);
        
        $categoryModel = new ExpenseCategoryModel();
        $data['categories'] = $categoryModel->where('status', 'active')->orderBy('category_name', 'ASC')->findAll();
        $data['bank_accounts'] = $this->bankAccountModel->where('status', 'active')->orderBy('bank_name', 'ASC')->findAll();
        
        $data['title'] = 'Expense Management';
        $data['filters'] = $filters;
        
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

        $expenseId = $this->expenseModel->getInsertID();
        $data = $this->request->getPost();

        // --- ACCOUNTING LEDGER ---
        $categoryModel = new ExpenseCategoryModel();
        $cat = $categoryModel->find($data['category_id']);
        $categoryName = $cat ? $cat['category_name'] : 'General Expense';
        
        // Ensure category account exists or use generic
        $paymentAccount = (strpos(strtolower($data['payment_mode']), 'cash') !== false) ? 'Cash' : 'Bank Account';
        
        // Dr Expense
        $this->accountingModel->postEntry($categoryName, $data['expense_date'], $data['amount'], 0, $data['description'], 'expense', $expenseId);
        // Cr Bank/Cash
        $this->accountingModel->postEntry($paymentAccount, $data['expense_date'], 0, $data['amount'], "Expense: $categoryName", 'expense', $expenseId);
        // ------------------------

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
