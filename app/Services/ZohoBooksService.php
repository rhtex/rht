<?php

namespace App\Services;

use App\Models\ZohoSettingsModel;

class ZohoBooksService
{
    protected $authService;
    protected $settings;
    protected $headers;

    public function __construct()
    {
        $this->authService = new ZohoAuthService();
        $settingsModel = new ZohoSettingsModel();
        $this->settings = $settingsModel->getSettings();

        $accessToken = $this->authService->getAccessToken();
        $this->headers = [
            'Authorization: Zoho-oauthtoken ' . $accessToken,
            'Content-Type: application/json',
            'X-com-zoho-invoice-organizationid: ' . $this->settings['organization_id']
        ];
    }

    /**
     * Fetch contacts from Zoho
     * @param string $type 'customer' or 'vendor'
     * @param int $page Page number for pagination
     */
    public function getContacts($type = 'customer', $page = 1)
    {
        $contactType = ($type === 'customer') ? 'customer' : 'vendor';
        // IMPORTANT: detailedlist=true is required to get billing_address and shipping_address
        $url = $this->settings['api_base_url'] . '/contacts?contact_type=' . $contactType . '&page=' . $page . '&detailedlist=true';

        return $this->makeRequest($url);
    }

    /**
     * Get individual contact details by contact ID
     * This endpoint returns complete contact information including full billing and shipping addresses
     * @param string $contactId Zoho contact ID
     */
    public function getContactById($contactId)
    {
        $url = $this->settings['api_base_url'] . '/contacts/' . $contactId;
        return $this->makeRequest($url);
    }

    /**
     * Push a contact to Zoho (Create or Update)
     */
    public function pushContact($data, $contactId = null)
    {
        $url = $this->settings['api_base_url'] . '/contacts';
        $method = 'POST';

        if ($contactId) {
            $url .= '/' . $contactId;
            $method = 'PUT';
        }

        return $this->makeRequest($url, $method, $data);
    }

    private function makeRequest($url, $method = 'GET', $data = null)
    {
        $curl = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_TIMEOUT => 30, // Increased from default to 30 seconds
            CURLOPT_CONNECTTIMEOUT => 10, // Connection timeout 10 seconds
        ];

