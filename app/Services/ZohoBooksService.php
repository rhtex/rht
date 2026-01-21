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
        $url = $this->settings['api_base_url'] . '/contacts?contact_type=' . $contactType . '&page=' . $page;

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
        ];

        if ($data) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['success' => false, 'message' => $err];
        }

        $result = json_decode($response, true);
        if (isset($result['code']) && $result['code'] == 0) {
            return ['success' => true, 'data' => $result];
        }

        return ['success' => false, 'message' => $result['message'] ?? 'Unknown Zoho API error', 'raw' => $result];
    }
}
