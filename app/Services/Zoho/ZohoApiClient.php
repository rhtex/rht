<?php

namespace App\Services\Zoho;

use App\Models\Zoho\ZohoSettingModel;
use App\Models\Zoho\ZohoSyncLogModel;

class ZohoApiClient
{
    protected ZohoSettingModel $settingModel;
    protected ZohoSyncLogModel $logModel;
    protected ZohoOAuthService $oauthService;
    protected ZohoRateLimiterService $rateLimiter;

    public function __construct()
    {
        $this->settingModel = new ZohoSettingModel();
        $this->logModel     = new ZohoSyncLogModel();
        $this->oauthService = new ZohoOAuthService();
        $this->rateLimiter  = new ZohoRateLimiterService();
    }

    public function getRateLimiter(): ZohoRateLimiterService
    {
        return $this->rateLimiter;
    }

    public function getRateLimitUsage(): array
    {
        return $this->rateLimiter->getUsageStats($this->settingModel->getConfig(false));
    }

    public function get(string $endpoint, array $query = [], array $meta = []): array
    {
        return $this->request('GET', $endpoint, null, $query, $meta);
    }

    public function post(string $endpoint, array $payload = [], array $query = [], array $meta = []): array
    {
        return $this->request('POST', $endpoint, $payload, $query, $meta);
    }

    public function put(string $endpoint, array $payload = [], array $query = [], array $meta = []): array
    {
        return $this->request('PUT', $endpoint, $payload, $query, $meta);
    }

    public function delete(string $endpoint, array $query = [], array $meta = []): array
    {
        return $this->request('DELETE', $endpoint, null, $query, $meta);
    }

    /**
     * Fetch all pages for a Zoho list endpoint using page_context.has_more_page.
     */
    public function getAllPages(string $endpoint, string $listKey, array $query = [], int $maxPages = 50, array $meta = []): array
    {
        $page    = 1;
        $perPage = (int) ($query['per_page'] ?? 200);
        $records = [];

        while ($page <= $maxPages) {
            $query['page']     = $page;
            $query['per_page'] = $perPage;

            $res = $this->get($endpoint, $query, array_merge($meta, ['operation' => ($meta['operation'] ?? 'LIST') . '_P' . $page]));
            if (empty($res['success'])) {
                return [
                    'success' => false,
                    'message' => $res['message'] ?? 'Failed to fetch list from Zoho Books.',
                    'records' => $records,
                    'error'   => $res,
                ];
            }

            $data      = $res['data'] ?? [];
            $pageItems = $data[$listKey] ?? [];
            if (is_array($pageItems)) {
                foreach ($pageItems as $item) {
                    $records[] = $item;
                }
            }

            $hasMore = !empty($data['page_context']['has_more_page']);
            if (!$hasMore || empty($pageItems)) {
                break;
            }
            $page++;
        }

        return [
            'success' => true,
            'records' => $records,
            'count'   => count($records),
        ];
    }

    /**
     * Retrieve available Zoho Books organizations and cache them in zoho_settings.
     */
    public function fetchOrganizations(): array
    {
        $res = $this->request('GET', '/organizations', null, [], [
            'module'          => 'organizations',
            'operation'       => 'FETCH_ORGANIZATIONS',
            'skip_org_param'  => true,
        ]);

        if (empty($res['success'])) {
            return $res;
        }

        $orgs = $res['data']['organizations'] ?? [];
        $config = $this->settingModel->getConfig(false);

        $selectedOrgId   = $config['organization_id'] ?? '';
        $selectedOrgName = $config['organization_name'] ?? '';

        if (!empty($orgs)) {
            if (empty($selectedOrgId)) {
                // Prefer default org or first org
                $chosen = $orgs[0];
                foreach ($orgs as $org) {
                    if (!empty($org['is_default_org'])) {
                        $chosen = $org;
                        break;
                    }
                }
                $selectedOrgId   = (string) ($chosen['organization_id'] ?? '');
                $selectedOrgName = (string) ($chosen['name'] ?? '');
            } else {
                foreach ($orgs as $org) {
                    if ((string) ($org['organization_id'] ?? '') === (string) $selectedOrgId) {
                        $selectedOrgName = (string) ($org['name'] ?? $selectedOrgName);
                        break;
                    }
                }
            }
        }

        $this->settingModel->saveConfig([
            'organization_id'          => $selectedOrgId,
            'organization_name'        => $selectedOrgName,
            'organizations_cache_json' => $orgs,
            'last_connected_at'        => date('Y-m-d H:i:s'),
            'last_error'               => null,
        ]);

        return [
            'success'           => true,
            'organizations'     => $orgs,
            'organization_id'   => $selectedOrgId,
            'organization_name' => $selectedOrgName,
            'message'           => 'Retrieved ' . count($orgs) . ' organization(s) from Zoho Books.',
        ];
    }

