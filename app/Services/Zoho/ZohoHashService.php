<?php

namespace App\Services\Zoho;

class ZohoHashService
{
    /**
     * Compute a deterministic SHA-256 hash of a normalized array of business fields.
     */
    public static function hash(array $payload): string
    {
        $canonical = self::canonicalize($payload);
        return hash('sha256', json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public static function canonicalize($value)
    {
        if (is_array($value)) {
            $isList = array_keys($value) === range(0, count($value) - 1);
            if (!$isList) {
                ksort($value);
            }
            $result = [];
            foreach ($value as $k => $v) {
                // Ignore volatile metadata timestamps when computing content hash
                if (in_array((string) $k, ['created_at', 'updated_at', 'last_modified_time', 'created_time'], true)) {
                    continue;
                }
                if (is_string($k) && is_numeric($v) && preg_match('/(amount|rate|price|quantity|subtotal|balance|discount|charge|percentage|total)/i', $k)) {
                    $result[$k] = number_format((float) $v, 2, '.', '');
                } else {
                    $result[$k] = self::canonicalize($v);
                }
            }
            return $result;
        }

        if (is_float($value) || (is_string($value) && is_numeric($value) && strpos($value, '.') !== false)) {
            return number_format((float) $value, 2, '.', '');
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }
}
