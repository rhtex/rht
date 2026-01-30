<?php

namespace App\Controllers;

use App\Models\VendorModel;
use App\Models\StateModel;
use App\Models\AddressModel;
use App\Models\ProductItemModel;
use App\Models\VendorCreditModel;
use App\Services\ZohoBooksService;

class VendorController extends BaseController
{
    protected $vendorModel;
    protected $stateModel;
    protected $addressModel;
    protected $itemModel;
    protected $creditModel;
    protected $zohoService;

    public function __construct()
    {
        $this->vendorModel = new VendorModel();
        $this->stateModel = new StateModel();
        $this->addressModel = new AddressModel();
        $this->itemModel = new ProductItemModel();
        $this->creditModel = new VendorCreditModel();
        $this->zohoService = new ZohoBooksService();
    }

    public function index()
    {
        $limit = 25;
        $page = $this->request->getGet('page') ?? 1;
        $offset = ($page - 1) * $limit;

        $filters = [
            'search'     => $this->request->getGet('search'),
            'status'     => $this->request->getGet('status'),
            'sort_by'    => $this->request->getGet('sort_by'),
            'sort_order' => $this->request->getGet('sort_order'),
        ];

        if ($this->request->isAJAX()) {
            $vendors = $this->vendorModel->getVendorsWithFilters($filters, $limit, $offset);
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $vendors,
                'has_more' => count($vendors) == $limit
            ]);
        }

        $data['vendors'] = $this->vendorModel->getVendorsWithFilters($filters, $limit, 0);
        $data['title'] = 'Vendor Management';
        $data['filters'] = $filters;
        $data['has_more'] = count($data['vendors']) == $limit;

        return view('vendors/index', $data);
    }

    public function create()
    {
        $data['states'] = $this->stateModel->orderBy('name', 'ASC')->findAll();
        $data['title'] = 'Add New Vendor';
        return view('vendors/form', $data);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Save Vendor
        $vendorData = $this->request->getPost();
        if (!$this->vendorModel->insert($vendorData)) {
            return redirect()->back()->withInput()->with('errors', $this->vendorModel->errors());
        }
        $vendorId = $this->vendorModel->getInsertID();

        // 2. Save Address
        $this->saveAddresses('vendor', $vendorId);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to save vendor data.');
        }

        // Push to Zoho
        $this->pushToZoho($vendorId);

        return redirect()->to('vendors')->with('success', 'Vendor added successfully.');
    }

    public function edit($id)
    {
        $data['vendor'] = $this->vendorModel->find($id);
        if (!$data['vendor']) {
            return redirect()->to('vendors')->with('error', 'Vendor not found.');
        }

        $data['billing_address'] = $this->addressModel->getActiveAddress('vendor', $id, 'billing');

        $data['states'] = $this->stateModel->orderBy('name', 'ASC')->findAll();
        $data['title'] = 'Edit Vendor';
        return view('vendors/form', $data);
    }

    public function update($id)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Update Vendor
        $vendorData = $this->request->getPost();
        if (!$this->vendorModel->update($id, $vendorData)) {
            return redirect()->back()->withInput()->with('errors', $this->vendorModel->errors());
        }

        // 2. Update Address
        $this->saveAddresses('vendor', $id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update vendor data.');
        }

        // Push to Zoho
        $this->pushToZoho($id);

        return redirect()->to('vendors')->with('success', 'Vendor updated successfully.');
    }

    public function view($id)
    {
        $data['vendor'] = $this->vendorModel->find($id);
        if (!$data['vendor']) {
            return redirect()->to('vendors')->with('error', 'Vendor not found.');
        }

        // Get Address
        $data['billing_address'] = $this->addressModel->getActiveAddress('vendor', $id, 'billing');
        $data['billing_state'] = $data['billing_address'] ? $this->stateModel->find($data['billing_address']['state_id']) : null;

        // Fetch Transactions
        $billModel = new \App\Models\BillModel();
        $data['bills'] = $billModel->where('vendor_id', $id)->orderBy('bill_date', 'DESC')->findAll();

        $paymentModel = new \App\Models\PaymentModel();
        $data['payments'] = $paymentModel->getPaymentsByVendor($id);

        $data['credits'] = $this->creditModel->getCreditsByVendor($id);
        $data['available_credit'] = $this->creditModel->getAvailableBalance($id);

        $shipmentModel = new \App\Models\ReturnShipmentModel();
        $data['shipments'] = $shipmentModel->where('vendor_id', $id)->orderBy('created_at', 'DESC')->findAll();

        // Pending Balance calculation (matching Model logic)
        $pendingBalanceSql = "(
            CASE 
                WHEN balance_type = 'Dr' THEN -opening_balance 
                ELSE opening_balance 
            END + 
            COALESCE((SELECT SUM(balance) FROM bills WHERE vendor_id = vendors.id), 0)
        )";
        // Since we already have the vendor, let's just do a manual calc or fetch again with calc.
        // Easiest is to use the model method we just added but for a single vendor.
        $vendorWithBalance = $this->vendorModel->getVendorsWithFilters(['search' => $data['vendor']['name']], 1, 0);
        $data['pending_balance'] = !empty($vendorWithBalance) ? $vendorWithBalance[0]['pending_balance'] : 0;

        $data['title'] = $data['vendor']['name'];
        return view('vendors/view', $data);
    }

    public function delete($id)
    {
        $this->vendorModel->delete($id);
        $this->addressModel->where('owner_type', 'vendor')->where('owner_id', $id)->delete();
        return redirect()->to('vendors')->with('success', 'Vendor deleted successfully.');
    }

    public function statement($id)
    {
        $data['vendor'] = $this->vendorModel->find($id);
        if (!$data['vendor']) {
            return redirect()->to('vendors')->with('error', 'Vendor not found.');
        }

        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');

        // Fetch Bills
        $billModel = new \App\Models\BillModel();
        $billsBuilder = $billModel->where('vendor_id', $id);
        if ($dateFrom) $billsBuilder->where('bill_date >=', $dateFrom);
        if ($dateTo) $billsBuilder->where('bill_date <=', $dateTo);
        $bills = $billsBuilder->orderBy('bill_date', 'ASC')->findAll();

        // Fetch Payments
        $paymentModel = new \App\Models\PaymentModel();
        $paymentsBuilder = $paymentModel->select('payments.*, bills.bill_number')
                                 ->join('bills', 'bills.id = payments.bill_id')
                                 ->where('bills.vendor_id', $id);
        if ($dateFrom) $paymentsBuilder->where('payment_date >=', $dateFrom);
        if ($dateTo) $paymentsBuilder->where('payment_date <=', $dateTo);
        $payments = $paymentsBuilder->orderBy('payment_date', 'ASC')->findAll();

        // Fetch Credits (Returns)
        $creditsBuilder = $this->creditModel->where('vendor_id', $id);
        if ($dateFrom) $creditsBuilder->where('created_at >=', $dateFrom . ' 00:00:00');
        if ($dateTo) $creditsBuilder->where('created_at <=', $dateTo . ' 23:59:59');
        $credits = $creditsBuilder->orderBy('created_at', 'ASC')->findAll();

        // Prepare Transactions
        $transactions = [];

        // Initial Balance
        $openingBalance = $data['vendor']['opening_balance'];
        $openingType = $data['vendor']['balance_type']; // Dr or Cr

        if ($dateFrom) {
            // Calculate balance before dateFrom
            $prevBills = $billModel->where('vendor_id', $id)->where('bill_date <', $dateFrom)->selectSum('total_amount')->first();
            $prevPayments = $paymentModel->join('bills', 'bills.id = payments.bill_id')
                                         ->where('bills.vendor_id', $id)->where('payment_date <', $dateFrom)->selectSum('amount')->first();
            $prevCredits = $this->creditModel->where('vendor_id', $id)->where('created_at <', $dateFrom . ' 00:00:00')->selectSum('amount')->first();

            // Vendor Liability: Cr (Opening Cr + Bills) - Dr (Opening Dr + Payments + Credits)
            $prevCredit = ($openingType == 'Cr' ? $openingBalance : 0) + ($prevBills['total_amount'] ?? 0);
            $prevDebit = ($openingType == 'Dr' ? $openingBalance : 0) + ($prevPayments['amount'] ?? 0) + ($prevCredits['amount'] ?? 0);

            if ($prevCredit >= $prevDebit) {
                $openingBalance = $prevCredit - $prevDebit;
                $openingType = 'Cr';
            } else {
                $openingBalance = $prevDebit - $prevCredit;
                $openingType = 'Dr';
            }
            $data['opening_balance_desc'] = 'Balance as on ' . date('d M, Y', strtotime($dateFrom));
        } else {
            $data['opening_balance_desc'] = 'Opening Balance';
        }

        $data['opening_balance'] = $openingBalance;
        $data['opening_type'] = $openingType;

        // Bills (Credit - Increase Liability)
        foreach ($bills as $bill) {
            $transactions[] = [
                'date' => $bill['bill_date'],
                'type' => 'Bill',
                'reference' => $bill['bill_number'],
                'debit' => 0,
                'credit' => $bill['total_amount'],
                'description' => 'Purchase Bill #' . $bill['bill_number']
            ];
        }

        // Payments (Debit - Decrease Liability)
        foreach ($payments as $pay) {
            $transactions[] = [
                'date' => $pay['payment_date'],
                'type' => 'Payment',
                'reference' => $pay['payment_number'] ?: 'PAY-'.$pay['id'],
                'debit' => $pay['amount'],
                'credit' => 0,
                'description' => 'Payment for Bill #' . $pay['bill_number'] . ' (' . $pay['payment_mode'] . ')'
            ];
        }

        // Credits / Returns (Debit - Decrease Liability)
        foreach ($credits as $credit) {
            $transactions[] = [
                'date' => date('Y-m-d', strtotime($credit['created_at'])),
                'type' => 'Credit Note',
                'reference' => $credit['reference_no'],
                'debit' => $credit['amount'],
                'credit' => 0,
                'description' => 'Vendor Credit: ' . $credit['notes']
            ];
        }

        // Sort by date
        usort($transactions, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        $data['transactions'] = $transactions;
        $data['date_from'] = $dateFrom;
        $data['date_to'] = $dateTo;
        
        $data['billing_address'] = $this->addressModel->getActiveAddress('vendor', $id, 'billing');
        $data['billing_state'] = $data['billing_address'] ? $this->stateModel->find($data['billing_address']['state_id']) : null;

        $format = $this->request->getGet('format');

        if ($format == 'pdf') {
            $data['is_pdf'] = true;
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml(view('vendors/statement', $data));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("Statement_{$data['vendor']['name']}_" . date('Ymd') . ".pdf", ["Attachment" => true]);
            exit();
        }

        if ($format == 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=Statement_' . str_replace(' ', '_', $data['vendor']['name']) . '_' . date('Ymd') . '.csv');
            $output = fopen('php://output', 'w');
            
            // Header Info
            fputcsv($output, ['RASI DESIGNS']);
            fputcsv($output, ['Vendor Statement']);
            fputcsv($output, ['Vendor:', $data['vendor']['name']]);
            fputcsv($output, ['Period:', ($dateFrom ?: 'Beginning') . ' - ' . ($dateTo ?: date('Y-m-d'))]);
            fputcsv($output, ['Net Balance Payable:', number_format(abs($finalBalance), 2, '.', '') . ' ' . ($finalBalance >= 0 ? 'Cr' : 'Dr')]);
            fputcsv($output, []);
            
            // Table Header
            fputcsv($output, ['Date', 'Type', 'Reference', 'Description', 'Debit (Dr)', 'Credit (Cr)', 'Running Balance']);
            
            // Opening Balance
            fputcsv($output, [
                $dateFrom ?: '---',
                'Opening Balance',
                '',
                $data['opening_balance_desc'],
                $openingType == 'Dr' ? number_format($opening_balance, 2, '.', '') : '0.00',
                $openingType == 'Cr' ? number_format($opening_balance, 2, '.', '') : '0.00',
                number_format($opening_balance, 2, '.', '') . ' ' . $openingType
            ]);
            
            $runningBalance = ($openingType == 'Cr' ? $opening_balance : -$opening_balance);
            foreach ($transactions as $t) {
                $runningBalance += ($t['credit'] - $t['debit']);
                $balanceType = $runningBalance >= 0 ? 'Cr' : 'Dr';
                fputcsv($output, [
                    $t['date'],
                    $t['type'],
                    $t['reference'],
                    $t['description'],
                    $t['debit'] > 0 ? number_format($t['debit'], 2, '.', '') : '0.00',
                    $t['credit'] > 0 ? number_format($t['credit'], 2, '.', '') : '0.00',
                    number_format(abs($runningBalance), 2, '.', '') . ' ' . $balanceType
                ]);
            }

            fputcsv($output, []);
            fputcsv($output, ['', '', '', 'TOTALS', number_format($totalDebit, 2, '.', ''), number_format($totalCredit, 2, '.', ''), '']);
            fputcsv($output, ['', '', '', 'NET PAYABLE', '', '', number_format(abs($finalBalance), 2, '.', '') . ' ' . ($finalBalance >= 0 ? 'Cr' : 'Dr')]);
            
            fclose($output);
            exit();
        }

        return view('vendors/statement', $data);
    }

    private function pushToZoho($id)
    {
        $vendor = $this->vendorModel->find($id);
        if (!$vendor) return;

        $billing = $this->addressModel->getActiveAddress('vendor', $id, 'billing');
        $billingState = $billing ? $this->stateModel->find($billing['state_id']) : null;

        $data = [
            'contact_name' => $vendor['name'],
            'company_name' => $vendor['name'], // Vendor name is the company name
            'contact_type' => 'vendor',
            'email'        => $vendor['email'],
            'phone'        => $vendor['phone'],
            'mobile'       => $vendor['whatsapp_number'] ?? $vendor['phone'],
            'website'      => $vendor['website'],
            'gst_no'       => $vendor['gstin'],
            'pan'          => $vendor['pan_number'],
            'billing_address' => [
                'address' => $billing['address_line1'] ?? '',
                'street2' => $billing['address_line2'] ?? '',
                'city'    => $billing['city'] ?? '',
                'state'   => $billingState['name'] ?? '',
                'zip'     => $billing['pincode'] ?? '',
                'country' => 'India'
            ]
        ];

        $response = $this->zohoService->pushContact($data, $vendor['zoho_contact_id']);
        
        if ($response['success']) {
            $zohoId = $response['data']['contact']['contact_id'];
            $this->vendorModel->update($id, [
                'zoho_contact_id' => $zohoId,
                'zoho_sync_at'    => date('Y-m-d H:i:s')
            ]);
        } else {
            log_message('error', 'Zoho Push Error for Vendor ' . $id . ': ' . $response['message']);
        }
    }

    public function syncZoho()
    {
        $page = 1;
        $hasMore = true;
        $syncedCount = 0;

        while ($hasMore) {
            $response = $this->zohoService->getContacts('vendor', $page);
            
            if (!$response['success']) {
                if ($syncedCount > 0) break;
                return redirect()->to('vendors')->with('error', 'Failed to fetch from Zoho: ' . $response['message']);
            }

            $contacts = $response['data']['contacts'] ?? [];
            if (empty($contacts)) break;

            foreach ($contacts as $contact) {
                $existing = $this->vendorModel->where('zoho_contact_id', $contact['contact_id'])->first();
                
                $vendorData = [
                    'zoho_contact_id' => $contact['contact_id'],
                    'name'            => $contact['company_name'] ?: $contact['contact_name'], // Prefer company name
                    'email'           => $contact['email'],
                    'phone'           => $contact['phone'],
                    'website'         => $contact['website'],
                    'zoho_sync_at'    => date('Y-m-d H:i:s'),
                    'status'          => 'active'
                ];

                if ($existing) {
                    $this->vendorModel->update($existing['id'], $vendorData);
                    $vendorId = $existing['id'];
                } else {
                    $byEmail = $this->vendorModel->where('email', $contact['email'])->first();
                    if ($byEmail && !empty($contact['email'])) {
                        $this->vendorModel->update($byEmail['id'], $vendorData);
                        $vendorId = $byEmail['id'];
                    } else {
                        $this->vendorModel->insert($vendorData);
                        $vendorId = $this->vendorModel->getInsertID();
                    }
                }

                if (isset($contact['billing_address'])) {
                    $this->syncAddress($vendorId, 'vendor', 'billing', $contact['billing_address']);
                }

                $syncedCount++;
            }

            $hasMore = $response['data']['page_context']['has_more_page'] ?? false;
            $page++;
        }

        return redirect()->to('vendors')->with('success', "Successfully synced $syncedCount vendors from Zoho.");
    }

    private function syncAddress($ownerId, $ownerType, $addressType, $zohoAddr)
    {
        if (empty($zohoAddr['address']) && empty($zohoAddr['city'])) return;

        $stateName = $zohoAddr['state'] ?? '';
        $state = $this->stateModel->where('name', $stateName)->first();
        
        $newData = [
            'owner_type'    => $ownerType,
            'owner_id'      => $ownerId,
            'address_type'  => $addressType,
            'address_line1' => $zohoAddr['address'] ?? '',
            'address_line2' => $zohoAddr['street2'] ?? '',
            'city'          => $zohoAddr['city'] ?? '',
            'pincode'       => $zohoAddr['zip'] ?? '',
            'state_id'      => $state['id'] ?? null,
            'country_id'    => 1,
            'is_active'     => 1
        ];

        $existing = $this->addressModel->getActiveAddress($ownerType, $ownerId, $addressType);
        
        if ($existing) {
            $isChanged = false;
            foreach (['address_line1', 'city', 'pincode', 'state_id'] as $field) {
                if (($existing[$field] ?? '') != ($newData[$field] ?? '')) {
                    $isChanged = true;
                    break;
                }
            }

            if ($isChanged) {
                $this->addressModel->deactivateOthers($ownerType, $ownerId, $addressType);
                $this->addressModel->insert($newData);
            }
        } else {
            $this->addressModel->insert($newData);
        }
    }

    /**
     * Save addresses from form data
     */
    private function saveAddresses($ownerType, $ownerId)
    {
        // Get address data from POST
        $addressData = [
            'owner_type'    => $ownerType,
            'owner_id'      => $ownerId,
            'address_type'  => 'billing',
            'address_line1' => $this->request->getPost('address_line1'),
            'address_line2' => $this->request->getPost('address_line2'),
            'city'          => $this->request->getPost('address_city'),
            'state_id'      => $this->request->getPost('address_state_id'),
            'pincode'       => $this->request->getPost('address_pincode'),
            'country_id'    => 1, // India
            'is_active'     => 1
        ];

        // Check if address exists
        $existing = $this->addressModel->getActiveAddress($ownerType, $ownerId, 'billing');
        
        if ($existing) {
            // Check if address has changed
            $isChanged = false;
            foreach (['address_line1', 'address_line2', 'city', 'state_id', 'pincode'] as $field) {
                if (($existing[$field] ?? '') != ($addressData[$field] ?? '')) {
                    $isChanged = true;
                    break;
                }
            }

            if ($isChanged) {
                // Deactivate old address and create new one
                $this->addressModel->deactivateOthers($ownerType, $ownerId, 'billing');
                $this->addressModel->insert($addressData);
            }
        } else {
            // Create new address
            $this->addressModel->insert($addressData);
        }
    }

    public function pendingReturns()
    {
        $filters = [
            'vendor_id'     => $this->request->getGet('vendor_id'),
            'return_action' => $this->request->getGet('return_action') ?? 'Pending'
        ];

        $data['items'] = $this->itemModel->getRejectedItems($filters);
        $data['vendors'] = $this->vendorModel->findAll();
        $data['filters'] = $filters;
        $data['title'] = 'Pending Returns';
        
        return view('vendors/pending_returns', $data);
    }

    public function processReturn($itemId)
    {
        $action = $this->request->getPost('return_action'); // Returned or Exchanged
        
        if (!in_array($action, ['Returned', 'Exchanged'])) {
            return redirect()->back()->with('error', 'Invalid action selected.');
        }

        $data = [
            'return_action' => $action,
             // If exchanged, maybe status changes? detailed tracking might require new item, 
             // but for now we track action on the rejected item.
             // If exchanged, arguably status is 'returned' too or kept as rejected with note.
            'status' => 'returned' 
        ];

        $this->itemModel->update($itemId, $data);
        return redirect()->back()->with('success', 'Item marked as ' . $action);
    }

    public function createReturnBatch()
    {
        $data['vendors'] = $this->vendorModel->findAll();
        $data['title'] = 'Create Return Shipment';
        return view('vendors/create_return', $data);
    }

    public function scanReturnItem()
    {
        $barcode = $this->request->getPost('barcode');
        $vendorId = $this->request->getPost('vendor_id');
        
        $item = $this->itemModel->where('barcode', $barcode)->first();
        
        if (!$item) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Item not found with this barcode.'
            ]);
        }
        
        if ($item['vendor_id'] != $vendorId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'This item does not belong to the selected vendor.'
            ]);
        }
        
        if ($item['status'] != 'rejected' || $item['is_approved'] != 'Rejected') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'This item is not rejected and cannot be returned.'
            ]);
        }
        
        if ($item['return_action'] != 'Pending') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'This item has already been processed as ' . $item['return_action'] . '.'
            ]);
        }
        
        // Get product name
        $productModel = new \App\Models\ProductModel();
        $product = $productModel->find($item['product_id']);
        
        return $this->response->setJSON([
            'success' => true,
            'item' => [
                'id' => $item['id'],
                'barcode' => $item['barcode'],
                'product_name' => $product['product_name'] ?? 'Unknown',
                'purchase_price' => $item['purchase_price'],
                'rejection_reason' => $item['rejection_reason']
            ]
        ]);
    }

    public function processReturnBatch()
    {
        $vendorId = $this->request->getPost('vendor_id');
        $action = $this->request->getPost('return_action');
        $itemIds = $this->request->getPost('item_ids'); // Array of item IDs
        
        if (empty($itemIds)) {
            return redirect()->back()->with('error', 'No items selected for return.');
        }
        
        if (!in_array($action, ['Returned', 'Exchanged'])) {
            return redirect()->back()->with('error', 'Invalid return action.');
        }
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        $totalAmount = 0;
        $processedCount = 0;
        $validItemIds = [];
        
        foreach ($itemIds as $itemId) {
            $item = $this->itemModel->find($itemId);
            
            if ($item && $item['vendor_id'] == $vendorId && $item['return_action'] == 'Pending') {
                $this->itemModel->update($itemId, [
                    'return_action' => $action,
                    'status' => 'returned'
                ]);
                
                if ($action == 'Returned') {
                    $totalAmount += $item['purchase_price'];
                }
                
                $processedCount++;
                $validItemIds[] = $itemId;
            }
        }
        
        // Create Return Shipment Record FIRST to get ID
        if ($processedCount > 0) {
            $refNo = 'RET-' . date('Ymd') . '-' . strtoupper(substr(md5(time()), 0, 6));
            
            $shipmentModel = new \App\Models\ReturnShipmentModel();
            $shipmentId = $shipmentModel->insert([
                'vendor_id' => $vendorId,
                'reference_no' => $refNo,
                'return_date' => date('Y-m-d'),
                'item_count' => $processedCount,
                'status' => 'Pending',
                'notes' => "Return action: {$action}"
            ]);
            
            // Link items to shipment
            $this->itemModel->whereIn('id', $validItemIds)->set(['return_shipment_id' => $shipmentId])->update();
        }
        
        // Create vendor credit if action is Returned
        if ($action == 'Returned' && $totalAmount > 0) {
            // Ref number is already generated
            
            $this->creditModel->insert([
                'vendor_id' => $vendorId,
                'amount' => $totalAmount,
                'reference_no' => $refNo,
                'notes' => "Credit for {$processedCount} returned items",
                'status' => 'Unused',
                'created_by' => session('user_id')
            ]);
        }
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to process return batch.');
        }
        
        $message = "Successfully processed {$processedCount} items as {$action}.";
        if ($action == 'Returned' && $totalAmount > 0) {
            $message .= " Vendor credit of ₹" . number_format($totalAmount, 2) . " created.";
        }
        
        return redirect()->to('vendors/returns/shipments')->with('success', $message);
    }

    public function listShipments()
    {
        $limit = 25;
        $offset = intval($this->request->getGet('offset') ?? 0);
        
        $vendor_id = $this->request->getGet('vendor_id');
        $delivery_status = $this->request->getGet('delivery_status');
        $search = $this->request->getGet('search');

        $shipmentModel = new \App\Models\ReturnShipmentModel();
        $vendorModel = new \App\Models\VendorModel();

        $builder = $shipmentModel->select('return_shipments.*, vendors.name as vendor_name')
                                 ->join('vendors', 'vendors.id = return_shipments.vendor_id', 'left');

        // Apply filters
        if ($vendor_id !== null && $vendor_id !== '') {
            $builder->where('return_shipments.vendor_id', $vendor_id);
        }
        if ($delivery_status !== null && $delivery_status !== '') {
            $builder->where('return_shipments.delivery_status', $delivery_status);
        }
        if ($search !== null && $search !== '') {
            $builder->like('return_shipments.reference_no', $search);
        }

        $countBuilder = clone $builder;
        $totalResults = $countBuilder->countAllResults(false);

        $shipments = $builder->orderBy('return_shipments.created_at', 'DESC')
                             ->findAll($limit, $offset);

        $data = [
            'title'           => 'Return Shipments',
            'shipments'       => $shipments,
            'vendors'         => $vendorModel->where('status', 'active')->findAll(),
            'total_count'     => $totalResults,
            'filters'         => [
                'vendor_id'       => $vendor_id,
                'delivery_status' => $delivery_status,
                'search'          => $search,
            ],
            'limit'           => $limit,
            'offset'          => $offset,
            'has_more'        => ($offset + $limit) < $totalResults
        ];

        if ($this->request->isAJAX()) {
            return view('vendors/return_shipment_rows', $data);
        }
        
        return view('vendors/return_shipments', $data);
    }

    public function updateShipment($id)
    {
        $shipmentModel = new \App\Models\ReturnShipmentModel();
        $shipment = $shipmentModel->find($id);
        
        if (!$shipment) {
            return redirect()->back()->with('error', 'Shipment not found.');
        }

        $validationRules = [
            'transport_name'  => 'required',
            'waybill_number'  => 'required',
            'waybill_date'    => 'required|valid_date',
            'waybill_image'   => 'permit_empty|max_size[waybill_image,2048]|is_image[waybill_image]'
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->with('error', 'Validation failed: ' . implode(', ', $this->validator->getErrors()));
        }

        $data = [
            'transport_name'  => $this->request->getPost('transport_name'),
            'waybill_number'  => $this->request->getPost('waybill_number'),
            'waybill_date'    => $this->request->getPost('waybill_date') ?: null,
            'ewaybill_number' => $this->request->getPost('ewaybill_number'),
            'packages_count'  => $this->request->getPost('packages_count') ?: null,
        ];

        $img = $this->request->getFile('waybill_image');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            $img->move(ROOTPATH . 'public/uploads/vendor_returns', $newName);
            $data['waybill_image'] = $newName;
        }
        
        $shipmentModel->update($id, $data);
        return redirect()->back()->with('success', 'Waybill information updated successfully.');
    }

    public function updateShipmentStatus($id)
    {
        $shipmentModel = new \App\Models\ReturnShipmentModel();
        $shipment = $shipmentModel->find($id);
        
        if (!$shipment) {
            return redirect()->back()->with('error', 'Shipment not found.');
        }

        $status = $this->request->getPost('delivery_status');
        $data = [
            'delivery_status' => $status,
            'status' => ($status == 'Completed') ? 'Delivered' : 'Shipped'
        ];
        
        $shipmentModel->update($id, $data);
        return redirect()->back()->with('success', 'Shipment status updated successfully.');
    }
}
