<?php
namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;

class UsersController extends BaseController
{
    public function __construct()
    {
        $this->middleware = ['permission:Users'];
    }
    public function index()
    {
        $userModel = new UserModel();

        // Fetch all users from the users table
        $data['users'] = $userModel->findAll();
        $data['pageTitle'] = 'List Users';

        // Load the view and pass the users data
        return view('/users/users_list', $data);
    }


    public function view($id)
    {
        $userModel = new UserModel();
        $session = session();
        $isloggedin = $session->get('isLoggedIn');
        if ($isloggedin != TRUE) {
            helper(['form']);
            echo view('signin');
        } else {
            $roleModel = new RoleModel();
            $userRoleModel = new UserRoleModel();

            // Fetch the user based on the provided ID
            $user = $userModel->find($id);

            if (!$user) {
                return redirect()->to('/list_users')->with('error', 'User not found.');
            }

            // Fetch all available roles
            $roles = $roleModel->findAll();

            // Fetch the role assigned to this user (if any)
            $userRole = $userRoleModel->where('user_id', $id)->first();
            $assignedRoleId = $userRole ? $userRole['role_id'] : null;

            $data['user'] = $user;
            $data['roles'] = $roles;
            $data['assignedRoleId'] = $assignedRoleId;
            $data['pageTitle'] = 'View User and Roles';

            return view('/users/view_user', $data);
        }
    }

    public function assignRole($id)
    {
        $userRoleModel = new UserRoleModel();
        $roleId = $this->request->getPost('role');

        // Check if role is already assigned
        $existingRole = $userRoleModel->where('user_id', $id)->first();

        if ($existingRole) {
            // Update existing role assignment
            $userRoleModel->update($existingRole['id'], ['role_id' => $roleId]);
        } else {
            // Insert new role assignment
            $userRoleModel->insert(['user_id' => $id, 'role_id' => $roleId]);
        }

        return redirect()->to('/users/view/' . $id)->with('success', 'Role assigned successfully.');
    }
}
