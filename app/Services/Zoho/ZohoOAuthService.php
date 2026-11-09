<?php

namespace App\Services\Zoho;

use App\Models\Zoho\ZohoSettingModel;
use App\Models\Zoho\ZohoSyncLogModel;

class ZohoOAuthService
{
    protected ZohoSettingModel $settingModel;
    protected ZohoSyncLogModel $logModel;

    public function __construct()
    {
        $this->settingModel = new ZohoSettingModel();
        $this->logModel     = new ZohoSyncLogModel();
    }

    /**
     * Build the Zoho OAuth 2.0 Authorization URL.
     */
    public function getAuthorizationUrl(?string $state = null): string
    {
        $config = $this->settingModel->getConfig(true);
        $dc     = ZohoDataCenterRegistry::get($config['data_center'] ?? 'in');

        if (empty($config['client_id'])) {
            throw new \RuntimeException('Zoho Client ID is not configured.');
        }

        $redirectUri = !empty($config['oauth_redirect_url'])
            ? $config['oauth_redirect_url']
            : site_url('settings/integrations/zoho/callback');

        $params = [
            'scope'         => 'ZohoBooks.fullaccess.all',
            'client_id'     => $config['client_id'],
            'state'         => $state ?: bin2hex(random_bytes(12)),
            'response_type' => 'code',
            'redirect_uri'  => $redirectUri,
            'access_type'   => 'offline',
            'prompt'        => 'consent',
        ];

        return $dc['authorize_url'] . '?' . http_build_query($params);
    }

    /**
     * Exchange OAuth authorization code for access_token + refresh_token.
     */
    public function exchangeCodeForTokens(string $code, ?string $location = null): array
    {
        $config = $this->settingModel->getConfig(true);

        // Map Zoho callback location param if provided
        if (!empty($location)) {
            $locMap = [
                'in' => 'in',
                'us' => 'com',
                'eu' => 'eu',
                'au' => 'com.au',
                'jp' => 'jp',
                'ca' => 'ca',
                'sa' => 'sa',
                'uk' => 'uk',
            ];
            $mappedDc = $locMap[strtolower($location)] ?? null;
            if ($mappedDc && $mappedDc !== $config['data_center']) {
                $dcInfo = ZohoDataCenterRegistry::get($mappedDc);
                $this->settingModel->saveConfig([
                    'data_center'  => $mappedDc,
                    'api_base_url' => $dcInfo['api_base_url'],
                ]);
                $config = $this->settingModel->getConfig(true);
            }
        }

        $dc = ZohoDataCenterRegistry::get($config['data_center'] ?? 'in');
        $redirectUri = !empty($config['oauth_redirect_url'])
            ? $config['oauth_redirect_url']
            : site_url('settings/integrations/zoho/callback');

        $postFields = [
            'code'          => $code,
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code',
        ];

        $response = $this->executeOAuthPost($dc['token_url'], $postFields, 'OAUTH_EXCHANGE_CODE');

        if (!empty($response['error']) || empty($response['access_token'])) {
            $errMsg = $response['error_description'] ?? $response['error'] ?? 'Failed to exchange authorization code with Zoho.';
            $this->settingModel->saveConfig(['last_error' => $errMsg]);
            return ['success' => false, 'message' => $errMsg, 'raw' => $response];
        }

        $expiresIn = (int) ($response['expires_in'] ?? 3600);
        $update    = [
            'access_token'      => $response['access_token'],
            'token_expires_at'  => date('Y-m-d H:i:s', time() + max(300, $expiresIn - 120)),
            'last_connected_at' => date('Y-m-d H:i:s'),
            'last_error'        => null,
        ];

        if (!empty($response['refresh_token'])) {
            $update['refresh_token'] = $response['refresh_token'];
        }
        if (!empty($response['api_domain'])) {
            $update['api_base_url'] = rtrim($response['api_domain'], '/') . '/books/v3';
        }

        $this->settingModel->saveConfig($update);

        return [
            'success' => true,
            'message' => 'Successfully connected to Zoho Books OAuth 2.0.',
            'data'    => $response,
        ];
    }

