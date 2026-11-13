<?php

namespace App\Services\Zoho;

class ZohoSyncProgressService
{
    protected static function getFilePath(string $sessionId): string
    {
        $safeId = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $sessionId);
        if ($safeId === '') {
            $safeId = 'default';
        }
        $dir = WRITEPATH . 'cache/zoho_sync/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        return $dir . 'progress_' . $safeId . '.json';
    }

    public static function update(string $sessionId, int $pct, string $message, string $status = 'running', array $extra = []): void
    {
        $file = self::getFilePath($sessionId);
        $data = [
            'pct'        => max(0, min(100, $pct)),
            'message'    => $message,
            'status'     => $status, // 'running', 'completed', 'failed'
            'extra'      => $extra,
            'updated_at' => microtime(true),
            'timestamp'  => date('Y-m-d H:i:s'),
        ];
        @file_put_contents($file, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX);
    }

    public static function get(string $sessionId): array
    {
        $file = self::getFilePath($sessionId);
        if (!file_exists($file)) {
            return [
                'pct'        => 0,
                'message'    => 'Waiting to start...',
                'status'     => 'idle',
                'extra'      => [],
                'updated_at' => 0,
            ];
        }

        $content = @file_get_contents($file);
        $data = json_decode($content, true);
        if (!is_array($data)) {
            return [
                'pct'        => 0,
                'message'    => 'Initializing...',
                'status'     => 'running',
                'extra'      => [],
                'updated_at' => 0,
            ];
        }
        return $data;
    }

    public static function clear(string $sessionId): void
    {
        $file = self::getFilePath($sessionId);
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
