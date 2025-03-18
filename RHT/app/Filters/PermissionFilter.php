<?php
// app/Filters/PermissionFilter.php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\ModulePermissionModel;
use App\Models\RoleModel;
use App\Models\ModuleModel;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Check if user is logged in
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Get user role and current module (you can define your own logic to get the current module)
        $roleId = $session->get('role_id');
        $currentModule = $arguments[0]; // Pass module name from controller

        // Load models
        $moduleModel = new ModuleModel();
        $modulePermissionModel = new ModulePermissionModel();

        // Fetch module based on name
        $module = $moduleModel->where('name', $currentModule)->first();
        if (!$module) {
            return redirect()->back()->with('error', 'Invalid module');
        }

        // Check permissions for the role and module
        $permissions = $modulePermissionModel->where('role_id', $roleId)
            ->where('module_id', $module['id'])
            ->first();

        if (!$permissions) {
            return redirect()->back()->with('error', 'Access denied');
        }

        // You can add further checks here based on permissions (e.g., CRUD actions)

        // If everything is fine, continue
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something after the request is processed
    }
}
