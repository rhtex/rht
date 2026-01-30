<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LoanModel;
use App\Models\LoanPaymentModel;
use App\Models\EmployeeModel;

class LoanController extends BaseController
{
    protected $loanModel;
    protected $employeeModel;
    protected $loanPaymentModel;

    public function __construct()
    {
        $this->loanModel = new LoanModel();
        $this->employeeModel = new EmployeeModel();
        $this->loanPaymentModel = new LoanPaymentModel();
    }

    public function index()
    {
        $filters = [
            'employee_id'   => $this->request->getGet('employee_id'),
            'status'        => $this->request->getGet('status'),
            'credit_status' => $this->request->getGet('credit_status'),
        ];

        $data['loans'] = $this->loanModel->getLoansWithFilters($filters);
        $data['employees'] = $this->employeeModel->where('status', 'active')->orderBy('first_name', 'ASC')->findAll();
        $data['title'] = 'Loans';
        $data['filters'] = $filters;
        
        return view('loans/index', $data);
    }

    public function create()
    {
        $data['employees'] = $this->employeeModel->where('status', 'active')->findAll();
        return view('loans/create', $data);
    }

    public function store()
    {
        $rules = [
            'employee_id'       => 'required|integer',
            'loan_amount'       => 'required|numeric',
            'monthly_deduction' => 'required|numeric',
            'loan_date'         => 'required|valid_date',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $data['status'] = 'Pending';
        $data['remaining_amount'] = $data['loan_amount']; // Initially remaining is full amount

        $this->loanModel->save($data);

        return redirect()->to('loans')->with('success', 'Loan request created successfully.');
    }

    public function edit($id)
    {
        $data['loan'] = $this->loanModel->find($id);
        if (!$data['loan']) {
            return redirect()->to('loans')->with('error', 'Loan not found.');
        }
        $data['employees'] = $this->employeeModel->findAll(); // Show all in case inactive has loan
        return view('loans/edit', $data);
    }

    public function update($id)
    {
        $rules = [
            'loan_amount'       => 'required|numeric',
            'monthly_deduction' => 'required|numeric',
            'remaining_amount'  => 'required|numeric',
            'status'            => 'required|in_list[Pending,Approved,Rejected,Completed]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id'                => $id,
            'loan_amount'       => $this->request->getPost('loan_amount'),
            'monthly_deduction' => $this->request->getPost('monthly_deduction'),
            'remaining_amount'  => $this->request->getPost('remaining_amount'),
            'status'            => $this->request->getPost('status'),
        ];
        
        // Set processed_date when status changes to Approved
        $oldLoan = $this->loanModel->find($id);
        if ($data['status'] == 'Approved' && (!$oldLoan['processed_date'] || $oldLoan['status'] != 'Approved')) {
            $data['processed_date'] = date('Y-m-d');
        }
        
        // Set credited_date if provided and update credit_status
        if ($this->request->getPost('credited_date')) {
            $data['credited_date'] = $this->request->getPost('credited_date');
            $data['credit_status'] = 'Credited';
        }
        
        $this->loanModel->save($data);

        return redirect()->to('loans')->with('success', 'Loan updated successfully.');
    }

    
    public function view($id)
    {
        $data['loan'] = $this->loanModel
            ->select('loans.*, employees.first_name, employees.last_name, employees.mobile_number')
            ->join('employees', 'employees.id = loans.employee_id')
            ->where('loans.id', $id)
            ->first();
        
        if (!$data['loan']) {
            return redirect()->to('loans')->with('error', 'Loan not found.');
        }
        
        // Get payment history
        $data['payments'] = $this->loanPaymentModel
            ->where('loan_id', $id)
            ->orderBy('payment_date', 'DESC')
            ->findAll();
        
        return view('loans/view', $data);
    }
    
    public function delete($id)
    {
        $this->loanModel->delete($id);
        return redirect()->to('loans')->with('success', 'Loan deleted successfully.');
    }
}
