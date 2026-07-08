<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CustomerModel;
use App\Models\VendorModel;
use App\Models\AddressModel;
use App\Models\StateModel;
use App\Models\CountryModel;

class ApiController extends ResourceController
{
    protected $customerModel;
    protected $vendorModel;
    protected $addressModel;
    protected $stateModel;
    protected $countryModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->vendorModel = new VendorModel();
        $this->addressModel = new AddressModel();
        $this->stateModel = new StateModel();
        $this->countryModel = new CountryModel();
    }

    /**
     * Sync customer address
     * POST /api/customers/:id/address/sync
     */
    public function syncCustomerAddress($id)
    {
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            return $this->failNotFound('Customer not found');
        }

        $json = $this->request->getJSON(true);
        $type = $json['type'] ?? $this->request->getPost('type');
        $addressData = $json['address_data'] ?? $this->request->getPost('address_data');

        if (!$type || !in_array($type, ['billing', 'shipping'])) {
            return $this->fail('type must be billing or shipping');
        }

        if (!$addressData) {
            return $this->fail('address_data is required');
        }

        try {
            // Deactivate existing addresses of this type
            $this->addressModel->deactivateOthers('customer', $id, $type);

            // Create new address
            $data = [
                'owner_type' => 'customer',
                'owner_id' => $id,
                'type' => $type,
                'address_line1' => $addressData['address_line1'] ?? '',
                'address_line2' => $addressData['address_line2'] ?? '',
                'city' => $addressData['city'] ?? '',
                'state_id' => $addressData['state_id'] ?? null,
                'country_id' => $addressData['country_id'] ?? null,
                'pincode' => $addressData['pincode'] ?? '',
                'is_active' => 1
            ];

            if ($this->addressModel->insert($data)) {
                return $this->respondCreated([
                    'success' => true,
                    'message' => 'Address synced successfully',
                    'data' => $data
                ]);
            }

            return $this->fail('Failed to sync address');

        } catch (\Exception $e) {
            return $this->failServerError('Error syncing address: ' . $e->getMessage());
        }
    }

    /**
     * Get customer details with addresses
     * GET /api/customers/:id
     */
    public function getCustomer($id)
    {
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            return $this->failNotFound('Customer not found');
        }

        $billingAddress = $this->addressModel->getActiveAddress('customer', $id, 'billing');
        $shippingAddress = $this->addressModel->getActiveAddress('customer', $id, 'shipping');

        return $this->respond([
            'success' => true,
            'data' => [
                'customer' => $customer,
                'billing_address' => $billingAddress,
                'shipping_address' => $shippingAddress
            ]
        ]);
    }

    /**
     * Update customer data
     * PUT /api/customers/:id
     */
    public function updateCustomer($id)
    {
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            return $this->failNotFound('Customer not found');
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        // Remove fields that shouldn't be updated via API
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($this->customerModel->update($id, $data)) {
            return $this->respondUpdated([
                'success' => true,
                'message' => 'Customer updated successfully',
                'data' => $this->customerModel->find($id)
            ]);
        }

        return $this->fail('Failed to update customer', 400, null, $this->customerModel->errors());
    }

    /**
     * Sync vendor address
     * POST /api/vendors/:id/address/sync
     */
    public function syncVendorAddress($id)
    {
        $vendor = $this->vendorModel->find($id);
        if (!$vendor) {
            return $this->failNotFound('Vendor not found');
        }

        $json = $this->request->getJSON(true);
        $type = $json['type'] ?? $this->request->getPost('type');
        $addressData = $json['address_data'] ?? $this->request->getPost('address_data');

        if (!$type || !in_array($type, ['billing', 'shipping'])) {
            return $this->fail('type must be billing or shipping');
        }

        if (!$addressData) {
            return $this->fail('address_data is required');
        }

        try {
            // Deactivate existing addresses of this type
            $this->addressModel->deactivateOthers('vendor', $id, $type);

            // Create new address
            $data = [
                'owner_type' => 'vendor',
                'owner_id' => $id,
                'type' => $type,
                'address_line1' => $addressData['address_line1'] ?? '',
                'address_line2' => $addressData['address_line2'] ?? '',
                'city' => $addressData['city'] ?? '',
                'state_id' => $addressData['state_id'] ?? null,
                'country_id' => $addressData['country_id'] ?? null,
                'pincode' => $addressData['pincode'] ?? '',
                'is_active' => 1
            ];

            if ($this->addressModel->insert($data)) {
                return $this->respondCreated([
                    'success' => true,
                    'message' => 'Address synced successfully',
                    'data' => $data
                ]);
            }

            return $this->fail('Failed to sync address');

        } catch (\Exception $e) {
            return $this->failServerError('Error syncing address: ' . $e->getMessage());
        }
    }

    /**
     * Get vendor details with addresses
     * GET /api/vendors/:id
     */
    public function getVendor($id)
    {
        $vendor = $this->vendorModel->find($id);
        if (!$vendor) {
            return $this->failNotFound('Vendor not found');
        }

        $billingAddress = $this->addressModel->getActiveAddress('vendor', $id, 'billing');
        $shippingAddress = $this->addressModel->getActiveAddress('vendor', $id, 'shipping');

        return $this->respond([
            'success' => true,
            'data' => [
                'vendor' => $vendor,
                'billing_address' => $billingAddress,
                'shipping_address' => $shippingAddress
            ]
        ]);
    }

    /**
     * Update vendor data
     * PUT /api/vendors/:id
     */
    public function updateVendor($id)
    {
        $vendor = $this->vendorModel->find($id);
        if (!$vendor) {
            return $this->failNotFound('Vendor not found');
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        // Remove fields that shouldn't be updated via API
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($this->vendorModel->update($id, $data)) {
            return $this->respondUpdated([
                'success' => true,
                'message' => 'Vendor updated successfully',
                'data' => $this->vendorModel->find($id)
            ]);
        }

        return $this->fail('Failed to update vendor', 400, null, $this->vendorModel->errors());
    }

    /**
     * Get list of customers
     * GET /api/customers
     */
    public function getCustomers()
    {
        $limit = $this->request->getGet('limit') ?? 50;
        $offset = $this->request->getGet('offset') ?? 0;
        $search = $this->request->getGet('search') ?? '';

        $builder = $this->customerModel->builder();

        if ($search) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->orLike('phone', $search)
                ->groupEnd();
        }

        $customers = $builder->limit($limit, $offset)->get()->getResultArray();
        $total = $builder->countAllResults(false);

        return $this->respond([
            'success' => true,
            'data' => $customers,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Get list of vendors
     * GET /api/vendors
     */
    public function getVendors()
    {
        $limit = $this->request->getGet('limit') ?? 50;
        $offset = $this->request->getGet('offset') ?? 0;
        $search = $this->request->getGet('search') ?? '';

        $builder = $this->vendorModel->builder();

        if ($search) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->orLike('phone', $search)
                ->groupEnd();
        }

        $vendors = $builder->limit($limit, $offset)->get()->getResultArray();
        $total = $builder->countAllResults(false);

        return $this->respond([
            'success' => true,
            'data' => $vendors,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }
}