    /**
     * Test the Zoho Books API connection and verify organization access.
     */
    public function testConnection(): array
    {
        try {
            $orgResult = $this->fetchOrganizations();
            if (empty($orgResult['success'])) {
                return $orgResult;
            }

            $orgId   = $orgResult['organization_id'] ?? '';
            $orgName = $orgResult['organization_name'] ?? '';

            return [
                'success'           => true,
                'organization_id'   => $orgId,
                'organization_name' => $orgName,
                'organizations'     => $orgResult['organizations'] ?? [],
                'message'           => 'Connected to Zoho Books successfully' . ($orgName ? " (Organization: {$orgName} [{$orgId}])" : '.'),
            ];
        } catch (\Throwable $e) {
            $this->settingModel->saveConfig(['last_error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Core HTTP request dispatcher with automatic OAuth token refresh (401) and rate-limit retry (429).
     */
    public function request(string $method, string $endpoint, ?array $payload = null, array $query = [], array $meta = []): array
    {
        $config = $this->settingModel->getConfig(true);
        $dc     = ZohoDataCenterRegistry::get($config['data_center'] ?? 'in');

        $baseUrl = !empty($config['api_base_url'])
            ? rtrim($config['api_base_url'], '/')
            : $dc['api_base_url'];

        $endpoint = '/' . ltrim($endpoint, '/');

        if (empty($meta['skip_org_param'])) {
            $orgId = $query['organization_id'] ?? $config['organization_id'] ?? '';
            if (empty($orgId)) {
                return [
                    'success' => false,
                    'code'    => 400,
                    'message' => 'Zoho Organization ID is not configured. Please connect and select a Zoho Books Organization.',
                ];
            }
            $query['organization_id'] = $orgId;
        }

        $url = $baseUrl . $endpoint;
        if (!empty($query)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($query);
        }

        $maxRetries = 3;
        $attempt    = 0;
        $forceTokenRefresh = false;
        $orgIdForLimiter   = (string) ($query['organization_id'] ?? $config['organization_id'] ?? 'default');

        while ($attempt < $maxRetries) {
            $attempt++;
            try {
                $accessToken = $this->oauthService->getValidAccessToken($forceTokenRefresh);
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'code'    => 401,
                    'message' => $e->getMessage(),
                ];
            }

            // Enforce:
            // 1) Concurrent Connections limit (Free Plan: 5 concurrent connections max)
            // 2) Per-Minute Rate Limit (100 req/min cap, ~1.6 req/sec smooth pacing)
            // 3) Daily API Allowance (Free Plan: 1,000 API calls/day)
            $acquired = $this->rateLimiter->acquireBeforeRequest($config, 25);
            if (empty($acquired['allowed'])) {
                $this->logModel->logApiCall([
                    'batch_id'         => $meta['batch_id'] ?? ZohoSyncContext::getBatchId(),
                    'queue_id'         => $meta['queue_id'] ?? null,
                    'module'           => $meta['module'] ?? 'system',
                    'local_record_id'  => $meta['local_record_id'] ?? null,
                    'zoho_record_id'   => $meta['zoho_record_id'] ?? null,
                    'direction'        => $meta['direction'] ?? 'TO_ZOHO',
                    'operation'        => $meta['operation'] ?? strtoupper($method),
                    'http_method'      => strtoupper($method),
                    'endpoint'         => $url,
                    'request_payload'  => $payload,
                    'response_code'    => 429,
                    'response_payload' => ['reason' => $acquired['reason'] ?? 'RATE_LIMITED', 'message' => $acquired['message'] ?? ''],
                    'duration_ms'      => (int) ($acquired['waited_ms'] ?? 0),
                    'status'           => 'RATE_LIMITED',
                    'error_message'    => $acquired['message'] ?? 'Zoho Books rate/quota limit reached.',
                ]);

                return [
                    'success'      => false,
                    'http_code'    => 429,
                    'code'         => 429,
                    'rate_limited' => true,
                    'reason'       => $acquired['reason'] ?? 'RATE_LIMITED',
                    'message'      => $acquired['message'] ?? 'Zoho Books API rate or daily quota limit reached.',
                ];
            }

            $headers = [
                'Authorization: Zoho-oauthtoken ' . $accessToken,
                'Accept: application/json',
            ];

            $responseHeaders = [];
            $ch = curl_init($url);
            $curlOpts = [
                CURLOPT_CUSTOMREQUEST  => strtoupper($method),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 45,
                CURLOPT_CONNECTTIMEOUT => 12,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_HEADERFUNCTION => static function ($curl, $headerLine) use (&$responseHeaders) {
                    $len   = strlen($headerLine);
                    $parts = explode(':', $headerLine, 2);
                    if (count($parts) === 2) {
                        $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                    }
                    return $len;
                },
            ];

            if ($payload !== null && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'], true)) {
                $headers[] = 'Content-Type: application/json;charset=UTF-8';
                $curlOpts[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_UNICODE);
            }

            $curlOpts[CURLOPT_HTTPHEADER] = $headers;
            curl_setopt_array($ch, $curlOpts);

            $start = microtime(true);
            try {
                $rawBody  = curl_exec($ch);
                $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr  = curl_error($ch);
            } finally {
                curl_close($ch);
                // Always release the concurrency slot (1..5) immediately after HTTP transfer completes
                $this->rateLimiter->releaseConcurrencySlot();
            }

            $durationMs = (int) round((microtime(true) - $start) * 1000);
            $decoded    = is_string($rawBody) ? (json_decode($rawBody, true) ?: []) : [];
            $zohoCode   = isset($decoded['code']) ? (int) $decoded['code'] : -1;
            $zohoMsg    = (string) ($decoded['message'] ?? '');

            // Handle 401 Unauthorized once by refreshing access token
            if ($httpCode === 401 && !$forceTokenRefresh) {
                $forceTokenRefresh = true;
                continue;
            }

            // Handle 429 Rate Limit (exceeding 100 requests/min or daily limit) with Retry-After / exponential backoff
            if ($httpCode === 429 || in_array($zohoCode, [45, 1040], true)) {
                $retryAfterSec = isset($responseHeaders['retry-after']) ? max(2, (int) $responseHeaders['retry-after']) : ($attempt * 3);
                $this->rateLimiter->recordRateLimitHit($orgIdForLimiter, $retryAfterSec);

                $this->logModel->logApiCall([
                    'batch_id'         => $meta['batch_id'] ?? ZohoSyncContext::getBatchId(),
                    'queue_id'         => $meta['queue_id'] ?? null,
                    'module'           => $meta['module'] ?? 'system',
                    'local_record_id'  => $meta['local_record_id'] ?? null,
                    'zoho_record_id'   => $meta['zoho_record_id'] ?? null,
                    'direction'        => $meta['direction'] ?? 'TO_ZOHO',
                    'operation'        => $meta['operation'] ?? strtoupper($method),
                    'http_method'      => strtoupper($method),
                    'endpoint'         => $url,
                    'request_payload'  => $payload,
                    'response_code'    => $httpCode ?: 429,
                    'response_payload' => $decoded ?: $rawBody,
                    'duration_ms'      => $durationMs,
                    'status'           => 'RATE_LIMITED',
                    'error_message'    => ($zohoMsg ?: 'Zoho API rate limit (HTTP 429) reached') . " — backing off {$retryAfterSec}s.",
                ]);

                if ($attempt < $maxRetries) {
                    sleep(min($retryAfterSec, 10));
                    continue;
                }
            }

            $isSuccess = empty($curlErr) && $httpCode >= 200 && $httpCode < 300 && ($zohoCode === 0 || !isset($decoded['code']));

            $this->logModel->logApiCall([
                'batch_id'         => $meta['batch_id'] ?? ZohoSyncContext::getBatchId(),
                'queue_id'         => $meta['queue_id'] ?? null,
                'module'           => $meta['module'] ?? 'system',
                'local_record_id'  => $meta['local_record_id'] ?? null,
                'zoho_record_id'   => $meta['zoho_record_id'] ?? null,
                'direction'        => $meta['direction'] ?? 'TO_ZOHO',
                'operation'        => $meta['operation'] ?? strtoupper($method),
                'http_method'      => strtoupper($method),
                'endpoint'         => $url,
                'request_payload'  => $payload,
                'response_code'    => $httpCode,
                'response_payload' => $decoded ?: $rawBody,
                'duration_ms'      => $durationMs,
                'status'           => $isSuccess ? 'SUCCESS' : 'FAILED',
                'error_message'    => $isSuccess ? null : ($curlErr ?: ($zohoMsg ?: "HTTP {$httpCode}")),
            ]);

            if ($isSuccess) {
                return [
                    'success'   => true,
                    'http_code' => $httpCode,
                    'zoho_code' => $zohoCode,
                    'message'   => $zohoMsg ?: 'OK',
                    'data'      => $decoded,
                ];
            }

            return [
                'success'   => false,
                'http_code' => $httpCode,
                'zoho_code' => $zohoCode,
                'message'   => $curlErr ?: ($zohoMsg ?: "Zoho Books API error (HTTP {$httpCode})"),
                'data'      => $decoded,
            ];
        }

        return [
            'success' => false,
            'message' => 'Exceeded maximum Zoho API retry attempts.',
        ];
    }
}

