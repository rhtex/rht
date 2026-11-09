<?php

namespace App\Controllers;

use App\Models\Zoho\ZohoSettingModel;
use App\Models\Zoho\ZohoSyncConflictModel;
use App\Services\Zoho\ZohoApiClient;
use App\Services\Zoho\ZohoDataCenterRegistry;
use App\Services\Zoho\ZohoOAuthService;
use App\Services\Zoho\ZohoRateLimiterService;
use App\Services\Zoho\ZohoTaxMapperService;

class ZohoSettingsController extends BaseController
{
    protected ZohoSettingModel $settingModel;
    protected ZohoOAuthService $oauthService;
    protected ZohoApiClient $apiClient;

    public function __construct()
    {
        $this->settingModel = new ZohoSettingModel();
        $this->oauthService = new ZohoOAuthService();
        $this->apiClient    = new ZohoApiClient();
    }

    public function index()
    {
        $config        = $this->settingModel->getConfig(false);
        $dataCenters   = ZohoDataCenterRegistry::getAll();
        $openConflicts = (new ZohoSyncConflictModel())->countOpen();
        $planPresets   = ZohoRateLimiterService::getPlanPresets();
        $rateUsage     = $this->apiClient->getRateLimitUsage();

        return view('zoho/settings', [
            'title'         => 'Zoho Books Configuration',
            'config'        => $config,
            'dataCenters'   => $dataCenters,
            'openConflicts' => $openConflicts,
            'planPresets'   => $planPresets,
            'rateUsage'     => $rateUsage,
        ]);
    }

    public function save()
    {
        $post = $this->request->getPost();
        $dc   = $post['data_center'] ?? 'in';
        $dcInfo = ZohoDataCenterRegistry::get($dc);

        $apiBaseUrl = trim((string) ($post['api_base_url'] ?? ''));
        if ($apiBaseUrl === '' || !empty($post['auto_update_dc_url'])) {
            $apiBaseUrl = $dcInfo['api_base_url'];
        }

        $plan        = strtolower(trim((string) ($post['subscription_plan'] ?? 'free')));
        $planPresets = ZohoRateLimiterService::getPlanPresets();
        $preset      = $planPresets[$plan] ?? $planPresets['free'];

        $dailyLimit  = (int) ($post['daily_api_limit'] ?? $preset['daily_limit']);
        $minuteLimit = min(100, max(10, (int) ($post['per_minute_limit'] ?? $preset['per_minute'])));
        $concurrent  = (int) ($post['max_concurrent_connections'] ?? $preset['max_concurrent']);
        if ($plan === 'free') {
            $concurrent = min(5, max(1, $concurrent));
        } else {
            $concurrent = min(10, max(1, $concurrent));
        }

        $input = [
            'is_enabled'                 => !empty($post['is_enabled']) ? 1 : 0,
            'auto_sync_enabled'          => !empty($post['auto_sync_enabled']) ? 1 : 0,
            'sync_mode'                  => $post['sync_mode'] ?? 'immediate',
            'data_center'                => $dc,
            'client_id'                  => $post['client_id'] ?? '',
            'client_secret'              => $post['client_secret'] ?? '',
            'refresh_token'              => $post['refresh_token'] ?? '',
            'organization_id'            => $post['organization_id'] ?? '',
            'organization_name'          => $post['organization_name'] ?? '',
            'oauth_redirect_url'         => $post['oauth_redirect_url'] ?: site_url('settings/integrations/zoho/callback'),
            'api_base_url'               => $apiBaseUrl,
            'conflict_strategy'          => $post['conflict_strategy'] ?? 'manual_review',
            'preserve_document_numbers'  => !empty($post['preserve_document_numbers']) ? 1 : 0,
            'subscription_plan'          => $plan,
            'daily_api_limit'            => max(100, $dailyLimit),
            'per_minute_limit'           => $minuteLimit,
            'max_concurrent_connections' => $concurrent,
        ];

        $userId = (int) (session('user_id') ?? 0) ?: null;
        $this->settingModel->saveConfig($input, $userId);

        $savedConfig = $this->settingModel->getConfig(false);
        if (!empty($savedConfig['has_client_id']) && !empty($savedConfig['has_client_secret']) && empty($savedConfig['has_refresh_token'])) {
            return redirect()->to('settings/integrations/zoho')
                ->with('success', 'Zoho credentials saved! Now click "Connect to Zoho Books (OAuth 2.0)" on the right panel to grant access and generate your Refresh Token.');
        }

        return redirect()->to('settings/integrations/zoho')
            ->with('success', 'Zoho Books configuration saved securely.');
    }