        if ($data) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            log_message('error', 'Zoho API cURL Error: ' . $err . ' URL: ' . $url);
            return ['success' => false, 'message' => $err];
        }

        $result = json_decode($response, true);
        if (isset($result['code']) && $result['code'] == 0) {
            return ['success' => true, 'data' => $result];
        }

        log_message('error', 'Zoho API Error: ' . ($result['message'] ?? 'Unknown error') . ' URL: ' . $url);
        return ['success' => false, 'message' => $result['message'] ?? 'Unknown Zoho API error', 'raw' => $result];
    }

    // ==================== BILLS API ====================

    /**
     * Get bills from Zoho Books
     */
    public function getBills($page = 1, $filters = [])
    {
        $url = $this->settings['api_base_url'] . '/bills?page=' . $page;

        if (!empty($filters['vendor_id'])) {
            $url .= '&vendor_id=' . $filters['vendor_id'];
        }

        if (!empty($filters['status'])) {
            $url .= '&status=' . $filters['status'];
        }

        return $this->makeRequest($url);
    }

    /**
     * Get single bill by Zoho bill ID
     */
    public function getBillById($zohoBillId)
    {
        $url = $this->settings['api_base_url'] . '/bills/' . $zohoBillId;
        return $this->makeRequest($url);
    }

    /**
     * Create bill in Zoho Books
     */
    public function createBill($billData)
    {
        $url = $this->settings['api_base_url'] . '/bills';
        return $this->makeRequest($url, 'POST', $billData);
    }

    /**
     * Update bill in Zoho Books
     */
    public function updateBill($zohoBillId, $billData)
    {
        $url = $this->settings['api_base_url'] . '/bills/' . $zohoBillId;
        return $this->makeRequest($url, 'PUT', $billData);
    }

    /**
     * Void a bill in Zoho Books
     */
    public function voidBill($zohoBillId)
    {
        $url = $this->settings['api_base_url'] . '/bills/' . $zohoBillId . '/status/void';
        return $this->makeRequest($url, 'POST');
    }

    // ==================== VENDOR PAYMENTS API ====================

    /**
     * Get vendor payments from Zoho Books
     */
    public function getVendorPayments($page = 1)
    {
        $url = $this->settings['api_base_url'] . '/vendorpayments?page=' . $page;
        return $this->makeRequest($url);
    }

    /**
     * Get single vendor payment by Zoho payment ID
     */
    public function getPaymentById($zohoPaymentId)
    {
        $url = $this->settings['api_base_url'] . '/vendorpayments/' . $zohoPaymentId;
        return $this->makeRequest($url);
    }

    /**
     * Create vendor payment in Zoho Books
     */
    public function createVendorPayment($paymentData)
    {
        $url = $this->settings['api_base_url'] . '/vendorpayments';
        return $this->makeRequest($url, 'POST', $paymentData);
    }
    // ==================== INVOICES API ====================

    /**
     * Get invoices from Zoho Books
     */
    public function getInvoices($page = 1, $filters = [])
    {
        $url = $this->settings['api_base_url'] . '/invoices?page=' . $page;

        if (!empty($filters['customer_id'])) {
            $url .= '&customer_id=' . $filters['customer_id'];
        }

        if (!empty($filters['status'])) {
            $url .= '&status=' . $filters['status'];
        }

        return $this->makeRequest($url);
    }

    /**
     * Get single invoice by Zoho ID
     */
    public function getInvoiceById($zohoInvoiceId)
    {
        $url = $this->settings['api_base_url'] . '/invoices/' . $zohoInvoiceId;
        return $this->makeRequest($url);
    }

    /**
     * Create invoice in Zoho Books
     */
    public function createInvoice($invoiceData)
    {
        $url = $this->settings['api_base_url'] . '/invoices';
        return $this->makeRequest($url, 'POST', $invoiceData);
    }

    /**
     * Update invoice in Zoho Books
     */
    public function updateInvoice($zohoInvoiceId, $invoiceData)
    {
        $url = $this->settings['api_base_url'] . '/invoices/' . $zohoInvoiceId;
        return $this->makeRequest($url, 'PUT', $invoiceData);
    }

    /**
     * Void an invoice in Zoho Books
     */
    public function voidInvoice($zohoInvoiceId)
    {
        $url = $this->settings['api_base_url'] . '/invoices/' . $zohoInvoiceId . '/status/void';
        return $this->makeRequest($url, 'POST');
    }

    // ==================== CUSTOMER PAYMENTS API ====================

    /**
     * Get customer payments from Zoho Books
     */
    public function getCustomerPayments($page = 1)
    {
        $url = $this->settings['api_base_url'] . '/customerpayments?page=' . $page;
        return $this->makeRequest($url);
    }

    /**
     * Get single customer payment by Zoho ID
     */
    public function getCustomerPaymentById($zohoPaymentId)
    {
        $url = $this->settings['api_base_url'] . '/customerpayments/' . $zohoPaymentId;
        return $this->makeRequest($url);
    }

    /**
     * Create customer payment in Zoho Books
     */
    public function createCustomerPayment($paymentData)
    {
        $url = $this->settings['api_base_url'] . '/customerpayments';
        return $this->makeRequest($url, 'POST', $paymentData);
    }

    // ==================== ESTIMATES (QUOTATIONS) API ====================

    /**
     * Create estimate in Zoho Books
     */
    public function createEstimate($data)
    {
        $url = $this->settings['api_base_url'] . '/estimates';
        return $this->makeRequest($url, 'POST', $data);
    }

    /**
     * Update estimate in Zoho Books
     */
    public function updateEstimate($zohoEstimateId, $data)
    {
        $url = $this->settings['api_base_url'] . '/estimates/' . $zohoEstimateId;
        return $this->makeRequest($url, 'PUT', $data);
    }

    /**
     * Void an estimate in Zoho Books
     */
    public function voidEstimate($zohoEstimateId)
    {
        $url = $this->settings['api_base_url'] . '/estimates/' . $zohoEstimateId . '/status/void';
        return $this->makeRequest($url, 'POST');
    }

    // ==================== SALES ORDERS API ====================

    /**
     * Create sales order in Zoho Books
     */
    public function createSalesOrder($data)
    {
        $url = $this->settings['api_base_url'] . '/salesorders';
        return $this->makeRequest($url, 'POST', $data);
    }

    /**
     * Update sales order in Zoho Books
     */
    public function updateSalesOrder($zohoSalesOrderId, $data)
    {
        $url = $this->settings['api_base_url'] . '/salesorders/' . $zohoSalesOrderId;
        return $this->makeRequest($url, 'PUT', $data);
    }

    /**
     * Void a sales order in Zoho Books
     */
    public function voidSalesOrder($zohoSalesOrderId)
    {
        $url = $this->settings['api_base_url'] . '/salesorders/' . $zohoSalesOrderId . '/status/void';
        return $this->makeRequest($url, 'POST');
    }

    // ==================== CREDIT NOTES (SALES RETURNS) API ====================

    /**
     * Get credit notes from Zoho Books
     */
    public function getCreditNotes($page = 1)
    {
        $url = $this->settings['api_base_url'] . '/creditnotes?page=' . $page;
        return $this->makeRequest($url);
    }

    /**
     * Get single credit note by Zoho ID
     */
    public function getCreditNoteById($zohoCreditNoteId)
    {
        $url = $this->settings['api_base_url'] . '/creditnotes/' . $zohoCreditNoteId;
        return $this->makeRequest($url);
    }

    /**
     * Create credit note in Zoho Books
     */
    public function createCreditNote($data)
    {
        $url = $this->settings['api_base_url'] . '/creditnotes';
        return $this->makeRequest($url, 'POST', $data);
    }

    /**
     * Update credit note in Zoho Books
     */
    public function updateCreditNote($zohoCreditNoteId, $data)
    {
        $url = $this->settings['api_base_url'] . '/creditnotes/' . $zohoCreditNoteId;
        return $this->makeRequest($url, 'PUT', $data);
    }

    /**
     * Void a credit note in Zoho Books
     */
    public function voidCreditNote($zohoCreditNoteId)
    {
        $url = $this->settings['api_base_url'] . '/creditnotes/' . $zohoCreditNoteId . '/status/void';
        return $this->makeRequest($url, 'POST');
    }
}
