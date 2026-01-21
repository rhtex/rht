<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RoleModel;

class RoleController extends BaseController
{
    protected $roleModel;
    protected $db;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data['roles'] = $this->roleModel->findAll();
        return view('roles/index', $data);
    }

    public function create()
    {
        return view('roles/create');
    }

    public function store()
    {
        $data = $this->request->getPost();

        if (!$this->roleModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->roleModel->errors());
        }

        return redirect()->to('roles')->with('success', 'Role created successfully.');
    }

    public function edit($id)
    {
        $data['role'] = $this->roleModel->find($id);
        if (!$data['role']) {
            return redirect()->to('roles')->with('error', 'Role not found.');
        }
        return view('roles/edit', $data);
    }

    public function update($id)
    {
        $role = $this->roleModel->find($id);
        if (!$role) {
            return redirect()->to('roles')->with('error', 'Role not found.');
        }

        $data = [
            'role_name'   => $this->request->getPost('role_name'),
            'description' => $this->request->getPost('description'),
        ];

        // Perform update
        if (!$this->roleModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->roleModel->errors());
        }

        return redirect()->to('roles')->with('success', 'Role updated successfully.');
    }

    public function delete($id)
    {
        // Check if role is assigned to any user
        $userModel = new \App\Models\UserModel();
        if ($userModel->where('role_id', $id)->countAllResults() > 0) {
            return redirect()->to('roles')->with('error', 'Cannot delete role because it is assigned to users.');
        }

        $this->roleModel->delete($id);
        return redirect()->to('roles')->with('success', 'Role deleted successfully.');
    }

    public function permissions($id)
    {
        $data['role'] = $this->roleModel->find($id);
        if (!$data['role']) {
            return redirect()->to('roles')->with('error', 'Role not found.');
        }

        // Get all modules with permissions
        $builder = $this->db->table('modules');
        $modules = $builder->get()->getResultArray();

        $permBuilder = $this->db->table('permissions');
        foreach ($modules as &$module) {
            $module['permissions'] = $permBuilder->where('module_id', $module['id'])->get()->getResultArray();
        }
        $data['modules'] = $modules;

        // Get assigned permissions
        $data['assigned_permissions'] = $this->db->table('role_permissions')
            ->where('role_id', $id)
            ->get()
            ->getResultArray();
        
        // Flatten for easier checking
        $data['assigned_ids'] = array_column($data['assigned_permissions'], 'permission_id');

        return view('roles/permissions', $data);
    }

    public function updatePermissions($id)
    {
        $permissions = $this->request->getPost('permissions'); // Array of permission IDs

        $this->db->transStart();
        // Remove old
        $this->db->table('role_permissions')->where('role_id', $id)->delete();
        
        // Add new
        if (!empty($permissions)) {
            $data = [];
            foreach ($permissions as $permId) {
                $data[] = [
                    'role_id'       => $id,
                    'permission_id' => $permId
                ];
            }
            $this->db->table('role_permissions')->insertBatch($data);
        }
        $this->db->transComplete();

        return redirect()->to('roles')->with('success', 'Permissions updated successfully.');
    }
}
