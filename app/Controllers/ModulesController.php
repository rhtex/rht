<?php

namespace App\Controllers;

use App\Models\ModuleModel;
use App\Models\RoleModel;
use App\Models\ModulePermissionModel;
use CodeIgniter\Controller;

class ModulesController extends Controller
{
    public function index()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Modules', 'read');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                $moduleModel = new ModuleModel();
                $modules = $moduleModel->findAll();

                return view('modules/index', ['modules' => $modules, 'pageTitle' => 'List of Roles']);
            }
        }
    }

    public function create()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Modules', 'create');
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
                    'pageTitle' => 'Create Modules'
                ];
                return view('modules/create', $data);
            }
        }
    }

    public function store()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Modules', 'create');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                $moduleModel = new ModuleModel();
                $roleModel = new RoleModel(); // Load RoleModel
                $modulePermissionModel = new ModulePermissionModel(); // Load ModulePermissionModel

                // Prepare data for the new module
                $data = [
                    'name' => $this->request->getPost('name'),
                    'description' => $this->request->getPost('description')
                ];

                // Insert the new module
                $moduleId = $moduleModel->insert($data); // Store the returned ID of the inserted module

                // Get all roles
                $roles = $roleModel->findAll();

                // Prepare permissions data for all roles
                $permissionsData = [];
                foreach ($roles as $role) {
                    $permissionsData[] = [
                        'role_id' => $role['id'],
                        // Link permission to the role
                        'module_id' => $moduleId,
                        // Link permission to the new module
                        'create_permission' => 0,
                        // Assuming you want to give create permission to all roles
                        'read_permission' => 0,
                        // Assuming read permission is granted
                        'update_permission' => 0,
                        // Assuming update permission is granted
                        'delete_permission' => 0,
                        // Assuming delete permission is granted
                    ];
                }

                // Insert module permissions for all roles
                if (!empty($permissionsData)) {
                    $modulePermissionModel->insertBatch($permissionsData);
                }

                // Redirect after successful insertion
                return redirect()->to('/modules')->with('success', 'Module created successfully with permissions for all roles.');
            }
        }
    }


    public function edit($id)
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Modules', 'update');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                $moduleModel = new ModuleModel();
                $module = $moduleModel->find($id);

                if (!$module) {
                    throw new \CodeIgniter\Exceptions\PageNotFoundException('Module not found');
                }
                return view('modules/edit', ['module' => $module, 'pageTitle' => 'Edit Modules']);
            }
        }
    }

    public function update($id)
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Modules', 'update');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                $moduleModel = new ModuleModel();
                $data = [
                    'name' => $this->request->getPost('name'),
                    'description' => $this->request->getPost('description')
                ];
                $moduleModel->update($id, $data);
                return redirect()->to('/modules');
            }
        }
    }

    public function delete($id)
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Modules', 'delete');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                $moduleModel = new ModuleModel();
                $moduleModel->delete($id);
                return redirect()->to('/modules');
            }
        }
    }
}
?>