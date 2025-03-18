<?php 

namespace App\Controllers;  
use CodeIgniter\Controller;

  
class ProfileController extends Controller
{
    public function index()
    {
        $session = session();
        $isloggedin = $session->get('isLoggedIn');
        if ($isloggedin != TRUE) {
            return redirect()->to('/signin');
        }
        else {
            $data['pageTitle'] = 'My Profile';
            echo view('profile', $data);
        }
    }
}