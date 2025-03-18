<?php

namespace App\Controllers;

use App\Models\SalaryHistoryModel;
use App\Models\EmployeeModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class SalaryHistoryController extends Controller
{
    protected $salaryHistoryModel;

    public function __construct()
    {
        $this->salaryHistoryModel = new SalaryHistoryModel();
    }

    /**
     * View all salary histories of employees.
     * Handles permission check before showing the list.
     */ public function index()
    {
        // Permission check
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('SalaryHistory', 'read');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else if (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
        } else {
            // Fetch only active salary history records
            $data['pageTitle'] = 'Salary History List';
            $data['salaryHistories'] = $this->salaryHistoryModel->where('status', 'Active')->findAll(); // Only active records
            $employeeModel = new EmployeeModel();
            $data['employees'] = $employeeModel->where('status', 'active')->orderBy('first_name', 'ASC')->findAll(); // Fetch active employees
            return view('salary_history/list', $data);
        }
    }


    /**
     * Show the form to create a new salary history record.
     */
    public function create()
    {
        // Permission check
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('SalaryHistory', 'create');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else if (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
        } else {
            $employeeModel = new EmployeeModel();
            $employees = $employeeModel->where('status', 'active')->orderBy('first_name', 'ASC')->findAll(); // Fetch all employees
            // Pass the employee data to the view
            $data = [
                'pageTitle' => 'Create Salary History',
                'employees' => $employees // Add employees data to the view
            ];
            return view('salary_history/create', $data);
        }
    }

    /**
     * Store a new salary history record.
     */
    public function store()
    {
        // Permission check
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('SalaryHistory', 'create');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else if (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
        } else {
            // Store the salary history data
            $this->salaryHistoryModel->save([
                'employee_id' => $this->request->getPost('employee_id'),
                'salary_amount' => $this->request->getPost('salary_amount'),
                'start_date' => $this->request->getPost('start_date'),
                'end_date' => $this->request->getPost('end_date'),
                'status' => $this->request->getPost('status') ?? 'Inactive'
            ]);

            return redirect()->to('/salary-history');
        }
    }

    /**
     * Show the form to edit an existing salary history record.
     */
    public function edit($id)
    {
        // Permission check
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('SalaryHistory', 'update');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else if (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
        } else {
            // Fetch the salary history record for editing
            $employeeModel = new EmployeeModel();
            $employees = $employeeModel->where('status', 'active')->orderBy('first_name', 'ASC')->findAll(); // Fetch all employees
            // Pass the employee data to the view
            $data = [
                'pageTitle' => 'Create Salary History',
                'employees' => $employees, // Add employees data to the view
                'salaryHistory' => $this->salaryHistoryModel->find($id)
            ];
            return view('salary_history/edit', $data);
        }
    }

    /**
     * Update an existing salary history record.
     */
    public function update($id)
    {
        // Permission check
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('SalaryHistory', 'update');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else if (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
        } else {
            // Update the salary history record
            $this->salaryHistoryModel->update($id, [
                'employee_id' => $this->request->getPost('employee_id'),
                'salary_amount' => $this->request->getPost('salary_amount'),
                'start_date' => $this->request->getPost('start_date'),
                'end_date' => $this->request->getPost('end_date'),
                'status' => $this->request->getPost('status')
            ]);

            return redirect()->to('/salary-history');
        }
    }

    /**
     * Delete a salary history record.
     */
    public function delete($id)
    {
        // Permission check
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('SalaryHistory', 'delete');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else if (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
        } else {
            // Delete the salary history record
            $this->salaryHistoryModel->delete($id);
            return redirect()->to('/salary-history');
        }
    }

    /**
     * Show a single salary history record based on the salary ID.
     */
    public function show($salary_id)
    {
        // Permission check
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('SalaryHistory', 'read');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else if (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
        } else {
            // Retrieve the salary history record
            $data['salaryHistory'] = $this->salaryHistoryModel->find($salary_id);
            $data['pageTitle'] = 'View Salary History Record';
            // Pass the data to the view
            return view('salary_history/view', $data);
        }
    }
    public function viewemployeesalaryhistory()
    {
        // Permission check
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('SalaryHistory', 'read');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else if (!$check) {
            $data['pageTitle'] = 'Access Denied';
            return view('/access_denied', $data);
        } else {
            // Retrieve the salary history record
            $employeeModel = new EmployeeModel();
            $data['employees'] = $employeeModel->where('status', 'active')->orderBy('first_name', 'ASC')->findAll(); // Fetch active employees
            $data['pageTitle'] = 'View Salary History';
            // Pass the data to the view
            return view('salary_history/viewsalaryhistory', $data);
        }
    }

    public function getSalaryHistory()
    {
        // Get employee ID from POST request
        $employeeId = $this->request->getPost('employee_id');

        // Fetch salary history for the selected employee (active or all records)
        $salaryHistories = $this->salaryHistoryModel->where('employee_id', $employeeId)->findAll();

        // Prepare the response data
        $response = [];
        foreach ($salaryHistories as $salaryHistory) {
            $response[] = [
                'id' => $salaryHistory['salary_id'],
                'employee_name' => $this->getEmployeeName($salaryHistory['employee_id']),
                'salary_amount' => $salaryHistory['salary_amount'],
                'start_date' => $salaryHistory['start_date'],
                'end_date' => $salaryHistory['end_date'],
                'status' => $salaryHistory['status'],
                'actions' => '<a href="/salary-history/show/' . $salaryHistory['salary_id'] . '" class="btn btn-sm btn-primary">View</a>
                                <a href="/salary-history/edit/' . $salaryHistory['salary_id'] . '" class="btn btn-sm btn-secondary">Edit</a>
                                <a href="/salary-history/delete/' . $salaryHistory['salary_id'] . '" class="btn btn-sm btn-danger">Delete</a>',
            ];
        }

        // Return the data as JSON
        return $this->response->setJSON($response);
    }

    private function getEmployeeName($employeeId)
    {
        $employeeModel = new EmployeeModel();
        $employee = $employeeModel->find($employeeId);
        return $employee ? $employee['first_name'] . ' ' . $employee['last_name'] : 'N/A';
    }
}
