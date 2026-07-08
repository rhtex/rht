<?php

namespace App\Controllers;

use App\Models\YarnWarpingSizingDcModel;
use App\Models\YarnWarpingSizingDcItemModel;
use App\Models\YarnWarpingSizingReceiptModel;
use App\Models\YarnWarpingSizingReceiptItemModel;
use App\Models\YarnWarpingSizingDcBeamModel;
use App\Models\YarnWarpingSizingDcColorEndsModel;
use App\Models\YarnBeamModel;
use App\Models\YarnBeamLedgerModel;
use App\Models\YarnStockMovementModel;

class YarnWarpingSizingController extends BaseController
{
    protected $dcModel;
    protected $dcItemModel;
    protected $receiptModel;
    protected $receiptItemModel;
    protected $dcBeamModel;
    protected $colorEndsModel;
    protected $yBeamModel;
    protected $yBeamLedgerModel;
    protected $movementModel;

    public function __construct()
    {
        $this->dcModel = new YarnWarpingSizingDcModel();
        $this->dcItemModel = new YarnWarpingSizingDcItemModel();
        $this->receiptModel = new YarnWarpingSizingReceiptModel();
        $this->receiptItemModel = new YarnWarpingSizingReceiptItemModel();
        $this->dcBeamModel = new YarnWarpingSizingDcBeamModel();
        $this->colorEndsModel = new YarnWarpingSizingDcColorEndsModel();
        $this->yBeamModel = new YarnBeamModel();
        $this->yBeamLedgerModel = new YarnBeamLedgerModel();
        $this->movementModel = new YarnStockMovementModel();
    }

    public function index()
    {
        // Auto-migrate column if not exists
        $db = \Config\Database::connect();
        if (!$db->fieldExists('warp_yarn_type', 'production_yarn_warping_sizing_dc_items')) {
            $db->query("ALTER TABLE production_yarn_warping_sizing_dc_items ADD COLUMN warp_yarn_type VARCHAR(50) NULL AFTER warp_weft");
        }

        $data['dcs'] = $this->dcModel->orderBy('id', 'DESC')->findAll();
        $data['title'] = 'Yarn Warping & Sizing Job Work';
        return view('production/yarn_warping_sizing/index', $data);
    }

    public function create()
    {
        $data['availableYarns'] = $this->movementModel->getInventory();
        
        // Filter empty beams to exclude damaged ones
        $data['emptyBeams'] = $this->yBeamModel
            ->where('status', 'Empty')
            ->where('condition_status', 'Active')
            ->findAll();

        $lastDc = $this->dcModel->orderBy('id', 'DESC')->first();
        $nextNum = $lastDc ? ((int)str_replace('DC-WS-', '', $lastDc['dc_number'])) + 1 : 1001;
        $data['nextDcNumber'] = 'DC-WS-' . $nextNum;

        $data['title'] = 'Create Warping & Sizing Delivery Challan';
        return view('production/yarn_warping_sizing/dc_form', $data);
    }

