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
        'conflict_strategy',
        'preserve_document_numbers',
        'subscription_plan',
        'daily_api_limit',
        'per_minute_limit',
        'max_concurrent_connections',
        'finance_default_account_map_json',
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
            if (!$this->db->tableExists('zoho_settings') || !$this->db->tableExists('zoho_sync_mapping')) {
                $migration = new \App\Database\Migrations\CreateZohoIntegrationTables();
                $migration->up();
            } else {
                $cols = array_map('strtolower', $this->db->getFieldNames('zoho_settings'));
                if (!in_array('subscription_plan', $cols, true)) {
                    $migration = new \App\Database\Migrations\CreateZohoIntegrationTables();
                    $migration->up();
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

        if ($decryptSecrets) {
            $row['client_id']     = $this->decryptSecret($row['client_id_enc'] ?? null);
            $row['client_secret'] = $this->decryptSecret($row['client_secret_enc'] ?? null);
            $row['refresh_token'] = $this->decryptSecret($row['refresh_token_enc'] ?? null);
            $row['access_token']  = $this->decryptSecret($row['access_token_enc'] ?? null);
        }

        // Masked versions safe for UI rendering
        $row['client_id_masked']     = $this->maskSecret($this->decryptSecret($row['client_id_enc'] ?? null), 6);
        $row['client_secret_masked'] = $this->maskSecret($this->decryptSecret($row['client_secret_enc'] ?? null), 4);
        $row['refresh_token_masked'] = $this->maskSecret($this->decryptSecret($row['refresh_token_enc'] ?? null), 4);
        $row['has_client_id']        = !empty($row['client_id_enc']);
        $row['has_client_secret']    = !empty($row['client_secret_enc']);
        $row['has_refresh_token']    = !empty($row['refresh_token_enc']);
        $row['has_access_token']     = !empty($row['access_token_enc']);
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

        // Only update secrets if non-empty and not containing mask bullets
        foreach (['client_id', 'client_secret', 'refresh_token', 'access_token'] as $secretKey) {
            if (isset($input[$secretKey])) {
                $val = trim((string) $input[$secretKey]);
                if ($val !== '' && strpos($val, '••••') === false && strpos($val, '****') === false) {
                    $update[$secretKey . '_enc'] = $this->encryptSecret($val);
                } elseif (!empty($input['clear_' . $secretKey])) {
                    $update[$secretKey . '_enc'] = null;
                }
            }
        }

        if (array_key_exists('token_expires_at', $input)) {
            $update['token_expires_at'] = $input['token_expires_at'];
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
        $calcHmac   = hash_hmac('sha256', $iv . $cipherText, $key, true);

        if (!hash_equals($hmac, $calcHmac)) {
            return '';
        }

        $plain = openssl_decrypt($cipherText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $plain !== false ? $plain : '';
    }

    protected function getEncryptionKey(): string
    {
        $envKey = env('encryption.key', '');
        if (!empty($envKey)) {
            return hash('sha256', (string) $envKey, true);
        }
        $dbName = env('database.default.database', 'rasidev_hr');
        return hash('sha256', ROOTPATH . '|RHT_ZOHO_VAULT_v1|' . $dbName, true);
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

