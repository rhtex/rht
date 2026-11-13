<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\EwayBillModel;
use App\Models\InvoiceModel;
use App\Models\SalesReturnModel;
use App\Models\ReturnShipmentModel;
use App\Models\StateModel;
use App\Models\TransportModel;
use App\Services\Zoho\ZohoEwayBillService;

class EwayBillController extends BaseController
{
    protected EwayBillModel $ewayModel;
    protected ZohoEwayBillService $ewayService;
    protected InvoiceModel $invoiceModel;
    protected SalesReturnModel $returnModel;
    protected CustomerModel $customerModel;
    protected TransportModel $transportModel;
    protected StateModel $stateModel;

    public function __construct()
    {
        $this->ewayModel      = new EwayBillModel();
        $this->ewayService    = new ZohoEwayBillService();
        $this->invoiceModel   = new InvoiceModel();
        $this->returnModel    = new SalesReturnModel();
        $this->customerModel  = new CustomerModel();
        $this->transportModel = new TransportModel();
        $this->stateModel     = new StateModel();
    }

    /**
     * e-Way Bill Console Dashboard
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $statusFilter = $this->request->getGet('status');
        $search       = $this->request->getGet('search');

        $builder = $db->table('eway_bills');

        if (!empty($statusFilter)) {
            $builder->where('status', $statusFilter);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('ewaybill_number', $search)
                ->orLike('document_number', $search)
                ->orLike('vehicle_number', $search)
                ->orLike('transporter_name', $search)
                ->groupEnd();
        }

        $records = $builder->orderBy('id', 'DESC')->limit(100)->get()->getResultArray();

        // Summary counts
        $counts = [
            'total'             => $db->table('eway_bills')->countAllResults(),
            'generated'         => $db->table('eway_bills')->where('status', 'generated')->countAllResults(),
            'part_a_generated'  => $db->table('eway_bills')->where('status', 'part_a_generated')->countAllResults(),
            'cancelled'         => $db->table('eway_bills')->where('status', 'cancelled')->countAllResults(),
            'failed'            => $db->table('eway_bills')->where('status', 'failed')->countAllResults(),
        ];

        return view('eway_bills/index', [
            'title'        => 'e-Way Bill Management Console',
            'records'      => $records,
            'counts'       => $counts,
            'statusFilter' => $statusFilter,
            'search'       => $search,
        ]);
    }

    /**
     * Create / Generate e-Way Bill Page
     */
    public function create()
    {
        $docType = $this->request->getGet('doc_type') ?? 'invoice';
        $docId   = (int) $this->request->getGet('doc_id');

        $document    = null;
        $party       = null;
        $shipAddress = null;
        $billAddress = null;

        if ($docId > 0) {
            $addrModel = new \App\Models\AddressModel();
            if ($docType === 'invoice') {
                $document = $this->invoiceModel->getInvoiceById($docId);
                if ($document) {
                    $party       = $this->customerModel->find($document['customer_id'] ?? 0);
                    $billAddress = $addrModel->select('addresses.*, states.name as state_name, states.gst_state_code')
                        ->join('states', 'states.id = addresses.state_id', 'left')
                        ->where('owner_type', 'customer')
                        ->where('owner_id', (int) ($document['customer_id'] ?? 0))
                        ->where('address_type', 'billing')
                        ->where('is_active', 1)
                        ->first();
                    $shipAddress = $addrModel->select('addresses.*, states.name as state_name, states.gst_state_code')
                        ->join('states', 'states.id = addresses.state_id', 'left')
                        ->where('owner_type', 'customer')
                        ->where('owner_id', (int) ($document['customer_id'] ?? 0))
                        ->where('address_type', 'shipping')
                        ->where('is_active', 1)
                        ->first() ?: $billAddress;
                }
            } elseif ($docType === 'credit_note') {
                $document = $this->returnModel->find($docId);
                if ($document) {
                    $party       = $this->customerModel->find($document['customer_id'] ?? 0);
                    $billAddress = $addrModel->select('addresses.*, states.name as state_name, states.gst_state_code')
                        ->join('states', 'states.id = addresses.state_id', 'left')
                        ->where('owner_type', 'customer')
                        ->where('owner_id', (int) ($document['customer_id'] ?? 0))
                        ->where('address_type', 'billing')
                        ->where('is_active', 1)
                        ->first();
                    $shipAddress = $addrModel->select('addresses.*, states.name as state_name, states.gst_state_code')
                        ->join('states', 'states.id = addresses.state_id', 'left')
                        ->where('owner_type', 'customer')
                        ->where('owner_id', (int) ($document['customer_id'] ?? 0))
                        ->where('address_type', 'shipping')
                        ->where('is_active', 1)
                        ->first() ?: $billAddress;
                }
            }
        }

        $transports = $this->transportModel->findAll();
        $states     = $this->stateModel->findAll();

        return view('eway_bills/generate', [
            'title'       => 'Generate e-Way Bill',
            'docType'     => $docType,
            'docId'       => $docId,
            'document'    => $document,
            'party'       => $party,
            'billAddress' => $billAddress,
            'shipAddress' => $shipAddress,
            'transports'  => $transports,
            'states'      => $states,
        ]);
    }

