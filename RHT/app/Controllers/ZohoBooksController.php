<?php

namespace App\Controllers;

use App\Models\OAuthTokenModel;
use App\Models\CustomerModel;
use App\Models\SupplierModel;
use App\Models\CustomerAddressModel;
use App\Models\SupplierAddressModel;

class ZohoBooksController extends BaseController
{
    private $base_url;
    private $organization_id;

    public function __construct()
    {
        $this->base_url = 'https://books.zoho.com/api/v3';
        $this->organization_id = '648833159';
    }

    public function getInvoices()
    {
        $tokenModel = new OAuthTokenModel();
        $token = $tokenModel->getToken();

        if (!$token) {
            return 'No access token available.';
        }

        $client = \Config\Services::curlrequest();
        $response = $client->get("{$this->base_url}/invoices", [
            'headers' => [
                'Authorization' => "Zoho-oauthtoken {$token['access_token']}",
            ],
        ]);

        return $response->getBody();
    }

    public function createContact($customerData, $shippingAddress, $billingAddress, $contact_type)
    {
        $tokenModel = new OAuthTokenModel();
        $token = $tokenModel->getToken();

        if (!$token) {
            return 'No access token available.';
        }
        
        // Prepare data for Zoho Books API
        $zohoBooksData = [
            'contact_name' => $customerData['contact_name'],
            'company_name' => $customerData['company_name'],
            'contact_type' => $contact_type,
            'customer_sub_type' => $customerData['tax_type']   == 1 ? 'business' : 'individual',
            'is_portal_enabled' => false,
            'payment_terms' => (int)$customerData['payment_terms'],
            'billing_address' => [
                'address' => $billingAddress['address1'],
                'street2' => $billingAddress['address2'],
                'state_code' => $billingAddress['state'],
                'city' => $billingAddress['city'],
                'state' => getStateByCode($billingAddress['state']),
                'zip' => (int)$billingAddress['zip'],
                'country' => $billingAddress['country'],
                'phone' => $customerData['contact_no']
            ],
            'shipping_address' => [
                'address' => $shippingAddress['address1'],
                'street2' => $shippingAddress['address2'],
                'state_code' => $shippingAddress['state'],
                'city' => $shippingAddress['city'],
                'state' => getStateByCode($shippingAddress['state']),
                'zip' => (int)$shippingAddress['zip'],
                'country' => $shippingAddress['country'],
                'phone' => $customerData['contact_no']
            ],
            'opening_balance_amount' => 0,
            'exchange_rate' => 1, // Example exchange rate
            'place_of_contact' => $billingAddress['state'],
            'gst_no' => $customerData['gst_no'],
            'gst_treatment' => $customerData['tax_type'] == 1 ? 'business_gst' : 'consumer',
        ];
        $response = $this->makeZohoApiRequest('POST', 'contacts', $zohoBooksData);

        // Handle the response as needed
        if ($response === 401) {
            // Handle token refresh
            $oauth = new OAuthController();
            $oauth->refreshToken();
            $this->createContact($customerData, $shippingAddress, $billingAddress, $contact_type);
        } elseif ($response === 200) {
            return $response;
        } else {
            return $response;
        }
    }
    public function updateContact($customerData, $shippingAddress, $billingAddress, $contact_type)
    {
        $tokenModel = new OAuthTokenModel();
        $token = $tokenModel->getToken();

        if (!$token) {
            return 'No access token available.';
        }


        // Prepare data for Zoho Books API
        $zohoBooksData = [
            'contact_name' => $customerData['contact_name'],
            'company_name' => $customerData['company_name'],
            'contact_type' => $contact_type,
            'customer_sub_type' => $customerData['tax_type']   == 1 ? 'business' : 'individual',
            'payment_terms' => (int)$customerData['payment_terms'],
            'billing_address' => [
                'address' => $billingAddress['address1'],
                'street2' => $billingAddress['address2'],
                'state_code' => $billingAddress['state'],
                'city' => $billingAddress['city'],
                'state' => getStateByCode($billingAddress['state']),
                'zip' => (int)$billingAddress['zip'],
                'country' => $billingAddress['country'],
                'phone' => $customerData['contact_no'],
            ],
            'shipping_address' => [
                'address' => $shippingAddress['address1'],
                'street2' => $shippingAddress['address2'],
                'state_code' => $shippingAddress['state'],
                'city' => $shippingAddress['city'],
                'state' => getStateByCode($shippingAddress['state']),
                'zip' => (int)$shippingAddress['zip'],
                'country' => $shippingAddress['country'],
                'phone' => $customerData['contact_no'],
            ],
            'place_of_contact' => $billingAddress['state'],
            'gst_no' => $customerData['gst_no'],
            'gst_treatment' => $customerData['tax_type'] == 1 ? 'business_gst' : 'consumer',
        ];

        $response = $this->makeZohoApiRequest('PUT', 'contacts/' . $customerData['zoho_contact_id'], $zohoBooksData);

        // Handle the response as needed

        return $response;
    }
    private function makeZohoApiRequest($method, $endpoint, $data = null)
    {
        $tokenModel = new OAuthTokenModel();
        $token = $tokenModel->getToken();

        if (!$token) {
            throw new \Exception('No access token available.');
        }

        $url = "{$this->base_url}/{$endpoint}?organization_id={$this->organization_id}";
        $headers = [
            'Authorization: Zoho-oauthtoken ' . $token['access_token'],
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5000);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);

