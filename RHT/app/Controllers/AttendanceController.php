<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class AttendanceController extends Controller
{
    /**
     * This function is used to view attendance details of employees.
     * It will check permission first, if no permission, it will show sign-in page.
     * If permission is denied, it will show access denied page.
     * If permission is granted, it will fetch employee data and show the attendance page.
     */
    public function view_attendance()
    {
        // Permission check
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Attendance', 'read');
        // If no permission, show sign-in page
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
            // If permission is denied, show access denied page
        } else if (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
            // If permission is granted, fetch employee data and show the attendance page
        } else {
            // Load the EmployeeModel to fetch employee data
            $employeeModel = new EmployeeModel();
            $employees = $employeeModel->where('status', 'active')->orderBy('first_name', 'ASC')->findAll(); // Fetch all employees
            // Pass the employee data to the view
            $data = [
                'pageTitle' => 'View Attendance Details',
                'employees' => $employees // Add employees data to the view
            ];
            echo view('employees/view_attendance', $data);
        }
    }

    /**
     * Handles permission check and fetching of active employees before showing the add attendance page.
     * If permission is denied, it will show access denied page.
     * If permission is granted, it will fetch employee data and show the add attendance page.
     */
    public function add_attendance()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Attendance', 'create');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                $employeeModel = new EmployeeModel();
                $employees = $employeeModel->where('status', 'active')->findAll();
                $session = session();
                $sessRole = $session->get('role_id');
                $checkAdmin = ($sessRole == 1) ? true : false;
                $data = [
                    'pageTitle' => 'Add Attendance Details',
                    'employees' => $employees,
                    'admin' => $checkAdmin
                ];
                echo view('employees/add_attendance', $data);
            }
        }
    }
    /**
     * Stores attendance records into the database, handling existing records and validation.
     * Checks for permission to read attendance records and handles access denied and sign in redirection.
     * Handles form submission and processes each attendance record for the given date.
     * Updates existing records if found, otherwise inserts new ones.
     * Sets success or error flash messages and redirects to view attendance page.
     * @return void
     */
    public function store_attendance()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Attendance', 'read');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                $session = session();
                try {
                    $attendanceModel = new AttendanceModel();
                    // Process form submission
                    $attendanceData = $this->request->getPost('attendance');
                    $attendanceDate = $this->request->getPost('attendance_date'); // Ensure attendance_date is fetched
                    foreach ($attendanceData as $data) {
                        // Assign attendance_date to each data entry
                        $data['attendance_date'] = $attendanceDate;
                        // Check if attendance record already exists for the employee on the given date
                        $existingAttendance = $attendanceModel
                            ->where('employee_id', $data['employee_id'])
                            ->where('attendance_date', $data['attendance_date'])
                            ->first();
                        if ($existingAttendance) {
                            // If record exists, update it
                            $checkupdate = $permissionController->checkPermission('Attendance', 'update');
                            if (!$checkupdate) {
                                $session->setFlashdata('error', 'Attendance Already Captured.');
                            } else {
                                $attendanceModel->update($existingAttendance['id'], $data);
                                $session->setFlashdata('success', 'Attendance records updated successfully.');
                            }
                        } else {
                            // Otherwise, insert new record
                            $attendanceModel->insert($data);
                            // Set success flash message
                            $session->setFlashdata('success', 'Attendance records saved successfully.');
                        }
                    }
                } catch (\Exception $e) {
                    // Set error flash message
                    $session->setFlashdata('error', 'Failed to save attendance records.' . $e);
                }
                // Redirect to view attendance page after processing
                return redirect()->to('/view_attendance');
            }
        }
    }

    /**
     * Handles AJAX requests to fetch attendance data for a specific employee
     * within a specified month and year.
     *
     * Retrieves the month and employee ID from the request, calculates the
     * date range for the entire month, and queries the attendance model
     * to fetch all attendance records for that employee within the date range.
     * Returns the data in JSON format.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON response containing attendance data
     */
    public function search_by_month_year_ajax()
    {
        // Get the incoming data from the AJAX request
        $searchMonth = $this->request->getVar('searchMonth');
        $searchEmployee2 = $this->request->getVar('searchEmployee2');
        // Prepare the date range for the selected month and year
        $startDate = $searchMonth . '-01'; // First day of the month
        $endDate = date('Y-m-t', strtotime($startDate)); // Last day of the month
        // Initialize the model
        $attendanceModel = new AttendanceModel();
        // Fetch the attendance data for the selected employee and date range
        $attendanceData = $attendanceModel->getAttendanceByMonthYear($searchEmployee2, $startDate, $endDate);
        // Return the data in the expected format
        return $this->response->setJSON([
            'attendanceData' => $attendanceData
        ]);
    }

    /**
     * Retrieves attendance data for a specific date and returns it as a JSON response.
     *
     * This method accepts a 'date' query parameter via GET request and uses the AttendanceModel
     * to fetch all attendance records for that date. The retrieved records are structured in an
     * associative array with employee IDs as keys and their corresponding entry time, exit time,
     * status, and salary as values. The data is then returned as a JSON response.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON response containing attendance data
     */
    public function get_attendance_data()
    {
        $date = $this->request->getGet('date');
        $attendanceModel = new AttendanceModel();
        $attendanceData = [];

        if ($date) {
            // Retrieve attendance records for the selected date
            $attendanceRecords = $attendanceModel->where('attendance_date', $date)->findAll();

            foreach ($attendanceRecords as $record) {
                $attendanceData[$record['employee_id']] = [
                    'entry_time' => $record['entry_time'],
                    'exit_time' => $record['exit_time'],
                    'status' => $record['attendance_status'],
                    'salary' => $record['salary_count']
                ];
            }
        }

        return $this->response->setJSON(['attendanceData' => $attendanceData]);
    }
}
