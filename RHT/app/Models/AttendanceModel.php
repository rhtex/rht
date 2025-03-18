<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'id';
    protected $allowedFields = ['employee_id', 'attendance_date', 'entry_time', 'exit_time','attendance_status','salary_count'];

    protected $validationRules = [
        'employee_id' => 'required|integer',
        'attendance_date' => 'required|valid_date',
        'entry_time' => 'permit_empty',
        'exit_time' => 'permit_empty',
        'attendance_status' => 'permit_empty',
        'salary_count' => 'permit_empty',
    ];

    protected $validationMessages = [
        'employee_id' => [
            'required' => 'Employee ID is required.',
            'integer' => 'Employee ID must be an integer.'
        ],
        'attendance_date' => [
            'required' => 'Attendance date is required.',
            'valid_date' => 'Please enter a valid date for attendance.'
        ],
        'entry_time' => [
            'validTime' => 'Please enter a valid entry time.',
        ],
        'exit_time' => [
            'validTime' => 'Please enter a valid exit time.',
        ],
        'attendance_status' => [
            'validTime' => 'Please enter a valid Attendance Status.',
        ],
        'salary_count' => [
            'validTime' => 'Please enter a valid Salary Count.',
        ],
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function validateData($data)
    {
        return $this->validate($this->validationRules, $this->validationMessages);
    }

    protected function allowFields($data)
    {
        if (!isset($data['entry_time'])) {
            $data['entry_time'] = null;
        }
        if (!isset($data['exit_time'])) {
            $data['exit_time'] = null;
        }
        return $data;
    }

    public function insert($data = null, bool $returnID = true)
    {
        $data = $this->allowFields($data);
        return parent::insert($data, $returnID);
    }

    public function update($id = null, $data = null): bool
    {
        $data = $this->allowFields($data);
        return parent::update($id, $data);
    }

    // Callback function to validate time fields
    public function validTime(string $str = null, string &$error = null): bool
    {
        if (!empty($str)) {
            // Check if $str is a valid time format (HH:MM)
            if (preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $str)) {
                return true;
            } else {
                return false;
            }
        }
        // If empty, it's valid (permit_empty rule will handle it)
        return true;
    }
    public function searchByDateEmployee($searchDate, $employeeName)
    {
        $builder = $this->db->table($this->table);
        $builder->select('*');
        $builder->where('attendance_date', $searchDate);
        $builder->like('employee_name', $employeeName); // Replace with actual column name for employee name
        return $builder->get()->getResultArray();
    }

    // Method to get attendance by month and year for an employee
    public function getAttendanceByMonthYear($employeeId, $startDate, $endDate)
    {
        // Query the database to fetch attendance data for the given employee and date range
        $builder = $this->builder();
        $builder->select('attendance_date, attendance_status, salary_count, entry_time, exit_time')
                ->where('employee_id', $employeeId)
                ->where('attendance_date >=', $startDate)
                ->where('attendance_date <=', $endDate)
                ->orderBy('attendance_date', 'ASC');

        // Execute the query and return the results
        $query = $builder->get();

        // Initialize an array to hold the formatted data
        $attendanceData = [];

        // Loop through the query results and format them into a simple array
        foreach ($query->getResult() as $row) {
            // Store the status along with entry and exit times
            $attendanceData[$row->attendance_date] = [
                'status' => $row->attendance_status,
                'entry_time' => $row->entry_time,
                'exit_time' => $row->exit_time,
                'salary_count' => $row->salary_count ." day salary",
            ];
        }

        return $attendanceData;
    }
}
