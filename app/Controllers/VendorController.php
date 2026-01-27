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
        $data['vendors'] = $this->vendorModel
            ->select('vendors.*, addresses.address_line1, addresses.city, states.name as state_name')
            ->join('addresses', 'addresses.owner_id = vendors.id AND addresses.owner_type = "vendor" AND addresses.address_type = "billing" AND addresses.is_active = 1', 'left')
            ->join('states', 'states.id = addresses.state_id', 'left')
            ->findAll();
        $data['title'] = 'Vendor Management';
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

    public function delete($id)
    {
        $this->vendorModel->delete($id);
        $this->addressModel->where('owner_type', 'vendor')->where('owner_id', $id)->delete();
        return redirect()->to('vendors')->with('success', 'Vendor deleted successfully.');
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
        $shipmentModel = new \App\Models\ReturnShipmentModel();
        $data['shipments'] = $shipmentModel->getShipmentsWithVendor();
        $data['title'] = 'Return Shipments';
        return view('vendors/return_shipments', $data);
    }

    public function updateShipment($id)
    {
        $shipmentModel = new \App\Models\ReturnShipmentModel();
        $data = [
            'transport_name' => $this->request->getPost('transport_name'),
            'waybill_number' => $this->request->getPost('waybill_number'),
            'waybill_date' => $this->request->getPost('waybill_date') ?: null,
            'ewaybill_number' => $this->request->getPost('ewaybill_number'),
            'packages_count' => $this->request->getPost('packages_count') ?: null,
            'status' => $this->request->getPost('status')
        ];
        
        $shipmentModel->update($id, $data);
        return redirect()->back()->with('success', 'Shipment updated successfully.');
    }
}
