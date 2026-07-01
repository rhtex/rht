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

    public function loadedWarps()
    {
        $db = \Config\Database::connect();
        // Fetch loaded beams, join with dc_beams to get ends, color, meters details, and join with receipt/expenses to determine costing.
        $data['beams'] = $db->table('production_beams pb')
            ->select('pb.*, dcb.ends, dcb.color, dcb.meters, dcb.remarks as txn_remarks, dcb.receipt_id')
            ->join('production_yarn_warping_sizing_dc_beams dcb', 'dcb.beam_number = pb.beam_number AND dcb.returned_status = "Loaded"', 'left')
            ->where('pb.status', 'Loaded')
            ->groupBy('pb.id')
            ->orderBy('pb.beam_number', 'ASC')
            ->get()
            ->getResultArray();

        // Calculate pro-rated landed cost for each beam based on its receipt
        foreach ($data['beams'] as &$beam) {
            $beam['cost'] = 0;
            if (!empty($beam['receipt_id'])) {
                // Get all beams returned in this receipt to calculate total meters
                $receiptBeams = $db->table('production_yarn_warping_sizing_dc_beams')
                    ->where('receipt_id', $beam['receipt_id'])
                    ->where('returned_status', 'Loaded')
                    ->get()
                    ->getResultArray();

                $totalMeters = 0;
                foreach ($receiptBeams as $rb) {
                    $totalMeters += (float)$rb['meters'];
                }

                // Get receipt details for expenses
                $receipt = $db->table('production_yarn_warping_sizing_receipts')
                    ->where('id', $beam['receipt_id'])
                    ->get()
                    ->getRowArray();

                if ($receipt) {
                    $totalExpenses = (float)$receipt['transport_charges'] + (float)$receipt['loading_charges'] + (float)$receipt['packing_charges'] + (float)$receipt['other_expenses'];

                    // Get receipt items to calculate raw yarn cost and get job charges
                    $receiptItems = $db->table('production_yarn_warping_sizing_receipt_items ri')
                        ->select('ri.quantity_received_kg, ri.job_work_charges, dci.dc_id, dci.mill_name, dci.yarn_count, dci.warp_weft, dci.yarn_type, dci.current_color')
                        ->join('production_yarn_warping_sizing_dc_items dci', 'dci.id = ri.dc_item_id')
                        ->where('ri.receipt_id', $beam['receipt_id'])
                        ->get()
                        ->getResultArray();

                    $totalRawYarnCost = 0;
                    $jobCharges = 0;
                    if (!empty($receiptItems)) {
                        // Job work charges are stored on the receipt items
                        $jobCharges = (float)$receiptItems[0]['job_work_charges']; 
                    }
                    
                    foreach ($receiptItems as $ri) {
                        // Fetch raw cost from movements
                        $issuanceMovement = $db->table('production_yarn_stock_movements')
                            ->where('movement_type', 'Issue_Job_Work')
                            ->where('reference_id', $ri['dc_id'])
                            ->where('yarn_count', $ri['yarn_count'])
                            ->where('brand_mill', $ri['mill_name'])
                            ->where('color', $ri['current_color'] ?: 'Raw')
                            ->where('yarn_type', $ri['yarn_type'])
                            ->where('warp_weft', $ri['warp_weft'])
                            ->get()
                            ->getRowArray();
                        
                        $rawCost = $issuanceMovement ? abs((float)$issuanceMovement['cost_per_kg']) : 0;
                        $totalRawYarnCost += (float)$ri['quantity_received_kg'] * $rawCost;
                    }

                    $grandTotalLandedCost = $totalRawYarnCost + $jobCharges + $totalExpenses;
                    if ($totalMeters > 0) {
                        $landedCostPerMeter = $grandTotalLandedCost / $totalMeters;
                        $beam['cost'] = $landedCostPerMeter * (float)$beam['meters'];
                    }
                }
            }
        }
        unset($beam);
            
        $data['title'] = 'Load Warps';

        return view('production/yarn_beams/loaded_warps', $data);
    }

    public function allDcs()
    {
        $db = \Config\Database::connect();
        
        $dyeingDcs = $db->table('production_yarn_dyeing_dcs')
            ->select('id, dc_number, dc_date, vendor_name, status, "Dyeing" as type, "production/yarn-dyeing/view" as view_url')
            ->get()->getResultArray();
            
        $warpingDcs = $db->table('production_yarn_warping_sizing_dcs')
            ->select('id, dc_number, dc_date, vendor_name, status, "Warping & Sizing" as type, "production/yarn-warping-sizing/view" as view_url')
            ->get()->getResultArray();
            
        $twistingDcs = $db->table('production_yarn_twisting_dcs')
            ->select('id, dc_number, dc_date, vendor_name, status, "Twisting" as type, "production/yarn-twisting/view" as view_url')
            ->get()->getResultArray();
            
        $weavingDcs = $db->table('production_yarn_weaving_dcs')
            ->select('id, dc_number, dc_date, vendor_name, status, "Weaving" as type, "production/yarn-weaving/view" as view_url')
            ->get()->getResultArray();

        $allDcs = array_merge($dyeingDcs, $warpingDcs, $twistingDcs, $weavingDcs);
        
        // Sort by date descending
        usort($allDcs, function($a, $b) {
            return strtotime($b['dc_date']) - strtotime($a['dc_date']);
        });

        $data['dcs'] = $allDcs;
        $data['title'] = 'All Delivery Challans';

        return view('production/yarn_beams/all_dcs', $data);
    }
}
