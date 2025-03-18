<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;


class DashboardController extends Controller
{
    // protected $session;
    public function index()
    {
        $permissionController = new PermissionsController();
        $check = $permissionController->checkPermission('Dashboard', 'read');
        if ($check === 'No') {
            $data['pageTitle'] = 'Sign In';
            helper(['form']);
            echo view('/signin', $data);
        } else {
            if (!$check) {
                $data['pageTitle'] = 'Access Denied';
                return view('/access_denied', $data); // Redirected in the permission check
            } else {
                $data['pageTitle'] = 'Dashboard';
                echo view('dashboard', $data);
            }
        }
    }
}

?>