<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\EmployeeModel;
use App\Models\UserModel;
use App\Controllers\PermissionsController;
use App\Models\AttendanceModel;
use App\Models\EmployeePaymentsModel;
use App\Models\SalaryHistoryModel;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware = ['permission:Employee'];
    }
    public function view_employee($id)
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Employee', 'read');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                $employeeModel = new \App\Models\EmployeeModel();
                $userModel = new \App\Models\UserModel();
                // Fetch the employee data
                $data['employee'] = $employeeModel->find($id);
                if (!$data['employee']) {
                    // Handle case where employee is not found
                    $data['pageTitle'] = 'Employee Not Found';
                    return view('/employee_not_found', $data);
                }
                // Check if a user exists for this employee
                $data['userExists'] = $userModel->where('employee_id', $id)->first() !== null;
                $data['pageTitle'] = 'View Employee';
                return view('employees/view_employee', $data);
            }
        }
    }
    public function add_employee()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Employee', 'create');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                $data['pageTitle'] = 'Add Employee';
                echo view('employees/add_employee', $data);
            }
        }
    }
    public function list_employees()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Employee', 'read');
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
                $data['employees'] = $employeeModel->findAll();
                $data['pageTitle'] = 'List Employees';
                echo view('employees/list_employee', $data);
            }
        }
    }
    public function view_salary()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Employee', 'read');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                $data = [
                    'pageTitle' => 'View Salary Details'
                ];
                echo view('employees/view_salary', $data);
            }
        }
    }
    public function calculate_salary()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Employee', 'create');
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
                $data['employees'] = $employeeModel->where('status', 'active')->orderBy('first_name', 'ASC')->findAll();
                $data['pageTitle'] = 'Calculate Salary';
                echo view('employees/calculate_salary', $data);
            }
        }
    }
    public function generate()
    {
        // Get POST data (employee_id and month)
        $employeeId = $this->request->getPost('employee_id');
        $selectedMonth = $this->request->getPost('month'); // Format: YYYY-MM

        if (!$employeeId || !$selectedMonth) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid data']);
        }

        // Retrieve employee data based on ID
        $employeeModel = new EmployeeModel();
        $employee = $employeeModel->find($employeeId);

        if (!$employee) {
            return $this->response->setJSON(['success' => false, 'message' => 'Employee not found']);
        }

        // Calculate the salary for the month (example calculation logic)
        return $this->calculateSalary($employeeId, $selectedMonth);
    }

    // Example salary calculation function (you can implement your own logic)
    private function calculateSalary($employee, $month)
    {
        // Example calculation logic
        // You can fetch from other tables like salary details, attendance, etc.
        $salaryHistoryModel = new SalaryHistoryModel();

        $monthSalaryRow = $salaryHistoryModel->where('employee_id', $employee)->where('status', 'Active')->first();
        $monthSalary = $monthSalaryRow['salary_amount'];

        if (!$monthSalary) {
            return $this->response->setJSON(['success' => false, 'message' => 'Salary history not found']);
        } else {
            // Assuming you have a model instance for the attendance table
            $attendanceModel = new AttendanceModel(); // Adjust this based on your actual model name

            // Fetch the sum of salary_count for the specific employee in the given month
            $monthStart = $month . '-01'; // Start of the month
            $monthEnd = $month . '-31';   // End of the month
            $numberOfDays = $this->getNumberOfDaysInMonth($month);
            // Query to sum salary_count
            $totalSalaryCount = $attendanceModel
                ->where('employee_id', $employee) // Filter by employee_id
                ->where('attendance_date >=', $monthStart) // Filter by start date of the month
                ->where('attendance_date <=', $monthEnd)   // Filter by end date of the month
                ->where('salary_count >', 0)               // Only consider records where salary_count is greater than 0
                ->findAll();                                 // Get the first (and only) result, since we're only summing
            $salaryCounts = array_column($totalSalaryCount, 'salary_count');
            $presentDays = 0;
            $holidayDays = 0;
            $leaveDays = 0; // Leave days are treated as absent with a specific status
            $halfDays = 0;

            // Loop through each attendance record and calculate counts
            foreach ($totalSalaryCount as $attendance) {
                switch ($attendance['attendance_status']) {
                    case 4: // Half Day
                        $halfDays++;
                        break;
                    case 1: // Present
                        $presentDays++;
                        break;
                    case 2: // Leave day
                        $leaveDays++;
                        break;
                    case 3: // Holiday
                        $holidayDays++;
                        break;
                    default:
                        // Handle any unexpected status (optional)
                        break;
                }
            }

            // Optionally, sum the salary_count values
            $totalSalaryCountSum = array_sum($salaryCounts);
            // If $totalSalaryCount is null (no records found), set it to 0
            $perdaySalary = round($monthSalary / $numberOfDays, 2);
            $salaryAmount = $perdaySalary * $totalSalaryCountSum;
            $employeeModel = new EmployeeModel();
            $employeeDetails = $employeeModel->find($employee);
            $employeepayments = new EmployeePaymentsModel();
            $employeepayments->insert([
                'employee_id' => $employee,
                'salary_amount' => $salaryAmount,
                'month' => $month,
                'payslip_date' => date('Y-m-d'),
                'year' => date('Y'),
                'monthly_salary' => $monthSalary,
                'present_days' => $totalSalaryCountSum,
                'per_day_salary' => $perdaySalary,
                'salary_count_days' => $numberOfDays
            ]);
            $responseSalary = [
                'success' => true,
                'message' => 'Salary calculated successfully',
                'salary_amount' => $salaryAmount,
                'total_days' => $totalSalaryCountSum,
                'perday_salary' => $perdaySalary,
                'month' => $month,
                'employee_id' => $employee,
                'employee_name' => $employeeDetails['first_name'] . ' ' . $employeeDetails['last_name'],
                'employee_salary' => $monthSalary,
                'total_present_days' => $presentDays + $halfDays*0.5 + $holidayDays,
                'present_days' => $presentDays,
                'holiday_days' => $holidayDays,
                'leave_days' => $leaveDays,
                'half_days' => $halfDays,
            ];
            return $this->response->setJSON($responseSalary);
        }
    }
    function getNumberOfDaysInMonth($month)
    {
        // Create a DateTime object for the first day of the selected month
        $date = new \DateTime($month . '-01');

        // Modify the object to go to the last day of the selected month
        $date->modify('last day of this month');

        // Get the day of the month (i.e., the number of days in the month)
        return $date->format('j'); // 'j' gives the day of the month (1-31)
    }
    public function edit_employee($id)
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Employee', 'update');
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
                $data['employee'] = $employeeModel->find($id);
                $data['pageTitle'] = 'Edit Employee';
                return view('employees/edit_employee', $data);
            }
        }
    }
    public function create_employee()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Employee', 'create');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                if ($this->request->getMethod() === 'POST') {
                    // Validate and store uploaded files
                    $addressProofFile = $this->request->getFile('address_proof_upload');
                    $employeePhotoFile = $this->request->getFile('employee_photo_upload');
                    // Validate files
                    if ($addressProofFile->isValid() && $addressProofFile->getSize() > 0) {
                        $addressProofFileName = $addressProofFile->getRandomName();
                        $addressProofFile->move(ROOTPATH . 'public/uploads/employeeaddressproof', $addressProofFileName);
                        $addressProofPath = '/public/uploads/employeeaddressproof/' . $addressProofFileName;
                    } else {
                        // Handle validation errors or default value if required
                        $addressProofPath = '/public/uploads/default_address_proof.jpg'; // Example default path
                    }
                    if ($employeePhotoFile->isValid() && $employeePhotoFile->getSize() > 0) {
                        $employeePhotoFileName = $employeePhotoFile->getRandomName();
                        $employeePhotoFile->move(ROOTPATH . 'public/uploads/employeePhoto', $employeePhotoFileName);
                        $employeePhotoPath = '/public/uploads/employeePhoto/' . $employeePhotoFileName;
                    } else {
                        // Handle validation errors or default value if required
                        $employeePhotoPath = '/public/uploads/default_employee_photo.jpg'; // Example default path
                    }
                    // Prepare data for database insertion
                    $data = [
                        'first_name' => $this->request->getPost('first_name'),
                        'last_name' => $this->request->getPost('last_name'),
                        'guardian_name' => $this->request->getPost('guardian_name'),
                        'guardian_type' => $this->request->getPost('guardian_type'),
                        'guardian_mobile' => $this->request->getPost('guardian_mobile'),
                        'employee_mobile' => $this->request->getPost('employee_mobile'),
                        'email' => $this->request->getPost('email'),
                        'address' => $this->request->getPost('address'),
                        'address_proof_no' => $this->request->getPost('address_proof_no'),
                        'address_proof_type' => $this->request->getPost('address_proof_type'),
                        'address_proof_upload' => $addressProofPath,
                        'employee_photo_upload' => $employeePhotoPath,
                        'designation' => $this->request->getPost('designation')
                    ];
                    // Insert data into database
                    $employeeModel = new EmployeeModel();
                    $employeeModel->insert($data);
                    return redirect()->to('/list_employees');
                } else {
                    $data['pageTitle'] = 'Add Employee';
                    echo view('employees/add_employee', $data);
                }
            }
        }
    }
    public function update_employee()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Employee', 'update');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                if ($this->request->getMethod() === 'POST') {
                    $employeeModel = new EmployeeModel();
                    $id = $this->request->getPost('id');
                    // Validate and store uploaded files
                    $addressProofFile = $this->request->getFile('address_proof_upload');
                    $employeePhotoFile = $this->request->getFile('employee_photo_upload');
                    // Validate files
                    if ($addressProofFile->isValid() && $addressProofFile->getSize() > 0) {
                        $addressProofFileName = $addressProofFile->getRandomName();
                        $addressProofFile->move(ROOTPATH . 'public/uploads/employeeaddressproof', $addressProofFileName);
                        $addressProofPath = '/public/uploads/employeeaddressproof/' . $addressProofFileName;
                    } else {
                        // Keep existing path if no new file uploaded
                        $addressProofPath = $this->request->getPost('existing_address_proof');
                    }
                    if ($employeePhotoFile->isValid() && $employeePhotoFile->getSize() > 0) {
                        $employeePhotoFileName = $employeePhotoFile->getRandomName();
                        $employeePhotoFile->move(ROOTPATH . 'public/uploads/employeePhoto', $employeePhotoFileName);
                        $employeePhotoPath = '/public/uploads/employeePhoto/' . $employeePhotoFileName;
                    } else {
                        // Keep existing path if no new file uploaded
                        $employeePhotoPath = $this->request->getPost('existing_employee_photo');
                    }
                    // Prepare data for database update
                    $data = [
                        'first_name' => $this->request->getPost('first_name'),
                        'last_name' => $this->request->getPost('last_name'),
                        'guardian_name' => $this->request->getPost('guardian_name'),
                        'guardian_type' => $this->request->getPost('guardian_type'),
                        'guardian_mobile' => $this->request->getPost('guardian_mobile'),
                        'employee_mobile' => $this->request->getPost('employee_mobile'),
                        'email' => $this->request->getPost('email'),
                        'address' => $this->request->getPost('address'),
                        'address_proof_no' => $this->request->getPost('address_proof_no'),
                        'address_proof_type' => $this->request->getPost('address_proof_type'),
                        'address_proof_upload' => $addressProofPath,
                        'employee_photo_upload' => $employeePhotoPath,
                        'designation' => $this->request->getPost('designation'),
                        'inactive_date' => $this->request->getPost('inactive_date'),
                        'status' => $this->request->getPost('status')
                    ];
                    // Update data in database
                    $employeeModel->update($id, $data);
                    // Redirect after successful update
                    return redirect()->to('/list_employees');
                }
            }
        }
    }
    public function createUserFromEmployee($employeeId)
    {
        // Check permission to create a user
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('User', 'create');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            return view('/signin', $data);
        } elseif (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
        } else {
            // Fetch employee details
            $employeeModel = new EmployeeModel();
            $employee = $employeeModel->find($employeeId);
            if (!$employee) {
                // Handle case where employee is not found
                $data['pageTitle'] = 'Employee Not Found';
                return view('/employee_not_found', $data);
            }
            // Prepare user data
            $userData = [
                'name'        => $employee['first_name'] . ' ' . $employee['last_name'],
                'email'       => $employee['email'],
                'password'    => password_hash('rhtex@123', PASSWORD_DEFAULT),  // Default password
                'employee_id' => $employeeId,
                'role_id'     => 2,  // Default role ID; adjust based on requirements
                'created_at'  => date('Y-m-d H:i:s')
            ];
            // Insert new user
            $userModel = new UserModel();
            $userModel->insert($userData);
            // Redirect with success message
            return redirect()->to('/list_employees')->with('message', 'User created successfully from employee.');
        }
    }
}
