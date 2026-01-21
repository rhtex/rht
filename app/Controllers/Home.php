<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Home extends BaseController
{
    public function index()
    {
        return view('dashboard/index');
    }

    public function lang($locale)
    {
        $session = session();
        $session->remove('lang');
        $session->set('lang', $locale);
        return redirect()->back();
    }
}
