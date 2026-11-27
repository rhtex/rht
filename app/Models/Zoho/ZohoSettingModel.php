<?php

namespace App\Models\Zoho;

use CodeIgniter\Model;

class ZohoSettingModel extends Model
{
    protected $table            = 'zoho_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'is_enabled',
        'auto_sync_enabled',
        'sync_mode',
        'data_center',
        'client_id_enc',
        'client_secret_enc',
        'refresh_token_enc',
        'access_token_enc',
        'token_expires_at',
        'organization_id',
        'organization_name',
        'organizations_cache_json',
        'oauth_redirect_url',
        'api_base_url',
        'ewaybill_api_service',
        'inventory_api_base_url',
        'oauth_scopes',
        'ewaybill_gst_username',
        'ewaybill_tax_settings_id',
        'conflict_strategy',
        'preserve_document_numbers',
        'subscription_plan',
        'daily_api_limit',
        'per_minute_limit',
        'max_concurrent_connections',
        'finance_default_account_map_json',
        'env_profiles_json',
        'last_connected_at',
        'last_sync_at',
        'last_error',
        'updated_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected static bool $schemaEnsured = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    /**
     * Automatically ensures Zoho tables exist in the database if migration was not manually run yet.
     */
    public function ensureSchema(): void
    {
        if (self::$schemaEnsured) {
            return;
        }
        self::$schemaEnsured = true;

        try {
            $loadMigration = static function (): \App\Database\Migrations\CreateZohoIntegrationTables {
                if (!class_exists(\App\Database\Migrations\CreateZohoIntegrationTables::class, false)) {
                    require_once APPPATH . 'Database/Migrations/2026-11-10-000001_CreateZohoIntegrationTables.php';
                }
                return new \App\Database\Migrations\CreateZohoIntegrationTables();
            };

            if (!$this->db->tableExists('zoho_settings') || !$this->db->tableExists('zoho_sync_mapping')) {
                $loadMigration()->up();
            } else {
                $cols = array_map('strtolower', $this->db->getFieldNames('zoho_settings'));
                if (!in_array('subscription_plan', $cols, true) || !in_array('ewaybill_api_service', $cols, true)) {
                    $loadMigration()->up();
                }
                if (!in_array('env_profiles_json', $cols, true)) {
                    $this->db->query("ALTER TABLE `zoho_settings` ADD COLUMN `env_profiles_json` LONGTEXT NULL AFTER `finance_default_account_map_json`");
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'ZohoSettingModel::ensureSchema error: ' . $e->getMessage());
        }
    }

    /**
     * Get the single Zoho configuration row with decrypted secrets for backend use.
     */
    public function getConfig(bool $decryptSecrets = true): array
    {
        $row = $this->orderBy('id', 'ASC')->first();
        if (!$row) {
            $default = [
                'is_enabled'                 => 0,
                'auto_sync_enabled'          => 1,
                'sync_mode'                  => 'immediate',
                'data_center'                => 'in',
                'api_base_url'               => 'https://www.zohoapis.in/books/v3',
                'ewaybill_api_service'       => 'inventory',
                'inventory_api_base_url'     => 'https://www.zohoapis.in/inventory/v1',
                'oauth_scopes'               => 'ZohoBooks.fullaccess.all,ZohoInventory.fullaccess.all',
                'oauth_redirect_url'         => site_url('settings/integrations/zoho/callback'),
                'conflict_strategy'          => 'manual_review',
                'preserve_document_numbers'  => 1,
                'subscription_plan'          => 'free',
                'daily_api_limit'            => 1000,
                'per_minute_limit'           => 90,
                'max_concurrent_connections' => 5,
            ];
            $this->insert($default);
            $row = $this->orderBy('id', 'ASC')->first() ?? $default;
        }

        if (empty($row['oauth_redirect_url'])) {
            $row['oauth_redirect_url'] = site_url('settings/integrations/zoho/callback');
        }
        if (empty($row['ewaybill_api_service']) || !in_array($row['ewaybill_api_service'], ['inventory', 'books'], true)) {
            $row['ewaybill_api_service'] = 'inventory';
        }
        if (empty($row['inventory_api_base_url'])) {
            if (!empty($row['api_base_url']) && strpos($row['api_base_url'], '/books/v3') !== false) {
                $row['inventory_api_base_url'] = str_replace('/books/v3', '/inventory/v1', rtrim($row['api_base_url'], '/'));
            } else {
                $dcInfo = \App\Services\Zoho\ZohoDataCenterRegistry::get($row['data_center'] ?? 'in');
                $row['inventory_api_base_url'] = $dcInfo['inventory_api_base_url'];
            }
        }
        if (empty($row['oauth_scopes'])) {
            $row['oauth_scopes'] = 'ZohoBooks.fullaccess.all,ZohoInventory.fullaccess.all';
        }

        $rawProfiles   = !empty($row['env_profiles_json']) ? (json_decode($row['env_profiles_json'], true) ?: []) : [];
        $profiles      = $this->normalizeEnvProfiles($rawProfiles);
        $currentHost   = $this->detectCurrentHost();
        $activeEnv     = $this->resolveActiveEnvironment($profiles, $currentHost);
        $activeProfile = $profiles[$activeEnv] ?? [];

        // Use active environment profile credentials when present, falling back to legacy columns
        $effClientIdEnc     = !empty($activeProfile['client_id_enc']) ? $activeProfile['client_id_enc'] : ($row['client_id_enc'] ?? null);
        $effClientSecretEnc = !empty($activeProfile['client_secret_enc']) ? $activeProfile['client_secret_enc'] : ($row['client_secret_enc'] ?? null);
        $effRefreshTokenEnc = !empty($activeProfile['refresh_token_enc']) ? $activeProfile['refresh_token_enc'] : ($row['refresh_token_enc'] ?? null);
        $effAccessTokenEnc  = !empty($activeProfile['access_token_enc']) ? $activeProfile['access_token_enc'] : ($row['access_token_enc'] ?? null);

        if (!empty($activeProfile['token_expires_at'])) {
            $row['token_expires_at'] = $activeProfile['token_expires_at'];
        }
        if (!empty($activeProfile['oauth_redirect_url'])) {
            $row['oauth_redirect_url'] = $activeProfile['oauth_redirect_url'];
        }

        if ($decryptSecrets) {
            $row['client_id']     = $this->decryptSecret($effClientIdEnc);
            $row['client_secret'] = $this->decryptSecret($effClientSecretEnc);
            $row['refresh_token'] = $this->decryptSecret($effRefreshTokenEnc);
            $row['access_token']  = $this->decryptSecret($effAccessTokenEnc);
        }

        // Masked versions safe for UI rendering (active profile)
        $row['client_id_masked']     = $this->maskSecret($this->decryptSecret($effClientIdEnc), 6);
        $row['client_secret_masked'] = $this->maskSecret($this->decryptSecret($effClientSecretEnc), 4);
        $row['refresh_token_masked'] = $this->maskSecret($this->decryptSecret($effRefreshTokenEnc), 4);
        $row['has_client_id']        = !empty($effClientIdEnc);
        $row['has_client_secret']    = !empty($effClientSecretEnc);
        $row['has_refresh_token']    = !empty($effRefreshTokenEnc);
        $row['has_access_token']     = !empty($effAccessTokenEnc);
        $row['active_env']           = $activeEnv;
        $row['active_host']          = $currentHost;
        $row['env_profiles']         = $this->buildUiEnvProfiles($profiles);
        $row['organizations']        = !empty($row['organizations_cache_json'])
            ? (json_decode($row['organizations_cache_json'], true) ?: [])
            : [];
        $row['finance_account_map']  = !empty($row['finance_default_account_map_json'])
            ? (json_decode($row['finance_default_account_map_json'], true) ?: [])
            : [];

        return $row;
    }

    /**
     * Save configuration fields, encrypting any newly provided credentials.
     */
    public function saveConfig(array $input, ?int $userId = null): bool
    {
        $current = $this->getConfig(false);
        $update  = [];

        if (array_key_exists('is_enabled', $input)) {
            $update['is_enabled'] = (int) !empty($input['is_enabled']);
        }
        if (array_key_exists('auto_sync_enabled', $input)) {
            $update['auto_sync_enabled'] = (int) !empty($input['auto_sync_enabled']);
        }
        if (!empty($input['sync_mode']) && in_array($input['sync_mode'], ['immediate', 'queue_cron'], true)) {
            $update['sync_mode'] = $input['sync_mode'];
        }
        if (!empty($input['data_center'])) {
            $update['data_center'] = trim($input['data_center']);
        }
        if (array_key_exists('organization_id', $input)) {
            $update['organization_id'] = trim((string) $input['organization_id']);
        }
        if (array_key_exists('organization_name', $input)) {
            $update['organization_name'] = trim((string) $input['organization_name']);
        }
        if (array_key_exists('oauth_redirect_url', $input)) {
            $update['oauth_redirect_url'] = trim((string) $input['oauth_redirect_url']);
        }
        if (array_key_exists('api_base_url', $input)) {
            $update['api_base_url'] = rtrim(trim((string) $input['api_base_url']), '/');
        }
        if (!empty($input['ewaybill_api_service']) && in_array($input['ewaybill_api_service'], ['inventory', 'books'], true)) {
            $update['ewaybill_api_service'] = $input['ewaybill_api_service'];
        }
        if (array_key_exists('inventory_api_base_url', $input)) {
            $invUrl = rtrim(trim((string) $input['inventory_api_base_url']), '/');
            if ($invUrl !== '') {
                $update['inventory_api_base_url'] = $invUrl;
            }
        }
        if (array_key_exists('oauth_scopes', $input)) {
            $scopes = trim((string) $input['oauth_scopes']);
            if ($scopes !== '') {
                $update['oauth_scopes'] = $scopes;
            }
        }
        if (array_key_exists('ewaybill_gst_username', $input)) {
            $update['ewaybill_gst_username'] = trim((string) $input['ewaybill_gst_username']) ?: null;
        }
        if (array_key_exists('ewaybill_tax_settings_id', $input)) {
            $update['ewaybill_tax_settings_id'] = trim((string) $input['ewaybill_tax_settings_id']) ?: null;
        }
        if (!empty($input['conflict_strategy']) && in_array($input['conflict_strategy'], ['manual_review', 'latest_timestamp', 'prefer_zoho', 'prefer_local'], true)) {
            $update['conflict_strategy'] = $input['conflict_strategy'];
        }
        if (array_key_exists('preserve_document_numbers', $input)) {
            $update['preserve_document_numbers'] = (int) !empty($input['preserve_document_numbers']);
        }
        if (!empty($input['subscription_plan']) && in_array($input['subscription_plan'], ['free', 'standard', 'professional', 'premium', 'custom'], true)) {
            $update['subscription_plan'] = $input['subscription_plan'];
        }
        if (array_key_exists('daily_api_limit', $input)) {
            $update['daily_api_limit'] = max(100, (int) $input['daily_api_limit']);
        }
        if (array_key_exists('per_minute_limit', $input)) {
            // Zoho Books per-minute cap is fixed at 100 req/min across all subscription tiers
            $update['per_minute_limit'] = min(100, max(10, (int) $input['per_minute_limit']));
        }
        if (array_key_exists('max_concurrent_connections', $input)) {
            $plan = $update['subscription_plan'] ?? ($current['subscription_plan'] ?? 'free');
            $maxAllowedConcurrent = ($plan === 'free') ? 5 : 10;
            $update['max_concurrent_connections'] = min($maxAllowedConcurrent, max(1, (int) $input['max_concurrent_connections']));
        }

        // Update 4-environment credential profiles when submitted from settings UI,
        // or update the active environment profile when OAuth refreshes/exchanges tokens.
        $rawProfiles = !empty($current['env_profiles_json']) ? (json_decode($current['env_profiles_json'], true) ?: []) : [];
        $profiles    = $this->normalizeEnvProfiles($rawProfiles);
        $activeEnv   = $current['active_env'] ?? $this->resolveActiveEnvironment($profiles, $this->detectCurrentHost());
        $profilesChanged = false;

        if (!empty($input['env_profiles']) && is_array($input['env_profiles'])) {
            foreach (['localhost', 'dev', 'test', 'prod'] as $envKey) {
                if (!isset($input['env_profiles'][$envKey]) || !is_array($input['env_profiles'][$envKey])) {
                    continue;
                }
                $envInput = $input['env_profiles'][$envKey];
                if (array_key_exists('hostnames', $envInput)) {
                    $profiles[$envKey]['hostnames'] = trim((string) $envInput['hostnames']);
                }
                if (array_key_exists('oauth_redirect_url', $envInput)) {
                    $profiles[$envKey]['oauth_redirect_url'] = trim((string) $envInput['oauth_redirect_url']);
                }
                foreach (['client_id', 'client_secret', 'refresh_token', 'access_token'] as $secretKey) {
                    if (isset($envInput[$secretKey])) {
                        $val = trim((string) $envInput[$secretKey]);
                        if ($val !== '' && strpos($val, '••••') === false && strpos($val, '****') === false) {
                            $profiles[$envKey][$secretKey . '_enc'] = $this->encryptSecret($val);
                        } elseif (!empty($envInput['clear_' . $secretKey])) {
                            $profiles[$envKey][$secretKey . '_enc'] = null;
                        }
                    }
                }
            }
            $profilesChanged = true;
        }

        // Only update active/legacy secrets if non-empty and not containing mask bullets
        foreach (['client_id', 'client_secret', 'refresh_token', 'access_token'] as $secretKey) {
            if (isset($input[$secretKey])) {
                $val = trim((string) $input[$secretKey]);
                if ($val !== '' && strpos($val, '••••') === false && strpos($val, '****') === false) {
                    $encVal = $this->encryptSecret($val);
                    $update[$secretKey . '_enc'] = $encVal;
                    $profiles[$activeEnv][$secretKey . '_enc'] = $encVal;
                    $profilesChanged = true;
                } elseif (!empty($input['clear_' . $secretKey])) {
                    $update[$secretKey . '_enc'] = null;
                    $profiles[$activeEnv][$secretKey . '_enc'] = null;
                    $profilesChanged = true;
                }
            }
        }

        if (array_key_exists('token_expires_at', $input)) {
            $update['token_expires_at'] = $input['token_expires_at'];
            $profiles[$activeEnv]['token_expires_at'] = $input['token_expires_at'];
            $profilesChanged = true;
        }

        if ($profilesChanged) {
            $update['env_profiles_json'] = json_encode($profiles);
        }
        if (array_key_exists('organizations_cache_json', $input)) {
            $update['organizations_cache_json'] = is_array($input['organizations_cache_json'])
                ? json_encode($input['organizations_cache_json'])
                : $input['organizations_cache_json'];
        }
        if (array_key_exists('finance_default_account_map_json', $input)) {
            $update['finance_default_account_map_json'] = is_array($input['finance_default_account_map_json'])
                ? json_encode($input['finance_default_account_map_json'])
                : $input['finance_default_account_map_json'];
        }
        if (array_key_exists('last_connected_at', $input)) {
            $update['last_connected_at'] = $input['last_connected_at'];
        }
        if (array_key_exists('last_sync_at', $input)) {
            $update['last_sync_at'] = $input['last_sync_at'];
        }
        if (array_key_exists('last_error', $input)) {
            $update['last_error'] = $input['last_error'];
        }
        if ($userId !== null) {
            $update['updated_by'] = $userId;
        }

        return (bool) $this->update($current['id'], $update);
    }

    /**
     * Authenticated AES-256-CBC + HMAC-SHA256 encryption.
     */
    public function encryptSecret(?string $plainText): ?string
    {
        if ($plainText === null || $plainText === '') {
            return null;
        }

        $key    = $this->getEncryptionKey();
        $ivLen  = openssl_cipher_iv_length('AES-256-CBC');
        $iv     = random_bytes($ivLen);
        $cipher = openssl_encrypt($plainText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($cipher === false) {
            return null;
        }

        $hmac = hash_hmac('sha256', $iv . $cipher, $key, true);
        return base64_encode($iv . $hmac . $cipher);
    }

    /**
     * Decrypts a secret encrypted by encryptSecret().
     */
    public function decryptSecret(?string $encoded): string
    {
        if ($encoded === null || $encoded === '') {
            return '';
        }

        $raw = base64_decode($encoded, true);
        if ($raw === false) {
            return '';
        }

        $key   = $this->getEncryptionKey();
        $ivLen = openssl_cipher_iv_length('AES-256-CBC');
        if (strlen($raw) <= ($ivLen + 32)) {
            return '';
        }

        $iv         = substr($raw, 0, $ivLen);
        $hmac       = substr($raw, $ivLen, 32);
        $cipherText = substr($raw, $ivLen + 32);

        $keysToTry = [$this->getEncryptionKey(), $this->getLegacyEncryptionKey()];
        foreach ($keysToTry as $key) {
            $calcHmac = hash_hmac('sha256', $iv . $cipherText, $key, true);
            if (hash_equals($hmac, $calcHmac)) {
                $plain = openssl_decrypt($cipherText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
                return $plain !== false ? $plain : '';
            }
        }

        return '';
    }

    protected function getEncryptionKey(): string
    {
        $envKey = env('encryption.key', '');
        if (!empty($envKey)) {
            return hash('sha256', (string) $envKey, true);
        }
        return hash('sha256', 'RHT_ZOHO_VAULT_v2_SHARED', true);
    }

    protected function getLegacyEncryptionKey(): string
    {
        $dbName = env('database.default.database', 'rasidev_hr');
        return hash('sha256', ROOTPATH . '|RHT_ZOHO_VAULT_v1|' . $dbName, true);
    }

    public function detectCurrentHost(): string
    {
        if (!is_cli()) {
            $rawHost = (string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
        } else {
            $rawHost = (string) (parse_url((string) (config('App')->baseURL ?? ''), PHP_URL_HOST) ?: 'localhost');
        }
        $host = strtolower(trim($rawHost));
        // Strip port if present (e.g. localhost:8080 -> localhost)
        if (strpos($host, ':') !== false && substr($host, 0, 1) !== '[') {
            $host = explode(':', $host)[0];
        }
        return $host;
    }

    protected function normalizeEnvProfiles(array $raw): array
    {
        $defaults = [
            'localhost' => ['label' => 'Localhost', 'hostnames' => 'localhost,127.0.0.1'],
            'dev'       => ['label' => 'Dev',       'hostnames' => ''],
            'test'      => ['label' => 'Test',      'hostnames' => ''],
            'prod'      => ['label' => 'Prod',      'hostnames' => ''],
        ];

        $out = [];
        foreach ($defaults as $key => $def) {
            $existing  = is_array($raw[$key] ?? null) ? $raw[$key] : [];
            $out[$key] = [
                'hostnames'          => (string) ($existing['hostnames'] ?? $def['hostnames']),
                'oauth_redirect_url' => (string) ($existing['oauth_redirect_url'] ?? ''),
                'client_id_enc'      => $existing['client_id_enc'] ?? null,
                'client_secret_enc'  => $existing['client_secret_enc'] ?? null,
                'refresh_token_enc'  => $existing['refresh_token_enc'] ?? null,
                'access_token_enc'   => $existing['access_token_enc'] ?? null,
                'token_expires_at'   => $existing['token_expires_at'] ?? null,
            ];
        }
        return $out;
    }

    public function resolveActiveEnvironment(array $profiles, ?string $host = null): string
    {
        if (is_cli()) {
            $cliEnv = strtolower(trim((string) env('ZOHO_ENV', '')));
            if (in_array($cliEnv, ['localhost', 'dev', 'test', 'prod'], true)) {
                return $cliEnv;
            }
        }

        $host = strtolower(trim((string) ($host ?? $this->detectCurrentHost())));
        if ($host !== '') {
            foreach (['localhost', 'dev', 'test', 'prod'] as $envKey) {
                $list = array_filter(array_map('trim', explode(',', strtolower((string) ($profiles[$envKey]['hostnames'] ?? '')))));
                foreach ($list as $pattern) {
                    if ($pattern === $host) {
                        return $envKey;
                    }
                    if (strpos($pattern, '*.') === 0 && str_ends_with($host, substr($pattern, 1))) {
                        return $envKey;
                    }
                }
            }

            if (in_array($host, ['localhost', '127.0.0.1', '::1'], true) || str_ends_with($host, '.local') || str_ends_with($host, '.test')) {
                return 'localhost';
            }
            if (str_starts_with($host, 'dev.') || str_contains($host, '.dev.')) {
                return 'dev';
            }
            if (str_starts_with($host, 'test.') || str_starts_with($host, 'staging.') || str_starts_with($host, 'uat.')) {
                return 'test';
            }
            return 'prod';
        }

        return (ENVIRONMENT === 'development') ? 'dev' : 'prod';
    }

    protected function buildUiEnvProfiles(array $profiles): array
    {
        $labels = ['localhost' => 'Localhost', 'dev' => 'Dev', 'test' => 'Test', 'prod' => 'Prod'];
        $ui     = [];
        foreach ($labels as $key => $label) {
            $p        = $profiles[$key] ?? [];
            $ui[$key] = [
                'label'                => $label,
                'hostnames'            => $p['hostnames'] ?? '',
                'oauth_redirect_url'   => $p['oauth_redirect_url'] ?? '',
                'has_client_id'        => !empty($p['client_id_enc']),
                'has_client_secret'    => !empty($p['client_secret_enc']),
                'has_refresh_token'    => !empty($p['refresh_token_enc']),
                'has_access_token'     => !empty($p['access_token_enc']),
                'client_id_masked'     => $this->maskSecret($this->decryptSecret($p['client_id_enc'] ?? null), 6),
                'client_secret_masked' => $this->maskSecret($this->decryptSecret($p['client_secret_enc'] ?? null), 4),
                'refresh_token_masked' => $this->maskSecret($this->decryptSecret($p['refresh_token_enc'] ?? null), 4),
            ];
        }
        return $ui;
    }

    public function maskSecret(string $secret, int $visibleChars = 4): string
    {
        $len = strlen($secret);
        if ($len === 0) {
            return '';
        }
        if ($len <= $visibleChars * 2) {
            return str_repeat('•', 8) . substr($secret, -2);
        }
        return substr($secret, 0, $visibleChars) . '••••••••••••' . substr($secret, -$visibleChars);
    }
}

