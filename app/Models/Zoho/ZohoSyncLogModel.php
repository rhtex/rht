<?php

namespace App\Models\Zoho;

use CodeIgniter\Model;

class ZohoSyncLogModel extends Model
{
    protected $table            = 'zoho_sync_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'batch_id',
        'queue_id',
        'module',
        'local_record_id',
        'zoho_record_id',
        'direction',
        'operation',
        'http_method',
        'endpoint',
        'request_payload',
        'response_code',
        'response_payload',
        'duration_ms',
        'status',
        'error_message',
        'created_at',
    ];

    protected $useTimestamps = false;

    public function logApiCall(array $data): int
    {
        $reqPayload = $data['request_payload'] ?? null;
        if (is_array($reqPayload)) {
            $reqPayload = json_encode($this->redactSensitiveArray($reqPayload), JSON_UNESCAPED_UNICODE);
        } elseif (is_string($reqPayload)) {
            $reqPayload = $this->redactSensitiveString($reqPayload);
        }

        $resPayload = $data['response_payload'] ?? null;
        if (is_array($resPayload)) {
            $resPayload = json_encode($this->redactSensitiveArray($resPayload), JSON_UNESCAPED_UNICODE);
        } elseif (is_string($resPayload)) {
            $resPayload = $this->redactSensitiveString($resPayload);
        }

        return (int) $this->insert([
            'batch_id'         => $data['batch_id'] ?? null,
            'queue_id'         => $data['queue_id'] ?? null,
            'module'           => $data['module'] ?? 'system',
            'local_record_id'  => $data['local_record_id'] ?? null,
            'zoho_record_id'   => $data['zoho_record_id'] ?? null,
            'direction'        => $data['direction'] ?? 'TO_ZOHO',
            'operation'        => $data['operation'] ?? 'API_CALL',
            'http_method'      => $data['http_method'] ?? 'GET',
            'endpoint'         => $this->redactSensitiveString((string) ($data['endpoint'] ?? '')),
            'request_payload'  => $reqPayload,
            'response_code'    => $data['response_code'] ?? null,
            'response_payload' => $resPayload,
            'duration_ms'      => $data['duration_ms'] ?? null,
            'status'           => $data['status'] ?? 'SUCCESS',
            'error_message'    => $data['error_message'] ?? null,
            'created_at'       => date('Y-m-d H:i:s'),
        ], true);
    }

    protected function redactSensitiveArray(array $arr): array
    {
        $sensitiveKeys = ['client_secret', 'refresh_token', 'access_token', 'code', 'authorization'];
        foreach ($arr as $k => $v) {
            if (in_array(strtolower((string) $k), $sensitiveKeys, true)) {
                $arr[$k] = '[REDACTED]';
            } elseif (is_array($v)) {
                $arr[$k] = $this->redactSensitiveArray($v);
            }
        }
        return $arr;
    }

    protected function redactSensitiveString(string $str): string
    {
        return (string) preg_replace(
            '/((?:client_secret|refresh_token|access_token|Zoho-oauthtoken)[=:\s"\']+)([^"&\s\']+)/i',
            '$1[REDACTED]',
            $str
        );
    }
}