    public function connect()
    {
        try {
            $config = $this->settingModel->getConfig(true);
            if (empty($config['client_id'])) {
                return redirect()->to('settings/integrations/zoho')
                    ->with('error', 'Please enter your Zoho Client ID and Client Secret in the form, then click "Save Configuration" before clicking Connect.');
            }
            if (empty($config['client_secret'])) {
                return redirect()->to('settings/integrations/zoho')
                    ->with('error', 'Please enter your Zoho Client Secret in the form, then click "Save Configuration" before clicking Connect.');
            }

            $state = bin2hex(random_bytes(16));
            session()->set('zoho_oauth_state', $state);
            $authUrl = $this->oauthService->getAuthorizationUrl($state);
            return redirect()->to($authUrl);
        } catch (\Throwable $e) {
            return redirect()->to('settings/integrations/zoho')
                ->with('error', 'Cannot initiate Zoho OAuth 2.0: ' . $e->getMessage());
        }
    }

    public function callback()
    {
        $error = $this->request->getGet('error');
        if (!empty($error)) {
            $desc = $this->request->getGet('error_description') ?: $error;
            return redirect()->to('settings/integrations/zoho')
                ->with('error', 'Zoho OAuth authorization declined or failed: ' . $desc);
        }

        $code     = (string) $this->request->getGet('code');
        $location = (string) $this->request->getGet('location');

        if ($code === '') {
            return redirect()->to('settings/integrations/zoho')
                ->with('error', 'No authorization code received from Zoho.');
        }

        $res = $this->oauthService->exchangeCodeForTokens($code, $location ?: null);
        if (empty($res['success'])) {
            return redirect()->to('settings/integrations/zoho')
                ->with('error', $res['message'] ?? 'Failed to exchange OAuth code.');
        }

        // Automatically retrieve organizations from Zoho Books
        $orgRes = $this->apiClient->fetchOrganizations();
        if (!empty($orgRes['success'])) {
            return redirect()->to('settings/integrations/zoho')
                ->with('success', 'Connected to Zoho Books! ' . ($orgRes['message'] ?? ''));
        }

        return redirect()->to('settings/integrations/zoho')
            ->with('success', 'OAuth tokens saved. Please verify your Zoho Organization ID.');
    }

    public function testConnection()
    {
        $res = $this->apiClient->testConnection();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($res);
        }

        if (!empty($res['success'])) {
            return redirect()->to('settings/integrations/zoho')->with('success', $res['message']);
        }
        return redirect()->to('settings/integrations/zoho')->with('error', $res['message'] ?? 'Connection test failed.');
    }

    public function refreshToken()
    {
        $res = $this->oauthService->refreshAccessToken();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($res);
        }

        if (!empty($res['success'])) {
            return redirect()->to('settings/integrations/zoho')->with('success', $res['message']);
        }
        return redirect()->to('settings/integrations/zoho')->with('error', $res['message'] ?? 'Token refresh failed.');
    }

    public function disconnect()
    {
        $res = $this->oauthService->disconnect();
        return redirect()->to('settings/integrations/zoho')->with('success', $res['message']);
    }

    public function selectOrganization()
    {
        $orgId = trim((string) $this->request->getPost('organization_id'));
        if ($orgId === '') {
            return redirect()->to('settings/integrations/zoho')->with('error', 'Please select a valid Zoho Organization.');
        }

        $config  = $this->settingModel->getConfig(false);
        $orgName = '';
        foreach (($config['organizations'] ?? []) as $org) {
            if ((string) ($org['organization_id'] ?? '') === $orgId) {
                $orgName = (string) ($org['name'] ?? '');
                break;
            }
        }

        $this->settingModel->saveConfig([
            'organization_id'   => $orgId,
            'organization_name' => $orgName,
        ]);

        // Prime Taxes and Chart of Accounts cache for the selected organization
        try {
            (new ZohoTaxMapperService($this->apiClient))->ensureMetadataCache(true);
        } catch (\Throwable $e) {
            // Non-fatal
        }

        return redirect()->to('settings/integrations/zoho')
            ->with('success', 'Selected Zoho Organization: ' . ($orgName ?: $orgId));
    }
}