    /**
     * API / AJAX: Lookup Document details for instant form autofill
     */
    public function lookupDocument()
    {
        $docType = $this->request->getGet('doc_type') ?? 'invoice';
        $query   = trim((string) $this->request->getGet('q'));

        $db        = \Config\Database::connect();
        $addrModel = new \App\Models\AddressModel();

        if ($docType === 'invoice') {
            $builder = $db->table('invoices')
                ->select('invoices.*, customers.name as party_name, customers.gstin as party_gstin')
                ->join('customers', 'customers.id = invoices.customer_id', 'left');

            if (is_numeric($query)) {
                $builder->where('invoices.id', (int) $query);
            } else {
                $builder->like('invoices.invoice_number', $query);
            }

            $doc = $builder->get(1)->getRowArray();
            if ($doc) {
                // Fetch customer's billing address
                $billAddr = $addrModel->select('addresses.*, states.name as state_name, states.gst_state_code')
                    ->join('states', 'states.id = addresses.state_id', 'left')
                    ->where('owner_type', 'customer')
                    ->where('owner_id', (int) ($doc['customer_id'] ?? 0))
                    ->where('address_type', 'billing')
                    ->where('is_active', 1)
                    ->first();

                // Fetch customer's shipping address, fallback to billing
                $shipAddr = $addrModel->select('addresses.*, states.name as state_name, states.gst_state_code')
                    ->join('states', 'states.id = addresses.state_id', 'left')
                    ->where('owner_type', 'customer')
                    ->where('owner_id', (int) ($doc['customer_id'] ?? 0))
                    ->where('address_type', 'shipping')
                    ->where('is_active', 1)
                    ->first() ?: $billAddr;

                // Check if already has an active EWB
                $existing = $this->ewayModel->getActiveByDocument('invoice', (int) $doc['id']);
                return $this->response->setJSON([
                    'success'  => true,
                    'document' => [
                        'id'                  => $doc['id'],
                        'document_number'     => $doc['invoice_number'],
                        'document_date'       => $doc['invoice_date'],
                        'total_amount'        => $doc['total_amount'],
                        'cgst_amount'         => $doc['cgst_amount'] ?? 0,
                        'sgst_amount'         => $doc['sgst_amount'] ?? 0,
                        'igst_amount'         => $doc['igst_amount'] ?? 0,
                        'party_name'          => $doc['party_name'] ?? 'N/A',
                        'party_gstin'         => $doc['party_gstin'] ?? '',
                        'bill_to_address_1'   => $billAddr['address_line1'] ?? '',
                        'bill_to_address_2'   => $billAddr['address_line2'] ?? '',
                        'bill_to_place'       => $billAddr['city'] ?? '',
                        'bill_to_pincode'     => $billAddr['pincode'] ?? '',
                        'bill_to_state_code'  => $billAddr['gst_state_code'] ?? '33',
                        'to_address_1'        => $shipAddr['address_line1'] ?? '',
                        'to_address_2'        => $shipAddr['address_line2'] ?? '',
                        'to_place'            => $shipAddr['city'] ?? '',
                        'to_pincode'          => $shipAddr['pincode'] ?? '',
                        'to_state_code'       => $shipAddr['gst_state_code'] ?? '33',
                        'existing_ewb'        => $existing ? $existing['ewaybill_number'] : null,
                    ],
                ]);
            }
        } elseif ($docType === 'credit_note') {
            $builder = $db->table('sales_returns')
                ->select('sales_returns.*, customers.name as party_name, customers.gstin as party_gstin')
                ->join('customers', 'customers.id = sales_returns.customer_id', 'left');

            if (is_numeric($query)) {
                $builder->where('sales_returns.id', (int) $query);
            } else {
                $builder->like('sales_returns.return_number', $query);
            }

            $doc = $builder->get(1)->getRowArray();
            if ($doc) {
                $billAddr = $addrModel->select('addresses.*, states.name as state_name, states.gst_state_code')
                    ->join('states', 'states.id = addresses.state_id', 'left')
                    ->where('owner_type', 'customer')
                    ->where('owner_id', (int) ($doc['customer_id'] ?? 0))
                    ->where('address_type', 'billing')
                    ->where('is_active', 1)
                    ->first();

                $shipAddr = $addrModel->select('addresses.*, states.name as state_name, states.gst_state_code')
                    ->join('states', 'states.id = addresses.state_id', 'left')
                    ->where('owner_type', 'customer')
                    ->where('owner_id', (int) ($doc['customer_id'] ?? 0))
                    ->where('address_type', 'shipping')
                    ->where('is_active', 1)
                    ->first() ?: $billAddr;

                $existing = $this->ewayModel->getActiveByDocument('credit_note', (int) $doc['id']);
                return $this->response->setJSON([
                    'success'  => true,
                    'document' => [
                        'id'                  => $doc['id'],
                        'document_number'     => $doc['return_number'],
                        'document_date'       => $doc['return_date'],
                        'total_amount'        => $doc['total_amount'],
                        'cgst_amount'         => 0,
                        'sgst_amount'         => 0,
                        'igst_amount'         => 0,
                        'party_name'          => $doc['party_name'] ?? 'N/A',
                        'party_gstin'         => $doc['party_gstin'] ?? '',
                        'bill_to_address_1'   => $billAddr['address_line1'] ?? '',
                        'bill_to_address_2'   => $billAddr['address_line2'] ?? '',
                        'bill_to_place'       => $billAddr['city'] ?? '',
                        'bill_to_pincode'     => $billAddr['pincode'] ?? '',
                        'bill_to_state_code'  => $billAddr['gst_state_code'] ?? '33',
                        'to_address_1'        => $shipAddr['address_line1'] ?? '',
                        'to_address_2'        => $shipAddr['address_line2'] ?? '',
                        'to_place'            => $shipAddr['city'] ?? '',
                        'to_pincode'          => $shipAddr['pincode'] ?? '',
                        'to_state_code'       => $shipAddr['gst_state_code'] ?? '33',
                        'existing_ewb'        => $existing ? $existing['ewaybill_number'] : null,
                    ],
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Document not found or no matching record.',
        ]);
    }

    /**
     * Process Generation (Part A or Full Part A + B)
     */
    public function store()
    {
        $input = $this->request->getPost();

        $rules = [
            'document_type'   => 'required|in_list[invoice,credit_note,delivery_challan,purchase_bill]',
            'document_id'     => 'required|is_natural_no_zero',
            'transport_mode'  => 'required|in_list[1,2,3,4]',
            'distance_km'     => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $userId = session('user_id') ? (int) session('user_id') : 1;
        $result = $this->ewayService->generate($input, $userId);

        if (!empty($result['success'])) {
            return redirect()->to(site_url('eway_bills'))->with('success', $result['message']);
        }

        return redirect()->back()->withInput()->with('error', $result['message'] ?? 'Failed to generate e-Way Bill');
    }

    /**
     * Update Part-B (Conveyance / Vehicle Change)
     */
    public function updatePartB($id)
    {
        $input  = $this->request->getPost();
        $userId = session('user_id') ? (int) session('user_id') : 1;

        $rules = [
            'vehicle_number' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', 'Vehicle Number is mandatory to update Part-B.');
        }

        $res = $this->ewayService->updatePartB((int) $id, $input, $userId);

        if (!empty($res['success'])) {
            return redirect()->to(site_url('eway_bills'))->with('success', $res['message']);
        }

        return redirect()->back()->with('error', $res['message'] ?? 'Failed to update Part-B conveyance.');
    }

    /**
     * Cancel e-Way Bill
     */
    public function cancel($id)
    {
        $reason  = $this->request->getPost('cancel_reason_code') ?? '1';
        $remarks = trim((string) $this->request->getPost('cancel_remarks'));
        $userId  = session('user_id') ? (int) session('user_id') : 1;

        if (empty($remarks)) {
            return redirect()->back()->with('error', 'Cancellation remarks are mandatory.');
        }

        $res = $this->ewayService->cancel((int) $id, $reason, $remarks, $userId);

        if (!empty($res['success'])) {
            return redirect()->to(site_url('eway_bills'))->with('success', $res['message']);
        }

        return redirect()->back()->with('error', $res['message'] ?? 'Failed to cancel e-Way Bill.');
    }

    /**
     * Regenerate e-Way Bill
     */
    public function regenerate($id)
    {
        $input  = $this->request->getPost();
        $userId = session('user_id') ? (int) session('user_id') : 1;

        $res = $this->ewayService->regenerate((int) $id, $input, $userId);

        if (!empty($res['success'])) {
            return redirect()->to(site_url('eway_bills'))->with('success', $res['message']);
        }

        return redirect()->back()->with('error', $res['message'] ?? 'Failed to regenerate e-Way Bill.');
    }

    /**
     * Sync Status with Zoho Books
     */
    public function sync($id)
    {
        $res = $this->ewayService->syncStatus((int) $id);

        if (!empty($res['success'])) {
            return redirect()->to(site_url('eway_bills'))->with('success', 'Status synced successfully with Zoho Books. Current Status: ' . strtoupper($res['status']));
        }

        return redirect()->back()->with('error', $res['message'] ?? 'Sync failed.');
    }
}

