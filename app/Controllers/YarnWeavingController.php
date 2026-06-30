<?php

namespace App\Controllers;

use App\Models\YarnWeavingDcModel;
use App\Models\YarnWeavingDcItemModel;
use App\Models\YarnWeavingReceiptModel;
use App\Models\YarnWeavingReceiptItemModel;
use App\Models\YarnStockMovementModel;

class YarnWeavingController extends BaseController
{
    protected $dcModel;
    protected $dcItemModel;
    protected $receiptModel;
    protected $receiptItemModel;
    protected $movementModel;

    public function __construct()
    {
        $this->dcModel = new YarnWeavingDcModel();
        $this->dcItemModel = new YarnWeavingDcItemModel();
        $this->receiptModel = new YarnWeavingReceiptModel();
        $this->receiptItemModel = new YarnWeavingReceiptItemModel();
        $this->movementModel = new YarnStockMovementModel();
    }

    public function index()
    {
        $data['dcs'] = $this->dcModel->orderBy('id', 'DESC')->findAll();
        $data['title'] = 'Yarn Weaving Job Work';
        return view('production/yarn_weaving/index', $data);
    }

    public function create()
    {
        $data['availableYarns'] = $this->movementModel->getInventory();
        
        $lastDc = $this->dcModel->orderBy('id', 'DESC')->first();
        $nextNum = $lastDc ? ((int)str_replace('DC-WEV-', '', $lastDc['dc_number'])) + 1 : 1001;
        $data['nextDcNumber'] = 'DC-WEV-' . $nextNum;

        $data['title'] = 'Create Weaving Delivery Challan';
        return view('production/yarn_weaving/dc_form', $data);
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
                'quantity_issued_kg' => (float)$item['quantity_issued_kg'],
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
                'cost_per_kg'   => $costPerKg,
                'warehouse'     => 'Main Warehouse',
                'movement_type' => 'Issue_Job_Work',
                'reference_id'  => $dcId,
                'remarks'       => 'Issued for Weaving to ' . $dcData['vendor_name'] . ' (DC: ' . $dcData['dc_number'] . ')',
                'created_by'    => session('user_id'),
                'created_at'    => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to save Delivery Challan.');
        }

