<?php

namespace App\Controllers;

use App\Models\YarnJobWorkDcModel;
use App\Models\YarnJobWorkDcItemModel;
use App\Models\YarnJobWorkReceiptModel;
use App\Models\YarnJobWorkReceiptItemModel;
use App\Models\YarnStockMovementModel;

class YarnJobWorkController extends BaseController
{
    protected $beamModel;
    protected $colorEndsModel;
    protected $yBeamModel;
    protected $yBeamLedgerModel;

    public function __construct()
    {
        $this->dcModel = new YarnJobWorkDcModel();
        $this->dcItemModel = new YarnJobWorkDcItemModel();
        $this->receiptModel = new YarnJobWorkReceiptModel();
        $this->receiptItemModel = new YarnJobWorkReceiptItemModel();
        $this->movementModel = new YarnStockMovementModel();
        $this->beamModel = new \App\Models\YarnJobWorkDcBeamModel();
        $this->colorEndsModel = new \App\Models\YarnJobWorkDcColorEndsModel();
        $this->yBeamModel = new \App\Models\YarnBeamModel();
        $this->yBeamLedgerModel = new \App\Models\YarnBeamLedgerModel();
    }

    public function index()
    {
        $type = trim($this->request->getGet('type') ?: 'Dyeing');
        $data['selectedType'] = $type;

        if (in_array($type, ['Warping & Sizing', 'Warping', 'Sizing'])) {
            $data['dcs'] = $this->dcModel->whereIn('job_work_type', ['Warping & Sizing', 'Warping', 'Sizing'])->orderBy('id', 'DESC')->findAll();
        } else {
            $data['dcs'] = $this->dcModel->where('job_work_type', $type)->orderBy('id', 'DESC')->findAll();
        }

        $data['title'] = $type . ' Job Work';
        return view('production/yarn_job_work/dc_index', $data);
    }

    public function create()
    {
        $type = trim($this->request->getGet('type') ?: 'Dyeing');
        $data['selectedType'] = $type;

        // Fetch current available yarns and empty beams
        $data['availableYarns'] = $this->movementModel->getInventory();
        $data['availableBeams'] = $this->yBeamModel->where('status', 'Empty')->where('location', 'In-House')->where('condition_status', 'Active')->findAll();
        
        // Generate next DC Number
        $lastDc = $this->dcModel->orderBy('id', 'DESC')->first();
        $nextNum = $lastDc ? ((int)str_replace('DC-YARN-', '', $lastDc['dc_number'])) + 1 : 1001;
        $data['nextDcNumber'] = 'DC-YARN-' . $nextNum;

        $data['title'] = 'Create ' . $type . ' Delivery Challan';
        
        if (in_array($type, ['Warping & Sizing', 'Warping', 'Sizing'])) {
            return view('production/yarn_job_work/dc_form_warping_sizing', $data);
        } elseif ($type === 'Dyeing') {
            return view('production/yarn_job_work/dc_form_dyeing', $data);
        } else {
            return view('production/yarn_job_work/dc_form_generic', $data);
        }
    }

