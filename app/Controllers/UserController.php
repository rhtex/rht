<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;

class UserController extends BaseController
{
    protected $userModel;
    protected $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    public function index()
    {
        $data['users'] = $this->userModel
            ->select('users.*, roles.role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->findAll();
        
        return view('users/index', $data);
    }

    public function create()
    {
        $data['roles'] = $this->roleModel->findAll();
        return view('users/create', $data);
    }

    public function store()
    {
        $rules = [
            'name'     => 'required|min_length[3]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role_id'  => 'required|integer',
            'status'   => 'required|in_list[active,inactive]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->userModel->save([
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role_id'  => $this->request->getPost('role_id'),
            'status'   => $this->request->getPost('status'),
        ]);

        return redirect()->to('users')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $data['user'] = $this->userModel->find($id);
        if (!$data['user']) {
            return redirect()->to('users')->with('error', 'User not found.');
        }
        $data['roles'] = $this->roleModel->findAll();
        return view('users/edit', $data);
    }

    public function update($id)
    {
        $rules = [
            'name'     => 'required|min_length[3]|max_length[100]',
            'email'    => "required|valid_email|is_unique[users.email,id,$id]",
            'role_id'  => 'required|integer',
            'status'   => 'required|in_list[active,inactive]'
        ];

        // Only validate password if it's being changed
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $rules['password'] = 'min_length[8]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id'       => $id,
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'role_id'  => $this->request->getPost('role_id'),
            'status'   => $this->request->getPost('status'),
        ];

        if (!empty($password)) {
            $data['password'] = $password;
        }

        $this->userModel->save($data);

        return redirect()->to('users')->with('success', 'User updated successfully.');
    }

    public function delete($id)
    {
        $this->userModel->delete($id);
        return redirect()->to('users')->with('success', 'User deleted successfully.');
    }

    public function convertFromEmployee($employeeId)
    {
        $employeeModel = new \App\Models\EmployeeModel();
        $employee = $employeeModel->find($employeeId);
        
        if (!$employee) {
            return redirect()->to('employees')->with('error', 'Employee not found.');
        }

        // Check if already a user
        $existingUser = $this->userModel->where('employee_id', $employeeId)->first();
        if ($existingUser) {
            return redirect()->to('employees')->with('error', lang('App.already_a_user'));
        }

        $data['employee'] = $employee;
        $data['roles'] = $this->roleModel->findAll();
        
        return view('users/convert', $data);
    }

    public function storeFromEmployee()
    {
        $rules = [
            'name'        => 'required|min_length[3]|max_length[100]',
            'email'       => 'required|valid_email|is_unique[users.email]',
            'password'    => 'required|min_length[8]',
            'role_id'     => 'required|integer',
            'employee_id' => 'required|integer|is_unique[users.employee_id]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (!$this->userModel->save([
            'name'        => $this->request->getPost('name'),
            'email'       => $this->request->getPost('email'),
            'password'    => $this->request->getPost('password'),
            'role_id'     => $this->request->getPost('role_id'),
            'employee_id' => $this->request->getPost('employee_id'),
            'status'      => 'active',
        ])) {
            return redirect()->back()->withInput()->with('error', 'Failed to save user account.');
        }

        return redirect()->to('users')->with('success', 'Employee converted to user successfully.');
    }
}