    public function store()
    {
        $dcData = [
            'dc_number'            => $this->request->getPost('dc_number'),
            'dc_date'              => $this->request->getPost('dc_date'),
            'vendor_name'          => $this->request->getPost('vendor_name'),
            'expected_return_date' => $this->request->getPost('expected_return_date') ?: null,
            'vehicle_details'      => $this->request->getPost('vehicle_details'),
            'remarks'              => $this->request->getPost('remarks'),
            'design_pattern'       => $this->request->getPost('design_pattern'),
            'total_ends'           => (int)$this->request->getPost('total_ends'),
            'status'               => 'Open',
            'created_by'           => session('user_id'),
        ];

        $rules = $this->dcModel->getValidationRules();
        if (isset($rules['dc_number'])) {
            $rules['dc_number'] = str_replace(',id,{id}', '', $rules['dc_number']);
        }
        if (isset($rules['status'])) {
            unset($rules['status']);
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Validate ends count sum
        $endsBreakdown = $this->request->getPost('ends_breakdown') ?: [];
        $totalEndsInput = (int)$this->request->getPost('total_ends');
        $sumEnds = 0;
        foreach ($endsBreakdown as $eb) {
            $sumEnds += (int)($eb['ends_count'] ?? 0);
        }

        if ($sumEnds !== $totalEndsInput) {
            return redirect()->back()->withInput()->with('error', 'Validation Error: Sum of color-wise ends (' . $sumEnds . ') must exactly match Total Ends (' . $totalEndsInput . ').');
        }

        $items = $this->request->getPost('items');
        if (empty($items) || !is_array($items)) {
            return redirect()->back()->withInput()->with('error', 'At least one yarn item must be added.');
        }

        // Validate cones issued does not exceed available stock
        foreach ($items as $item) {
            $conesIssued = (int)($item['cones_issued'] ?? 0);
            if ($conesIssued > 0) {
                $stockCheck = $this->movementModel->getInventory([
                    'yarn_count' => $item['yarn_count'],
                    'yarn_type'  => $item['yarn_type'],
                    'color'      => $item['current_color'] ?: 'Raw',
                    'brand_mill' => $item['mill_name'],
                    'lot_number' => $item['lot_number'] ?: '',
                    'csp'        => $item['csp'] ?: '',
                    'warp_weft'  => $item['warp_weft'],
                ]);
                $availableCones = !empty($stockCheck) ? (int)$stockCheck[0]['cones_available'] : 0;
                if ($conesIssued > $availableCones) {
                    return redirect()->back()->withInput()->with('error',
                        'Cannot issue ' . $conesIssued . ' cones for ' . $item['yarn_count'] . ' (' . $item['mill_name'] . '). Only ' . $availableCones . ' cones available in stock.');
                }
            }
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->dcModel->skipValidation(true);
        $this->dcModel->save($dcData);
        $dcId = $this->dcModel->getInsertID();

        // Save Color Ends Breakdown
        foreach ($endsBreakdown as $eb) {
            if (!empty($eb['color']) && (int)$eb['ends_count'] > 0) {
                $this->colorEndsModel->save([
                    'dc_id'      => $dcId,
                    'color'      => $eb['color'],
                    'ends_count' => (int)$eb['ends_count']
                ]);
            }
        }

        // Save Items and Stock Movement
        foreach ($items as $item) {
            $itemData = [
                'dc_id'              => $dcId,
                'mill_name'          => $item['mill_name'],
                'yarn_count'         => $item['yarn_count'],
                'warp_weft'          => $item['warp_weft'],
                'warp_yarn_type'     => !empty($item['warp_yarn_type']) ? $item['warp_yarn_type'] : null,
                'csp'                => $item['csp'] ?: null,
                'lot_number'         => $item['lot_number'] ?: null,
                'yarn_type'          => $item['yarn_type'],
                'current_color'      => $item['current_color'] ?: 'Raw',
                'quantity_issued_kg' => (float)$item['quantity_issued_kg'],
            ];

            $conesIssued = (int)($item['cones_issued'] ?? 0);
            $itemData['cones_issued'] = $conesIssued;

            $this->dcItemModel->skipValidation(true);
            $this->dcItemModel->save($itemData);

            $stockCheck = $this->movementModel->getInventory([
                'yarn_count' => $item['yarn_count'],
                'yarn_type'  => $item['yarn_type'],
                'color'      => $item['current_color'] ?: 'Raw',
                'brand_mill' => $item['mill_name'],
                'lot_number' => $item['lot_number'] ?: '',
                'csp'        => $item['csp'] ?: '',
                'warp_weft'  => $item['warp_weft'],
            ]);
            
            $costPerKg = !empty($stockCheck) ? (float)$stockCheck[0]['avg_cost_per_kg'] : 0;

            $this->movementModel->save([
                'yarn_name'     => 'Yarn (' . $item['yarn_count'] . ')',
                'yarn_count'    => $item['yarn_count'],
                'yarn_type'     => $item['yarn_type'],
                'color'         => $item['current_color'] ?: 'Raw',
                'brand_mill'    => $item['mill_name'],
                'lot_number'    => $item['lot_number'] ?: null,
                'csp'           => $item['csp'] ?: null,
                'warp_weft'     => $item['warp_weft'],
                'quantity_kg'   => -((float)$item['quantity_issued_kg']),
                'quantity_cones'=> -$conesIssued,
                'cost_per_kg'   => $costPerKg,
                'warehouse'     => 'Main Warehouse',
                'movement_type' => 'Issue_Job_Work',
                'reference_id'  => $dcId,
                'remarks'       => 'Issued for Warping & Sizing to ' . $dcData['vendor_name'] . ' (DC: ' . $dcData['dc_number'] . ')',
                'created_by'    => session('user_id'),
                'created_at'    => date('Y-m-d H:i:s')
            ]);
        }

        // Process issued beams
        $beams = $this->request->getPost('beams') ?: [];
        foreach ($beams as $beamNum) {
            if (empty($beamNum)) continue;

            $this->dcBeamModel->save([
                'dc_id'       => $dcId,
                'beam_number' => $beamNum,
                'remarks'     => 'Issued empty on DC ' . $dcData['dc_number']
            ]);

            $beamRow = $this->yBeamModel->where('beam_number', $beamNum)->first();
            if ($beamRow) {
                $this->yBeamModel->update($beamRow['id'], [
                    'location'       => $dcData['vendor_name'],
                    'current_holder' => $dcData['vendor_name'],
                    'status'         => 'Empty'
                ]);

                $this->yBeamLedgerModel->save([
                    'beam_id'          => $beamRow['id'],
                    'transaction_date' => $dcData['dc_date'],
                    'transaction_type' => 'Issue_Warping_Sizing',
                    'reference_id'     => $dcId,
                    'from_location'    => 'In-House',
                    'to_location'      => $dcData['vendor_name'],
                    'status_from'      => 'Empty',
                    'status_to'        => 'Empty',
                    'remarks'          => 'Sent empty for warping & sizing on DC ' . $dcData['dc_number'],
                    'created_by'       => session('user_id')
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to save Delivery Challan.');
        }

        return redirect()->to('production/yarn-warping-sizing')->with('success', 'Delivery Challan created successfully.');
    }

    public function view($id)
    {
        $data['dc'] = $this->dcModel->find($id);
        if (!$data['dc']) {
            return redirect()->to('production/yarn-warping-sizing')->with('error', 'Delivery Challan not found.');
        }

        $items = $this->dcItemModel->where('dc_id', $id)->findAll();
        foreach ($items as &$item) {
            $movement = $this->movementModel
                ->where('movement_type', 'Issue_Job_Work')
                ->where('reference_id', $id)
                ->where('yarn_count', $item['yarn_count'])
                ->where('brand_mill', $item['mill_name'])
                ->where('color', $item['current_color'] ?: 'Raw')
                ->where('warp_weft', $item['warp_weft'])
                ->first();
            $item['cost_per_kg'] = $movement ? abs((float)$movement['cost_per_kg']) : 0;
        }
        $data['items'] = $items;
        $data['title'] = 'Warping & Sizing Delivery Challan: ' . $data['dc']['dc_number'];

        $data['colorEnds'] = $this->colorEndsModel->where('dc_id', $id)->findAll();
        $data['beams'] = $this->dcBeamModel->where('dc_id', $id)->findAll();

        $data['receipts'] = $this->receiptModel->where('dc_id', $id)->findAll();
        foreach ($data['receipts'] as &$r) {
            $r['items'] = $this->receiptItemModel
                ->select('production_yarn_warping_sizing_receipt_items.*, production_yarn_warping_sizing_dc_items.mill_name, production_yarn_warping_sizing_dc_items.yarn_count, production_yarn_warping_sizing_dc_items.warp_weft, production_yarn_warping_sizing_dc_items.lot_number, production_yarn_warping_sizing_dc_items.csp, production_yarn_warping_sizing_dc_items.yarn_type, production_yarn_warping_sizing_dc_items.current_color')
                ->join('production_yarn_warping_sizing_dc_items', 'production_yarn_warping_sizing_dc_items.id = production_yarn_warping_sizing_receipt_items.dc_item_id')
                ->where('receipt_id', $r['id'])
                ->findAll();

            foreach ($r['items'] as &$ri) {
                // Fetch raw cost from movements
                $issuanceMovement = $this->movementModel
                    ->where('movement_type', 'Issue_Job_Work')
                    ->where('reference_id', $id)
                    ->where('yarn_count', $ri['yarn_count'])
                    ->where('brand_mill', $ri['mill_name'])
                    ->where('color', $ri['current_color'] ?: 'Raw')
                    ->where('yarn_type', $ri['yarn_type'])
                    ->where('warp_weft', $ri['warp_weft'])
                    ->first();
                $ri['raw_cost'] = $issuanceMovement ? abs((float)$issuanceMovement['cost_per_kg']) : 0;
            }

            $r['beams'] = $this->dcBeamModel->where('receipt_id', $r['id'])->findAll();
        }

        return view('production/yarn_warping_sizing/dc_view', $data);
    }

    public function edit($id)
    {
        $data['dc'] = $this->dcModel->find($id);
        if (!$data['dc']) {
            return redirect()->to('production/yarn-warping-sizing')->with('error', 'Delivery Challan not found.');
        }

        $receiptsCount = $this->receiptModel->where('dc_id', $id)->countAllResults();
        if ($receiptsCount > 0) {
            return redirect()->to('production/yarn-warping-sizing')->with('error', 'Cannot edit because receipts exist.');
        }

        $dcItems = $this->dcItemModel->where('dc_id', $id)->findAll();
        foreach ($dcItems as &$item) {
            $movement = $this->movementModel
                ->where('movement_type', 'Issue_Job_Work')
                ->where('reference_id', $id)
                ->where('yarn_count', $item['yarn_count'])
                ->where('brand_mill', $item['mill_name'])
                ->where('color', $item['current_color'] ?: 'Raw')
                ->where('warp_weft', $item['warp_weft'])
                ->first();
            $item['avg_cost_per_kg'] = $movement ? abs((float)$movement['cost_per_kg']) : 0;
        }
        $data['dcItems'] = $dcItems;
        $data['availableYarns'] = $this->movementModel->getInventory();
        
        $data['emptyBeams'] = $this->yBeamModel
            ->where('status', 'Empty')
            ->where('condition_status', 'Active')
            ->findAll();
        
        $data['colorEnds'] = $this->colorEndsModel->where('dc_id', $id)->findAll();
        $data['beams'] = $this->dcBeamModel->where('dc_id', $id)->findAll();
        
        $data['title'] = 'Edit Warping & Sizing Delivery Challan';
        return view('production/yarn_warping_sizing/dc_form', $data);
    }

    public function update($id)
    {
        $dc = $this->dcModel->find($id);
        if (!$dc) {
            return redirect()->to('production/yarn-warping-sizing')->with('error', 'DC not found.');
        }

        $receiptsCount = $this->receiptModel->where('dc_id', $id)->countAllResults();
        if ($receiptsCount > 0) {
            return redirect()->to('production/yarn-warping-sizing')->with('error', 'Cannot update because receipts exist.');
        }

        $dcData = [
            'dc_number'            => $this->request->getPost('dc_number'),
            'dc_date'              => $this->request->getPost('dc_date'),
            'vendor_name'          => $this->request->getPost('vendor_name'),
            'expected_return_date' => $this->request->getPost('expected_return_date') ?: null,
            'vehicle_details'      => $this->request->getPost('vehicle_details'),
            'remarks'              => $this->request->getPost('remarks'),
            'design_pattern'       => $this->request->getPost('design_pattern'),
            'total_ends'           => (int)$this->request->getPost('total_ends'),
            'updated_by'           => session('user_id'),
        ];

        $rules = $this->dcModel->getValidationRules();
        if (isset($rules['dc_number'])) {
            $rules['dc_number'] = str_replace('{id}', $id, $rules['dc_number']);
        }
        if (isset($rules['status'])) {
            unset($rules['status']);
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Validate ends count sum
        $endsBreakdown = $this->request->getPost('ends_breakdown') ?: [];
        $totalEndsInput = (int)$this->request->getPost('total_ends');
        $sumEnds = 0;
        foreach ($endsBreakdown as $eb) {
            $sumEnds += (int)($eb['ends_count'] ?? 0);
        }

        if ($sumEnds !== $totalEndsInput) {
            return redirect()->back()->withInput()->with('error', 'Validation Error: Sum of color-wise ends (' . $sumEnds . ') must exactly match Total Ends (' . $totalEndsInput . ').');
        }

        $items = $this->request->getPost('items');
        if (empty($items) || !is_array($items)) {
            return redirect()->back()->withInput()->with('error', 'At least one yarn item must be added.');
        }

        // Validate cones issued does not exceed available stock
        // Note: During update, old movements will be reverted first, so we check against current stock
        // plus what will be returned from the old DC items
        $oldItems = $this->dcItemModel->where('dc_id', $id)->findAll();
        $oldConesByKey = [];
        foreach ($oldItems as $oi) {
            $key = ($oi['yarn_count'] ?? '') . '|' . ($oi['yarn_type'] ?? '') . '|' . ($oi['current_color'] ?: 'Raw') . '|' . ($oi['mill_name'] ?? '') . '|' . ($oi['lot_number'] ?? '') . '|' . ($oi['csp'] ?? '') . '|' . ($oi['warp_weft'] ?? '');
            $oldConesByKey[$key] = ($oldConesByKey[$key] ?? 0) + (int)($oi['cones_issued'] ?? 0);
        }

        foreach ($items as $item) {
            $conesIssued = (int)($item['cones_issued'] ?? 0);
            if ($conesIssued > 0) {
                $stockCheck = $this->movementModel->getInventory([
                    'yarn_count' => $item['yarn_count'],
                    'yarn_type'  => $item['yarn_type'],
                    'color'      => $item['current_color'] ?: 'Raw',
                    'brand_mill' => $item['mill_name'],
                    'lot_number' => $item['lot_number'] ?: '',
                    'csp'        => $item['csp'] ?: '',
                    'warp_weft'  => $item['warp_weft'],
                ]);
                $availableCones = !empty($stockCheck) ? (int)$stockCheck[0]['cones_available'] : 0;

                // Add back cones from the old DC that will be reverted
                $key = ($item['yarn_count'] ?? '') . '|' . ($item['yarn_type'] ?? '') . '|' . ($item['current_color'] ?: 'Raw') . '|' . ($item['mill_name'] ?? '') . '|' . ($item['lot_number'] ?? '') . '|' . ($item['csp'] ?? '') . '|' . ($item['warp_weft'] ?? '');
                $availableCones += ($oldConesByKey[$key] ?? 0);

                if ($conesIssued > $availableCones) {
                    return redirect()->back()->withInput()->with('error',
                        'Cannot issue ' . $conesIssued . ' cones for ' . $item['yarn_count'] . ' (' . $item['mill_name'] . '). Only ' . $availableCones . ' cones available in stock.');
                }
            }
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->dcModel->skipValidation(true);
        $this->dcModel->update($id, $dcData);

        // Revert old beams
        $oldBeams = $this->dcBeamModel->where('dc_id', $id)->findAll();
        foreach ($oldBeams as $ob) {
            $beamRow = $this->yBeamModel->where('beam_number', $ob['beam_number'])->first();
            if ($beamRow) {
                $this->yBeamModel->update($beamRow['id'], [
                    'location'       => 'In-House',
                    'current_holder' => 'In-House',
                    'status'         => 'Empty'
                ]);
            }
        }

        $this->dcItemModel->where('dc_id', $id)->delete();
        $this->colorEndsModel->where('dc_id', $id)->delete();
        $this->dcBeamModel->where('dc_id', $id)->delete();
        $this->yBeamLedgerModel->where('reference_id', $id)->where('transaction_type', 'Issue_Warping_Sizing')->delete();
        $this->movementModel->where('movement_type', 'Issue_Job_Work')->where('reference_id', $id)->delete();

        // Save Color Ends Breakdown
        foreach ($endsBreakdown as $eb) {
            if (!empty($eb['color']) && (int)$eb['ends_count'] > 0) {
                $this->colorEndsModel->save([
                    'dc_id'      => $id,
                    'color'      => $eb['color'],
                    'ends_count' => (int)$eb['ends_count']
                ]);
            }
        }

        // Save Items and Stock Movement
        foreach ($items as $item) {
            $itemData = [
                'dc_id'              => $id,
                'mill_name'          => $item['mill_name'],
                'yarn_count'         => $item['yarn_count'],
                'warp_weft'          => $item['warp_weft'],
                'warp_yarn_type'     => !empty($item['warp_yarn_type']) ? $item['warp_yarn_type'] : null,
                'csp'                => $item['csp'] ?: null,
                'lot_number'         => $item['lot_number'] ?: null,
                'yarn_type'          => $item['yarn_type'],
                'current_color'      => $item['current_color'] ?: 'Raw',
            ];
            $conesIssued = (int)($item['cones_issued'] ?? 0);
            $itemData['cones_issued'] = $conesIssued;

            $this->dcItemModel->skipValidation(true);
            $this->dcItemModel->save($itemData);

            $stockCheck = $this->movementModel->getInventory([
                'yarn_count' => $item['yarn_count'],
                'yarn_type'  => $item['yarn_type'],
                'color'      => $item['current_color'] ?: 'Raw',
                'brand_mill' => $item['mill_name'],
                'lot_number' => $item['lot_number'] ?: '',
                'csp'        => $item['csp'] ?: '',
                'warp_weft'  => $item['warp_weft'],
            ]);
            
            $costPerKg = !empty($stockCheck) ? (float)$stockCheck[0]['avg_cost_per_kg'] : 0;

            $this->movementModel->save([
                'yarn_name'     => 'Yarn (' . $item['yarn_count'] . ')',
                'yarn_count'    => $item['yarn_count'],
                'yarn_type'     => $item['yarn_type'],
                'color'         => $item['current_color'] ?: 'Raw',
                'brand_mill'    => $item['mill_name'],
                'lot_number'    => $item['lot_number'] ?: null,
                'csp'           => $item['csp'] ?: null,
                'warp_weft'     => $item['warp_weft'],
                'quantity_kg'   => -((float)$item['quantity_issued_kg']),
                'quantity_cones'=> -$conesIssued,
                'cost_per_kg'   => $costPerKg,
                'warehouse'     => 'Main Warehouse',
                'movement_type' => 'Issue_Job_Work',
                'reference_id'  => $id,
                'remarks'       => 'Issued for Warping & Sizing to ' . $dcData['vendor_name'] . ' (DC: ' . $dcData['dc_number'] . ')',
                'created_by'    => session('user_id'),
                'created_at'    => date('Y-m-d H:i:s')
            ]);
        }

        // Process issued beams
        $beams = $this->request->getPost('beams') ?: [];
        foreach ($beams as $beamNum) {
            if (empty($beamNum)) continue;

            $this->dcBeamModel->save([
                'dc_id'       => $id,
                'beam_number' => $beamNum,
                'remarks'     => 'Issued empty on DC ' . $dcData['dc_number']
            ]);

            $beamRow = $this->yBeamModel->where('beam_number', $beamNum)->first();
            if ($beamRow) {
                $this->yBeamModel->update($beamRow['id'], [
                    'location'       => $dcData['vendor_name'],
                    'current_holder' => $dcData['vendor_name'],
                    'status'         => 'Empty'
                ]);

                $this->yBeamLedgerModel->save([
                    'beam_id'          => $beamRow['id'],
                    'transaction_date' => $dcData['dc_date'],
                    'transaction_type' => 'Issue_Warping_Sizing',
                    'reference_id'     => $id,
                    'from_location'    => 'In-House',
                    'to_location'      => $dcData['vendor_name'],
                    'status_from'      => 'Empty',
                    'status_to'        => 'Empty',
                    'remarks'          => 'Sent empty for warping & sizing on DC ' . $dcData['dc_number'],
                    'created_by'       => session('user_id')
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update Delivery Challan.');
        }

        return redirect()->to('production/yarn-warping-sizing')->with('success', 'Delivery Challan updated successfully.');
    }

    public function delete($id)
    {
        $dc = $this->dcModel->find($id);
        if (!$dc) {
            return redirect()->to('production/yarn-warping-sizing')->with('error', 'Delivery Challan not found.');
        }

        $receiptsCount = $this->receiptModel->where('dc_id', $id)->countAllResults();
        if ($receiptsCount > 0) {
            return redirect()->to('production/yarn-warping-sizing')->with('error', 'Cannot delete because receipts exist.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Revert beams status back to In-House
        $oldBeams = $this->dcBeamModel->where('dc_id', $id)->findAll();
        foreach ($oldBeams as $ob) {
            $beamRow = $this->yBeamModel->where('beam_number', $ob['beam_number'])->first();
            if ($beamRow) {
                $this->yBeamModel->update($beamRow['id'], [
                    'location'       => 'In-House',
                    'current_holder' => 'In-House',
                    'status'         => 'Empty'
                ]);
            }
        }

        $this->dcItemModel->where('dc_id', $id)->delete();
        $this->colorEndsModel->where('dc_id', $id)->delete();
        $this->dcBeamModel->where('dc_id', $id)->delete();
        $this->yBeamLedgerModel->where('reference_id', $id)->where('transaction_type', 'Issue_Warping_Sizing')->delete();
        $this->movementModel->where('movement_type', 'Issue_Job_Work')->where('reference_id', $id)->delete();
        $this->dcModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('production/yarn-warping-sizing')->with('error', 'Failed to delete Delivery Challan.');
        }

        return redirect()->to('production/yarn-warping-sizing')->with('success', 'Delivery Challan deleted successfully.');
    }

    public function receiptCreate($dcId)
    {
        $data['dc'] = $this->dcModel->find($dcId);
        if (!$data['dc']) {
            return redirect()->to('production/yarn-warping-sizing')->with('error', 'DC not found.');
        }

        $data['items'] = $this->dcItemModel->where('dc_id', $dcId)->findAll();
        
        // Find beams sent on this DC that have not been received yet
        $data['beamsSent'] = $this->dcBeamModel->where('dc_id', $dcId)->where('receipt_id', null)->findAll();
        $data['colorEnds'] = $this->colorEndsModel->where('dc_id', $dcId)->findAll();

        $lastRec = $this->receiptModel->orderBy('id', 'DESC')->first();
        $nextNum = $lastRec ? ((int)str_replace('REC-WS-', '', $lastRec['receipt_number'])) + 1 : 1001;
        $data['nextReceiptNumber'] = 'REC-WS-' . $nextNum;

        $data['title'] = 'Receive Warping & Sizing';
        return view('production/yarn_warping_sizing/receipt_form', $data);
    }

    public function receiptStore($dcId)
    {
        $dc = $this->dcModel->find($dcId);
        if (!$dc) {
            return redirect()->to('production/yarn-warping-sizing')->with('error', 'DC not found.');
        }

        $receiptData = [
            'dc_id'             => $dcId,
            'receipt_number'    => $this->request->getPost('receipt_number'),
            'receipt_date'      => $this->request->getPost('receipt_date'),
            'transport_charges' => (float)($this->request->getPost('transport_charges') ?? 0),
            'loading_charges'   => (float)($this->request->getPost('loading_charges') ?? 0),
            'packing_charges'   => (float)($this->request->getPost('packing_charges') ?? 0),
            'other_expenses'    => (float)($this->request->getPost('other_expenses') ?? 0),
            'remarks'           => $this->request->getPost('remarks'),
            'created_by'        => session('user_id'),
        ];

        $rules = $this->receiptModel->getValidationRules();
        if (isset($rules['dc_id'])) {
            unset($rules['dc_id']);
        }
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $items = $this->request->getPost('items');
        
        $db = \Config\Database::connect();
        $db->transStart();

        $this->receiptModel->skipValidation(true);
        $this->receiptModel->save($receiptData);
        $receiptId = $this->receiptModel->getInsertID();

        $totalReceivedWeight = 0;
        foreach ($items as $dcItemId => $item) {
            $totalReceivedWeight += (float)($item['quantity_received_kg'] ?? 0);
        }

        $totalSharedExpenses = $receiptData['transport_charges'] + $receiptData['loading_charges'] + $receiptData['packing_charges'] + $receiptData['other_expenses'];
        $jobCharges = (float)($this->request->getPost('job_work_charges') ?? 0);

        foreach ($items as $dcItemId => $item) {
            $dcItem = $this->dcItemModel->find($dcItemId);
            
            $qtyReceived = (float)($item['quantity_received_kg'] ?? 0);
            $qtyWastage = (float)($item['quantity_used_kg'] ?? 0); // Form's "Used Qty" represents the difference (wastage/loss)
            
            $receivedColor = $item['received_color'] ?: 'Raw';

            if ($qtyReceived <= 0 && $qtyWastage <= 0) {
                continue;
            }

            $issued = (float)$dcItem['quantity_issued_kg'];
            $alreadyReceived = (float)$dcItem['quantity_received_kg'];
            $alreadyWasted = (float)$dcItem['quantity_wastage_kg'];
            $pending = $issued - ($alreadyReceived + $alreadyWasted);

            if ($qtyReceived + $qtyWastage > $pending) {
                return redirect()->back()->withInput()->with('error', 'Error: Total cannot exceed pending.');
            }

            $conesReceived = (int)($item['cones_received'] ?? 0);
            $conesIssued = (int)$dcItem['cones_issued'];
            $conesRecdExcludingCurrent = (int)$dcItem['cones_received'];
            $conesUsedExcludingCurrent = (int)$dcItem['cones_used'];
            $pendingCones = $conesIssued - ($conesRecdExcludingCurrent + $conesUsedExcludingCurrent);
            $conesUsed = max(0, $pendingCones - $conesReceived);

            $this->receiptItemModel->skipValidation(true);
            $this->receiptItemModel->save([
                'receipt_id'           => $receiptId,
                'dc_item_id'           => $dcItemId,
                'quantity_received_kg' => $qtyReceived,
                'quantity_wastage_kg'  => $qtyWastage,
                'cones_received'       => $conesReceived,
                'cones_used'           => $conesUsed,
                'job_work_charges'     => $jobCharges,
            ]);

            $newReceived = (float)$dcItem['quantity_received_kg'] + $qtyReceived;
            $newWastage = (float)$dcItem['quantity_wastage_kg'] + $qtyWastage;
            $newConesReceived = (int)$dcItem['cones_received'] + $conesReceived;
            $newConesUsed = (int)$dcItem['cones_used'] + $conesUsed;
            
            $this->dcItemModel->skipValidation(true);
            $this->dcItemModel->update($dcItemId, [
                'quantity_received_kg' => $newReceived,
                'quantity_wastage_kg'  => $newWastage,
                'cones_received'       => $newConesReceived,
                'cones_used'           => $newConesUsed
            ]);

            $issuanceQuery = $this->movementModel->where('movement_type', 'Issue_Job_Work')
                                                 ->where('reference_id', $dcId)
                                                 ->where('yarn_count', $dcItem['yarn_count'])
                                                 ->where('brand_mill', $dcItem['mill_name'])
                                                 ->where('color', $dcItem['current_color'] ?: 'Raw')
                                                 ->where('yarn_type', $dcItem['yarn_type'])
                                                 ->where('warp_weft', $dcItem['warp_weft']);
            if (!empty($dcItem['lot_number'])) {
                $issuanceQuery->where('lot_number', $dcItem['lot_number']);
            }
            if (!empty($dcItem['csp'])) {
                $issuanceQuery->where('csp', $dcItem['csp']);
            }
            $issuanceMovement = $issuanceQuery->first();
            $rawCostPerKg = $issuanceMovement ? abs((float)$issuanceMovement['cost_per_kg']) : 0;

            $itemSharedExpense = $totalReceivedWeight > 0 ? (($qtyReceived / $totalReceivedWeight) * $totalSharedExpenses) : 0;
            $itemSharedJobWork = $totalReceivedWeight > 0 ? (($qtyReceived / $totalReceivedWeight) * $jobCharges) : 0;

            $proRatedRawCostConsumed = ($qtyReceived + $qtyWastage) * $rawCostPerKg;
            $itemTotalLandedCost = $proRatedRawCostConsumed + $itemSharedJobWork + $itemSharedExpense;
            $finalLandedCostPerKg = $qtyReceived > 0 ? ($itemTotalLandedCost / $qtyReceived) : 0;

            if ($qtyReceived > 0) {
                $this->movementModel->save([
                    'yarn_name'     => 'Yarn (' . $dcItem['yarn_count'] . ')',
                    'yarn_count'    => $dcItem['yarn_count'],
                    'yarn_type'     => $dcItem['yarn_type'],
                    'color'         => $receivedColor,
                    'brand_mill'    => $dcItem['mill_name'],
                    'lot_number'    => $dcItem['lot_number'],
                    'csp'           => $dcItem['csp'],
                    'warp_weft'     => $dcItem['warp_weft'],
                    'quantity_kg'   => $qtyReceived,
                    'cost_per_kg'   => $finalLandedCostPerKg,
                    'warehouse'     => 'Main Warehouse',
                    'movement_type' => 'Receipt_Job_Work',
                    'reference_id'  => $receiptId,
                    'remarks'       => 'Received from ' . $dc['vendor_name'] . ' against ' . $dc['dc_number'],
                    'created_by'    => session('user_id'),
                    'created_at'    => date('Y-m-d H:i:s')
                ]);
            }
        }

        // Process returned beams with detailed specifications
        $beamsReturn = $this->request->getPost('beams_return') ?: [];
        if (!empty($beamsReturn) && is_array($beamsReturn)) {
            $yarnDetailsList = [];
            foreach ($items as $dcItemId => $item) {
                if ((float)($item['quantity_received_kg'] ?? 0) > 0) {
                    $dcItem = $this->dcItemModel->find($dcItemId);
                    $yarnDetailsList[] = $dcItem['yarn_count'] . ' (' . ($item['received_color'] ?: 'Raw') . ')';
                }
            }
            $yarnDetailsStr = implode(', ', array_unique($yarnDetailsList));

            foreach ($beamsReturn as $beamNum => $beamData) {
                $returnStatus = $beamData['status'] ?? 'Not Returned';
                if ($returnStatus === 'Not Returned') {
                    continue;
                }

                $meters = ($returnStatus === 'Loaded') ? (float)($beamData['meters'] ?? 0) : null;
                $sizingNo = ($returnStatus === 'Loaded') ? ($beamData['sizing_no'] ?? null) : null;
                $beamColor = ($returnStatus === 'Loaded') ? ($beamData['color'] ?? null) : null;
                $returnDate = ($returnStatus === 'Loaded') ? ($beamData['return_date'] ?? null) : null;
                $ends = ($returnStatus === 'Loaded') ? (int)($beamData['ends'] ?? 0) : null;

                $this->dcBeamModel->where('dc_id', $dcId)->where('beam_number', $beamNum)->set([
                    'receipt_id'      => $receiptId,
                    'returned_status' => $returnStatus,
                    'ends'            => $ends,
                    'meters'          => $meters,
                    'sizing_no'       => $sizingNo,
                    'color'           => $beamColor,
                    'return_date'     => $returnDate
                ])->update();

                $beamRow = $this->yBeamModel->where('beam_number', $beamNum)->first();
                if ($beamRow) {
                    $this->yBeamModel->update($beamRow['id'], [
                        'location'       => 'In-House',
                        'current_holder' => 'In-House',
                        'status'         => $returnStatus
                    ]);

                    $ledgerRemarks = 'Received ' . strtolower($returnStatus) . ' from warping & sizing on Receipt ' . $receiptData['receipt_number'];
                    if ($returnStatus === 'Loaded') {
                        $ledgerRemarks .= ". Sizing No: {$sizingNo}, Color: {$beamColor}, Length: {$meters} M";
                    }

                    $this->yBeamLedgerModel->save([
                        'beam_id'          => $beamRow['id'],
                        'transaction_date' => $receiptData['receipt_date'],
                        'transaction_type' => 'Receipt_Warping_Sizing',
                        'reference_id'     => $receiptId,
                        'from_location'    => $dc['vendor_name'],
                        'to_location'      => 'In-House',
                        'status_from'      => 'Empty',
                        'status_to'        => $returnStatus,
                        'yarn_details'     => ($returnStatus === 'Loaded') ? $yarnDetailsStr : '',
                        'remarks'          => $ledgerRemarks,
                        'created_by'       => session('user_id')
                    ]);
                }
            }
        }

        // Check if DC is fully completed
        $allDcItems = $this->dcItemModel->where('dc_id', $dcId)->findAll();
        $isCompleted = true;
        foreach ($allDcItems as $item) {
            $totalAccounted = (float)$item['quantity_received_kg'] + (float)$item['quantity_wastage_kg'];
            if ($totalAccounted < (float)$item['quantity_issued_kg']) {
                $isCompleted = false;
                break;
            }
        }

        $this->dcModel->skipValidation(true);
        $this->dcModel->update($dcId, [
            'status' => $isCompleted ? 'Completed' : 'Partially Received'
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to save receipt.');
        }

        return redirect()->to('production/yarn-warping-sizing/view/' . $dcId)->with('success', 'Yarn received successfully.');
    }

    public function receiptEdit($id)
    {
        $data['receipt'] = $this->receiptModel->find($id);
        if (!$data['receipt']) {
            return redirect()->back()->with('error', 'Receipt not found.');
        }
        $dcId = $data['receipt']['dc_id'];
        $data['dc'] = $this->dcModel->find($dcId);
        $data['items'] = $this->dcItemModel->where('dc_id', $dcId)->findAll();
        
        $data['beamsReceived'] = $this->dcBeamModel->where('dc_id', $dcId)->where('receipt_id', $id)->findAll();
        $data['beamsPending'] = $this->dcBeamModel->where('dc_id', $dcId)->where('receipt_id', null)->findAll();
        $data['colorEnds'] = $this->colorEndsModel->where('dc_id', $dcId)->findAll();

        $receiptItems = $this->receiptItemModel->where('receipt_id', $id)->findAll();
        $data['receiptItemsKeyed'] = [];
        foreach ($receiptItems as $ri) {
            $data['receiptItemsKeyed'][$ri['dc_item_id']] = $ri;
        }

        $data['isEdit'] = true;
        $data['title'] = 'Edit Warping & Sizing Receipt: ' . $data['receipt']['receipt_number'];
        return view('production/yarn_warping_sizing/receipt_form', $data);
    }

    public function receiptView($id)
    {
        $data['receipt'] = $this->receiptModel->find($id);
        if (!$data['receipt']) {
            return redirect()->back()->with('error', 'Receipt not found.');
        }
        $dcId = $data['receipt']['dc_id'];
        $data['dc'] = $this->dcModel->find($dcId);
        $data['items'] = $this->dcItemModel->where('dc_id', $dcId)->findAll();
        
        $data['beamsReceived'] = $this->dcBeamModel->where('dc_id', $dcId)->where('receipt_id', $id)->findAll();
        $data['beamsPending'] = $this->dcBeamModel->where('dc_id', $dcId)->where('receipt_id', null)->findAll();
        $data['colorEnds'] = $this->colorEndsModel->where('dc_id', $dcId)->findAll();

        $receiptItems = $this->receiptItemModel->where('receipt_id', $id)->findAll();
        $data['receiptItemsKeyed'] = [];
        foreach ($receiptItems as $ri) {
            $data['receiptItemsKeyed'][$ri['dc_item_id']] = $ri;
        }

        $data['isView'] = true;
        $data['isEdit'] = true; // Trigger edit-value logic
        $data['title'] = 'View Warping & Sizing Receipt: ' . $data['receipt']['receipt_number'];
        return view('production/yarn_warping_sizing/receipt_form', $data);
    }

    public function receiptUpdate($id)
    {
        $receipt = $this->receiptModel->find($id);
        if (!$receipt) {
            return redirect()->back()->with('error', 'Receipt not found.');
        }

        $dcId = $receipt['dc_id'];
        $dc = $this->dcModel->find($dcId);

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. REVERT OLD RECEIPT INVENTORY & QUANTITIES
        $receiptItems = $this->receiptItemModel->where('receipt_id', $id)->findAll();
        foreach ($receiptItems as $ri) {
            $dcItem = $this->dcItemModel->find($ri['dc_item_id']);
            if ($dcItem) {
                $newReceived = max(0, (float)$dcItem['quantity_received_kg'] - (float)$ri['quantity_received_kg']);
                $newWastage = max(0, (float)$dcItem['quantity_wastage_kg'] - (float)$ri['quantity_wastage_kg']);
                $newConesRecd = max(0, (int)$dcItem['cones_received'] - (int)($ri['cones_received'] ?? 0));
                $newConesUsed = max(0, (int)$dcItem['cones_used'] - (int)($ri['cones_used'] ?? 0));
                
                $this->dcItemModel->update($ri['dc_item_id'], [
                    'quantity_received_kg' => $newReceived,
                    'quantity_wastage_kg'  => $newWastage,
                    'cones_received'       => $newConesRecd,
                    'cones_used'           => $newConesUsed
                ]);
            }
        }

        $this->movementModel->where('movement_type', 'Receipt_Job_Work')
                            ->where('reference_id', $id)
                            ->delete();

        // Revert received beams associated with this receipt
        $oldReceivedBeams = $this->dcBeamModel->where('receipt_id', $id)->findAll();
        foreach ($oldReceivedBeams as $oldBeam) {
            $beamRow = $this->yBeamModel->where('beam_number', $oldBeam['beam_number'])->first();
            if ($beamRow) {
                $this->yBeamModel->update($beamRow['id'], [
                    'location'       => 'At Job Work',
                    'current_holder' => $dc['vendor_name'] ?? 'Job Worker',
                    'status'         => 'Empty'
                ]);
            }
        }
        $this->dcBeamModel->where('receipt_id', $id)->set([
            'receipt_id'      => null,
            'returned_status' => null,
            'meters'          => null,
            'sizing_no'       => null,
            'color'           => null,
            'return_date'     => null
        ])->update();
        
        $this->yBeamLedgerModel->where('reference_id', $id)->where('transaction_type', 'Receipt_Warping_Sizing')->delete();
        $this->receiptItemModel->where('receipt_id', $id)->delete();

        // 2. APPLY NEW UPDATED RECEIPT DATA
        $receiptData = [
            'receipt_number'    => $this->request->getPost('receipt_number'),
            'receipt_date'      => $this->request->getPost('receipt_date'),
            'transport_charges' => (float)($this->request->getPost('transport_charges') ?? 0),
            'loading_charges'   => (float)($this->request->getPost('loading_charges') ?? 0),
            'packing_charges'   => (float)($this->request->getPost('packing_charges') ?? 0),
            'other_expenses'    => (float)($this->request->getPost('other_expenses') ?? 0),
            'remarks'           => $this->request->getPost('remarks'),
            'updated_by'        => session('user_id'),
        ];

        $this->receiptModel->update($id, $receiptData);

        $items = $this->request->getPost('items') ?: [];
        $totalReceivedWeight = 0;
        foreach ($items as $dcItemId => $item) {
            $totalReceivedWeight += (float)($item['quantity_received_kg'] ?? 0);
        }

        $totalSharedExpenses = $receiptData['transport_charges'] + $receiptData['loading_charges'] + $receiptData['packing_charges'] + $receiptData['other_expenses'];
        $jobCharges = (float)($this->request->getPost('job_work_charges') ?? 0);

        foreach ($items as $dcItemId => $item) {
            $dcItem = $this->dcItemModel->find($dcItemId);
            
            $qtyReceived = (float)($item['quantity_received_kg'] ?? 0);
            $qtyWastage = (float)($item['quantity_used_kg'] ?? 0);
            
            $receivedColor = $item['received_color'] ?: 'Raw';

            if ($qtyReceived <= 0 && $qtyWastage <= 0) {
                continue;
            }

            $issued = (float)$dcItem['quantity_issued_kg'];
            $alreadyReceived = (float)$dcItem['quantity_received_kg'];
            $alreadyWasted = (float)$dcItem['quantity_wastage_kg'];
            $pending = $issued - ($alreadyReceived + $alreadyWasted);

            if ($qtyReceived + $qtyWastage > $pending) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Error: Total cannot exceed pending.');
            }

            $conesReceived = (int)($item['cones_received'] ?? 0);
            $conesIssued = (int)$dcItem['cones_issued'];
            $conesRecdExcludingCurrent = (int)$dcItem['cones_received'];
            $conesUsedExcludingCurrent = (int)$dcItem['cones_used'];
            $pendingCones = $conesIssued - ($conesRecdExcludingCurrent + $conesUsedExcludingCurrent);
            $conesUsed = max(0, $pendingCones - $conesReceived);

            $this->receiptItemModel->save([
                'receipt_id'           => $id,
                'dc_item_id'           => $dcItemId,
                'quantity_received_kg' => $qtyReceived,
                'quantity_wastage_kg'  => $qtyWastage,
                'cones_received'       => $conesReceived,
                'cones_used'           => $conesUsed,
                'job_work_charges'     => $jobCharges,
            ]);

            $newReceived = (float)$dcItem['quantity_received_kg'] + $qtyReceived;
            $newWastage = (float)$dcItem['quantity_wastage_kg'] + $qtyWastage;
            $newConesReceived = (int)$dcItem['cones_received'] + $conesReceived;
            $newConesUsed = (int)$dcItem['cones_used'] + $conesUsed;
            
            $this->dcItemModel->update($dcItemId, [
                'quantity_received_kg' => $newReceived,
                'quantity_wastage_kg'  => $newWastage,
                'cones_received'       => $newConesReceived,
                'cones_used'           => $newConesUsed
            ]);

            $issuanceQuery = $this->movementModel->where('movement_type', 'Issue_Job_Work')
                                                 ->where('reference_id', $dcId)
                                                 ->where('yarn_count', $dcItem['yarn_count'])
                                                 ->where('brand_mill', $dcItem['mill_name'])
                                                 ->where('color', $dcItem['current_color'] ?: 'Raw')
                                                 ->where('yarn_type', $dcItem['yarn_type'])
                                                 ->where('warp_weft', $dcItem['warp_weft']);
            if (!empty($dcItem['lot_number'])) {
                $issuanceQuery->where('lot_number', $dcItem['lot_number']);
            }
            if (!empty($dcItem['csp'])) {
                $issuanceQuery->where('csp', $dcItem['csp']);
            }
            $issuanceMovement = $issuanceQuery->first();
            $rawCostPerKg = $issuanceMovement ? abs((float)$issuanceMovement['cost_per_kg']) : 0;

            $itemSharedExpense = $totalReceivedWeight > 0 ? (($qtyReceived / $totalReceivedWeight) * $totalSharedExpenses) : 0;
            $itemSharedJobWork = $totalReceivedWeight > 0 ? (($qtyReceived / $totalReceivedWeight) * $jobCharges) : 0;

            $proRatedRawCostConsumed = ($qtyReceived + $qtyWastage) * $rawCostPerKg;
            $itemTotalLandedCost = $proRatedRawCostConsumed + $itemSharedJobWork + $itemSharedExpense;
            $finalLandedCostPerKg = $qtyReceived > 0 ? ($itemTotalLandedCost / $qtyReceived) : 0;

            if ($qtyReceived > 0) {
                $this->movementModel->save([
                    'yarn_name'     => 'Yarn (' . $dcItem['yarn_count'] . ')',
                    'yarn_count'    => $dcItem['yarn_count'],
                    'yarn_type'     => $dcItem['yarn_type'],
                    'color'         => $receivedColor,
                    'brand_mill'    => $dcItem['mill_name'],
                    'lot_number'    => $dcItem['lot_number'],
                    'csp'           => $dcItem['csp'],
                    'warp_weft'     => $dcItem['warp_weft'],
                    'quantity_kg'   => $qtyReceived,
                    'cost_per_kg'   => $finalLandedCostPerKg,
                    'warehouse'     => 'Main Warehouse',
                    'movement_type' => 'Receipt_Job_Work',
                    'reference_id'  => $id,
                    'remarks'       => 'Received from ' . $dc['vendor_name'] . ' against ' . $dc['dc_number'] . ' (Updated)',
                    'created_by'    => session('user_id'),
                    'created_at'    => date('Y-m-d H:i:s')
                ]);
            }
        }

        // Process returned beams
        $beamsReturn = $this->request->getPost('beams_return') ?: [];
        if (!empty($beamsReturn) && is_array($beamsReturn)) {
            $yarnDetailsList = [];
            foreach ($items as $dcItemId => $item) {
                if ((float)($item['quantity_received_kg'] ?? 0) > 0) {
                    $dcItem = $this->dcItemModel->find($dcItemId);
                    $yarnDetailsList[] = $dcItem['yarn_count'] . ' (' . ($item['received_color'] ?: 'Raw') . ')';
                }
            }
            $yarnDetailsStr = implode(', ', array_unique($yarnDetailsList));

            foreach ($beamsReturn as $beamNum => $beamData) {
                $returnStatus = $beamData['status'] ?? 'Not Returned';
                if ($returnStatus === 'Not Returned') {
                    continue;
                }

                $meters = ($returnStatus === 'Loaded') ? (float)($beamData['meters'] ?? 0) : null;
                $sizingNo = ($returnStatus === 'Loaded') ? ($beamData['sizing_no'] ?? null) : null;
                $beamColor = ($returnStatus === 'Loaded') ? ($beamData['color'] ?? null) : null;
                $returnDate = ($returnStatus === 'Loaded') ? ($beamData['return_date'] ?? null) : null;
                $ends = ($returnStatus === 'Loaded') ? (int)($beamData['ends'] ?? 0) : null;

                $this->dcBeamModel->where('dc_id', $dcId)->where('beam_number', $beamNum)->set([
                    'receipt_id'      => $id,
                    'returned_status' => $returnStatus,
                    'ends'            => $ends,
                    'meters'          => $meters,
                    'sizing_no'       => $sizingNo,
                    'color'           => $beamColor,
                    'return_date'     => $returnDate
                ])->update();

                $beamRow = $this->yBeamModel->where('beam_number', $beamNum)->first();
                if ($beamRow) {
                    $this->yBeamModel->update($beamRow['id'], [
                        'location'       => 'In-House',
                        'current_holder' => 'In-House',
                        'status'         => $returnStatus
                    ]);

                    $ledgerRemarks = 'Received ' . strtolower($returnStatus) . ' from warping & sizing on Receipt ' . $receiptData['receipt_number'] . ' (Updated)';
                    if ($returnStatus === 'Loaded') {
                        $ledgerRemarks .= ". Sizing No: {$sizingNo}, Color: {$beamColor}, Length: {$meters} M";
                    }

                    $this->yBeamLedgerModel->save([
                        'beam_id'          => $beamRow['id'],
                        'transaction_date' => $receiptData['receipt_date'],
                        'transaction_type' => 'Receipt_Warping_Sizing',
                        'reference_id'     => $id,
                        'from_location'    => $dc['vendor_name'],
                        'to_location'      => 'In-House',
                        'status_from'      => 'Empty',
                        'status_to'        => $returnStatus,
                        'yarn_details'     => ($returnStatus === 'Loaded') ? $yarnDetailsStr : '',
                        'remarks'          => $ledgerRemarks,
                        'created_by'       => session('user_id')
                    ]);
                }
            }
        }

        // Check if DC is fully completed
        $allDcItems = $this->dcItemModel->where('dc_id', $dcId)->findAll();
        $isCompleted = true;
        foreach ($allDcItems as $item) {
            $totalAccounted = (float)$item['quantity_received_kg'] + (float)$item['quantity_wastage_kg'];
            if ($totalAccounted < (float)$item['quantity_issued_kg']) {
                $isCompleted = false;
                break;
            }
        }

        $this->dcModel->update($dcId, [
            'status' => $isCompleted ? 'Completed' : 'Partially Received'
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update receipt.');
        }

        return redirect()->to('production/yarn-warping-sizing/view/' . $dcId)->with('success', 'Receipt updated successfully.');
    }

    public function receiptDelete($id)
    {
        $receipt = $this->receiptModel->find($id);
        if (!$receipt) {
            return redirect()->back()->with('error', 'Receipt not found.');
        }

        $dcId = $receipt['dc_id'];
        $receiptItems = $this->receiptItemModel->where('receipt_id', $id)->findAll();

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($receiptItems as $ri) {
            $dcItem = $this->dcItemModel->find($ri['dc_item_id']);
            if ($dcItem) {
                $newReceived = max(0, (float)$dcItem['quantity_received_kg'] - (float)$ri['quantity_received_kg']);
                $newWastage = max(0, (float)$dcItem['quantity_wastage_kg'] - (float)$ri['quantity_wastage_kg']);
                $newConesRecd = max(0, (int)$dcItem['cones_received'] - (int)($ri['cones_received'] ?? 0));
                $newConesUsed = max(0, (int)$dcItem['cones_used'] - (int)($ri['cones_used'] ?? 0));
                
                $this->dcItemModel->skipValidation(true);
                $this->dcItemModel->update($ri['dc_item_id'], [
                    'quantity_received_kg' => $newReceived,
                    'quantity_wastage_kg'  => $newWastage,
                    'cones_received'       => $newConesRecd,
                    'cones_used'           => $newConesUsed
                ]);
            }
        }

        $this->movementModel->where('movement_type', 'Receipt_Job_Work')
                            ->where('reference_id', $id)
                            ->delete();

        // Revert received beams associated with this receipt
        $oldReceivedBeams = $this->dcBeamModel->where('receipt_id', $id)->findAll();
        foreach ($oldReceivedBeams as $oldBeam) {
            $beamRow = $this->yBeamModel->where('beam_number', $oldBeam['beam_number'])->first();
            if ($beamRow) {
                $this->yBeamModel->update($beamRow['id'], [
                    'location'       => 'At Job Work',
                    'current_holder' => $this->dcModel->find($dcId)['vendor_name'] ?? 'Job Worker',
                    'status'         => 'Empty'
                ]);
            }
        }
        $this->dcBeamModel->where('receipt_id', $id)->set([
            'receipt_id'      => null,
            'returned_status' => null,
            'meters'          => null,
            'sizing_no'       => null,
            'color'           => null,
            'return_date'     => null
        ])->update();
        
        $this->yBeamLedgerModel->where('reference_id', $id)->where('transaction_type', 'Receipt_Warping_Sizing')->delete();

        $this->receiptItemModel->where('receipt_id', $id)->delete();
        $this->receiptModel->delete($id);

        $remainingReceipts = $this->receiptModel->where('dc_id', $dcId)->countAllResults();
        $newStatus = 'Open';
        if ($remainingReceipts > 0) {
            $allDcItems = $this->dcItemModel->where('dc_id', $dcId)->findAll();
            $isCompleted = true;
            foreach ($allDcItems as $item) {
                $totalAccounted = (float)$item['quantity_received_kg'] + (float)$item['quantity_wastage_kg'];
                if ($totalAccounted < (float)$item['quantity_issued_kg']) {
                    $isCompleted = false;
                    break;
                }
            }
            $newStatus = $isCompleted ? 'Completed' : 'Partially Received';
        }

        $this->dcModel->skipValidation(true);
        $this->dcModel->update($dcId, [
            'status' => $newStatus
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to delete receipt.');
        }

        return redirect()->to('production/yarn-warping-sizing/view/' . $dcId)->with('success', 'Receipt deleted and inventory reverted successfully.');
    }
}
