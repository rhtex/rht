<?php

namespace App\Controllers;

use App\Models\VendorModel;
use App\Models\StateModel;
use App\Models\AddressModel;
use App\Services\ZohoBooksService;

class VendorController extends BaseController
{
    protected $vendorModel;
    protected $stateModel;
    protected $addressModel;
    protected $zohoService;

    public function __construct()
    {
        $this->vendorModel = new VendorModel();
        $this->stateModel = new StateModel();
        $this->addressModel = new AddressModel();
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
            'company_name' => $vendor['company_name'],
            'contact_type' => 'vendor',
            'email'        => $vendor['email'],
            'phone'        => $vendor['phone'],
            'mobile'       => $vendor['whatsapp_number'] ?? $vendor['phone'],
            'website'      => $vendor['website'],
            'gst_no'       => $vendor['gstin'],
            'pan'          => $vendor['pan'],
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
                    'name'            => $contact['contact_name'],
                    'company_name'    => $contact['company_name'],
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
}
