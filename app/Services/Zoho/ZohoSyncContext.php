<?php

namespace App\Services\Zoho;

/**
 * Execution context guard to prevent infinite synchronization loops.
 *
 * Source markers:
 * - LOCAL:  User/API action originating inside CI4 ERP (eligible for outgoing CI4 -> Zoho sync).
 * - ZOHO:   Incoming synchronization from Zoho Books (MUST NOT trigger outgoing CI4 -> Zoho sync).
 * - SYSTEM: Internal reconciliation/migration task (MUST NOT trigger outgoing CI4 -> Zoho sync).
 */
class ZohoSyncContext
{
    public const SOURCE_LOCAL  = 'LOCAL';
    public const SOURCE_ZOHO   = 'ZOHO';
    public const SOURCE_SYSTEM = 'SYSTEM';

    private static string $currentSource = self::SOURCE_LOCAL;
    private static ?string $currentBatchId = null;

    public static function getSource(): string
    {
        return self::$currentSource;
    }

    public static function setSource(string $source): void
    {
        self::$currentSource = $source;
    }

    public static function isLocal(): bool
    {
        return self::$currentSource === self::SOURCE_LOCAL;
    }

    public static function isZohoOrSystem(): bool
    {
        return self::$currentSource === self::SOURCE_ZOHO || self::$currentSource === self::SOURCE_SYSTEM;
    }

    public static function getBatchId(): ?string
    {
        return self::$currentBatchId;
    }

    public static function setBatchId(?string $batchId): void
    {
        self::$currentBatchId = $batchId;
    }

    /**
     * Run a callback with source = ZOHO so any local DB writes inside it do not trigger outgoing Zoho sync.
     */
    public static function runAsZoho(callable $callback)
    {
        $prev = self::$currentSource;
        self::$currentSource = self::SOURCE_ZOHO;
        try {
            return $callback();
        } finally {
            self::$currentSource = $prev;
        }
    }

    /**
     * Run a callback with source = SYSTEM.
     */
    public static function runAsSystem(callable $callback)
    {
        $prev = self::$currentSource;
        self::$currentSource = self::SOURCE_SYSTEM;
        try {
            return $callback();
        } finally {
            self::$currentSource = $prev;
        }
    }
}

