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

        // Offload large payloads to disk to keep database lightweight & fast
        $payloadFileRef = null;
        if (($resPayload !== null && strlen($resPayload) > 2048) || ($reqPayload !== null && strlen($reqPayload) > 2048)) {
            $payloadFileRef = $this->storePayloadOnDisk($data['module'] ?? 'system', $data['operation'] ?? 'API', $reqPayload, $resPayload);
            // In database, store a reference pointer instead of full multi-megabyte JSON
            if ($payloadFileRef) {
                if ($resPayload !== null && strlen($resPayload) > 2048) {
                    $resPayload = json_encode(['_storage' => 'file', 'file' => $payloadFileRef, 'preview' => mb_substr($resPayload, 0, 500) . '...']);
                }
                if ($reqPayload !== null && strlen($reqPayload) > 2048) {
                    $reqPayload = json_encode(['_storage' => 'file', 'file' => $payloadFileRef, 'preview' => mb_substr($reqPayload, 0, 500) . '...']);
                }
            }
        }

        // Opportunistic cleanup of logs older than 90 days (1 in 50 chance per request)
        if (mt_rand(1, 50) === 1) {
            $this->pruneLogsOlderThan(90);
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

    /**
     * Store raw request & response JSON into daily log files on disk
     */
    protected function storePayloadOnDisk(string $module, string $op, ?string $req, ?string $res): ?string
    {
        try {
            $dir = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'zoho_payloads' . DIRECTORY_SEPARATOR . date('Y-m');
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $filename = sprintf('zoho_%s_%s_%s_%s.json', date('Y-m-d_His'), preg_replace('/[^a-zA-Z0-9_-]/', '', $module), preg_replace('/[^a-zA-Z0-9_-]/', '', $op), bin2hex(random_bytes(4)));
            $fullPath = $dir . DIRECTORY_SEPARATOR . $filename;
            $data = [
                'timestamp' => date('Y-m-d H:i:s'),
                'module'    => $module,
                'operation' => $op,
                'request'   => $req,
                'response'  => $res,
            ];
            @file_put_contents($fullPath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return date('Y-m') . '/' . $filename;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Fetch payload content for inspect modal, reading from disk if it was offloaded
     */
    public function getFullPayload(int $logId): array
    {
        $log = $this->find($logId);
        if (!$log) {
            return ['request' => '', 'response' => ''];
        }

        $res = $log['response_payload'] ?? '';
        $req = $log['request_payload'] ?? '';

        // Check if response points to disk file
        if ($res && str_contains($res, '"_storage":"file"')) {
            $parsed = json_decode($res, true);
            if (!empty($parsed['file'])) {
                $filePath = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'zoho_payloads' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $parsed['file']);
                if (file_exists($filePath)) {
                    $fileJson = json_decode(@file_get_contents($filePath), true);
                    if ($fileJson) {
                        $res = is_string($fileJson['response']) ? $fileJson['response'] : json_encode($fileJson['response'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                        $req = is_string($fileJson['request']) ? $fileJson['request'] : json_encode($fileJson['request'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    }
                }
            }
        }

        return ['request' => $req, 'response' => $res, 'endpoint' => $log['endpoint'] ?? ''];
    }

    /**
     * Prune database logs and disk files older than $days (default: 90 days)
     */
    public function pruneLogsOlderThan(int $days = 90): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // 1. Delete old rows from database
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        $deleted = $builder->where('created_at <', $cutoff)->delete();

        // 2. Prune old payload files on disk older than cutoff
        $baseDir = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'zoho_payloads';
        if (is_dir($baseDir)) {
            $cutoffTimestamp = strtotime("-{$days} days");
            $iter = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($baseDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iter as $file) {
                if ($file->isFile() && $file->getMTime() < $cutoffTimestamp) {
                    @unlink($file->getRealPath());
                } elseif ($file->isDir()) {
                    @rmdir($file->getRealPath());
                }
            }
        }

        return is_numeric($deleted) ? (int) $deleted : 0;
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

