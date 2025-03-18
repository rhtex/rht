<?php
namespace App\Controllers;
use CodeIgniter\Controller;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\RoleModel;
class SigninController extends Controller
{
    public function index()
    {
        $session = session();
        $isLoggedIn = $session->get('isLoggedIn');
        if ($isLoggedIn) {
            return redirect()->to('/profile');
        } else {
            helper(['form']);
            $data = [
                'pageTitle' => 'Signin'
            ];
            echo view('signin', $data);
        }
    }
    public function loginAuth()
    {
        $session = session();
        $userModel = new UserModel();
        $employeeModel = new \App\Models\EmployeeModel();
        $userRoleModel = new UserRoleModel();
        $roleModel = new RoleModel();
        // Get the email and password from the form
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');
        // Find the user by email
        $data = $userModel->where('email', $email)->first();
        $employee = $employeeModel->where('email', $email)->first();
        if ($data) {
            // Verify the password
            $pass = $data['password'];
            $authenticatePassword = password_verify($password, $pass);
            if ($authenticatePassword) {
                // Get the role_id from user_roles table
                $userrole = $userRoleModel->where('user_id', $data['id'])->first();
                $role_id = $userrole ? $userrole['role_id'] : null; // Handle if no role is found
                $role = $roleModel->find($role_id);
                // Set session data
                $ses_data = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role_id' => $role_id,
                    'role_name' => $role ? $role['name'] : null,
                    'photo' => $employee['employee_photo_upload'],
                    'isLoggedIn' => TRUE
                ];
                $session->set($ses_data);
                return redirect()->to('/dashboard');
            } else {
                // Incorrect password
                $session->setFlashdata('msg', 'Password is incorrect.');
                return redirect()->to('/signin');
            }
        } else {
            // Email does not exist
            $session->setFlashdata('msg', 'Email does not exist.');
            return redirect()->to('/signin');
        }
    }
    public function logout()
    {
        $session = session();
        $session->destroy(); // Destroy the session data to log out the user
        return redirect()->to('/signin'); // Redirect to the signin page
    }
}