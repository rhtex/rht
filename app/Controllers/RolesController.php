<?php

namespace App\Controllers;

use App\Models\RoleModel;
use CodeIgniter\Controller;

class RolesController extends Controller
{
    public function index()
    {
        $roleModel = new RoleModel();
        $roles = $roleModel->findAll();

        return view('roles/index', ['roles' => $roles, 'pageTitle' => 'List of Roles']);
    }

    public function create()
    {
        $data = [
            'pageTitle' => 'Create Roles'
        ];
        return view('roles/create', $data);
    }

    public function store()
    {
        $roleModel = new RoleModel();
        $moduleModel = new ModuleModel(); // Load ModuleModel
        $modulePermissionModel = new ModulePermissionModel(); // Load ModulePermissionModel
    
        // Prepare data for the new role
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description')
        ];
    
        // Insert the new role
        $roleId = $roleModel->insert($data); // Store the returned ID of the inserted role
    
        // Get all modules
        $modules = $moduleModel->findAll();
    
        // Prepare permissions data for all modules
        $permissionsData = [];
        foreach ($modules as $module) {
            $permissionsData[] = [
                'role_id' => $roleId, // Link permission to the new role
                'module_id' => $module['id'], // Link permission to each existing module
                'create_permission' => 0, // Set default permission values (modify as needed)
                'read_permission' => 0,
                'update_permission' => 0,
                'delete_permission' => 0,
            ];
        }
    
        // Insert module permissions for the new role
        if (!empty($permissionsData)) {
            $modulePermissionModel->insertBatch($permissionsData);
        }
    
        // Redirect after successful insertion
        return redirect()->to('/roles')->with('success', 'Role created successfully with permissions for all modules.');
    }
    

    public function edit($id)
    {
        $roleModel = new RoleModel();
        $role = $roleModel->find($id);

        if (!$role) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Role not found');
        }
        return view('roles/edit', ['role' => $role, 'pageTitle' => 'Create Roles']);
    }

    public function update($id)
    {
        $roleModel = new RoleModel();
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description')
        ];
        $roleModel->update($id, $data);
        return redirect()->to('/roles');
    }

    public function delete($id)
    {
        $roleModel = new RoleModel();
        $roleModel->delete($id);
        return redirect()->to('/roles');
    }
}


?>