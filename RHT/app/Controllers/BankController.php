<?php

namespace App\Controllers;

use App\Models\BankModel;
use App\Models\StatementModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class BankController extends Controller
{
    public function index()
    {
        $model = new BankModel();
        $data['banks'] = $model->findAll();
        $data['pageTitle'] = 'Bank List';
        return view('banks/list', $data);
    }

    public function create()
    {
        $data['pageTitle'] = 'Create Bank';
        return view('banks/create', $data);
    }

    public function store()
    {
        $model = new BankModel();

        $data = [
            'bank_name' => $this->request->getPost('bank_name'),
            'account_name' => $this->request->getPost('account_name'),
            'branch' => $this->request->getPost('branch'),
            'ifsc_code' => $this->request->getPost('ifsc_code'),
            'branch_address' => $this->request->getPost('branch_address'),
            'account_no' => $this->request->getPost('account_no'),
            'customer_id' => $this->request->getPost('customer_id'),
            'status' => $this->request->getPost('status'),
            'balance' => 0.00,
        ];

        $model->save($data);
        return redirect()->to('/banks');
    }

    public function edit($id)
    {
        $model = new BankModel();
        $data['bank'] = $model->find($id);
        $data['pageTitle'] = 'Edit Bank';
        return view('banks/edit', $data);
    }

    public function update($id)
    {
        $model = new BankModel();

        $data = [
            'bank_name' => $this->request->getPost('bank_name'),
            'account_name' => $this->request->getPost('account_name'),
            'branch' => $this->request->getPost('branch'),
            'ifsc_code' => $this->request->getPost('ifsc_code'),
            'branch_address' => $this->request->getPost('branch_address'),
            'account_no' => $this->request->getPost('account_no'),
            'customer_id' => $this->request->getPost('customer_id'),
            'status' => $this->request->getPost('status'),
            'balance' => 0.00,
        ];

        $model->update($id, $data);
        return redirect()->to('/banks');
    }

    public function show($id)
    {
        $model = new BankModel();
        $data['bank'] = $model->find($id);
        $data['pageTitle'] = 'Show Bank';
        return view('banks/show', $data);
    }
    public function addTransaction()
    {
        $bankModel = new BankModel();
        $data['banks'] = $bankModel->findAll();
        $data['pageTitle'] = 'Add New Transaction';
        return view('banks/add_transaction', $data);
    }

    public function storeTransaction()
    {
        $statementModel = new StatementModel();
        $session = session();
        $addedBy = $session->get('id');

        // Save transaction details
        $transactionData = [
            'bank_id' => $this->request->getPost('bank_id'),
            'added_by' => $addedBy,
            //
            'transaction_type' => $this->request->getPost('transaction_type'),
            'amount' => $this->request->getPost('amount'),
            'transaction_date' => $this->request->getPost('transaction_date'),
            'reference_no' => $this->request->getPost('reference_no'),
            'payment_others_reason' => $this->request->getPost('payment_others_reason'),
            'type_of_payment' => $this->request->getPost('type_of_payment'),
            'mode_of_payment' => $this->request->getPost('mode_of_payment'),
        ];

        // Determine depositor_name or receiver_name based on transaction type
        if ($transactionData['transaction_type'] === 'credit') {
            $transactionData['receiver_name'] = $this->request->getPost('depositor_name');
        } elseif ($transactionData['transaction_type'] === 'debit') {
            $transactionData['receiver_name'] = $this->request->getPost('receiver_name');
        }

        $statementModel->save($transactionData);

        // Update bank balance based on transaction type
        $bankModel = new BankModel();
        $bank = $bankModel->find($transactionData['bank_id']);
        if ($transactionData['transaction_type'] === 'credit') {
            $bank['balance'] += $transactionData['amount'];
        } elseif ($transactionData['transaction_type'] === 'debit') {
            $bank['balance'] -= $transactionData['amount'];
        }
        $bankModel->update($transactionData['bank_id'], $bank);

        return redirect()->to('/banks');
    }
    public function listStatements()
    {
        $statementModel = new StatementModel();
        $bankModel = new BankModel();
        $userModel = new UserModel();

        // Get the current month
        $currentMonthStart = date('Y-m-01');
        $currentMonthEnd = date('Y-m-t');

        // Get date range from query parameters
        $startDate = $this->request->getGet('start_date') ?: $currentMonthStart;
        $endDate = $this->request->getGet('end_date') ?: $currentMonthEnd;

        // Fetch statements within the date range
        $statements = $statementModel->where('transaction_date >=', $startDate)
            ->where('transaction_date <=', $endDate)
            ->findAll();

        // Fetch user and bank details
        foreach ($statements as &$statement) {
            $bank = $bankModel->find($statement['bank_id']);
            $user = $userModel->find($statement['added_by']);

            $statement['bank_name'] = $bank['bank_name'];
            $statement['user_name'] = $user['name'];
        }

        $data['statements'] = $statements;
        $data['startDate'] = $startDate;
        $data['endDate'] = $endDate;
        $data['pageTitle'] = 'Show Bank Statements';
        return view('banks/list_statements', $data);
    }
    public function viewStatement($id)
    {
        $statementModel = new StatementModel();
        $bankModel = new BankModel();
        $userModel = new UserModel();

        $statement = $statementModel->find($id);
        $bank = $bankModel->find($statement['bank_id']);
        $user = $userModel->find($statement['added_by']);

        $statement['bank_name'] = $bank['bank_name'];
        $statement['user_name'] = $user['name'];

        $data['statement'] = $statement;
        $data['pageTitle'] = 'View Statement';
        return view('banks/view_statement', $data);
    }

    public function editStatement($id)
    {
        $statementModel = new StatementModel();
        $bankModel = new BankModel();
        $userModel = new UserModel();

        $statement = $statementModel->find($id);
        $banks = $bankModel->findAll();
        $users = $userModel->findAll();

        $data['statement'] = $statement;
        $data['banks'] = $banks;
        $data['users'] = $users;
        $data['pageTitle'] = 'Edit Statement';
        return view('banks/edit_statement', $data);
    }

    public function updateStatement($id)
    {
        $statementModel = new StatementModel();

        $statementData = [
            'bank_id' => $this->request->getPost('bank_id'),
            'transaction_type' => $this->request->getPost('transaction_type'),
            'amount' => $this->request->getPost('amount'),
            'receiver_name' => $this->request->getPost('receiver_name'),
            'reference_no' => $this->request->getPost('reference_no'),
            'payment_others_reason' => $this->request->getPost('payment_others_reason'),
            'type_of_payment' => $this->request->getPost('type_of_payment'),
            'mode_of_payment' => $this->request->getPost('mode_of_payment'),
            'transaction_date' => $this->request->getPost('transaction_date')
        ];

        $statementModel->update($id, $statementData);

        return redirect()->to('/banks/list-statements');
    }
}


?>