        return redirect()->to('production/yarn-weaving')->with('success', 'Delivery Challan created successfully.');
    }

    public function view($id)
    {
        $data['dc'] = $this->dcModel->find($id);
        if (!$data['dc']) {
            return redirect()->to('production/yarn-weaving')->with('error', 'Delivery Challan not found.');
        }

        $data['items'] = $this->dcItemModel->where('dc_id', $id)->findAll();
        $data['title'] = 'Weaving Delivery Challan: ' . $data['dc']['dc_number'];

        $data['receipts'] = $this->receiptModel->where('dc_id', $id)->findAll();
        foreach ($data['receipts'] as &$r) {
            $r['items'] = $this->receiptItemModel
                ->select('production_yarn_weaving_receipt_items.*, production_yarn_weaving_dc_items.mill_name, production_yarn_weaving_dc_items.yarn_count, production_yarn_weaving_dc_items.warp_weft, production_yarn_weaving_dc_items.lot_number, production_yarn_weaving_dc_items.csp, production_yarn_weaving_dc_items.yarn_type, production_yarn_weaving_dc_items.current_color')
                ->join('production_yarn_weaving_dc_items', 'production_yarn_weaving_dc_items.id = production_yarn_weaving_receipt_items.dc_item_id')
                ->where('receipt_id', $r['id'])
                ->findAll();
        }

        return view('production/yarn_weaving/dc_view', $data);
    }

    public function edit($id)
    {
        $data['dc'] = $this->dcModel->find($id);
        if (!$data['dc']) {
            return redirect()->to('production/yarn-weaving')->with('error', 'Delivery Challan not found.');
        }

        $receiptsCount = $this->receiptModel->where('dc_id', $id)->countAllResults();
        if ($receiptsCount > 0) {
            return redirect()->to('production/yarn-weaving')->with('error', 'Cannot edit because receipts exist.');
        }

        $data['dcItems'] = $this->dcItemModel->where('dc_id', $id)->findAll();
        $data['availableYarns'] = $this->movementModel->getInventory();
        $data['title'] = 'Edit Weaving Delivery Challan';
        
        return view('production/yarn_weaving/dc_form', $data);
    }

    public function update($id)
    {
        $dc = $this->dcModel->find($id);
        if (!$dc) {
            return redirect()->to('production/yarn-weaving')->with('error', 'DC not found.');
        }

        $receiptsCount = $this->receiptModel->where('dc_id', $id)->countAllResults();
        if ($receiptsCount > 0) {
            return redirect()->to('production/yarn-weaving')->with('error', 'Cannot update because receipts exist.');
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
                'quantity_issued_kg' => (float)$item['quantity_issued_kg'],
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
                'cost_per_kg'   => $costPerKg,
                'warehouse'     => 'Main Warehouse',
                'movement_type' => 'Issue_Job_Work',
                'reference_id'  => $id,
                'remarks'       => 'Issued for Weaving to ' . $dcData['vendor_name'] . ' (DC: ' . $dcData['dc_number'] . ')',
                'created_by'    => session('user_id'),
                'created_at'    => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update Delivery Challan.');
        }

        return redirect()->to('production/yarn-weaving')->with('success', 'Delivery Challan updated successfully.');
    }

    public function delete($id)
    {
        $dc = $this->dcModel->find($id);
        if (!$dc) {
            return redirect()->to('production/yarn-weaving')->with('error', 'Delivery Challan not found.');
        }

        $receiptsCount = $this->receiptModel->where('dc_id', $id)->countAllResults();
        if ($receiptsCount > 0) {
            return redirect()->to('production/yarn-weaving')->with('error', 'Cannot delete because receipts exist.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->dcItemModel->where('dc_id', $id)->delete();
        $this->movementModel->where('movement_type', 'Issue_Job_Work')->where('reference_id', $id)->delete();
        $this->dcModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('production/yarn-weaving')->with('error', 'Failed to delete Delivery Challan.');
        }

        return redirect()->to('production/yarn-weaving')->with('success', 'Delivery Challan deleted successfully.');
    }

    public function receiptCreate($dcId)
    {
        $data['dc'] = $this->dcModel->find($dcId);
        if (!$data['dc']) {
            return redirect()->to('production/yarn-weaving')->with('error', 'DC not found.');
        }

        $data['items'] = $this->dcItemModel->where('dc_id', $dcId)->findAll();
        
        $lastRec = $this->receiptModel->orderBy('id', 'DESC')->first();
        $nextNum = $lastRec ? ((int)str_replace('REC-WEV-', '', $lastRec['receipt_number'])) + 1 : 1001;
        $data['nextReceiptNumber'] = 'REC-WEV-' . $nextNum;

        $data['title'] = 'Receive Woven Yarn Stock';
        return view('production/yarn_weaving/receipt_form', $data);
    }

    public function receiptStore($dcId)
    {
        $dc = $this->dcModel->find($dcId);
        if (!$dc) {
            return redirect()->to('production/yarn-weaving')->with('error', 'DC not found.');
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
                'quantity_received_kg' => $qtyReceived,
                'quantity_wastage_kg'  => $qtyWastage,
                'job_work_charges'     => $jobCharges,
            ]);

            $newReceived = (float)$dcItem['quantity_received_kg'] + $qtyReceived;
            $newWastage = (float)$dcItem['quantity_wastage_kg'] + $qtyWastage;
            
            $this->dcItemModel->skipValidation(true);
            $this->dcItemModel->update($dcItemId, [
                'quantity_received_kg' => $newReceived,
                'quantity_wastage_kg'  => $newWastage
            ]);

            $issuanceMovement = $this->movementModel->where('movement_type', 'Issue_Job_Work')
                                                   ->where('reference_id', $dcId)
                                                   ->where('yarn_count', $dcItem['yarn_count'])
                                                   ->where('brand_mill', $dcItem['mill_name'])
                                                   ->first();
            $rawCostPerKg = $issuanceMovement ? abs((float)$issuanceMovement['cost_per_kg']) : 0;

            $itemSharedExpense = $totalReceivedWeight > 0 ? (($qtyReceived / $totalReceivedWeight) * $totalSharedExpenses) : 0;

            $proRatedRawCostConsumed = ($qtyReceived + $qtyWastage) * $rawCostPerKg;
            $itemTotalLandedCost = $proRatedRawCostConsumed + ($qtyReceived * $jobCharges) + $itemSharedExpense;
            $finalLandedCostPerKg = $qtyReceived > 0 ? ($itemTotalLandedCost / $qtyReceived) : 0;

            if ($qtyReceived > 0) {
                $this->movementModel->save([
                    'yarn_name'     => 'Yarn (' . $dcItem['yarn_count'] . ')',
                    'yarn_count'    => $dcItem['yarn_count'],
                    'yarn_type'     => $dcItem['yarn_type'],
                    'color'         => $dcItem['current_color'],
                    'brand_mill'    => $dcItem['mill_name'],
                    'lot_number'    => $dcItem['lot_number'],
                    'csp'           => $dcItem['csp'],
                    'warp_weft'     => $dcItem['warp_weft'],
                    'quantity_kg'   => $qtyReceived,
                    'cost_per_kg'   => $finalLandedCostPerKg,
                    'warehouse'     => 'Main Warehouse',
                    'movement_type' => 'Receipt_Job_Work',
                    'reference_id'  => $receiptId,
                    'remarks'       => 'Received Woven from ' . $dc['vendor_name'] . ' against ' . $dc['dc_number'],
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

        return redirect()->to('production/yarn-weaving/view/' . $dcId)->with('success', 'Yarn received successfully.');
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
                
                $this->dcItemModel->skipValidation(true);
                $this->dcItemModel->update($ri['dc_item_id'], [
                    'quantity_received_kg' => $newReceived,
                    'quantity_wastage_kg'  => $newWastage
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

        return redirect()->to('production/yarn-weaving/view/' . $dcId)->with('success', 'Receipt deleted and inventory reverted successfully.');
    }
}
