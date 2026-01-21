<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MainSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed Roles
        $roles = [
            [
                'role_name'   => 'admin',
                'description' => 'Administrator with full access',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'role_name'   => 'staff',
                'description' => 'Regular employee with limited access',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        $roleBuilder = $this->db->table('roles');
        foreach ($roles as $role) {
            // Check if exists
            if ($roleBuilder->where('role_name', $role['role_name'])->countAllResults() === 0) {
                $roleBuilder->insert($role);
            }
        }

        // 2. Get Admin Role ID
        $adminRole = $roleBuilder->where('role_name', 'admin')->get()->getRow();

        // 3. Seed Admin User
        $users = [
            [
                'name'          => 'Super Admin',
                'email'         => 'admin@rasidev.com',
                'password'      => password_hash('12345678', PASSWORD_BCRYPT),
                'role_id'       => $adminRole->id,
                'status'        => 'active',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]
        ];

        $userBuilder = $this->db->table('users');
        foreach ($users as $user) {
            if ($userBuilder->where('email', $user['email'])->countAllResults() === 0) {
                $userBuilder->insert($user);
            }
        }
    }
}