        // Check for token expiration
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 401) {
            $refresh_token = $this->refreshAccessToken($token['refresh_token']);
            if ($refresh_token == 200) {
                $token = $tokenModel->getToken();
                $headers[0] = 'Authorization: Zoho-oauthtoken ' . $token['access_token'];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $result = curl_exec($ch);
            } else {
                throw new \Exception('Failed to refresh token.');
            }
        }

        if (curl_errno($ch)) {
            throw new \Exception('Error:' . curl_error($ch));
        }

        curl_close($ch);
        error_log($result);
        return $result;
    }

    private function refreshAccessToken($refreshToken)
    {
        $oauth = new OAuthController();
        $refresh = $oauth->refreshToken($refreshToken);
        if ($refresh == 200) {
            error_log("Token refreshed successfully.");
            return 200;
        } else {
            error_log("Failed to refresh token.");
            return 201;
        }
    }
    public function get_all_contacts()
    {
        $allcontacts = $this->makeZohoApiRequest('GET', 'contacts', '');
        $all_json = json_decode($allcontacts, true);


        foreach ($all_json['contacts'] as $contact) {
            $contactId = $contact['contact_id'];
            $data = $this->getContactDetails($contactId);

            $contact_no = '';
            if (!empty($data['contact']['phone'])) {
                $contact_no = $data['contact']['phone'];
            }
            if (!empty($data['contact']['mobile'])) {
                $contact_no .= " " . $data['contact']['mobile'];
            }
            $customerModel = new CustomerModel();
            $addressModel = new CustomerAddressModel();
            $supplierModel = new SupplierModel();
            $supplierAddressModel = new SupplierAddressModel();
            if ($data['contact']['contact_type'] == 'customer') {
                $customerData = [
                    'company_name' => $data['contact']['company_name'],
                    'contact_name' => $data['contact']['contact_name'],
                    'credit_limit' => 2500000,
                    'tax_type' => $data['contact']['gst_no'] != '' ? 1 : 2,
                    'gst_no' => $data['contact']['gst_no'],
                    'preferred_transport' => '',
                    'contact_no' => $contact_no,
                    'email' => $data['contact']['email'],
                    'payment_terms' => $data['contact']['payment_terms'],
                    'created_time' => date('Y-m-d H:i:s'),
                    'updated_time' => date('Y-m-d H:i:s'),
                    'zoho_contact_id' => $contactId,
                    'customer_sub_type' => $data['contact']['customer_sub_type']
                ];
                $customerModel->insert($customerData);
                $insertedCustomerId = $customerModel->insertID();


                $billingAddress = [
                    'customer_id' => $insertedCustomerId,
                    'address_type' => 'billing',
                    'address1' => $data['contact']['billing_address']['address'],
                    'address2' => $data['contact']['billing_address']['street2'],
                    'city' => $data['contact']['billing_address']['city'],
                    'state' => $data['contact']['billing_address']['state_code'],
                    'state_name' => $data['contact']['billing_address']['state'],
                    'country' => $data['contact']['billing_address']['country'],
                    'zip' => $data['contact']['billing_address']['zip'],
                    'created_time' => date('Y-m-d H:i:s'),
                    'updated_time' => date('Y-m-d H:i:s')
                ];
                $addressModel->insert($billingAddress);
                $shippingAddress = [
                    'customer_id' => $insertedCustomerId,
                    'address_type' => 'shipping',
                    'address1' => $data['contact']['shipping_address']['address'],
                    'address2' => $data['contact']['shipping_address']['street2'],
                    'city' => $data['contact']['shipping_address']['city'],
                    'state' => $data['contact']['shipping_address']['state_code'],
                    'state_name' => $data['contact']['billing_address']['state'],
                    'country' => $data['contact']['shipping_address']['country'],
                    'zip' => $data['contact']['shipping_address']['zip'],
                    'created_time' => date('Y-m-d H:i:s'),
                    'updated_time' => date('Y-m-d H:i:s')
                ];
                $addressModel->insert($shippingAddress);
            } else {
                $customerData = [
                    'company_name' => $data['contact']['company_name'],
                    'contact_name' => $data['contact']['contact_name'],
                    'tax_type' => $data['contact']['gst_no'] != '' ? 1 : 2,
                    'gst_no' => $data['contact']['gst_no'],
                    'contact_no' => $contact_no,
                    'email' => $data['contact']['email'],
                    'payment_terms' => $data['contact']['payment_terms'],
                    'created_time' => date('Y-m-d H:i:s'),
                    'updated_time' => date('Y-m-d H:i:s'),
                    'zoho_contact_id' => $contactId,
                    'customer_sub_type' => $data['contact']['customer_sub_type']
                ];
                $supplierModel->insert($customerData);
                $insertedSupplierId = $supplierModel->insertID();


                $billingAddress = [
                    'supplier_id' => $insertedSupplierId,
                    'address1' => $data['contact']['billing_address']['address'],
                    'address2' => $data['contact']['billing_address']['street2'],
                    'city' => $data['contact']['billing_address']['city'],
                    'state' => $data['contact']['billing_address']['state_code'],
                    'state_name' => $data['contact']['billing_address']['state'],
                    'country' => $data['contact']['billing_address']['country'],
                    'zip' => $data['contact']['billing_address']['zip'],
                    'created_time' => date('Y-m-d H:i:s'),
                    'updated_time' => date('Y-m-d H:i:s')
                ];
                $supplierAddressModel->insert($billingAddress);
            }
            echo 'Done';
        }
    }
    public function getContactDetails($contactId)
    {
        $getcontact = $this->makeZohoApiRequest('GET', 'contacts/' . $contactId, '');

        return json_decode($getcontact, true);
    }
}
