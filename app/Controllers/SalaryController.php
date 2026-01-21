<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SalaryModel;
use App\Models\EmployeeModel;
use App\Models\AttendanceModel;
use App\Models\LoanModel;
use App\Models\LoanPaymentModel;

class SalaryController extends BaseController
{
    protected $salaryModel;
    protected $employeeModel;
    protected $attendanceModel;
    protected $loanModel;
    protected $loanPaymentModel;
    protected $db;

    public function __construct()
    {
        $this->salaryModel = new SalaryModel();
        $this->employeeModel = new EmployeeModel();
        $this->attendanceModel = new AttendanceModel();
        $this->loanModel = new LoanModel();
        $this->loanPaymentModel = new LoanPaymentModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $month = $this->request->getGet('month') ?? date('Y-m');

        $data['salaries'] = $this->salaryModel
            ->select('salaries.*, employees.first_name, employees.last_name')
            ->join('employees', 'employees.id = salaries.employee_id')
            ->where('salary_month', $month . '-01')
            ->findAll();
        
        $data['month'] = $month;

        return view('salaries/index', $data);
    }

    public function calculate()
    {
        $month = $this->request->getGet('month') ?? date('Y-m');
        
        // 1. Get Active Employees
        $employees = $this->employeeModel->where('status', 'active')->findAll();
        
        // 2. Prepare Calculation Data
        $calculations = [];

        foreach ($employees as $emp) {
            // Basic
            $basic = $emp['basic_salary'];
            
            // Attendance
            // Count absences in that month
            $start = $month . '-01';
            $end = date('Y-m-t', strtotime($start));
            
            $absentCount = $this->attendanceModel
                ->where('employee_id', $emp['id'])
                ->where('attendance_date >=', $start)
                ->where('attendance_date <=', $end)
                ->where('status', 'Absent')
                ->countAllResults();

            $halfDayCount = $this->attendanceModel
                ->where('employee_id', $emp['id'])
                ->where('attendance_date >=', $start)
                ->where('attendance_date <=', $end)
                ->where('status', 'Half Day')
                ->countAllResults();
            
            $totalAbsentDays = $absentCount + ($halfDayCount * 0.5);
            
            // Simple logic: 30 days fixed or actual days? Let's use 30 for simplicity or actual days.
            // Let's use Days in Month
            $daysInMonth = date('t', strtotime($start));
            $perDay = $basic > 0 ? $basic / $daysInMonth : 0;
            $absentDeduction = $totalAbsentDays * $perDay;

            // Double Pay Days (multiplier > 1.0 and status = Present)
            $doublePayRecords = $this->attendanceModel
                ->where('employee_id', $emp['id'])
                ->where('attendance_date >=', $start)
                ->where('attendance_date <=', $end)
                ->where('status', 'Present')
                ->where('multiplier >', 1.0)
                ->findAll();
            
            $extraPay = 0;
            foreach ($doublePayRecords as $record) {
                $extraPay += ($record['multiplier'] - 1.0) * $perDay;
            }

            // Loans
            $loans = $this->loanModel
                ->where('employee_id', $emp['id'])
                ->where('status', 'Approved')
                ->where('credit_status', 'Credited')
                ->where('remaining_amount >', 0)
                ->findAll();
            
            $loanDeduction = 0;
            $loanDetails = [];

            foreach ($loans as $loan) {
                $deduct = min($loan['monthly_deduction'], $loan['remaining_amount']);
                $loanDeduction += $deduct;
                $loanDetails[] = [
                    'id' => $loan['id'],
                    'amount' => $deduct
                ];
            }

            $totalDeduction = $absentDeduction + $loanDeduction;
            $net = $basic - $totalDeduction + $extraPay;

            // Check if already generated
            $isGenerated = $this->salaryModel
                ->where('employee_id', $emp['id'])
                ->where('salary_month', $start)
                ->countAllResults() > 0;

            $calculations[] = [
                'employee' => $emp,
                'basic' => $basic,
                'absent_count' => $totalAbsentDays,
                'absent_deduction' => $absentDeduction,
                'extra_pay' => $extraPay,
                'loan_deduction' => $loanDeduction,
                'loan_details' => $loanDetails, // Need to pass this to store
                'net' => $net,
                'is_generated' => $isGenerated
            ];
        }

        $data = [
            'month' => $month,
            'calculations' => $calculations
        ];

        return view('salaries/calculate', $data);
    }

