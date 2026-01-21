<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\AuthService;

class AuthController extends BaseController
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('dashboard'); // Assuming dashboard route exists
        }
        return view('auth/login');
    }

    public function attemptLogin()
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[4]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->authService->validateLogin($email, $password);

        if ($user) {
            log_message('error', 'Login Successful for: ' . $email);
            $this->authService->loginUser($user);
            return redirect()->to('dashboard')->with('success', 'Welcome back!');
        } else {
            log_message('error', 'Login Failed for: ' . $email);
            return redirect()->back()->withInput()->with('error', 'Invalid login credentials or account inactive.');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    public function changePassword()
    {
        return view('auth/change_password');
    }

    public function updatePassword()
    {
        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find(session()->get('id'));

        if (!password_verify($this->request->getPost('current_password'), $user['password'])) {
            return redirect()->back()->with('error', 'Incorrect current password.');
        }

        $userModel->save([
            'id' => $user['id'],
            'password' => $this->request->getPost('new_password')
        ]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }
}
