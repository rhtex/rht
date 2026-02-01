<?php

namespace App\Services;

use App\Models\ZohoSettingsModel;
use CodeIgniter\I18n\Time;

class ZohoAuthService
{
    protected $settingsModel;
    protected $settings;

    public function __construct()
    {
        $this->settingsModel = new ZohoSettingsModel();
        $this->settings = $this->settingsModel->getSettings();
    }

    /**
     * Get a valid access token, refreshing if necessary
     */
    public function getAccessToken()
    {
        if (empty($this->settings['client_id']) || empty($this->settings['client_secret']) || empty($this->settings['refresh_token'])) {
            return null;
        }

        // Check if current token is still valid (with 5 minute buffer)
        if (!empty($this->settings['access_token']) && !empty($this->settings['token_expires_at'])) {
            $expiry = new Time($this->settings['token_expires_at']);
            if ($expiry->isAfter(Time::now()->addMinutes(5))) {
                return $this->settings['access_token'];
            }
        }

        return $this->refreshToken();
    }

    /**
     * Refresh the access token using the refresh token
     */
    public function refreshToken()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->settings['accounts_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'refresh_token' => $this->settings['refresh_token'],
                'client_id' => $this->settings['client_id'],
                'client_secret' => $this->settings['client_secret'],
                'grant_type' => 'refresh_token',
            ]),
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            log_message('error', 'Zoho Auth Error: ' . $err);
            return null;
        }

        $data = json_decode($response, true);

        if (isset($data['access_token'])) {
            $updateData = [
                'access_token' => $data['access_token'],
                'token_expires_at' => Time::now()->addSeconds($data['expires_in'] ?? 3600)->toDateTimeString(),
            ];

            $this->settingsModel->update(1, $updateData);
            return $data['access_token'];
        }

        log_message('error', 'Zoho Token Refresh Failed: ' . $response);
        return null;
    }

    /**
     * Generate Access and Refresh tokens from Grant Token (Authorization Code)
     */
    public function generateTokensFromGrant($grantToken)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->settings['accounts_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'code' => $grantToken,
                'client_id' => $this->settings['client_id'],
                'client_secret' => $this->settings['client_secret'],
                'grant_type' => 'authorization_code',
            ]),
        ]);

        // Log the attempt
        log_message('error', 'Zoho Grant Exchange Attempt: Code=' . substr($grantToken, 0, 5) . '... URL=' . $this->settings['accounts_url']);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            log_message('error', 'Zoho Grant Auth Error: ' . $err);
            return false;
        }

        log_message('error', 'Zoho Grant Response: ' . $response);

        $data = json_decode($response, true);

        if (isset($data['access_token']) && isset($data['refresh_token'])) {
            $updateData = [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'token_expires_at' => Time::now()->addSeconds($data['expires_in'] ?? 3600)->toDateTimeString(),
            ];

            $this->settingsModel->update(1, $updateData);
            return true;
        }

        if (isset($data['error'])) {
            $errorMsg = $data['error'];
            if (isset($data['error_description'])) {
                $errorMsg .= ' - ' . $data['error_description'];
            }
            log_message('error', 'Zoho Grant Token Exchange Failed: ' . $response);
            return $errorMsg; // Return actual error string
        }

        log_message('error', 'Zoho Grant Token Exchange Failed (Unknown Response): ' . $response);
        return "Unknown error from Zoho: " . substr($response, 0, 100);
    }
}
