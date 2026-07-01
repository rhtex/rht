<?php

namespace App\Controllers;

use App\Models\YarnDyeingDcModel;
use App\Models\YarnDyeingDcItemModel;
use App\Models\YarnDyeingReceiptModel;
use App\Models\YarnDyeingReceiptItemModel;
use App\Models\YarnStockMovementModel;

class YarnDyeingController extends BaseController
{
    protected $dcModel;
    protected $dcItemModel;
    protected $receiptModel;
    protected $receiptItemModel;
    protected $movementModel;

    public function __construct()
    {
        $this->dcModel = new YarnDyeingDcModel();
        $this->dcItemModel = new YarnDyeingDcItemModel();
        $this->receiptModel = new YarnDyeingReceiptModel();
        $this->receiptItemModel = new YarnDyeingReceiptItemModel();
        $this->movementModel = new YarnStockMovementModel();
    }

    public function index()
    {
        $data['dcs'] = $this->dcModel->orderBy('id', 'DESC')->findAll();
        $data['title'] = 'Yarn Dyeing Job Work';
        return view('production/yarn_dyeing/index', $data);
    }

    public function create()
    {
        $data['availableYarns'] = $this->movementModel->getInventory(['yarn_type' => 'Raw']);
        
        $colorModel = new \App\Models\ColorModel();
        $data['colorsList'] = $colorModel->where('status', 'Active')->orderBy('name', 'ASC')->findAll();

        $lastDc = $this->dcModel->orderBy('id', 'DESC')->first();
        $nextNum = $lastDc ? ((int)str_replace('DC-DYE-', '', $lastDc['dc_number'])) + 1 : 1001;
        $data['nextDcNumber'] = 'DC-DYE-' . $nextNum;

        $data['title'] = 'Create Dyeing Delivery Challan';
        return view('production/yarn_dyeing/dc_form', $data);
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

        $items = $this->request->getPost('items');
        if (empty($items) || !is_array($items)) {
            return redirect()->back()->withInput()->with('error', 'At least one yarn item must be added.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->dcModel->skipValidation(true);
        $this->dcModel->save($dcData);
        $dcId = $this->dcModel->getInsertID();

        foreach ($items as $item) {
            $itemData = [
                'dc_id'              => $dcId,
                'mill_name'          => $item['mill_name'],
                'yarn_count'         => $item['yarn_count'],
                'warp_weft'          => $item['warp_weft'],
                'csp'                => $item['csp'] ?: null,
                'lot_number'         => $item['lot_number'] ?: null,
                'yarn_type'          => $item['yarn_type'],
                'current_color'      => $item['current_color'] ?: 'Raw',
                'required_color'     => $item['required_color'] ?? null,
                'quantity_issued_kg' => (float)$item['quantity_issued_kg'],
                'cones_issued'       => (int)($item['cones_issued'] ?? 0),
            ];

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
                'quantity_cones'=> -((int)($item['cones_issued'] ?? 0)),
                'cost_per_kg'   => $costPerKg,
                'warehouse'     => 'Main Warehouse',
                'movement_type' => 'Issue_Job_Work',
                'reference_id'  => $dcId,
                'remarks'       => 'Issued for Dyeing to ' . $dcData['vendor_name'] . ' (DC: ' . $dcData['dc_number'] . ')',
                'created_by'    => session('user_id'),
                'created_at'    => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to save Delivery Challan.');
        }

        return redirect()->to('production/yarn-dyeing')->with('success', 'Delivery Challan created successfully.');
    }

    public function view($id)
    {
        $data['dc'] = $this->dcModel->find($id);
        if (!$data['dc']) {
            return redirect()->to('production/yarn-dyeing')->with('error', 'Delivery Challan not found.');
        }

        $data['items'] = $this->dcItemModel->where('dc_id', $id)->findAll();
        $data['title'] = 'Dyeing Delivery Challan: ' . $data['dc']['dc_number'];

        $data['receipts'] = $this->receiptModel->where('dc_id', $id)->findAll();
        foreach ($data['receipts'] as &$r) {
            $r['items'] = $this->receiptItemModel
                ->select('production_yarn_dyeing_receipt_items.*, production_yarn_dyeing_dc_items.mill_name, production_yarn_dyeing_dc_items.yarn_count, production_yarn_dyeing_dc_items.warp_weft, production_yarn_dyeing_dc_items.lot_number, production_yarn_dyeing_dc_items.csp, production_yarn_dyeing_dc_items.yarn_type, production_yarn_dyeing_dc_items.current_color')
                ->join('production_yarn_dyeing_dc_items', 'production_yarn_dyeing_dc_items.id = production_yarn_dyeing_receipt_items.dc_item_id')
                ->where('receipt_id', $r['id'])
                ->findAll();

            foreach ($r['items'] as &$ri) {
                // Fetch the cost per kg recorded for this receipt in stock movements ledger
                $sm = $this->movementModel
                    ->where('movement_type', 'Receipt_Job_Work')
                    ->where('reference_id', $r['id'])
                    ->where('yarn_count', $ri['yarn_count'])
                    ->where('brand_mill', $ri['mill_name'])
                    ->where('color', $ri['received_color'])
                    ->first();
                $ri['landed_cost_per_kg'] = $sm ? (float)$sm['cost_per_kg'] : 0.00;
            }
        }

        return view('production/yarn_dyeing/dc_view', $data);
    }

    public function edit($id)
    {
        $data['dc'] = $this->dcModel->find($id);
        if (!$data['dc']) {
            return redirect()->to('production/yarn-dyeing')->with('error', 'Delivery Challan not found.');
        }

        $receiptsCount = $this->receiptModel->where('dc_id', $id)->countAllResults();
        if ($receiptsCount > 0) {
            return redirect()->to('production/yarn-dyeing')->with('error', 'Cannot edit because receipts exist.');
        }

        $data['dcItems'] = $this->dcItemModel->where('dc_id', $id)->findAll();
        $data['availableYarns'] = $this->movementModel->getInventory(['yarn_type' => 'Raw']);

        $colorModel = new \App\Models\ColorModel();
        $data['colorsList'] = $colorModel->where('status', 'Active')->orderBy('name', 'ASC')->findAll();

        $data['title'] = 'Edit Dyeing Delivery Challan';
        
        return view('production/yarn_dyeing/dc_form', $data);
    }

    public function update($id)
    {
        $dc = $this->dcModel->find($id);
        if (!$dc) {
            return redirect()->to('production/yarn-dyeing')->with('error', 'DC not found.');
        }

        $receiptsCount = $this->receiptModel->where('dc_id', $id)->countAllResults();
        if ($receiptsCount > 0) {
            return redirect()->to('production/yarn-dyeing')->with('error', 'Cannot update because receipts exist.');
        }

        $dcData = [
            'dc_number'            => $this->request->getPost('dc_number'),
            'dc_date'              => $this->request->getPost('dc_date'),
            'vendor_name'          => $this->request->getPost('vendor_name'),
            'expected_return_date' => $this->request->getPost('expected_return_date') ?: null,
            'vehicle_details'      => $this->request->getPost('vehicle_details'),
            'remarks'              => $this->request->getPost('remarks'),
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

        $items = $this->request->getPost('items');
        if (empty($items) || !is_array($items)) {
            return redirect()->back()->withInput()->with('error', 'At least one yarn item must be added.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->dcModel->skipValidation(true);
        $this->dcModel->update($id, $dcData);

        $this->dcItemModel->where('dc_id', $id)->delete();
        $this->movementModel->where('movement_type', 'Issue_Job_Work')->where('reference_id', $id)->delete();

        foreach ($items as $item) {
            $itemData = [
                'dc_id'              => $id,
                'mill_name'          => $item['mill_name'],
                'yarn_count'         => $item['yarn_count'],
                'warp_weft'          => $item['warp_weft'],
                'csp'                => $item['csp'] ?: null,
                'lot_number'         => $item['lot_number'] ?: null,
                'yarn_type'          => $item['yarn_type'],
                'current_color'      => $item['current_color'] ?: 'Raw',
                'required_color'     => $item['required_color'] ?? null,
                'quantity_issued_kg' => (float)$item['quantity_issued_kg'],
                'cones_issued'       => (int)($item['cones_issued'] ?? 0),
            ];

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
                'quantity_cones'=> -((int)($item['cones_issued'] ?? 0)),
                'cost_per_kg'   => $costPerKg,
                'warehouse'     => 'Main Warehouse',
                'movement_type' => 'Issue_Job_Work',
                'reference_id'  => $id,
                'remarks'       => 'Issued for Dyeing to ' . $dcData['vendor_name'] . ' (DC: ' . $dcData['dc_number'] . ')',
                'created_by'    => session('user_id'),
                'created_at'    => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update Delivery Challan.');
        }

        return redirect()->to('production/yarn-dyeing')->with('success', 'Delivery Challan updated successfully.');
    }

    public function delete($id)
    {
        $dc = $this->dcModel->find($id);
        if (!$dc) {
            return redirect()->to('production/yarn-dyeing')->with('error', 'Delivery Challan not found.');
        }

        $receiptsCount = $this->receiptModel->where('dc_id', $id)->countAllResults();
        if ($receiptsCount > 0) {
            return redirect()->to('production/yarn-dyeing')->with('error', 'Cannot delete because receipts exist.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->dcItemModel->where('dc_id', $id)->delete();
        $this->movementModel->where('movement_type', 'Issue_Job_Work')->where('reference_id', $id)->delete();
        $this->dcModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('production/yarn-dyeing')->with('error', 'Failed to delete Delivery Challan.');
        }

        return redirect()->to('production/yarn-dyeing')->with('success', 'Delivery Challan deleted successfully.');
    }

    public function receiptCreate($dcId)
    {
        $data['dc'] = $this->dcModel->find($dcId);
        if (!$data['dc']) {
            return redirect()->to('production/yarn-dyeing')->with('error', 'DC not found.');
        }

        $data['items'] = $this->dcItemModel->where('dc_id', $dcId)->findAll();
        
        $colorModel = new \App\Models\ColorModel();
        $data['colorsList'] = $colorModel->where('status', 'Active')->orderBy('name', 'ASC')->findAll();

        $lastRec = $this->receiptModel->orderBy('id', 'DESC')->first();
        $nextNum = $lastRec ? ((int)str_replace('REC-DYE-', '', $lastRec['receipt_number'])) + 1 : 1001;
        $data['nextReceiptNumber'] = 'REC-DYE-' . $nextNum;

        $data['title'] = 'Receive Dyed Yarn Stock';
        return view('production/yarn_dyeing/receipt_form', $data);
    }

    public function receiptStore($dcId)
    {
        $dc = $this->dcModel->find($dcId);
        if (!$dc) {
            return redirect()->to('production/yarn-dyeing')->with('error', 'DC not found.');
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

        foreach ($items as $dcItemId => $item) {
            $dcItem = $this->dcItemModel->find($dcItemId);
            
            $qtyReceived = (float)($item['quantity_received_kg'] ?? 0);
            $qtyWastage = (float)($item['quantity_wastage_kg'] ?? 0);
            $jobCharges = (float)($item['job_work_charges'] ?? 0);
            $receivedColor = $item['received_color'] ?: 'Raw';
            $dyeingLotNumber = $item['dyeing_lot_number'] ?? null;
            $conesReceived = (int)($item['cones_received'] ?? 0);
            $conesWastage = (int)($item['cones_wastage'] ?? 0);

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

            $this->receiptItemModel->skipValidation(true);
            $this->receiptItemModel->save([
                'receipt_id'           => $receiptId,
                'dc_item_id'           => $dcItemId,
                'received_color'       => $receivedColor,
                'quantity_received_kg' => $qtyReceived,
                'quantity_wastage_kg'  => $qtyWastage,
                'job_work_charges'     => $jobCharges,
                'cones_received'       => $conesReceived,
                'cones_wastage'        => $conesWastage,
            ]);

            $newReceived = (float)$dcItem['quantity_received_kg'] + $qtyReceived;
            $newWastage = (float)$dcItem['quantity_wastage_kg'] + $qtyWastage;
            $newConesRecd = (int)$dcItem['cones_received'] + $conesReceived;
            $newConesWaste = (int)$dcItem['cones_wastage'] + $conesWastage;
            
            $this->dcItemModel->skipValidation(true);
            $this->dcItemModel->update($dcItemId, [
                'quantity_received_kg' => $newReceived,
                'quantity_wastage_kg'  => $newWastage,
                'cones_received'       => $newConesRecd,
                'cones_wastage'        => $newConesWaste
            ]);

            $issuanceMovement = $this->movementModel->where('movement_type', 'Issue_Job_Work')
                                                   ->where('reference_id', $dcId)
                                                   ->where('yarn_count', $dcItem['yarn_count'])
                                                   ->where('brand_mill', $dcItem['mill_name'])
                                                   ->first();
            $rawCostPerKg = $issuanceMovement ? abs((float)$issuanceMovement['cost_per_kg']) : 0;

            $itemSharedExpense = $totalReceivedWeight > 0 ? (($qtyReceived / $totalReceivedWeight) * $totalSharedExpenses) : 0;

            $proRatedRawCostConsumed = ($qtyReceived + $qtyWastage) * $rawCostPerKg;
            $itemTotalLandedCost = $proRatedRawCostConsumed + (($qtyReceived + $qtyWastage) * $jobCharges) + $itemSharedExpense;
            $finalLandedCostPerKg = $qtyReceived > 0 ? ($itemTotalLandedCost / $qtyReceived) : 0;

            if ($qtyReceived > 0) {
                $this->movementModel->save([
                    'yarn_name'     => 'Yarn (' . $dcItem['yarn_count'] . ')',
                    'yarn_count'    => $dcItem['yarn_count'],
                    'yarn_type'     => 'Dyed',
                    'color'         => $receivedColor,
                    'brand_mill'    => $dcItem['mill_name'],
                    'lot_number'    => $dyeingLotNumber ?: $dcItem['lot_number'],
                    'csp'           => $dcItem['csp'],
                    'warp_weft'     => $dcItem['warp_weft'],
                    'quantity_kg'   => $qtyReceived,
                    'cost_per_kg'   => $finalLandedCostPerKg,
                    'warehouse'     => 'Main Warehouse',
                    'movement_type' => 'Receipt_Job_Work',
                    'reference_id'  => $receiptId,
                    'remarks'       => 'Received Dyed from ' . $dc['vendor_name'] . ' against ' . $dc['dc_number'] . ' (Color: ' . $receivedColor . ')',
                    'created_by'    => session('user_id'),
                    'created_at'    => date('Y-m-d H:i:s')
                ]);
            }
        }

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

        return redirect()->to('production/yarn-dyeing/view/' . $dcId)->with('success', 'Yarn received successfully.');
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
                $newConesWaste = max(0, (int)$dcItem['cones_wastage'] - (int)($ri['cones_wastage'] ?? 0));
                
                $this->dcItemModel->skipValidation(true);
                $this->dcItemModel->update($ri['dc_item_id'], [
                    'quantity_received_kg' => $newReceived,
                    'quantity_wastage_kg'  => $newWastage,
                    'cones_received'       => $newConesRecd,
                    'cones_wastage'        => $newConesWaste
                ]);
            }
        }

        $this->movementModel->where('movement_type', 'Receipt_Job_Work')
                            ->where('reference_id', $id)
                            ->delete();

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

        return redirect()->to('production/yarn-dyeing/view/' . $dcId)->with('success', 'Receipt deleted and inventory reverted successfully.');
    }

    public function receiptView($id)
    {
        $data['receipt'] = $this->receiptModel->find($id);
        if (!$data['receipt']) {
            return redirect()->back()->with('error', 'Receipt not found.');
        }

        $dcId = $data['receipt']['dc_id'];
        $data['dc'] = $this->dcModel->find($dcId);
        
        $data['items'] = $this->receiptItemModel
            ->select('production_yarn_dyeing_receipt_items.*, production_yarn_dyeing_dc_items.mill_name, production_yarn_dyeing_dc_items.yarn_count, production_yarn_dyeing_dc_items.warp_weft, production_yarn_dyeing_dc_items.lot_number, production_yarn_dyeing_dc_items.csp, production_yarn_dyeing_dc_items.yarn_type, production_yarn_dyeing_dc_items.current_color')
            ->join('production_yarn_dyeing_dc_items', 'production_yarn_dyeing_dc_items.id = production_yarn_dyeing_receipt_items.dc_item_id')
            ->where('receipt_id', $id)
            ->findAll();

        foreach ($data['items'] as &$ri) {
            $sm = $this->movementModel
                ->where('movement_type', 'Receipt_Job_Work')
                ->where('reference_id', $id)
                ->where('yarn_count', $ri['yarn_count'])
                ->where('brand_mill', $ri['mill_name'])
                ->where('color', $ri['received_color'])
                ->first();
            $ri['landed_cost_per_kg'] = $sm ? (float)$sm['cost_per_kg'] : 0.00;
        }

        $colorModel = new \App\Models\ColorModel();
        $colors = $colorModel->where('status', 'Active')->findAll();
        $data['colorsList'] = [];
        foreach ($colors as $cl) {
            $data['colorsList'][$cl['name']] = $cl['color_palette'] ?: '#3498db';
        }

        $data['title'] = 'View Dyeing Receipt: ' . $data['receipt']['receipt_number'];
        return view('production/yarn_dyeing/receipt_view', $data);
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
        
        $colorModel = new \App\Models\ColorModel();
        $data['colorsList'] = $colorModel->where('status', 'Active')->orderBy('name', 'ASC')->findAll();

        // Key receipt item inputs by dc_item_id to pre-populate form values
        $receiptItems = $this->receiptItemModel->where('receipt_id', $id)->findAll();
        $data['receiptItemsKeyed'] = [];
        foreach ($receiptItems as $ri) {
            // Find lot_number from stock movements ledger for this receipt
            $sm = $this->movementModel
                ->where('movement_type', 'Receipt_Job_Work')
                ->where('reference_id', $id)
                ->where('yarn_count', $ri['yarn_count'] ?? '')
                ->where('brand_mill', $ri['mill_name'] ?? '')
                ->first();
            
            // If the join wasn't loaded in model, load parent dc item details to search movement accurately
            if (!isset($ri['yarn_count'])) {
                $dcItem = $this->dcItemModel->find($ri['dc_item_id']);
                if ($dcItem) {
                    $sm = $this->movementModel
                        ->where('movement_type', 'Receipt_Job_Work')
                        ->where('reference_id', $id)
                        ->where('yarn_count', $dcItem['yarn_count'])
                        ->where('brand_mill', $dcItem['mill_name'])
                        ->first();
                }
            }
            $ri['dyeing_lot_number'] = $sm ? $sm['lot_number'] : '';
            $data['receiptItemsKeyed'][$ri['dc_item_id']] = $ri;
        }

        $data['isEdit'] = true;
        $data['title'] = 'Edit Dyeing Receipt: ' . $data['receipt']['receipt_number'];
        return view('production/yarn_dyeing/receipt_form', $data);
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

        // 1. Revert previous receipt quantities in DC Items
        $oldItems = $this->receiptItemModel->where('receipt_id', $id)->findAll();
        foreach ($oldItems as $oi) {
            $dcItem = $this->dcItemModel->find($oi['dc_item_id']);
            if ($dcItem) {
                $revertedRecd = max(0, (float)$dcItem['quantity_received_kg'] - (float)$oi['quantity_received_kg']);
                $revertedWaste = max(0, (float)$dcItem['quantity_wastage_kg'] - (float)$oi['quantity_wastage_kg']);
                $revertedCones = max(0, (int)$dcItem['cones_received'] - (int)$oi['cones_received']);
                
                $this->dcItemModel->skipValidation(true);
                $this->dcItemModel->update($oi['dc_item_id'], [
                    'quantity_received_kg' => $revertedRecd,
                    'quantity_wastage_kg'  => $revertedWaste,
                    'cones_received'       => $revertedCones
                ]);
            }
        }

        // Delete previous receipt items and matching stock movements
        $this->movementModel->where('movement_type', 'Receipt_Job_Work')->where('reference_id', $id)->delete();
        $this->receiptItemModel->where('receipt_id', $id)->delete();

        // 2. Save Updated Receipt Details
        $receiptData = [
            'id'                => $id,
            'receipt_date'      => $this->request->getPost('receipt_date'),
            'transport_charges' => (float)($this->request->getPost('transport_charges') ?? 0),
            'loading_charges'   => (float)($this->request->getPost('loading_charges') ?? 0),
            'packing_charges'   => (float)($this->request->getPost('packing_charges') ?? 0),
            'other_expenses'    => (float)($this->request->getPost('other_expenses') ?? 0),
            'remarks'           => $this->request->getPost('remarks'),
        ];

        $this->receiptModel->skipValidation(true);
        $this->receiptModel->save($receiptData);

        $items = $this->request->getPost('items');
        $totalSharedExpenses = $receiptData['transport_charges'] + $receiptData['loading_charges'] + $receiptData['packing_charges'] + $receiptData['other_expenses'];
        $totalReceivedWeight = 0;

        foreach ($items as $dcItemId => $item) {
            $totalReceivedWeight += (float)($item['quantity_received_kg'] ?? 0);
        }

        foreach ($items as $dcItemId => $item) {
            $dcItem = $this->dcItemModel->find($dcItemId);
            
            $qtyReceived = (float)($item['quantity_received_kg'] ?? 0);
            $qtyWastage = (float)($item['quantity_wastage_kg'] ?? 0);
            $jobCharges = (float)($item['job_work_charges'] ?? 0);
            $receivedColor = $item['received_color'] ?: 'Raw';
            $dyeingLotNumber = $item['dyeing_lot_number'] ?? null;
            $conesReceived = (int)($item['cones_received'] ?? 0);
            $conesWastage = (int)($item['cones_wastage'] ?? 0);

            if ($qtyReceived <= 0 && $qtyWastage <= 0) {
                continue;
            }

            $this->receiptItemModel->skipValidation(true);
            $this->receiptItemModel->save([
                'receipt_id'           => $id,
                'dc_item_id'           => $dcItemId,
                'received_color'       => $receivedColor,
                'quantity_received_kg' => $qtyReceived,
                'quantity_wastage_kg'  => $qtyWastage,
                'job_work_charges'     => $jobCharges,
                'cones_received'       => $conesReceived,
                'cones_wastage'        => $conesWastage,
            ]);

            $newReceived = (float)$dcItem['quantity_received_kg'] + $qtyReceived;
            $newWastage = (float)$dcItem['quantity_wastage_kg'] + $qtyWastage;
            $newConesRecd = (int)$dcItem['cones_received'] + $conesReceived;
            
            $this->dcItemModel->skipValidation(true);
            $this->dcItemModel->update($dcItemId, [
                'quantity_received_kg' => $newReceived,
                'quantity_wastage_kg'  => $newWastage,
                'cones_received'       => $newConesRecd
            ]);

            $issuanceMovement = $this->movementModel->where('movement_type', 'Issue_Job_Work')
                                                   ->where('reference_id', $dcId)
                                                   ->where('yarn_count', $dcItem['yarn_count'])
                                                   ->where('brand_mill', $dcItem['mill_name'])
                                                   ->first();
            $rawCostPerKg = $issuanceMovement ? abs((float)$issuanceMovement['cost_per_kg']) : 0;

            $itemSharedExpense = $totalReceivedWeight > 0 ? (($qtyReceived / $totalReceivedWeight) * $totalSharedExpenses) : 0;

            $proRatedRawCostConsumed = ($qtyReceived + $qtyWastage) * $rawCostPerKg;
            $itemTotalLandedCost = $proRatedRawCostConsumed + (($qtyReceived + $qtyWastage) * $jobCharges) + $itemSharedExpense;
            $finalLandedCostPerKg = $qtyReceived > 0 ? ($itemTotalLandedCost / $qtyReceived) : 0;

            if ($qtyReceived > 0) {
                $this->movementModel->save([
                    'yarn_name'     => 'Yarn (' . $dcItem['yarn_count'] . ')',
                    'yarn_count'    => $dcItem['yarn_count'],
                    'yarn_type'     => 'Dyed',
                    'color'         => $receivedColor,
                    'brand_mill'    => $dcItem['mill_name'],
                    'lot_number'    => $dyeingLotNumber ?: $dcItem['lot_number'],
                    'csp'           => $dcItem['csp'],
                    'warp_weft'     => $dcItem['warp_weft'],
                    'quantity_kg'   => $qtyReceived,
                    'cost_per_kg'   => $finalLandedCostPerKg,
                    'warehouse'     => 'Main Warehouse',
                    'movement_type' => 'Receipt_Job_Work',
                    'reference_id'  => $id,
                    'remarks'       => 'Received Dyed from ' . $dc['vendor_name'] . ' against ' . $dc['dc_number'] . ' (Color: ' . $receivedColor . ')',
                    'created_by'    => session('user_id'),
                    'created_at'    => date('Y-m-d H:i:s')
                ]);
            }
        }

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
            return redirect()->back()->withInput()->with('error', 'Failed to update receipt.');
        }

        return redirect()->to('production/yarn-dyeing/view/' . $dcId)->with('success', 'Receipt updated successfully.');
    }
}
