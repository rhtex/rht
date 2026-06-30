<?php

namespace App\Controllers;

use App\Models\YarnBeamModel;
use App\Models\YarnBeamLedgerModel;

class YarnBeamController extends BaseController
{
    protected $beamModel;
    protected $ledgerModel;

    public function __construct()
    {
        $this->beamModel = new YarnBeamModel();
        $this->ledgerModel = new YarnBeamLedgerModel();
    }

    public function index()
    {
        $status = $this->request->getGet('status');
        $location = $this->request->getGet('location');

        $query = $this->beamModel->orderBy('beam_number', 'ASC');

        if (!empty($status)) {
            $query->where('status', $status);
        }
        if (!empty($location)) {
            $query->where('location', $location);
        }

        $data['beams'] = $query->findAll();
        $data['selectedStatus'] = $status;
        $data['selectedLocation'] = $location;
        $data['title'] = 'Beam Tracker';

        return view('production/yarn_beams/index', $data);
    }

    public function store()
    {
        $rules = [
            'beam_number' => 'required|is_unique[production_beams.beam_number]|min_length[2]|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->beamModel->save([
            'beam_number' => strtoupper($this->request->getPost('beam_number')),
            'status'      => 'Empty',
            'location'    => 'In-House',
            'remarks'     => $this->request->getPost('remarks')
        ]);

        return redirect()->to('production/yarn-beams')->with('success', 'Beam registered successfully.');
    }

    public function ledger($id)
    {
        $beam = $this->beamModel->find($id);
        if (!$beam) {
            return redirect()->to('production/yarn-beams')->with('error', 'Beam not found.');
        }

        $data['beam'] = $beam;
        $data['ledger'] = $this->ledgerModel->where('beam_id', $id)->orderBy('id', 'DESC')->findAll();
        $data['title'] = 'Beam Ledger - ' . $beam['beam_number'];

        return view('production/yarn_beams/ledger', $data);
    }

    public function updateCondition($id)
    {
        $beam = $this->beamModel->find($id);
        if (!$beam) {
            return redirect()->to('production/yarn-beams')->with('error', 'Beam not found.');
        }

        $condition = $this->request->getPost('condition_status');
        if (!in_array($condition, ['Active', 'Damaged'])) {
            return redirect()->to('production/yarn-beams')->with('error', 'Invalid condition status.');
        }

        $updateData = [
            'condition_status' => $condition,
            'damaged_date'     => ($condition === 'Damaged') ? date('Y-m-d') : null
        ];

        $this->beamModel->update($id, $updateData);

        // Also log in ledger
        $this->ledgerModel->save([
            'beam_id'          => $id,
            'transaction_date' => date('Y-m-d'),
            'transaction_type' => 'Manual_Adjustment',
            'from_location'    => $beam['location'],
            'to_location'      => $beam['location'],
            'status_from'      => $beam['status'],
            'status_to'        => $beam['status'],
            'remarks'          => 'Condition marked as ' . $condition . (($condition === 'Damaged') ? ' on ' . date('Y-m-d') : ''),
            'created_by'       => session('user_id')
        ]);

        return redirect()->to('production/yarn-beams')->with('success', 'Beam condition updated to ' . $condition . '.');
    }
}