    public function process()
    {
        $month = $this->request->getPost('month');
        $selectedEmpIds = $this->request->getPost('employee_ids'); // Array of IDs to process
        $exemptLoan = $this->request->getPost('exempt_loan') ?? []; // Array of employee IDs to exempt from loan deduction
        
        if (empty($month) || empty($selectedEmpIds)) {
            return redirect()->back()->with('error', 'No employees selected.');
        }

        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));
        $daysInMonth = date('t', strtotime($start));

        $this->db->transStart();

        foreach ($selectedEmpIds as $empId) {
            // Re-calculate to be safe (or trust hidden fields? Secure way is re-calculate)
            // For MVP speed, let's re-calculate logic briefly or fetch necessary parts.
            
            $emp = $this->employeeModel->find($empId);
            if (!$emp) continue;

            // Check if already exists
            $existing = $this->salaryModel
                ->where('employee_id', $empId)
                ->where('salary_month', $start)
                ->first();
            
            if ($existing) continue; // Skip if already done

            // --- Calc Logic (Same as above) ---
            $basic = $emp['basic_salary'];
            $absentCount = $this->attendanceModel
                ->where('employee_id', $empId)
                ->where('attendance_date >=', $start)
                ->where('attendance_date <=', $end)
                ->where('status', 'Absent')
                ->countAllResults();
            
            $halfDayCount = $this->attendanceModel
                ->where('employee_id', $empId)
                ->where('attendance_date >=', $start)
                ->where('attendance_date <=', $end)
                ->where('status', 'Half Day')
                ->countAllResults();
            
            $totalAbsentDays = $absentCount + ($halfDayCount * 0.5);
            
            $perDay = $basic > 0 ? $basic / $daysInMonth : 0;
            $absentDeduction = $totalAbsentDays * $perDay;

            // Double Pay Days
            $doublePayRecords = $this->attendanceModel
                ->where('employee_id', $empId)
                ->where('attendance_date >=', $start)
                ->where('attendance_date <=', $end)
                ->where('status', 'Present')
                ->where('multiplier >', 1.0)
                ->findAll();
            
            $extraPay = 0;
            foreach ($doublePayRecords as $record) {
                $extraPay += ($record['multiplier'] - 1.0) * $perDay;
            }

            // Loan - Check if exempted
            $loanDeduction = 0;
            $isLoanExempt = isset($exemptLoan[$empId]) && $exemptLoan[$empId] == 1;
            
            if (!$isLoanExempt) {
                $loans = $this->loanModel
                    ->where('employee_id', $empId)
                    ->where('status', 'Approved')
                    ->where('credit_status', 'Credited')
                    ->where('remaining_amount >', 0)
                    ->findAll();

                foreach ($loans as $loan) {
                    $deduct = min($loan['monthly_deduction'], $loan['remaining_amount']);
                    $loanDeduction += $deduct;
                    
                    // Update Loan Remaining
                    $newRemaining = $loan['remaining_amount'] - $deduct;
                    $status = $newRemaining <= 0 ? 'Completed' : 'Approved';
                    
                    $this->loanModel->save([
                        'id' => $loan['id'],
                        'remaining_amount' => $newRemaining,
                        'status' => $status
                    ]);
                    
                    // Log payment in loan_payments table
                    $this->loanPaymentModel->insert([
                        'loan_id' => $loan['id'],
                        'salary_id' => null, // Will update after salary insert
                        'payment_date' => date('Y-m-d'),
                        'amount_paid' => $deduct,
                        'remaining_after_payment' => $newRemaining,
                        'payment_month' => $month
                    ]);
                }
            }

            $totalDeductions = $absentDeduction + $loanDeduction;
            $net = $basic - $totalDeductions + $extraPay;

            // Save Salary
            $this->salaryModel->insert([
                'employee_id'  => $empId,
                'salary_month' => $start,
                'basic_salary' => $basic,
                'allowances'   => $extraPay, // Store extra pay as allowances
                'deductions'   => $totalDeductions,
                'net_salary'   => $net,
                'exempt_loan_deduction' => $isLoanExempt ? 1 : 0
            ]);
        }

        $this->db->transComplete();

        return redirect()->to('salaries?month='.$month)->with('success', 'Salaries processed successfully.');
    }

    public function payslip($id)
    {
        $salary = $this->salaryModel->select('salaries.*, employees.*') // Join ample data
            ->join('employees', 'employees.id = salaries.employee_id')
            ->where('salaries.id', $id)
            ->first();
        
        if (!$salary) {
            return redirect()->back()->with('error', 'Salary record not found.');
        }

        // Calculate attendance breakdown for this month
        $month = date('Y-m', strtotime($salary['salary_month']));
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));
        $daysInMonth = date('t', strtotime($start));
        
        // Get all attendance records for this employee in this month
        $attendanceRecords = $this->attendanceModel
            ->where('employee_id', $salary['employee_id'])
            ->where('attendance_date >=', $start)
            ->where('attendance_date <=', $end)
            ->findAll();
        
        // Count attendance statistics
        $presentDays = 0;
        $absentDays = 0;
        $leaveDays = 0;
        $doublePayDays = 0;
        
        foreach ($attendanceRecords as $record) {
            if ($record['status'] == 'Present') {
                $presentDays++;
                if (isset($record['multiplier']) && $record['multiplier'] > 1.0) {
                    $doublePayDays++;
                }
            } elseif ($record['status'] == 'Absent') {
                $absentDays++;
            } elseif ($record['status'] == 'Leave') {
                $leaveDays++;
            }
        }
        
        // Calculate per-day rate
        $perDayRate = $salary['basic_salary'] > 0 ? $salary['basic_salary'] / $daysInMonth : 0;
        
        // Calculate individual deductions
        $absentDeduction = $absentDays * $perDayRate;
        $loanDeduction = $salary['deductions'] - $absentDeduction;
        
        // Get loan details for this month
        $loans = $this->loanModel
            ->where('employee_id', $salary['employee_id'])
            ->where('status', 'Approved')
            ->findAll();
        
        $data = [
            'salary' => $salary,
            'daysInMonth' => $daysInMonth,
            'presentDays' => $presentDays,
            'absentDays' => $absentDays,
            'leaveDays' => $leaveDays,
            'doublePayDays' => $doublePayDays,
            'perDayRate' => $perDayRate,
            'absentDeduction' => $absentDeduction,
            'loanDeduction' => $loanDeduction,
            'extraPay' => $salary['allowances'], // Double pay stored in allowances
            'loans' => $loans
        ];

        return view('salaries/payslip', $data);
    }
    
    public function delete($id)
    {
        // Get salary record
        $salary = $this->salaryModel->find($id);
        
        if (!$salary) {
            return redirect()->back()->with('error', 'Salary record not found.');
        }
        
        // Check if salary is already paid
        if ($salary['is_paid'] == 1) {
            return redirect()->back()->with('error', 'Cannot delete a paid salary. Please mark as unpaid first.');
        }
        
        $this->db->transStart();
        
        // Get all loan payments for this salary
        $loanPayments = $this->loanPaymentModel
            ->where('payment_month', date('Y-m', strtotime($salary['salary_month'])))
            ->where('salary_id', $id)
            ->findAll();
        
        // Revert loan deductions
        foreach ($loanPayments as $payment) {
            $loan = $this->loanModel->find($payment['loan_id']);
            if ($loan) {
                // Add back the deducted amount to remaining
                $newRemaining = $loan['remaining_amount'] + $payment['amount_paid'];
                
                // Update loan status back to Approved if it was Completed
                $newStatus = $loan['status'];
                if ($loan['status'] == 'Completed' && $newRemaining > 0) {
                    $newStatus = 'Approved';
                }
                
                $this->loanModel->save([
                    'id' => $loan['id'],
                    'remaining_amount' => $newRemaining,
                    'status' => $newStatus
                ]);
            }
            
            // Delete the payment record
            $this->loanPaymentModel->delete($payment['id']);
        }
        
        // Delete salary record
        $this->salaryModel->delete($id);
        
        $this->db->transComplete();
        
        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to delete salary record.');
        }
        
        return redirect()->back()->with('success', 'Salary record deleted and loan deductions reverted successfully.');
    }
    
    public function markPaid($id)
    {
        $salary = $this->salaryModel->find($id);
        
        if (!$salary) {
            return redirect()->back()->with('error', 'Salary record not found.');
        }
        
        $this->salaryModel->save([
            'id' => $id,
            'is_paid' => 1,
            'paid_date' => date('Y-m-d')
        ]);
        
        return redirect()->back()->with('success', 'Salary marked as paid.');
    }
    
    public function markUnpaid($id)
    {
        $salary = $this->salaryModel->find($id);
        
        if (!$salary) {
            return redirect()->back()->with('error', 'Salary record not found.');
        }
        
        $this->salaryModel->save([
            'id' => $id,
            'is_paid' => 0,
            'paid_date' => null
        ]);
        
        return redirect()->back()->with('success', 'Salary marked as unpaid.');
    }
}
