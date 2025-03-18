<?php

namespace App\Controllers;

use App\Models\CombinationsModel;
use CodeIgniter\Controller;

class CombinationsController extends Controller
{
    public function index()
    {
        $model = new CombinationsModel();

        $data['combinations'] = $model->where('approval', 0)->findAll();
        $data['pageTitle'] = 'Combinations Approval';

        return view('combinations/viewCombinations', $data);
    }

    public function approve($id)
    {
        $session = session();
        $model = new CombinationsModel();

        $model->update($id, ['approval' => 1]);
        $session->setFlashdata('success', 'Approved successfully.');
        return redirect()->to('/combinations');
    }

    public function reject($id)
    {
        $session = session();
        $model = new CombinationsModel();

        $model->update($id, ['approval' => 2]);
        $session->setFlashdata('error', 'Rejected successfully.');
        return redirect()->to('/combinations');
    }
}