    public function store()
    {
        $dcData = [
            'dc_number'            => $this->request->getPost('dc_number'),
            'dc_date'              => $this->request->getPost('dc_date'),
            'vendor_name'          => $this->request->getPost('vendor_name'),
            'job_work_type'        => $this->request->getPost('job_work_type'),
            'expected_return_date' => $this->request->getPost('expected_return_date') ?: null,
            'vehicle_details'      => $this->request->getPost('vehicle_details'),
            'remarks'              => $this->request->getPost('remarks'),
            'design_pattern'       => $this->request->getPost('design_pattern') ?: null,
            'total_ends'           => $this->request->getPost('total_ends') ? (int)$this->request->getPost('total_ends') : null,
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

        // Skip internal model validation since we validated in the controller
        $this->dcModel->skipValidation(true);
        $this->dcModel->save($dcData);
        $dcId = $this->dcModel->getInsertID();

        // Save Beams and Color Ends if Warping & Sizing
        $jobType = $dcData['job_work_type'];
        if ($jobType === 'Warping & Sizing') {
            $beams = $this->request->getPost('beams');
            if (!empty($beams) && is_array($beams)) {
                foreach ($beams as $beam) {
                    if (!empty($beam['beam_number'])) {
                        $this->beamModel->save([
                            'dc_id'       => $dcId,
                            'beam_number' => $beam['beam_number'],
                            'remarks'     => $beam['remarks'] ?? null
                        ]);

                        // Update physical beam status & location
                        $beamRow = $this->yBeamModel->where('beam_number', $beam['beam_number'])->first();
                        if ($beamRow) {
                            $this->yBeamModel->update($beamRow['id'], [
                                'location'       => 'At Job Work',
                                'current_holder' => $dcData['vendor_name'],
                                'status'         => 'Empty'
                            ]);

                            // Log in Beam Ledger
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
                }
            }
            
            $colorEnds = $this->request->getPost('color_ends');
            if (!empty($colorEnds) && is_array($colorEnds)) {
                foreach ($colorEnds as $ce) {
                    if (!empty($ce['color']) && isset($ce['ends_count'])) {
                        $this->colorEndsModel->save([
                            'dc_id'      => $dcId,
                            'color'      => $ce['color'],
                            'ends_count' => (int)$ce['ends_count']
                        ]);
                    }
                }
            }
        }

        foreach ($items as $item) {
            // Save DC Item
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
            ];

            $this->dcItemModel->skipValidation(true);
            $this->dcItemModel->save($itemData);

            // Fetch average cost of this yarn in stock
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

            // Reduce stock (Negative movement)
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
                'remarks'       => 'Issued for ' . $dcData['job_work_type'] . ' to ' . $dcData['vendor_name'] . ' (DC: ' . $dcData['dc_number'] . ')',
                'created_by'    => session('user_id'),
                'created_at'    => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to create Delivery Challan.');
        }

        return redirect()->to('production/yarn-job-work?type=' . $jobType)->with('success', 'Delivery Challan created successfully.');
    }

    public function edit($id)
    {
        $data['dc'] = $this->dcModel->find($id);
        if (!$data['dc']) {
            return redirect()->to('production/yarn-job-work')->with('error', 'Delivery Challan not found.');
        }

        // Cannot edit if receipts have been recorded against it
        $receiptsCount = $this->receiptModel->where('dc_id', $id)->countAllResults();
        if ($receiptsCount > 0) {
            return redirect()->to('production/yarn-job-work')->with('error', 'Cannot edit Delivery Challan because receipts have already been recorded against it.');
        }

        $data['dcItems'] = $this->dcItemModel->where('dc_id', $id)->findAll();
        $data['beams'] = $this->beamModel->where('dc_id', $id)->findAll();
        $data['colorEnds'] = $this->colorEndsModel->where('dc_id', $id)->findAll();
        
        // Fetch available empty beams + beams already on this DC
        $inHouseBeams = $this->yBeamModel->where('status', 'Empty')->where('location', 'In-House')->where('condition_status', 'Active')->findAll();
        $dcBeamNumbers = array_column($data['beams'], 'beam_number');
        if (!empty($dcBeamNumbers)) {
            $alreadySelectedBeams = $this->yBeamModel->whereIn('beam_number', $dcBeamNumbers)->findAll();
            $data['availableBeams'] = array_merge($inHouseBeams, $alreadySelectedBeams);
        } else {
            $data['availableBeams'] = $inHouseBeams;
        }

        $type = $data['dc']['job_work_type'];
        $data['selectedType'] = $type;
        $data['availableYarns'] = $this->movementModel->getInventory();
        $data['title'] = 'Edit ' . $type . ' Delivery Challan';
        
        if (in_array($type, ['Warping & Sizing', 'Warping', 'Sizing'])) {
            return view('production/yarn_job_work/dc_form_warping_sizing', $data);
        } elseif ($type === 'Dyeing') {
            return view('production/yarn_job_work/dc_form_dyeing', $data);
        } else {
            return view('production/yarn_job_work/dc_form_generic', $data);
        }
    }

    public function update($id)
    {
        $dc = $this->dcModel->find($id);
        if (!$dc) {
            return redirect()->to('production/yarn-job-work')->with('error', 'DC not found.');
        }

        $receiptsCount = $this->receiptModel->where('dc_id', $id)->countAllResults();
        if ($receiptsCount > 0) {
            return redirect()->to('production/yarn-job-work')->with('error', 'Cannot update Delivery Challan because receipts have already been recorded against it.');
        }

        $dcData = [
            'dc_number'            => $this->request->getPost('dc_number'),
            'dc_date'              => $this->request->getPost('dc_date'),
            'vendor_name'          => $this->request->getPost('vendor_name'),
            'job_work_type'        => $this->request->getPost('job_work_type'),
            'expected_return_date' => $this->request->getPost('expected_return_date') ?: null,
            'vehicle_details'      => $this->request->getPost('vehicle_details'),
            'remarks'              => $this->request->getPost('remarks'),
            'design_pattern'       => $this->request->getPost('design_pattern') ?: null,
            'total_ends'           => $this->request->getPost('total_ends') ? (int)$this->request->getPost('total_ends') : null,
            'updated_by'           => session('user_id'),
        ];

        $rules = $this->dcModel->getValidationRules();
        if (isset($rules['dc_number'])) {
            // Replace {id} placeholder to ignore the current record during unique validation
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

        // Update DC
        $this->dcModel->skipValidation(true);
        $this->dcModel->update($id, $dcData);

        // Fetch old beams from this DC and revert their status
        $oldDcBeams = $this->beamModel->where('dc_id', $id)->findAll();
        foreach ($oldDcBeams as $oldBeam) {
            $beamRow = $this->yBeamModel->where('beam_number', $oldBeam['beam_number'])->first();
            if ($beamRow) {
                $this->yBeamModel->update($beamRow['id'], [
                    'location'       => 'In-House',
                    'current_holder' => null,
                    'status'         => 'Empty'
                ]);
            }
        }
        // Delete old beam ledger entries
        $this->yBeamLedgerModel->where('reference_id', $id)->where('transaction_type', 'Issue_Warping_Sizing')->delete();

        // Delete old items and old stock movements
        $this->dcItemModel->where('dc_id', $id)->delete();
        $this->beamModel->where('dc_id', $id)->delete();
        $this->colorEndsModel->where('dc_id', $id)->delete();
        
        $this->movementModel->where('movement_type', 'Issue_Job_Work')
                            ->where('reference_id', $id)
                            ->delete();

        // Insert new Beams and Color Ends if Warping & Sizing
        $jobType = $dcData['job_work_type'];
        if ($jobType === 'Warping & Sizing') {
            $beams = $this->request->getPost('beams');
            if (!empty($beams) && is_array($beams)) {
                foreach ($beams as $beam) {
                    if (!empty($beam['beam_number'])) {
                        $this->beamModel->save([
                            'dc_id'       => $id,
                            'beam_number' => $beam['beam_number'],
                            'remarks'     => $beam['remarks'] ?? null
                        ]);

                        // Update physical beam status & location
                        $beamRow = $this->yBeamModel->where('beam_number', $beam['beam_number'])->first();
                        if ($beamRow) {
                            $this->yBeamModel->update($beamRow['id'], [
                                'location'       => 'At Job Work',
                                'current_holder' => $dcData['vendor_name'],
                                'status'         => 'Empty'
                            ]);

                            // Log in Beam Ledger
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
                }
            }

            $colorEnds = $this->request->getPost('color_ends');
            if (!empty($colorEnds) && is_array($colorEnds)) {
                foreach ($colorEnds as $ce) {
                    if (!empty($ce['color']) && isset($ce['ends_count'])) {
                        $this->colorEndsModel->save([
                            'dc_id'      => $id,
                            'color'      => $ce['color'],
                            'ends_count' => (int)$ce['ends_count']
                        ]);
                    }
                }
            }
        }

        // Insert new items and new stock movements
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
            ];

            $this->dcItemModel->skipValidation(true);
            $this->dcItemModel->save($itemData);

            // Fetch average cost of this yarn in stock
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

            // Reduce stock (Negative movement)
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
                'remarks'       => 'Issued for ' . $dcData['job_work_type'] . ' to ' . $dcData['vendor_name'] . ' (DC: ' . $dcData['dc_number'] . ')',
                'created_by'    => session('user_id'),
                'created_at'    => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update Delivery Challan.');
        }

        return redirect()->to('production/yarn-job-work?type=' . $jobType)->with('success', 'Delivery Challan updated successfully.');
    }

    public function view($id)
    {
        $data['dc'] = $this->dcModel->find($id);
        if (!$data['dc']) {
            return redirect()->to('production/yarn-job-work')->with('error', 'Delivery Challan not found.');
        }

        $data['items'] = $this->dcItemModel->where('dc_id', $id)->findAll();
        $data['beams'] = $this->beamModel->where('dc_id', $id)->findAll();
        $data['colorEnds'] = $this->colorEndsModel->where('dc_id', $id)->findAll();
        
        // Fetch receipts against this DC
        $data['receipts'] = $this->receiptModel->where('dc_id', $id)->findAll();
        foreach ($data['receipts'] as &$r) {
            $r['items'] = $this->receiptItemModel
                ->select('production_yarn_job_work_receipt_items.*, 
                         production_yarn_job_work_dc_items.yarn_count, 
                         production_yarn_job_work_dc_items.required_color,
                         production_yarn_job_work_dc_items.quantity_issued_kg,
                         m.cost_per_kg as landed_cost_per_kg')
                ->join('production_yarn_job_work_dc_items', 'production_yarn_job_work_dc_items.id = production_yarn_job_work_receipt_items.dc_item_id')
                ->join('production_yarn_stock_movements m', 'm.movement_type = "Receipt_Job_Work" AND m.reference_id = ' . $r['id'] . ' AND m.yarn_count = production_yarn_job_work_dc_items.yarn_count AND m.brand_mill = production_yarn_job_work_dc_items.mill_name AND m.color = production_yarn_job_work_receipt_items.received_color', 'left')
                ->where('receipt_id', $r['id'])
                ->findAll();
        }

        $data['title'] = 'View Delivery Challan';
        return view('production/yarn_job_work/dc_view', $data);
    }

    public function receiptCreate($dcId)
    {
        $data['dc'] = $this->dcModel->find($dcId);
        if (!$data['dc'] || $data['dc']['status'] === 'Completed' || $data['dc']['status'] === 'Cancelled') {
            return redirect()->to('production/yarn-job-work')->with('error', 'Cannot receive items against this DC.');
        }

        $data['items'] = $this->dcItemModel->where('dc_id', $dcId)->findAll();
        $data['beamsSent'] = $this->beamModel->where('dc_id', $dcId)->where('receipt_id', null)->findAll();
        
        // Generate Receipt Number
        $lastRec = $this->receiptModel->orderBy('id', 'DESC')->first();
        $nextNum = $lastRec ? ((int)str_replace('REC-YARN-', '', $lastRec['receipt_number'])) + 1 : 1001;
        $data['nextReceiptNumber'] = 'REC-YARN-' . $nextNum;

        $data['title'] = 'Receive Yarn Stock';
        return view('production/yarn_job_work/receipt_form', $data);
    }

    public function receiptStore($dcId)
    {
        $dc = $this->dcModel->find($dcId);
        if (!$dc) {
            return redirect()->to('production/yarn-job-work')->with('error', 'DC not found.');
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

        // Clean dc_id from validation check since we set it programmatically
        $rules = $this->receiptModel->getValidationRules();
        if (isset($rules['dc_id'])) {
            unset($rules['dc_id']);
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $items = $this->request->getPost('items'); // Structure: [dc_item_id => [received_color, quantity_received_kg, quantity_used_kg, ...]]
        
        $db = \Config\Database::connect();
        $db->transStart();

        $this->receiptModel->skipValidation(true);
        $this->receiptModel->save($receiptData);
        $receiptId = $this->receiptModel->getInsertID();

        // Calculate total received weight across all items to pro-rate expenses
        $totalReceivedWeight = 0;
        foreach ($items as $dcItemId => $item) {
            $totalReceivedWeight += (float)($item['quantity_received_kg'] ?? 0);
        }

        $totalSharedExpenses = $receiptData['transport_charges'] + $receiptData['loading_charges'] + $receiptData['packing_charges'] + $receiptData['other_expenses'];

        foreach ($items as $dcItemId => $item) {
            $dcItem = $this->dcItemModel->find($dcItemId);
            
            $qtyReceived = (float)($item['quantity_received_kg'] ?? 0);
            
            // For Warping & Sizing, wastage is calculated as: Used Qty - Received Qty
            if (in_array($dc['job_work_type'], ['Warping & Sizing', 'Warping', 'Sizing'])) {
                $qtyUsed = (float)($item['quantity_used_kg'] ?? 0);
                $qtyWastage = max(0, $qtyUsed - $qtyReceived);
            } else {
                $qtyWastage = (float)($item['quantity_wastage_kg'] ?? 0);
            }
            
            $qtyShortage = 0.00;
            $qtyExcess = 0.00;
            $jobCharges = (float)($item['job_work_charges'] ?? 0);
            $receivedColor = $item['received_color'] ?: 'Raw';
            $dyeingLotNumber = $item['dyeing_lot_number'] ?? null;

            if ($qtyReceived <= 0 && $qtyWastage <= 0) {
                continue; // Nothing received or consumed
            }

            // Server-side validation: Received + Wastage (which equals Used Qty) cannot exceed pending
            $issued = (float)$dcItem['quantity_issued_kg'];
            $alreadyReceived = (float)$dcItem['quantity_received_kg'];
            $alreadyWasted = (float)$dcItem['quantity_wastage_kg'];
            $pending = $issued - ($alreadyReceived + $alreadyWasted);

            if ($qtyReceived + $qtyWastage > $pending) {
                return redirect()->back()->withInput()->with('error', 'Error: Total of Received + Wastage (' . ($qtyReceived + $qtyWastage) . ' Kg) cannot exceed the pending quantity (' . $pending . ' Kg) for item: ' . $dcItem['mill_name'] . ' - ' . $dcItem['yarn_count']);
            }

            // Save Receipt Item
            $this->receiptItemModel->skipValidation(true);
            $this->receiptItemModel->save([
                'receipt_id'           => $receiptId,
                'dc_item_id'           => $dcItemId,
                'received_color'       => $receivedColor,
                'quantity_received_kg' => $qtyReceived,
                'quantity_wastage_kg'  => $qtyWastage,
                'quantity_shortage_kg' => $qtyShortage,
                'quantity_excess_kg'   => $qtyExcess,
                'job_work_charges'     => $jobCharges,
            ]);

            // Update DC Item totals
            $newReceived = (float)$dcItem['quantity_received_kg'] + $qtyReceived;
            $newWastage = (float)$dcItem['quantity_wastage_kg'] + $qtyWastage;
            
            $this->dcItemModel->skipValidation(true);
            $this->dcItemModel->update($dcItemId, [
                'quantity_received_kg' => $newReceived,
                'quantity_wastage_kg'  => $newWastage
            ]);

            // Fetch original raw cost
            $issuanceMovement = $this->movementModel->where('movement_type', 'Issue_Job_Work')
                                                   ->where('reference_id', $dcId)
                                                   ->where('yarn_count', $dcItem['yarn_count'])
                                                   ->where('brand_mill', $dcItem['mill_name'])
                                                   ->first();
            $rawCostPerKg = $issuanceMovement ? abs((float)$issuanceMovement['cost_per_kg']) : 0;

            // Pro-rate expenses by weight
            $itemSharedExpense = $totalReceivedWeight > 0 ? (($qtyReceived / $totalReceivedWeight) * $totalSharedExpenses) : 0;

            // Landed Cost = [ (Received Qty * Raw Cost) + (Wastage Qty * Raw Cost) + (Received Qty * Job Charges) + Shared Expenses ] / Received Qty
            $proRatedRawCostConsumed = ($qtyReceived + $qtyWastage) * $rawCostPerKg;
            $itemTotalLandedCost = $proRatedRawCostConsumed + ($qtyReceived * $jobCharges) + $itemSharedExpense;
            $finalLandedCostPerKg = $qtyReceived > 0 ? ($itemTotalLandedCost / $qtyReceived) : 0;

            $finalColor = $receivedColor;
            $finalType = ($dc['job_work_type'] === 'Dyeing') ? 'Dyed' : $dcItem['yarn_type'];

            // Add processed yarn back to inventory (Positive stock movement)
            if ($qtyReceived > 0) {
                $this->movementModel->save([
                    'yarn_name'     => 'Yarn (' . $dcItem['yarn_count'] . ')',
                    'yarn_count'    => $dcItem['yarn_count'],
                    'yarn_type'     => $finalType,
                    'color'         => $finalColor,
                    'brand_mill'    => $dcItem['mill_name'],
                    'lot_number'    => ($dc['job_work_type'] === 'Dyeing' && !empty($dyeingLotNumber)) ? $dyeingLotNumber : $dcItem['lot_number'],
                    'csp'           => $dcItem['csp'],
                    'warp_weft'     => $dcItem['warp_weft'],
                    'quantity_kg'   => $qtyReceived,
                    'cost_per_kg'   => $finalLandedCostPerKg,
                    'warehouse'     => 'Main Warehouse',
                    'movement_type' => 'Receipt_Job_Work',
                    'reference_id'  => $receiptId,
                    'remarks'       => 'Received from ' . $dc['vendor_name'] . ' against ' . $dc['dc_number'] . ' (Color: ' . $finalColor . ', Receipt: ' . $receiptData['receipt_number'] . ')',
                    'created_by'    => session('user_id'),
                    'created_at'    => date('Y-m-d H:i:s')
                ]);
            }
        }

        // Process returned beams with detailed specifications
        $beamsReturn = $this->request->getPost('beams_return') ?: [];
        if (!empty($beamsReturn) && is_array($beamsReturn)) {
            // Build a yarn description string for the ledger
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
                    continue; // Skip
                }

                $meters = ($returnStatus === 'Loaded') ? (float)($beamData['meters'] ?? 0) : null;
                $sizingNo = ($returnStatus === 'Loaded') ? ($beamData['sizing_no'] ?? null) : null;
                $beamColor = ($returnStatus === 'Loaded') ? ($beamData['color'] ?? null) : null;
                $returnDate = ($returnStatus === 'Loaded') ? ($beamData['return_date'] ?? null) : null;

                // Associate with receipt and save specs
                $this->beamModel->where('dc_id', $dcId)->where('beam_number', $beamNum)->set([
                    'receipt_id'      => $receiptId,
                    'returned_status' => $returnStatus,
                    'meters'          => $meters,
                    'sizing_no'       => $sizingNo,
                    'color'           => $beamColor,
                    'return_date'     => $returnDate
                ])->update();

                // Update physical beam status & location
                $beamRow = $this->yBeamModel->where('beam_number', $beamNum)->first();
                if ($beamRow) {
                    $this->yBeamModel->update($beamRow['id'], [
                        'location'       => 'In-House',
                        'current_holder' => 'In-House',
                        'status'         => $returnStatus // 'Loaded' or 'Empty'
                    ]);

                    // Log in Beam Ledger with specifications
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

        return redirect()->to('production/yarn-job-work/view/' . $dcId)->with('success', 'Yarn received successfully.');
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

        // 1. Revert DC Item quantities
        foreach ($receiptItems as $ri) {
            $dcItem = $this->dcItemModel->find($ri['dc_item_id']);
            if ($dcItem) {
                $newReceived = max(0, (float)$dcItem['quantity_received_kg'] - (float)$ri['quantity_received_kg']);
                $newReturned = max(0, (float)$dcItem['quantity_returned_kg'] - (float)$ri['quantity_returned_kg']);
                $newWastage = max(0, (float)$dcItem['quantity_wastage_kg'] - (float)$ri['quantity_wastage_kg']);
                
                $this->dcItemModel->skipValidation(true);
                $this->dcItemModel->update($ri['dc_item_id'], [
                    'quantity_received_kg' => $newReceived,
                    'quantity_returned_kg' => $newReturned,
                    'quantity_wastage_kg'  => $newWastage
                ]);
            }
        }

        // 2. Delete stock movements
        $this->movementModel->whereIn('movement_type', ['Receipt_Job_Work', 'Receipt_Job_Work_Return'])
                            ->where('reference_id', $id)
                            ->delete();

        // Revert received beams associated with this receipt
        $oldReceivedBeams = $this->beamModel->where('receipt_id', $id)->findAll();
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
        $this->beamModel->where('receipt_id', $id)->set(['receipt_id' => null])->update();
        $this->yBeamLedgerModel->where('reference_id', $id)->where('transaction_type', 'Receipt_Warping_Sizing')->delete();

        // 3. Delete receipt items and receipt
        $this->receiptItemModel->where('receipt_id', $id)->delete();
        $this->receiptModel->delete($id);

        // 4. Update DC Status
        $remainingReceipts = $this->receiptModel->where('dc_id', $dcId)->countAllResults();
        $newStatus = 'Open';
        if ($remainingReceipts > 0) {
            $allDcItems = $this->dcItemModel->where('dc_id', $dcId)->findAll();
            $isCompleted = true;
            foreach ($allDcItems as $item) {
                $totalAccounted = (float)$item['quantity_received_kg'] + (float)$item['quantity_returned_kg'] + (float)$item['quantity_wastage_kg'];
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

        return redirect()->to('production/yarn-job-work/view/' . $dcId)->with('success', 'Receipt deleted and inventory reverted successfully.');
    }

    public function delete($id)
    {
        if (!$dc) {
            return redirect()->to('production/yarn-job-work')->with('error', 'Delivery Challan not found.');
        }

        $receiptsCount = $this->receiptModel->where('dc_id', $id)->countAllResults();
        if ($receiptsCount > 0) {
            return redirect()->to('production/yarn-job-work')->with('error', 'Cannot delete Delivery Challan because receipts have already been recorded against it.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Revert beams status
        $oldDcBeams = $this->beamModel->where('dc_id', $id)->findAll();
        foreach ($oldDcBeams as $oldBeam) {
            $beamRow = $this->yBeamModel->where('beam_number', $oldBeam['beam_number'])->first();
            if ($beamRow) {
                $this->yBeamModel->update($beamRow['id'], [
                    'location'       => 'In-House',
                    'current_holder' => null,
                    'status'         => 'Empty'
                ]);
            }
        }
        $this->yBeamLedgerModel->where('reference_id', $id)->where('transaction_type', 'Issue_Warping_Sizing')->delete();

        // 1. Revert stock (Delete negative movements)
        $this->movementModel->where('movement_type', 'Issue_Job_Work')
                            ->where('reference_id', $id)
                            ->delete();

        // 2. Delete DC items
        $this->dcItemModel->where('dc_id', $id)->delete();

        // 3. Delete DC
        $this->dcModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('production/yarn-job-work?type=' . $dc['job_work_type'])->with('error', 'Failed to delete Delivery Challan.');
        }

        return redirect()->to('production/yarn-job-work?type=' . $dc['job_work_type'])->with('success', 'Delivery Challan deleted successfully. Issued stock has been restored.');
    }

    public function receiptEdit($id)
    {
        $receipt = $this->receiptModel->find($id);
        if (!$receipt) {
            return redirect()->back()->with('error', 'Receipt not found.');
        }

        $dc = $this->dcModel->find($receipt['dc_id']);
        $items = $this->dcItemModel->where('dc_id', $receipt['dc_id'])->findAll();

        $receiptItems = $this->receiptItemModel->where('receipt_id', $id)->findAll();
        $receiptItemsKeyed = [];
        foreach ($receiptItems as $ri) {
            $receiptItemsKeyed[$ri['dc_item_id']] = $ri;
        }

        // Fetch beams currently received in this receipt
        $data['beamsReceived'] = $this->beamModel->where('receipt_id', $id)->findAll();
        // Fetch beams sent on this DC that are still pending (unreceived)
        $data['beamsPending'] = $this->beamModel->where('dc_id', $receipt['dc_id'])->where('receipt_id', null)->findAll();

        return view('production/yarn_job_work/receipt_form', [
            'receipt'            => $receipt,
            'dc'                 => $dc,
            'items'              => $items,
            'receiptItemsKeyed'  => $receiptItemsKeyed,
            'beamsReceived'      => $data['beamsReceived'],
            'beamsPending'       => $data['beamsPending'],
            'isEdit'             => true
        ]);
    }

    public function receiptUpdate($id)
    {
        $receipt = $this->receiptModel->find($id);
        if (!$receipt) {
            return redirect()->back()->with('error', 'Receipt not found.');
        }

        $dcId = $receipt['dc_id'];
        $dc = $this->dcModel->find($dcId);

        $validationRules = [
            'receipt_date' => 'required|valid_date[Y-m-d]',
        ];
        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $receiptData = [
            'receipt_date'      => $this->request->getPost('receipt_date'),
            'transport_charges' => (float)$this->request->getPost('transport_charges'),
            'loading_charges'   => (float)$this->request->getPost('loading_charges'),
            'packing_charges'   => (float)$this->request->getPost('packing_charges'),
            'other_expenses'    => (float)$this->request->getPost('other_expenses'),
            'remarks'           => $this->request->getPost('remarks'),
        ];

        $items = $this->request->getPost('items'); // Structure: [dc_item_id => [received_color, quantity_received_kg, ...]]

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. ROLLBACK previous receipt quantities and stock movements
        $oldReceiptItems = $this->receiptItemModel->where('receipt_id', $id)->findAll();
        foreach ($oldReceiptItems as $ori) {
            $dcItem = $this->dcItemModel->find($ori['dc_item_id']);
            if ($dcItem) {
                $rolledBackRecd = max(0, (float)$dcItem['quantity_received_kg'] - (float)$ori['quantity_received_kg']);
                $rolledBackWastage = max(0, (float)$dcItem['quantity_wastage_kg'] - (float)$ori['quantity_wastage_kg']);
                
                $this->dcItemModel->skipValidation(true);
                $this->dcItemModel->update($ori['dc_item_id'], [
                    'quantity_received_kg' => $rolledBackRecd,
                    'quantity_wastage_kg'  => $rolledBackWastage
                ]);
            }
        }

        // Delete previous stock movements for this receipt
        $this->movementModel->where('movement_type', 'Receipt_Job_Work')
                            ->where('reference_id', $id)
                            ->delete();

        // Revert received beams previously associated with this receipt
        $oldReceivedBeams = $this->beamModel->where('receipt_id', $id)->findAll();
        foreach ($oldReceivedBeams as $oldBeam) {
            $beamRow = $this->yBeamModel->where('beam_number', $oldBeam['beam_number'])->first();
            if ($beamRow) {
                $this->yBeamModel->update($beamRow['id'], [
                    'location'       => 'At Job Work',
                    'current_holder' => $dc['vendor_name'],
                    'status'         => 'Empty'
                ]);
            }
        }
        $this->beamModel->where('receipt_id', $id)->set(['receipt_id' => null])->update();
        $this->yBeamLedgerModel->where('reference_id', $id)->where('transaction_type', 'Receipt_Warping_Sizing')->delete();

        // Delete old receipt items
        $this->receiptItemModel->where('receipt_id', $id)->delete();

        // 2. PROCESS new receipt items and save
        $totalReceivedWeight = 0;
        foreach ($items as $dcItemId => $item) {
            $totalReceivedWeight += (float)($item['quantity_received_kg'] ?? 0);
        }

        $totalSharedExpenses = $receiptData['transport_charges'] + $receiptData['loading_charges'] + $receiptData['packing_charges'] + $receiptData['other_expenses'];

        foreach ($items as $dcItemId => $item) {
            $dcItem = $this->dcItemModel->find($dcItemId);
            
            $qtyReceived = (float)($item['quantity_received_kg'] ?? 0);
            $qtyWastage = (float)($item['quantity_wastage_kg'] ?? 0);
            $qtyShortage = 0.00;
            $qtyExcess = 0.00;
            $jobCharges = (float)($item['job_work_charges'] ?? 0);
            $receivedColor = $item['received_color'] ?: 'Raw';
            $dyeingLotNumber = $item['dyeing_lot_number'] ?? null;

            if ($qtyReceived <= 0 && $qtyWastage <= 0) {
                continue; // Nothing received for this item
            }

            // Server-side validation: Received + Wastage cannot exceed pending (based on rolled back values)
            $issued = (float)$dcItem['quantity_issued_kg'];
            $alreadyReceived = (float)$dcItem['quantity_received_kg'];
            $alreadyWasted = (float)$dcItem['quantity_wastage_kg'];
            $pending = $issued - ($alreadyReceived + $alreadyWasted);

            if ($qtyReceived + $qtyWastage > $pending) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Error: Total of Received Qty + Wastage (' . ($qtyReceived + $qtyWastage) . ' Kg) cannot exceed the pending quantity (' . $pending . ' Kg) for item: ' . $dcItem['mill_name'] . ' - ' . $dcItem['yarn_count']);
            }

            // Save new Receipt Item
            $this->receiptItemModel->skipValidation(true);
            $this->receiptItemModel->save([
                'receipt_id'           => $id,
                'dc_item_id'           => $dcItemId,
                'received_color'       => $receivedColor,
                'quantity_received_kg' => $qtyReceived,
                'quantity_wastage_kg'  => $qtyWastage,
                'quantity_shortage_kg' => $qtyShortage,
                'quantity_excess_kg'   => $qtyExcess,
                'job_work_charges'     => $jobCharges,
            ]);

            // Update DC Item totals
            $newReceived = (float)$dcItem['quantity_received_kg'] + $qtyReceived;
            $newWastage = (float)$dcItem['quantity_wastage_kg'] + $qtyWastage;
            
            $this->dcItemModel->skipValidation(true);
            $this->dcItemModel->update($dcItemId, [
                'quantity_received_kg' => $newReceived,
                'quantity_wastage_kg'  => $newWastage
            ]);

            // Calculate Landed Cost per Kg for this item
            $issuanceMovement = $this->movementModel->where('movement_type', 'Issue_Job_Work')
                                                   ->where('reference_id', $dcId)
                                                   ->where('yarn_count', $dcItem['yarn_count'])
                                                   ->where('brand_mill', $dcItem['mill_name'])
                                                   ->first();
            $rawCostPerKg = $issuanceMovement ? abs((float)$issuanceMovement['cost_per_kg']) : 0;

            // Pro-rate expenses by weight
            $itemSharedExpense = $totalReceivedWeight > 0 ? (($qtyReceived / $totalReceivedWeight) * $totalSharedExpenses) : 0;

            // Total raw cost for the received quantity
            $proRatedRawCostConsumed = $qtyReceived * $rawCostPerKg;

            // Landed Cost = Raw Cost Consumed + Job Charges + Shared Expenses
            $itemTotalLandedCost = $proRatedRawCostConsumed + ($qtyReceived * $jobCharges) + $itemSharedExpense;
            $finalLandedCostPerKg = $qtyReceived > 0 ? ($itemTotalLandedCost / $qtyReceived) : 0;

            // Determine received yarn specifications
            $finalColor = $receivedColor;
            $finalType = ($dc['job_work_type'] === 'Dyeing') ? 'Dyed' : $dcItem['yarn_type'];

            // Add back to inventory (Positive stock movement)
            if ($qtyReceived > 0) {
                $this->movementModel->save([
                    'yarn_name'     => 'Yarn (' . $dcItem['yarn_count'] . ')',
                    'yarn_count'    => $dcItem['yarn_count'],
                    'yarn_type'     => $finalType,
                    'color'         => $finalColor,
                    'brand_mill'    => $dcItem['mill_name'],
                    'lot_number'    => ($dc['job_work_type'] === 'Dyeing' && !empty($dyeingLotNumber)) ? $dyeingLotNumber : $dcItem['lot_number'],
                    'csp'           => $dcItem['csp'],
                    'warp_weft'     => $dcItem['warp_weft'],
                    'quantity_kg'   => $qtyReceived,
                    'cost_per_kg'   => $finalLandedCostPerKg,
                    'warehouse'     => 'Main Warehouse',
                    'movement_type' => 'Receipt_Job_Work',
                    'reference_id'  => $id,
                    'remarks'       => 'Received from ' . $dc['vendor_name'] . ' against ' . $dc['dc_number'] . ' (Color: ' . $finalColor . ', Receipt: ' . $receipt['receipt_number'] . ') [Updated]',
                    'created_by'    => session('user_id'),
                    'created_at'    => date('Y-m-d H:i:s')
                ]);
            }
        }

        // Process newly selected received beams
        $receivedBeams = $this->request->getPost('received_beams') ?: [];
        if (!empty($receivedBeams) && is_array($receivedBeams)) {
            // Build a yarn description string for the ledger
            $yarnDetailsList = [];
            foreach ($items as $dcItemId => $item) {
                if ((float)($item['quantity_received_kg'] ?? 0) > 0) {
                    $dcItem = $this->dcItemModel->find($dcItemId);
                    $yarnDetailsList[] = $dcItem['yarn_count'] . ' (' . ($item['received_color'] ?: 'Raw') . ')';
                }
            }
            $yarnDetailsStr = implode(', ', array_unique($yarnDetailsList));

            foreach ($receivedBeams as $beamNum) {
                // Associate with receipt
                $this->beamModel->where('dc_id', $dcId)->where('beam_number', $beamNum)->set(['receipt_id' => $id])->update();

                // Update physical beam status
                $beamRow = $this->yBeamModel->where('beam_number', $beamNum)->first();
                if ($beamRow) {
                    $this->yBeamModel->update($beamRow['id'], [
                        'location'       => 'In-House',
                        'current_holder' => 'In-House',
                        'status'         => 'Loaded'
                    ]);

                    // Log in Beam Ledger
                    $this->yBeamLedgerModel->save([
                        'beam_id'          => $beamRow['id'],
                        'transaction_date' => $receiptData['receipt_date'],
                        'transaction_type' => 'Receipt_Warping_Sizing',
                        'reference_id'     => $id,
                        'from_location'    => $dc['vendor_name'],
                        'to_location'      => 'In-House',
                        'status_from'      => 'Empty',
                        'status_to'        => 'Loaded',
                        'yarn_details'     => $yarnDetailsStr,
                        'remarks'          => 'Received loaded from warping & sizing on Receipt ' . $receipt['receipt_number'] . ' [Updated]',
                        'created_by'       => session('user_id')
                    ]);
                }
            }
        }

        // 3. Update Receipt Header
        $this->receiptModel->skipValidation(true);
        $this->receiptModel->update($id, $receiptData);

        // 4. Update DC Status
        $allDcItems = $this->dcItemModel->where('dc_id', $dcId)->findAll();
        $isCompleted = true;
        $hasAnyReceived = false;

        foreach ($allDcItems as $item) {
            $totalAccounted = (float)$item['quantity_received_kg'] + (float)$item['quantity_wastage_kg'];
            if ($totalAccounted < (float)$item['quantity_issued_kg']) {
                $isCompleted = false;
            }
            if ($totalAccounted > 0) {
                $hasAnyReceived = true;
            }
        }

        $newStatus = 'Open';
        if ($isCompleted) {
            $newStatus = 'Completed';
        } elseif ($hasAnyReceived) {
            $newStatus = 'Partially Received';
        }

        $this->dcModel->skipValidation(true);
        $this->dcModel->update($dcId, [
            'status' => $newStatus
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update receipt.');
        }

        return redirect()->to('production/yarn-job-work/view/' . $dcId)->with('success', 'Receipt updated successfully.');
    }
}
