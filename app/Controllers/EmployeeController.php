<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Models\SalaryIncrementModel;
use App\Models\CountryModel;
use App\Models\StateModel;
use App\Services\ImageUploadService;

class EmployeeController extends BaseController
{
    protected $employeeModel;
    protected $uploadService;
    protected $incrementModel;

    public function __construct()
    {
        $this->employeeModel = new EmployeeModel();
        $this->uploadService = new ImageUploadService();
        $this->incrementModel = new SalaryIncrementModel();
    }

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
            'employment_type' => $this->request->getGet('employment_type'),
        ];

        $data['employees'] = $this->employeeModel->getEmployeesWithFilters($filters);
        $data['title'] = 'Employees';
        $data['filters'] = $filters;

        return view('employees/index', $data);
    }

    public function create()
    {
        $countryModel = new CountryModel();
        $stateModel = new StateModel();

        $countries = $countryModel->findAll();

        // Sort India to the top
        usort($countries, function ($a, $b) {
            if ($a['name'] == 'India')
                return -1;
            if ($b['name'] == 'India')
                return 1;
            return strcmp($a['name'], $b['name']);
        });

        $data['countries'] = $countries;

        // Default India ID
        $india = array_values(array_filter($countries, function ($c) {
            return $c['name'] == 'India'; }))[0] ?? null;
        $data['default_country_id'] = $india ? $india['id'] : null;

        $data['states'] = $india ? $stateModel->where('country_id', $india['id'])->where('status', 'active')->orderBy('name', 'ASC')->findAll() : [];

        return view('employees/create', $data);
    }

    public function store()
    {
        $rules = $this->employeeModel->getValidationRules();

        // Add specific file validation rules
        $rules['photo'] = 'uploaded[photo]|max_size[photo,2048]|is_image[photo]|mime_in[photo,image/jpg,image/jpeg,image/png,image/webp]';
        // Address proofs can be optional or required depending on logic. Let's make them optional for now or basics.

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();

        // Handle File Uploads
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $data['photo'] = $this->uploadService->uploadImage($photo, 'uploads/employees/photos');
        }

        $proofFront = $this->request->getFile('address_proof_front_image');
        if ($proofFront && $proofFront->isValid() && !$proofFront->hasMoved()) {
            $data['address_proof_front_image'] = $this->uploadService->uploadImage($proofFront, 'uploads/employees/proofs');
        }

        $proofBack = $this->request->getFile('address_proof_back_image');
        if ($proofBack && $proofBack->isValid() && !$proofBack->hasMoved()) {
            $data['address_proof_back_image'] = $this->uploadService->uploadImage($proofBack, 'uploads/employees/proofs');
        }

        $this->employeeModel->save($data);

        return redirect()->to('employees')->with('success', 'Employee added successfully.');
    }

    public function edit($id)
    {
        $data['employee'] = $this->employeeModel->find($id);
        if (!$data['employee']) {
            return redirect()->to('employees')->with('error', 'Employee not found.');
        }

        // Get salary increment history
        $data['increments'] = $this->incrementModel
            ->where('employee_id', $id)
            ->orderBy('effective_date', 'DESC')
            ->findAll();

        $countryModel = new CountryModel();
        $stateModel = new StateModel();
        $countries = $countryModel->findAll();
        usort($countries, function ($a, $b) {
            if ($a['name'] == 'India')
                return -1;
            if ($b['name'] == 'India')
                return 1;
            return strcmp($a['name'], $b['name']);
        });
        $data['countries'] = $countries;

        $selectedCountryId = $data['employee']['country_id'];
        $data['states'] = $selectedCountryId ? $stateModel->where('country_id', $selectedCountryId)->where('status', 'active')->orderBy('name', 'ASC')->findAll() : [];

        return view('employees/edit', $data);
    }

    public function update($id)
    {
        // Validation without mandatory file uploads
        $rules = $this->employeeModel->getValidationRules();
        $rules['email'] = "permit_empty|valid_email|is_unique[employees.email,id,$id]"; // Override unique check
        // Remove uploaded check for update

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get old employee data for salary comparison
        $oldEmployee = $this->employeeModel->find($id);

        $data = $this->request->getPost();
        $data['id'] = $id;

        // Track salary increment if basic_salary changed
        $newSalary = $data['basic_salary'] ?? null;
        if ($oldEmployee && $newSalary && $oldEmployee['basic_salary'] != $newSalary) {
            $this->incrementModel->insert([
                'employee_id' => $id,
                'old_salary' => $oldEmployee['basic_salary'],
                'new_salary' => $newSalary,
                'effective_date' => date('Y-m-d'),
                'remarks' => 'Salary updated via employee edit'
            ]);
        }

        // Handle File Uploads (Only if new file is uploaded)
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $data['photo'] = $this->uploadService->uploadImage($photo, 'uploads/employees/photos');
        }

        $proofFront = $this->request->getFile('address_proof_front_image');
        if ($proofFront && $proofFront->isValid() && !$proofFront->hasMoved()) {
            $data['address_proof_front_image'] = $this->uploadService->uploadImage($proofFront, 'uploads/employees/proofs');
        }

        $proofBack = $this->request->getFile('address_proof_back_image');
        if ($proofBack && $proofBack->isValid() && !$proofBack->hasMoved()) {
            $data['address_proof_back_image'] = $this->uploadService->uploadImage($proofBack, 'uploads/employees/proofs');
        }

        $this->employeeModel->save($data);

        return redirect()->to('employees')->with('success', 'Employee updated successfully.');
    }

    public function delete($id)
    {
        $this->employeeModel->delete($id);
        return redirect()->to('employees')->with('success', 'Employee deleted successfully (Soft Delete).');
    }

    public function view($id)
    {
        $data['employee'] = $this->employeeModel->select('employees.*, states.name as state_name, countries.name as country_name')
            ->join('states', 'states.id = employees.state_id', 'left')
            ->join('countries', 'countries.id = employees.country_id', 'left')
            ->find($id);

        if (!$data['employee']) {
            return redirect()->to('employees')->with('error', 'Employee not found.');
        }

        // Get salary increment history
        $data['increments'] = $this->incrementModel
            ->where('employee_id', $id)
            ->orderBy('effective_date', 'DESC')
            ->findAll();

        // Get salary records (payslips)
        $salaryModel = new \App\Models\SalaryModel();
        $data['salaries'] = $salaryModel
            ->where('employee_id', $id)
            ->orderBy('salary_month', 'DESC')
            ->findAll();

        // Get loan records
        $loanModel = new \App\Models\LoanModel();
        $data['loans'] = $loanModel
            ->where('employee_id', $id)
            ->orderBy('loan_date', 'DESC')
            ->findAll();

        $data['title'] = $data['employee']['first_name'] . ' ' . $data['employee']['last_name'];

        return view('employees/view', $data);
    }
}
