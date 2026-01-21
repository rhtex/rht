<?php

namespace App\Services;

use App\Models\UserModel;

class AuthService
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function validateLogin(string $email, string $password)
    {
        $user = $this->userModel->where('email', $email)->first();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'active') {
                return $user;
            }
        }

        return null;
    }

    public function loginUser(array $user)
    {
        $session = session();
        
        // Fetch permissions for the user's role
        // For now, we assume we can get it via a join or updated query in UserModel
        // But to keep it simple, we'll fetch role details
        $roleModel = new \App\Models\RoleModel();
        $role = $roleModel->find($user['role_id']);
        
        $rolePermissionModel = new \App\Models\RolePermissionModel();
        $permissions = $rolePermissionModel->getPermissionsByRole((int)$user['role_id']);
        $permissionKeys = array_column($permissions, 'permission_key');
        
        $sessionData = [
            'user_id'       => $user['id'],
            'name'          => $user['name'],
            'email'         => $user['email'],
            'role_id'       => $user['role_id'],
            'role'          => $role['role_name'] ?? 'staff',
            'isLoggedIn'    => true,
            'permissions'   => $permissionKeys
        ];

        $session->set($sessionData);
        return true;
    }

    public function logout()
    {
        session()->destroy();
    }
}