    /**
     * Get a valid access token, automatically refreshing if expired or near expiry.
     */
    public function getValidAccessToken(bool $forceRefresh = false): string
    {
        $config = $this->settingModel->getConfig(true);

        if (!$forceRefresh && !empty($config['access_token']) && !empty($config['token_expires_at'])) {
            $expiresTs = strtotime($config['token_expires_at']);
            if ($expiresTs !== false && $expiresTs > (time() + 60)) {
                return $config['access_token'];
            }
        }

        $refreshed = $this->refreshAccessToken();
        if (empty($refreshed['success']) || empty($refreshed['access_token'])) {
            throw new \RuntimeException($refreshed['message'] ?? 'Unable to refresh Zoho Books access token.');
        }

        return $refreshed['access_token'];
    }

    /**
     * Refresh the Zoho OAuth 2.0 access token using the stored refresh_token.
     */
    public function refreshAccessToken(): array
    {
        $config = $this->settingModel->getConfig(true);
        if (empty($config['client_id']) || empty($config['client_secret']) || empty($config['refresh_token'])) {
            return [
                'success' => false,
                'message' => 'Missing Zoho Client ID, Client Secret, or Refresh Token. Please configure and connect Zoho Books.',
            ];
        }

        $dc = ZohoDataCenterRegistry::get($config['data_center'] ?? 'in');

        $postFields = [
            'refresh_token' => $config['refresh_token'],
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'grant_type'    => 'refresh_token',
        ];

        $response = $this->executeOAuthPost($dc['token_url'], $postFields, 'OAUTH_REFRESH_TOKEN');

        if (!empty($response['error']) || empty($response['access_token'])) {
            $errMsg = $response['error_description'] ?? $response['error'] ?? 'Failed to refresh Zoho access token.';
            $this->settingModel->saveConfig(['last_error' => $errMsg]);
            return ['success' => false, 'message' => $errMsg, 'raw' => $response];
        }

        $expiresIn = (int) ($response['expires_in'] ?? 3600);
        $expiresAt = date('Y-m-d H:i:s', time() + max(300, $expiresIn - 120));

        $this->settingModel->saveConfig([
            'access_token'      => $response['access_token'],
            'token_expires_at'  => $expiresAt,
            'last_connected_at' => date('Y-m-d H:i:s'),
            'last_error'        => null,
        ]);

        return [
            'success'          => true,
            'access_token'     => $response['access_token'],
            'token_expires_at' => $expiresAt,
            'message'          => 'Access token refreshed successfully.',
        ];
    }

    /**
     * Revoke tokens and disconnect Zoho integration.
     */
    public function disconnect(): array
    {
        $config = $this->settingModel->getConfig(true);
        $dc     = ZohoDataCenterRegistry::get($config['data_center'] ?? 'in');

        if (!empty($config['refresh_token'])) {
            try {
                $this->executeOAuthPost(
                    $dc['revoke_url'] . '?token=' . urlencode($config['refresh_token']),
                    [],
                    'OAUTH_REVOKE_TOKEN'
                );
            } catch (\Throwable $e) {
                // Continue local disconnect even if remote revoke fails
            }
        }

        $this->settingModel->update($config['id'], [
            'is_enabled'        => 0,
            'access_token_enc'  => null,
            'refresh_token_enc' => null,
            'token_expires_at'  => null,
            'last_error'        => null,
        ]);

        return ['success' => true, 'message' => 'Zoho Books has been disconnected.'];
    }

    protected function executeOAuthPost(string $url, array $postFields, string $operation): array
    {
        $start = microtime(true);
        $ch    = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($postFields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $rawBody  = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        $durationMs = (int) round((microtime(true) - $start) * 1000);
        $decoded    = is_string($rawBody) ? (json_decode($rawBody, true) ?: []) : [];

        $hasError = !empty($curlErr) || $httpCode >= 400 || !empty($decoded['error']);

        $this->logModel->logApiCall([
            'module'           => 'oauth',
            'direction'        => 'OAUTH',
            'operation'        => $operation,
            'http_method'      => 'POST',
            'endpoint'         => $url,
            'request_payload'  => $postFields,
            'response_code'    => $httpCode,
            'response_payload' => $decoded ?: $rawBody,
            'duration_ms'      => $durationMs,
            'status'           => $hasError ? 'FAILED' : 'SUCCESS',
            'error_message'    => $curlErr ?: ($decoded['error'] ?? null),
        ]);

        if (!empty($curlErr)) {
            return ['error' => $curlErr];
        }

        return $decoded;
    }
}

