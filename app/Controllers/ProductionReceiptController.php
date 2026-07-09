<?php

namespace App\Controllers;

use App\Models\ProductionReceiptModel;
use App\Models\ProductionReceiptItemModel;
use App\Models\ProductionAllocationModel;
use App\Models\ProductionWeaverSettlementModel;
use App\Models\WeaverModel;
use App\Models\YarnBeamModel;
use App\Models\BillModel;
use App\Models\BillItemModel;
use App\Models\ProductionAllocationWeftModel;

class ProductionReceiptController extends BaseController
{
    protected $receiptModel;
    protected $receiptItemModel;
    protected $allocationModel;
    protected $settlementModel;
    protected $weaverModel;

    public function __construct()
    {
        $this->receiptModel = new ProductionReceiptModel();
        $this->receiptItemModel = new ProductionReceiptItemModel();
        $this->allocationModel = new ProductionAllocationModel();
        $this->settlementModel = new ProductionWeaverSettlementModel();
        $this->weaverModel = new WeaverModel();
    }

    public function index()
    {
        $receipts = $this->receiptModel
            ->select('production_receipts.*, weavers.name as weaver_name, production_allocations.allocation_number')
            ->join('weavers', 'weavers.id = production_receipts.weaver_id')
            ->join('production_allocations', 'production_allocations.id = production_receipts.allocation_id', 'left')
            ->orderBy('id', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Production Receipts',
            'receipts' => $receipts
        ];

        return view('production/receipts/index', $data);
    }

    public function create($allocationId)
    {
        $allocation = $this->allocationModel
            ->select('production_allocations.*, weavers.name as weaver_name, weavers.id as weaver_id, weaver_looms.loom_number, production_beams.beam_number')
            ->join('weavers', 'weavers.id = production_allocations.weaver_id')
            ->join('weaver_looms', 'weaver_looms.id = production_allocations.loom_id', 'left')
            ->join('production_beams', 'production_beams.id = production_allocations.warp_beam_id', 'left')
            ->find($allocationId);

        if (!$allocation) {
            return redirect()->to('production/warp-allocations')->with('error', 'Allocation not found.');
        }

        $data = [
            'title' => 'Receive Production from Weaver',
            'allocation' => $allocation
        ];

        return view('production/receipts/form', $data);
    }

    public function store($allocationId)
    {
        $allocation = $this->allocationModel->find($allocationId);
        if (!$allocation) {
            return redirect()->back()->with('error', 'Allocation not found.');
        }

        $weaver = $this->weaverModel->find($allocation['weaver_id']);
        
        $items = $this->request->getPost('items');
        if (empty($items)) {
            return redirect()->back()->withInput()->with('error', 'No receipt items provided.');
        }

        $isFinal = $this->request->getPost('is_final_receipt') == 1;

        $db = \Config\Database::connect();
        $db->transStart();

        $receiptNumber = 'PR-' . date('Ymd') . '-' . rand(1000, 9999);
        $contractType = $allocation['contract_type'];
        
        $receiptData = [
            'receipt_number' => $receiptNumber,
            'receipt_date' => date('Y-m-d'),
            'weaver_id' => $allocation['weaver_id'],
            'allocation_id' => $allocationId,
            'status' => 'Pending QC',
            'remarks' => $this->request->getPost('remarks'),
            'created_by' => session('user_id'),
        ];

        $totalWagesOrBillAmount = 0;

        if ($contractType == 'Sale & Buy Back') {
            if (!$weaver['vendor_id']) {
                return redirect()->back()->withInput()->with('error', 'Vendor profile is required for Weaver under Sale & Buy Back contract.');
            }

            $billModel = new BillModel();
            $billNumber = $billModel->generateBillNumber();

            $billData = [
                'vendor_id' => $weaver['vendor_id'],
                'bill_number' => $billNumber,
                'bill_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d', strtotime('+15 days')),
                'status' => 'Open',
                'created_by' => session('user_id')
            ];
            
            $billId = $billModel->insert($billData);
            $receiptData['reference_document_type'] = 'Purchase Entry';
            $receiptData['reference_document_id'] = $billId;
        } else {
            $receiptData['reference_document_type'] = 'Job Work Receipt';
        }

        $receiptId = $this->receiptModel->insert($receiptData);
        $billItemModel = new BillItemModel();

        foreach ($items as $item) {
            $quantity = (float)$item['quantity'];
            $rate = (float)($item['rate'] ?? 0);
            $amount = $quantity * $rate;
            
            $totalWagesOrBillAmount += $amount;

            $this->receiptItemModel->insert([
                'receipt_id' => $receiptId,
                'item_type' => 'Finished Goods',
                'quality_status' => $item['quality_status'] ?? 'Good',
                'quantity' => $quantity,
                'weight' => $item['weight'] ?? 0,
                'rate_type' => $item['rate_type'] ?? null,
                'rate' => $rate,
                'wage_amount' => $amount
            ]);

            if ($contractType == 'Sale & Buy Back') {
                $billItemModel->insert([
                    'bill_id' => $receiptData['reference_document_id'],
                    'product_id' => null,
                    'item_name' => 'Finished Goods from Weaver',
                    'quantity' => $quantity,
                    'rate' => $rate,
                    'amount' => $amount,
                    'tax_percentage' => 0
                ]);
            }
        }

        if ($contractType == 'Sale & Buy Back') {
            $billModel->update($receiptData['reference_document_id'], [
                'subtotal' => $totalWagesOrBillAmount,
                'total_amount' => $totalWagesOrBillAmount,
                'balance' => $totalWagesOrBillAmount
            ]);
        } else {
            $settlementNumber = 'SET-' . date('Ymd') . '-' . rand(1000, 9999);
            $settlementId = $this->settlementModel->insert([
                'settlement_number' => $settlementNumber,
                'settlement_date' => date('Y-m-d'),
                'weaver_id' => $allocation['weaver_id'],
                'total_wages' => $totalWagesOrBillAmount,
                'deductions' => 0,
                'net_amount' => $totalWagesOrBillAmount,
                'status' => 'Pending',
                'created_by' => session('user_id')
            ]);
            
            $this->receiptModel->update($receiptId, ['reference_document_id' => $settlementId]);
        }

        if ($isFinal) {
            $this->allocationModel->update($allocationId, ['status' => 'Completed']);
            if (!empty($allocation['warp_beam_id'])) {
                $beamModel = new YarnBeamModel();
                $beamModel->update($allocation['warp_beam_id'], [
                    'status' => 'Empty',
                    'current_holder' => 'Company'
                ]);
            }
        } else {
            $this->allocationModel->update($allocationId, ['status' => 'Partially Returned']);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to process receipt.');
        }

        return redirect()->to('production/receipts')->with('success', 'Production receipt recorded successfully.');
    }

    public function view($id)
    {
        $receipt = $this->receiptModel
            ->select('production_receipts.*, weavers.name as weaver_name, production_allocations.allocation_number')
            ->join('weavers', 'weavers.id = production_receipts.weaver_id')
            ->join('production_allocations', 'production_allocations.id = production_receipts.allocation_id', 'left')
            ->find($id);

        if (!$receipt) {
            return redirect()->to('production/receipts')->with('error', 'Receipt not found.');
        }

        $items = $this->receiptItemModel->where('receipt_id', $id)->findAll();

        $data = [
            'title' => 'View Production Receipt',
            'receipt' => $receipt,
            'items' => $items
        ];

        return view('production/receipts/view', $data);
    }
}
