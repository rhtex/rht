<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class StateController extends Controller
{
    public function fetchStates()
    {
        helper('state');

        $country = $this->request->getPost('country');
        if ($country == 'India') {
            $states = getStates();
            return $this->response->setJSON($states);
        }

        return $this->response->setJSON([]);
    }
    public function getStateNamewithCode($state_code)
    {
        if ($state_code) {
            $states = getStateByCode($state_code);
            return $this->response->setJSON($states);
        }

        return $this->response->setJSON([]);
    }
}
