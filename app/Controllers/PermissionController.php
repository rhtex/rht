<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PermissionModel;
use App\Models\ModuleModel;

class PermissionController extends BaseController
{
    protected $permissionModel;
    protected $moduleModel;

    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
        $this->moduleModel = new ModuleModel();
    }

    public function index()
    {
        $data['permissions'] = $this->permissionModel->getPermissionsWithModule();
        return view('permissions/index', $data);
    }

    public function create()
    {
        $data['modules'] = $this->moduleModel->findAll();
        return view('permissions/create', $data);
    }

    public function store()
    {
        if (!$this->permissionModel->insert($this->request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $this->permissionModel->errors());
        }

        return redirect()->to('permissions')->with('success', 'Permission created successfully.');
    }

    public function edit($id)
    {
        $data['permission'] = $this->permissionModel->find($id);
        if (!$data['permission']) {
            return redirect()->to('permissions')->with('error', 'Permission not found.');
        }
        $data['modules'] = $this->moduleModel->findAll();
        return view('permissions/edit', $data);
    }

    public function update($id)
    {
        if (!$this->permissionModel->update($id, $this->request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $this->permissionModel->errors());
        }

        return redirect()->to('permissions')->with('success', 'Permission updated successfully.');
    }

    public function delete($id)
    {
        // Check if assigned to roles
        $db = \Config\Database::connect();
        if ($db->table('role_permissions')->where('permission_id', $id)->countAllResults() > 0) {
            return redirect()->to('permissions')->with('error', 'Cannot delete permission because it is assigned to roles.');
        }

        $this->permissionModel->delete($id);
        return redirect()->to('permissions')->with('success', 'Permission deleted successfully.');
    }
}
