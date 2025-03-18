<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;


class WeaverController extends Controller
{
    // protected $session;
    public function index()
    {
        $session = session();
        $isloggedin = $session->get('isLoggedIn');
        if ($isloggedin == TRUE) {
            return redirect()->to('/profile');
        } else {
            helper(['form']);
            echo view('signin');
        }
    }
}

?>