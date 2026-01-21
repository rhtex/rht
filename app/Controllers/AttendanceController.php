<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;

class AttendanceController extends BaseController
{
    protected $attendanceModel;
    protected $employeeModel;
    protected $db;

    public function __construct()
    {
        $this->attendanceModel = new AttendanceModel();
        $this->employeeModel = new EmployeeModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // Daily Entry Screen
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        
        // Fetch all active employees
        $employees = $this->employeeModel->where('status', 'active')->findAll();
        
        // Fetch existing attendance for this date
        $attendanceRecords = $this->attendanceModel
            ->where('attendance_date', $date)
            ->findAll();
        
        // Map attendance by employee_id for easy lookup
        $attendanceMap = [];
        foreach ($attendanceRecords as $record) {
            $attendanceMap[$record['employee_id']] = $record;
        }

        $data = [
            'date' => $date,
            'employees' => $employees,
            'attendanceMap' => $attendanceMap
        ];

        return view('attendance/index', $data);
    }

    public function store()
    {
        $date = $this->request->getPost('attendance_date');
        $attendanceData = $this->request->getPost('attendance'); // Array: [emp_id => ['status' => '...', 'check_in' => '...']]

        if (empty($date) || empty($attendanceData)) {
            return redirect()->back()->with('error', 'Invalid data provided.');
        }

        $this->db->transStart();

        foreach ($attendanceData as $empId => $data) {
            $emp = $this->employeeModel->find($empId);
            if (!$emp) continue;

            $hoursWorked = 0;
            $checkIn = !empty($data['check_in_time']) ? $data['check_in_time'] : null;
            $checkOut = !empty($data['check_out_time']) ? $data['check_out_time'] : null;

            if ($checkIn && $checkOut) {
                $time1 = \DateTime::createFromFormat('H:i', $checkIn);
                $time2 = \DateTime::createFromFormat('H:i', $checkOut);
                if ($time1 && $time2) {
                    $interval = $time1->diff($time2);
                    $hoursWorked = $interval->h + ($interval->i / 60);
                }
            }

            $targetHours = $emp['daily_working_hours'];
            $shortfall = max(0, $targetHours - $hoursWorked);
            $surplus = max(0, $hoursWorked - $targetHours);

            $sevenDaysAgo = date('Y-m-d', strtotime($date . ' -7 days'));

            // 1. Forward Recovery: If current surplus exists, fix past shortfalls
            if ($surplus > 0) {
                $pastShortfalls = $this->attendanceModel
                    ->where('employee_id', $empId)
                    ->where('attendance_date >=', $sevenDaysAgo)
                    ->where('attendance_date <', $date)
                    ->where('shortfall_hours >', 0)
                    ->where('is_recovered', 0)
                    ->orderBy('attendance_date', 'ASC')
                    ->findAll();

                foreach ($pastShortfalls as $past) {
                    if ($surplus <= 0) break;
                    $recoverable = min($surplus, $past['shortfall_hours']);
                    $newShortfall = $past['shortfall_hours'] - $recoverable;
                    $surplus -= $recoverable;

                    $this->attendanceModel->update($past['id'], [
                        'shortfall_hours' => $newShortfall,
                        'is_recovered'    => ($newShortfall <= 0.01) ? 1 : 0
                    ]);
                }
            }

            // 2. Backward Recovery: If current shortfall exists, check if past surpluses can fix it
            if ($shortfall > 0 && ($data['status'] == 'Present' || $data['status'] == 'Half Day')) {
                $pastSurpluses = $this->attendanceModel
                    ->where('employee_id', $empId)
                    ->where('attendance_date >=', $sevenDaysAgo)
                    ->where('attendance_date <', $date)
                    ->where('surplus_hours >', 0)
                    ->orderBy('attendance_date', 'ASC')
                    ->findAll();

                foreach ($pastSurpluses as $past) {
                    if ($shortfall <= 0) break;
                    $reclaimable = min($shortfall, $past['surplus_hours']);
                    $newSurplus = $past['surplus_hours'] - $reclaimable;
                    $shortfall -= $reclaimable;

                    $this->attendanceModel->update($past['id'], [
                        'surplus_hours' => $newSurplus
                    ]);
                }
            }

            // Check if record exists
            $existing = $this->attendanceModel->where('employee_id', $empId)
                                               ->where('attendance_date', $date)
                                               ->first();
            
            $saveData = [
                'employee_id'     => $empId,
                'attendance_date' => $date,
                'status'          => $data['status'],
                'check_in_time'   => $checkIn,
                'check_out_time'  => $checkOut,
                'hours_worked'    => $hoursWorked,
                'shortfall_hours' => ($data['status'] == 'Present' || $data['status'] == 'Half Day') ? $shortfall : 0,
                'surplus_hours'   => $surplus,
                'is_recovered'    => ($shortfall <= 0.01) ? 1 : 0,
                'multiplier'      => isset($data['double_pay']) && $data['double_pay'] == '1' ? 2.0 : 1.0,
            ];

            if ($existing) {
                $saveData['id'] = $existing['id'];
                $this->attendanceModel->save($saveData);
            } else {
                $this->attendanceModel->insert($saveData);
            }
        }

        $this->db->transComplete();

        return redirect()->to('attendance?date='.$date)->with('success', 'Attendance saved successfully.');
    }

    public function report()
    {
        $month = $this->request->getGet('month') ?? date('Y-m');
        
        $employees = $this->employeeModel->where('status', 'active')->findAll();

        // Get attendance for the whole month
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));

        $records = $this->attendanceModel
            ->where('attendance_date >=', $start)
            ->where('attendance_date <=', $end)
            ->findAll();
        
        // Build Report Data: [emp_id => [date => status]]
        $reportData = [];
        foreach ($records as $record) {
            $reportData[$record['employee_id']][$record['attendance_date']] = $record['status'];
        }

        $data = [
            'month' => $month,
            'daysInMonth' => date('t', strtotime($start)),
            'employees' => $employees,
            'reportData' => $reportData
        ];

        return view('attendance/report', $data);
    }

    public function shortfall()
    {
        $shortfalls = $this->attendanceModel
            ->select('attendance.*, employees.first_name, employees.last_name, employees.daily_working_hours')
            ->join('employees', 'employees.id = attendance.employee_id')
            ->where('shortfall_hours >', 0)
            ->where('is_recovered', 0)
            ->orderBy('attendance_date', 'DESC')
            ->findAll();

        return view('attendance/shortfall', ['shortfalls' => $shortfalls]);
    }
}
