<?php

namespace App\Controllers;

use App\Models\ModulePermissionModel;
use App\Models\RoleModel;
use App\Models\ModuleModel;
use CodeIgniter\RESTful\ResourceController;

class PermissionsController extends ResourceController
{
    protected $format = 'json'; // Set response format to JSON

    private $roleModel;
    private $moduleModel;
    private $permissionModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->moduleModel = new ModuleModel();
        $this->permissionModel = new ModulePermissionModel();
    }
    public function checkPermission($resource, $action)
    {
        $session = session();
        $userId = $session->get('id'); // Assuming you have the user ID stored in the session
        log_message('error', 'Access denied for role ID: ' . $userId);

        // If user ID is not found in session, deny access
        if (!$userId) {
            return "No";
        }

        $roleModel = new RoleModel();
        $modulePermissionModel = new ModulePermissionModel();

        $moduleModel = new ModuleModel();
        $modulePermissionModel = new ModulePermissionModel();

        // Fetch module based on name
        $module = $moduleModel->where('name', $resource)->first();
        if (!$module) {
            return redirect()->back()->with('error', 'Invalid module');
        }

        // Fetch user roles based on user ID (adjust according to your setup)
        $roles = $roleModel->join('user_roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.user_id', $userId)
            ->findColumn('role_id'); // Fetch all role IDs for the user
        log_message('error', 'Access: ' . ' to module ID: ' . ' for action: ' . $action);

        // Check if roles exist
        if (!empty($roles)) {
            // Loop through roles and check permissions
            log_message('error', 'AsizeD: ' . sizeof($roles));

            foreach ($roles as $role_id) {
                log_message('error', 'Access Check: ' . $role_id . ' to module ID: ' . $module['id'] . ' for action: ' . $action);
                $permission = $modulePermissionModel->where('role_id', $role_id)
                    ->where('module_id', $module['id']) // Assuming module_id is the resource
                    ->first(); // Get the first matching permission record
                log_message('error', 'permission: ' . sizeof($permission));
                // Check for the specified action permission
                if ($permission && $permission[$action . '_permission']) {

                    log_message('error', 'Access Not denied for role ID: ' . $role_id . ' to module ID: ' . $module['id'] . ' for action: ' . $action);

                    return true; // Permission granted

                } else {
                    log_message('error', 'Access denied for role ID: ' . $role_id . ' to module ID: ' . $module['id'] . ' for action: ' . $action);
                    return false;
                }
            }
        }

        // Access denied if no permissions were found
        return redirect()->to('/access-denied')->with('error', 'You do not have permission to access this page.');
    }



    public function index()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Permissions', 'read');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                $roles = $this->roleModel->findAll();
                $modules = $this->moduleModel->findAll();

                return view('permissions/index', [
                    'roles' => $roles,
                    'modules' => $modules,
                    'permissions' => [],
                    // Initialize permissions array if needed
                    'selected_role_id' => null,
                    // Initialize selected_role_id if needed
                    'pageTitle' => 'Manage Permissions',
                    'message' => '',
                    'role_name' => '',
                ]);
            }
        }
    }

    // POST - Fetch permissions for a role based on role_id
    public function fetchPermissions()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Permissions', 'read');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                $role_id = $this->request->getPost('role_id');

                if (empty($role_id)) {
                    return redirect()->back()->with('error', 'Please select a role.');
                }

                $roles = $this->roleModel->findAll();
                $modules = $this->moduleModel->findAll();

                $permissions = $this->permissionModel->where('role_id', $role_id)->findAll();
                $role = $this->roleModel->find($role_id);
                $role_name = !empty($role) ? $role['name'] : '';

                // Load the view with necessary data
                return view('permissions/index', [
                    'roles' => $roles,
                    'modules' => $modules,
                    'permissions' => $permissions,
                    'selected_role_id' => $role_id,
                    'pageTitle' => 'Manage Permissions',
                    'role_name' => $role_name,
                    'message' => '',
                ]);
            }
        }
    }

    // POST - Update permissions for a specific role
    public function updatePermissions()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Permissions', 'update');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                $role_id = $this->request->getPost('role_id');
                $permissions = $this->request->getPost('permissions');

                if ($role_id === null) {
                    return $this->failValidationError('Role ID is required.');
                }

                // Delete old permissions for the specified role
                $this->permissionModel->where('role_id', $role_id)->delete();

                // Insert new permissions
                foreach ($permissions as $module_id => $perm) {
                    $data = [
                        'role_id' => $role_id,
                        'module_id' => $module_id,
                        'create_permission' => isset($perm['create']) ? 1 : 0,
                        'read_permission' => isset($perm['read']) ? 1 : 0,
                        'update_permission' => isset($perm['update']) ? 1 : 0,
                        'delete_permission' => isset($perm['delete']) ? 1 : 0,
                    ];
                    $this->permissionModel->insert($data);
                }
                $roles = $this->roleModel->findAll();
                $modules = $this->moduleModel->findAll();
                return view('permissions/index', [
                    'roles' => $roles,
                    'modules' => $modules,
                    'permissions' => [],
                    // Initialize permissions array if needed
                    'selected_role_id' => null,
                    // Initialize selected_role_id if needed
                    'pageTitle' => 'Manage Permissions',
                    'message' => 'Permission Updated Successfully',
                    'role_name' => '',
                ]);
            }
        }
    }
}
