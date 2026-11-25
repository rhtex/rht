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
        $filters = [
            'month'       => $this->request->getGet('month') ?? date('Y-m'),
            'employee_id' => $this->request->getGet('employee_id'),
            'is_paid'     => $this->request->getGet('is_paid'),
        ];

        $data['salaries'] = $this->salaryModel->getSalariesWithFilters($filters);
        $data['employees'] = $this->employeeModel->where('status', 'active')->orderBy('first_name', 'ASC')->findAll();
        
        $data['month'] = $filters['month'];
        $data['filters'] = $filters;

        $stats = [
            'total'      => count($data['salaries']),
            'total_net'  => 0.0,
            'paid_count' => 0,
            'unpaid_count' => 0,
        ];
        foreach ($data['salaries'] as $s) {
            $stats['total_net'] += (float) ($s['net_salary'] ?? 0);
            if (!empty($s['is_paid'])) {
                $stats['paid_count']++;
            } else {
                $stats['unpaid_count']++;
            }
        }
        $data['stats'] = $stats;

        return view('salaries/index', $data);
    }

    /**
     * Compute monthly salary breakdown for a single employee.
     */
    private function computeEmployeeSalary(array $emp, string $start, string $end, int $daysInMonth, bool $isLoanExempt = false): array
    {
        $empId = (int) $emp['id'];
        $basic = (float) ($emp['basic_salary'] ?? 0);

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
        $perDay = $basic > 0 ? ($basic / $daysInMonth) : 0.0;
        $absentDeduction = round($totalAbsentDays * $perDay, 2);

        // Double Pay Days (multiplier > 1.0 and status = Present)
        $doublePayRecords = $this->attendanceModel
            ->where('employee_id', $empId)
            ->where('attendance_date >=', $start)
            ->where('attendance_date <=', $end)
            ->where('status', 'Present')
            ->where('multiplier >', 1.0)
            ->findAll();

        $extraPayRaw = 0.0;
        foreach ($doublePayRecords as $record) {
            $extraPayRaw += ((float) $record['multiplier'] - 1.0) * $perDay;
        }
        $extraPay = round($extraPayRaw, 2);

        // Cap loan deductions so net salary never goes negative
        $maxDeductibleForLoans = max(0.0, round($basic + $extraPay - $absentDeduction, 2));
        $loanDeduction = 0.0;
        $loanDetails = [];

        if (!$isLoanExempt && $maxDeductibleForLoans > 0) {
            $loans = $this->loanModel
                ->where('employee_id', $empId)
                ->where('status', 'Approved')
                ->where('credit_status', 'Credited')
                ->where('remaining_amount >', 0)
                ->findAll();

            foreach ($loans as $loan) {
                $availableCap = round($maxDeductibleForLoans - $loanDeduction, 2);
                if ($availableCap <= 0) {
                    break;
                }
                $deduct = round(min((float) $loan['monthly_deduction'], (float) $loan['remaining_amount'], $availableCap), 2);
                if ($deduct <= 0) {
                    continue;
                }
                $loanDeduction = round($loanDeduction + $deduct, 2);
                $loanDetails[] = [
                    'loan' => $loan,
                    'id' => $loan['id'],
                    'amount' => $deduct,
                ];
            }
        }

        $totalDeductions = round($absentDeduction + $loanDeduction, 2);
        $net = max(0.0, round($basic + $extraPay - $totalDeductions, 2));

        return [
            'basic' => round($basic, 2),
            'absent_count' => $totalAbsentDays,
            'absent_deduction' => $absentDeduction,
            'extra_pay' => $extraPay,
            'loan_deduction' => $loanDeduction,
            'loan_details' => $loanDetails,
            'total_deductions' => $totalDeductions,
            'net' => $net,
        ];
    }

    public function calculate()
    {
        $month = $this->request->getGet('month') ?? date('Y-m');
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));
        $daysInMonth = (int) date('t', strtotime($start));

        $employees = $this->employeeModel->where('status', 'active')->findAll();
        $calculations = [];

        foreach ($employees as $emp) {
            $calc = $this->computeEmployeeSalary($emp, $start, $end, $daysInMonth, false);

            $isGenerated = $this->salaryModel
                ->where('employee_id', $emp['id'])
                ->where('salary_month', $start)
                ->countAllResults() > 0;

            $calculations[] = [
                'employee' => $emp,
                'basic' => $calc['basic'],
                'absent_count' => $calc['absent_count'],
                'absent_deduction' => $calc['absent_deduction'],
                'extra_pay' => $calc['extra_pay'],
                'loan_deduction' => $calc['loan_deduction'],
                'loan_details' => $calc['loan_details'],
                'net' => $calc['net'],
                'is_generated' => $isGenerated,
            ];
        }

        return view('salaries/calculate', [
            'month' => $month,
            'calculations' => $calculations,
        ]);
    }

    public function process()
    {
        $month = $this->request->getPost('month');
        $selectedEmpIds = $this->request->getPost('employee_ids');
        $exemptLoan = $this->request->getPost('exempt_loan') ?? [];

        if (empty($month) || empty($selectedEmpIds)) {
            return redirect()->back()->with('error', 'No employees selected.');
        }

        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));
        $daysInMonth = (int) date('t', strtotime($start));

        $this->db->transStart();

        foreach ($selectedEmpIds as $empId) {
            $emp = $this->employeeModel->find($empId);
            if (!$emp) {
                continue;
            }

            $existing = $this->salaryModel
                ->where('employee_id', $empId)
                ->where('salary_month', $start)
                ->first();
            if ($existing) {
                continue;
            }

            $isLoanExempt = isset($exemptLoan[$empId]) && $exemptLoan[$empId] == 1;
            $calc = $this->computeEmployeeSalary($emp, $start, $end, $daysInMonth, $isLoanExempt);

            // Insert salary first so loan_payments links to the generated salary_id
            $salaryId = $this->salaryModel->insert([
                'employee_id'  => $empId,
                'salary_month' => $start,
                'basic_salary' => $calc['basic'],
                'allowances'   => $calc['extra_pay'],
                'deductions'   => $calc['total_deductions'],
                'net_salary'   => $calc['net'],
                'exempt_loan_deduction' => $isLoanExempt ? 1 : 0,
            ]);

            if (!$isLoanExempt && $salaryId) {
                foreach ($calc['loan_details'] as $item) {
                    $loan = $item['loan'];
                    $deduct = $item['amount'];
                    $newRemaining = max(0.0, round((float) $loan['remaining_amount'] - $deduct, 2));
                    $status = $newRemaining <= 0 ? 'Completed' : 'Approved';

                    $this->loanModel->save([
                        'id' => $loan['id'],
                        'remaining_amount' => $newRemaining,
                        'status' => $status,
                    ]);

                    $this->loanPaymentModel->insert([
                        'loan_id' => $loan['id'],
                        'salary_id' => $salaryId,
                        'payment_date' => date('Y-m-d'),
                        'amount_paid' => $deduct,
                        'remaining_after_payment' => $newRemaining,
                        'payment_month' => $month,
                    ]);
                }
            }
        }

        $this->db->transComplete();

        return redirect()->to('salaries?month=' . $month)->with('success', 'Salaries processed successfully.');
    }

    public function payslip($id)
    {
        $salary = $this->salaryModel
            ->select('salaries.*, employees.first_name, employees.last_name, employees.mobile_number')
            ->join('employees', 'employees.id = salaries.employee_id')
            ->where('salaries.id', $id)
            ->first();

        if (!$salary) {
            return redirect()->back()->with('error', 'Salary record not found.');
        }

        $month = date('Y-m', strtotime($salary['salary_month']));
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));
        $daysInMonth = (int) date('t', strtotime($start));

        $attendanceRecords = $this->attendanceModel
            ->where('employee_id', $salary['employee_id'])
            ->where('attendance_date >=', $start)
            ->where('attendance_date <=', $end)
            ->findAll();

        $presentDays = 0;
        $absentDays = 0.0;
        $halfDays = 0;
        $leaveDays = 0;
        $doublePayDays = 0;

        foreach ($attendanceRecords as $record) {
            if ($record['status'] == 'Present') {
                $presentDays++;
                if (isset($record['multiplier']) && $record['multiplier'] > 1.0) {
                    $doublePayDays++;
                }
            } elseif ($record['status'] == 'Absent') {
                $absentDays += 1.0;
            } elseif ($record['status'] == 'Half Day') {
                $halfDays++;
                $absentDays += 0.5;
            } elseif ($record['status'] == 'Leave') {
                $leaveDays++;
            }
        }

        $perDayRate = $salary['basic_salary'] > 0 ? ((float) $salary['basic_salary'] / $daysInMonth) : 0.0;
        $absentDeduction = round($absentDays * $perDayRate, 2);

        // Fetch actual loan payments linked to this salary (with month fallback for legacy rows)
        $loans = $this->loanPaymentModel
            ->select('loan_payments.loan_id as id, loans.loan_amount, loan_payments.amount_paid as monthly_deduction, loan_payments.remaining_after_payment as remaining_amount')
            ->join('loans', 'loans.id = loan_payments.loan_id')
            ->where('loan_payments.salary_id', $id)
            ->findAll();

        if (empty($loans) && empty($salary['exempt_loan_deduction'])) {
            $loans = $this->loanPaymentModel
                ->select('loan_payments.loan_id as id, loans.loan_amount, loan_payments.amount_paid as monthly_deduction, loan_payments.remaining_after_payment as remaining_amount')
                ->join('loans', 'loans.id = loan_payments.loan_id')
                ->where('loans.employee_id', $salary['employee_id'])
                ->where('loan_payments.payment_month', $month)
                ->findAll();
        }

        if (!empty($loans)) {
            $loanDeduction = round(array_sum(array_map(fn($l) => (float) $l['monthly_deduction'], $loans)), 2);
        } else {
            $loanDeduction = max(0.0, round((float) $salary['deductions'] - $absentDeduction, 2));
        }

        return view('salaries/payslip', [
            'salary' => $salary,
            'daysInMonth' => $daysInMonth,
            'presentDays' => $presentDays,
            'absentDays' => $absentDays,
            'halfDays' => $halfDays,
            'leaveDays' => $leaveDays,
            'doublePayDays' => $doublePayDays,
            'perDayRate' => round($perDayRate, 2),
            'absentDeduction' => $absentDeduction,
            'loanDeduction' => $loanDeduction,
            'extraPay' => $salary['allowances'],
            'loans' => $loans,
        ]);
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
