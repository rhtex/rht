<?php

namespace App\Controllers;

use App\Models\VendorCreditModel;
use App\Models\VendorModel;
use App\Models\BillModel;

class VendorCreditController extends BaseController
{
    protected $creditModel;
    protected $vendorModel;
    protected $billModel;

    public function __construct()
    {
        $this->creditModel = new VendorCreditModel();
        $this->vendorModel = new VendorModel();
        $this->billModel   = new BillModel();
    }

    public function index()
    {
        $filters = [
            'reference_no' => $this->request->getGet('reference_no'),
            'vendor_id'    => $this->request->getGet('vendor_id'),
            'status'       => $this->request->getGet('status'),
            'date_from'    => $this->request->getGet('date_from'),
            'date_to'      => $this->request->getGet('date_to'),
        ];

        // Aggregate totals for stats cards
        $db = \Config\Database::connect();
        $summaryBuilder = $db->table('vendor_credits vc')
            ->select('
                COUNT(*) as total_count,
                COALESCE(SUM(vc.amount), 0) as total_amount,
                COALESCE(SUM(vc.used_amount), 0) as used_amount,
                COALESCE(SUM(vc.balance), 0) as total_balance,
                COUNT(CASE WHEN vc.status IN (\'Open\', \'Unused\', \'Partial\') AND vc.balance > 0.01 THEN 1 END) as open_count,
                COALESCE(SUM(CASE WHEN vc.status IN (\'Open\', \'Unused\', \'Partial\') AND vc.balance > 0.01 THEN vc.balance ELSE 0 END), 0) as open_balance,
                COUNT(CASE WHEN vc.status IN (\'Closed\', \'Used\') OR vc.balance <= 0.009 THEN 1 END) as closed_count,
                COALESCE(SUM(CASE WHEN vc.status IN (\'Closed\', \'Used\') OR vc.balance <= 0.009 THEN vc.amount ELSE 0 END), 0) as closed_amount
            ', false);

        if (!empty($filters['reference_no'])) {
            $summaryBuilder->like('vc.reference_no', trim($filters['reference_no']));
        }
        if (!empty($filters['vendor_id'])) {
            $summaryBuilder->where('vc.vendor_id', $filters['vendor_id']);
        }
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'Open') {
                $summaryBuilder->whereIn('vc.status', ['Open', 'Unused', 'Partial']);
            } elseif ($filters['status'] === 'Closed') {
                $summaryBuilder->whereIn('vc.status', ['Closed', 'Used']);
            } else {
                $summaryBuilder->where('vc.status', $filters['status']);
            }
        }
        if (!empty($filters['date_from'])) {
            $summaryBuilder->where('vc.credit_note_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $summaryBuilder->where('vc.credit_note_date <=', $filters['date_to']);
        }
        $totals = $summaryBuilder->get()->getRowArray();

        // AJAX infinite scroll
        if ($this->request->isAJAX() && $this->request->getGet('load_more') == 1) {
            $offset = max(0, (int) ($this->request->getGet('offset') ?? 0));
            $limit  = max(1, (int) ($this->request->getGet('limit') ?? 20));
            $filters['offset'] = $offset;
            $filters['limit']  = $limit;

            $moreCredits = $this->creditModel->getCreditsWithVendor($filters);
            return view('vendor_credits/list_rows', ['credits' => $moreCredits]);
        }

        // Initial 50 records
        $filters['offset'] = 0;
        $filters['limit']  = 50;

        $data['credits'] = $this->creditModel->getCreditsWithVendor($filters);
        $data['vendors'] = $this->vendorModel->findAll();
        $data['filters'] = $filters;
        $data['title']   = 'Vendor Credits';

        $data['stats'] = [
            'total'         => (int) ($totals['total_count'] ?? 0),
            'total_amount'  => (float) ($totals['total_amount'] ?? 0),
            'used_amount'   => (float) ($totals['used_amount'] ?? 0),
            'total_balance' => (float) ($totals['total_balance'] ?? 0),
            'open_count'    => (int) ($totals['open_count'] ?? 0),
            'open_balance'  => (float) ($totals['open_balance'] ?? 0),
            'closed_count'  => (int) ($totals['closed_count'] ?? 0),
            'closed_amount' => (float) ($totals['closed_amount'] ?? 0),
        ];

        return view('vendor_credits/index', $data);
    }

    public function view($id)
    {
        $credit = $this->creditModel->select('vendor_credits.*, vendors.name as vendor_name, vendors.phone as vendor_phone, vendors.gstin as vendor_gstin, bills.bill_number')
            ->join('vendors', 'vendors.id = vendor_credits.vendor_id', 'left')
            ->join('bills', 'bills.id = vendor_credits.bill_id', 'left')
            ->find($id);

        if (!$credit) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setBody('Vendor Credit not found.');
            }
            return redirect()->to(site_url('vendor_credits'))->with('error', 'Vendor Credit not found.');
        }

        if ($this->request->isAJAX()) {
            return view('vendor_credits/detail_pane', ['credit' => $credit]);
        }

        return view('vendor_credits/view', ['credit' => $credit, 'title' => 'Vendor Credit ' . $credit['reference_no']]);
    }

    public function print($id)
    {
        $credit = $this->creditModel->select('vendor_credits.*, vendors.name as vendor_name, vendors.phone as vendor_phone, vendors.whatsapp_number as vendor_whatsapp, vendors.gstin as vendor_gstin, bills.bill_number, addresses.address_line1 as vendor_address1, addresses.address_line2 as vendor_address2, addresses.city as vendor_city, addresses.pincode as vendor_pincode, states.name as vendor_state')
            ->join('vendors', 'vendors.id = vendor_credits.vendor_id', 'left')
            ->join('bills', 'bills.id = vendor_credits.bill_id', 'left')
            ->join('addresses', 'addresses.owner_id = vendors.id AND addresses.owner_type = "vendor" AND addresses.address_type = "billing" AND addresses.is_active = 1', 'left')
            ->join('states', 'states.id = addresses.state_id', 'left')
            ->find($id);

        if (!$credit) {
            return redirect()->to(site_url('vendor_credits'))->with('error', 'Debit note not found.');
        }

        $printData = ['credit' => $credit, 'title' => 'Debit Note ' . $credit['reference_no']];

        if ($this->request->getGet('format') !== 'html') {
            try {
                $html = view('vendor_credits/print', array_merge($printData, ['auto_print' => false]));
                return \App\Libraries\PdfRenderer::streamDocument('debit_note', $html, 'DebitNote_' . ($credit['reference_no'] ?: $credit['id']));
            } catch (\Throwable $e) {
                log_message('error', 'Debit Note PDF generation error: ' . $e->getMessage());
            }
        }

        return view('vendor_credits/print', $printData);
    }

    public function syncZoho()
    {
        try {
            $service = new \App\Services\Zoho\Modules\VendorCreditSyncService();
            $res = $service->pullFromZoho(false, false);
            return redirect()->to(site_url('vendor_credits'))->with('success', 'Vendor credits pulled from Zoho Books successfully. Created: ' . ($res['created_count'] ?? 0) . ', Updated: ' . ($res['updated_count'] ?? 0));
        } catch (\Throwable $e) {
            return redirect()->to(site_url('vendor_credits'))->with('error', 'Zoho sync failed: ' . $e->getMessage());
        }
    }
}
