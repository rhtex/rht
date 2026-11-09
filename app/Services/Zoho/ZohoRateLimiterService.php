<?php

namespace App\Services\Zoho;

use App\Models\Zoho\ZohoSettingModel;

/**
 * Enforces Zoho Books API v3 Frequency, Concurrency, and Daily Quota Limits:
 *
 * 1. Per-Minute Rate Limit:
 *    - Hard Zoho Cap: 100 requests / minute / organization (~1.66 requests/sec, >= 600ms pacing).
 *    - Fixed across all subscription tiers. Exceeding triggers HTTP 429.
 *
 * 2. Daily API Limits (by Subscription Plan):
 *    - Free Plan:         1,000 API calls / 24-hour day
 *    - Standard Plan:     2,000 API calls / 24-hour day
 *    - Professional Plan: 5,000 API calls / 24-hour day
 *    - Premium/Ultimate: 10,000 API calls / 24-hour day
 *
 * 3. Concurrent Connections:
 *    - Free Plan:         5 concurrent connections maximum
 *    - Paid Plans:       10 concurrent connections maximum
 */
class ZohoRateLimiterService
{
    public const HARD_PER_MINUTE_CAP         = 100;
    public const DEFAULT_SAFE_PER_MINUTE     = 90;
    public const FREE_PLAN_DAILY_LIMIT       = 1000;
    public const FREE_PLAN_CONCURRENT_LIMIT  = 5;
    public const MIN_INTERVAL_MICROSECONDS   = 625000; // 625ms = 1.60 req/sec (under 100 req/min)

    public const PLAN_PRESETS = [
        'free' => [
            'code'           => 'free',
            'label'          => 'Free Plan (1,000 calls/day • 5 concurrent • 100/min)',
            'daily_limit'    => 1000,
            'per_minute'     => 90,
            'max_concurrent' => 5,
        ],
        'standard' => [
            'code'           => 'standard',
            'label'          => 'Standard Plan (2,000 calls/day • 10 concurrent • 100/min)',
            'daily_limit'    => 2000,
            'per_minute'     => 95,
            'max_concurrent' => 10,
        ],
        'professional' => [
            'code'           => 'professional',
            'label'          => 'Professional Plan (5,000 calls/day • 10 concurrent • 100/min)',
            'daily_limit'    => 5000,
            'per_minute'     => 95,
            'max_concurrent' => 10,
        ],
        'premium' => [
            'code'           => 'premium',
            'label'          => 'Premium / Elite / Ultimate (10,000 calls/day • 10 concurrent • 100/min)',
            'daily_limit'    => 10000,
            'per_minute'     => 95,
            'max_concurrent' => 10,
        ],
        'custom' => [
            'code'           => 'custom',
            'label'          => 'Custom Plan Limits',
            'daily_limit'    => 1000,
            'per_minute'     => 90,
            'max_concurrent' => 5,
        ],
    ];

    protected string $lockDir;

    /**
     * Active concurrency lock file handle held by the current request.
     * @var resource|null
     */
    protected $activeSlotHandle = null;
    protected ?int $activeSlotIndex = null;

    public function __construct()
    {
        $baseWritable = defined('WRITEPATH') ? WRITEPATH : (dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR);
        $this->lockDir = rtrim($baseWritable, '/\\') . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'zoho_rate_limit';
        if (!is_dir($this->lockDir)) {
            @mkdir($this->lockDir, 0775, true);
        }
    }

    public static function getPlanPresets(): array
    {
        return self::PLAN_PRESETS;
    }

    /**
     * Resolve effective rate limit configuration for the organization.
     */
    public function getEffectiveLimits(array $config = []): array
    {
        if (empty($config)) {
            try {
                $config = (new ZohoSettingModel())->getConfig(false);
            } catch (\Throwable $e) {
                $config = [];
            }
        }

        $plan = strtolower(trim((string) ($config['subscription_plan'] ?? 'free')));
        $preset = self::PLAN_PRESETS[$plan] ?? self::PLAN_PRESETS['free'];

        $dailyLimit = (int) ($config['daily_api_limit'] ?? 0);
        if ($dailyLimit <= 0) {
            $dailyLimit = $preset['daily_limit'];
        }

        // Per-minute limit is strictly capped at 100 requests/minute across all Zoho Books tiers
        $perMinuteLimit = (int) ($config['per_minute_limit'] ?? 0);
        if ($perMinuteLimit <= 0) {
            $perMinuteLimit = $preset['per_minute'];
        }
        $perMinuteLimit = min(self::HARD_PER_MINUTE_CAP, max(10, $perMinuteLimit));

        // Concurrent connections limit (Free Plan = 5 concurrent connections)
        $maxConcurrent = (int) ($config['max_concurrent_connections'] ?? 0);
        if ($maxConcurrent <= 0) {
            $maxConcurrent = $preset['max_concurrent'];
        }
        if ($plan === 'free') {
            $maxConcurrent = min(self::FREE_PLAN_CONCURRENT_LIMIT, max(1, $maxConcurrent));
        } else {
            $maxConcurrent = min(10, max(1, $maxConcurrent));
        }

        return [
            'subscription_plan'          => $plan,
            'plan_label'                 => $preset['label'],
            'daily_api_limit'            => $dailyLimit,
            'per_minute_limit'           => $perMinuteLimit,
            'hard_per_minute_cap'        => self::HARD_PER_MINUTE_CAP,
            'max_concurrent_connections' => $maxConcurrent,
            'min_interval_ms'            => (int) round(self::MIN_INTERVAL_MICROSECONDS / 1000),
            'organization_id'            => (string) ($config['organization_id'] ?? 'default'),
        ];
    }

    /**
     * Acquire both:
     * 1) A concurrency connection slot (1..N, max 5 on Free Plan),
     * 2) A daily & per-minute rate limit token (pacing at <= 1.6 req/sec and <= 100 req/min).
     *
     * Always call releaseSlot() in a `finally` block after curl_exec() completes!
     *
     * @return array ['allowed' => bool, 'slot' => int|null, 'waited_ms' => int, 'message' => string, 'usage' => array]
     */
    public function acquireBeforeRequest(array $config = [], int $maxWaitSeconds = 25): array
    {
        $limits    = $this->getEffectiveLimits($config);
        $orgId     = preg_replace('/[^a-zA-Z0-9_-]/', '', $limits['organization_id']) ?: 'default';
        $startWait = microtime(true);

        // Step 1: Acquire one of the N concurrent connection slots (e.g. 1..5 for Free Plan)
        $slotAcquired = $this->acquireConcurrencySlot($orgId, $limits['max_concurrent_connections'], $maxWaitSeconds);
        if (!$slotAcquired) {
            return [
                'allowed'   => false,
                'code'      => 429,
                'reason'    => 'CONCURRENT_LIMIT_REACHED',
                'waited_ms' => (int) round((microtime(true) - $startWait) * 1000),
                'message'   => sprintf(
                    'Zoho Books concurrent connection limit (%d concurrent connections) is currently busy. Please retry shortly.',
                    $limits['max_concurrent_connections']
                ),
            ];
        }

        // Step 2: Check Daily Quota (1,000 calls/day on Free Plan) & Per-Minute Sliding Window (100 req/min cap)
        try {
            $quotaCheck = $this->reserveRateWindowToken($orgId, $limits, $maxWaitSeconds);
            if (empty($quotaCheck['allowed'])) {
                $this->releaseConcurrencySlot();
                return array_merge($quotaCheck, [
                    'waited_ms' => (int) round((microtime(true) - $startWait) * 1000),
                ]);
            }

            return [
                'allowed'   => true,
                'slot'      => $this->activeSlotIndex,
                'waited_ms' => (int) round((microtime(true) - $startWait) * 1000),
                'usage'     => $quotaCheck['usage'] ?? [],
            ];
        } catch (\Throwable $e) {
            $this->releaseConcurrencySlot();
            return [
                'allowed'   => true,
                'slot'      => null,
                'waited_ms' => (int) round((microtime(true) - $startWait) * 1000),
            ];
        }
    }

    /**
     * Release the active concurrency slot immediately after the HTTP request finishes.
     */
    public function releaseConcurrencySlot(): void
    {
        if (is_resource($this->activeSlotHandle)) {
            @flock($this->activeSlotHandle, LOCK_UN);
            @fclose($this->activeSlotHandle);
        }
        $this->activeSlotHandle = null;
        $this->activeSlotIndex  = null;
    }

    /**
     * Notify the rate limiter when Zoho explicitly returns HTTP 429 so all workers back off.
     */
    public function recordRateLimitHit(string $orgId = 'default', int $cooldownSeconds = 10): void
    {
        $orgId = preg_replace('/[^a-zA-Z0-9_-]/', '', $orgId) ?: 'default';
        $stateFile = $this->lockDir . DIRECTORY_SEPARATOR . "rate_state_{$orgId}.json";

        $fp = @fopen($stateFile, 'c+');
        if (!is_resource($fp)) {
            return;
        }

        if (@flock($fp, LOCK_EX)) {
            $state = $this->readStateFromHandle($fp);
            $state['cooldown_until'] = microtime(true) + max(2, $cooldownSeconds);
            $state['last_429_at']    = date('Y-m-d H:i:s');
            $this->writeStateToHandle($fp, $state);
            @flock($fp, LOCK_UN);
        }
        @fclose($fp);
    }

    /**
     * Return live usage metrics for Settings and Sync Dashboard UI.
     */
    public function getUsageStats(array $config = []): array
    {
        $limits    = $this->getEffectiveLimits($config);
        $orgId     = preg_replace('/[^a-zA-Z0-9_-]/', '', $limits['organization_id']) ?: 'default';
        $stateFile = $this->lockDir . DIRECTORY_SEPARATOR . "rate_state_{$orgId}.json";

        $now   = microtime(true);
        $today = date('Y-m-d');
        $state = [
            'day'               => $today,
            'daily_count'       => 0,
            'minute_timestamps' => [],
            'last_request_ts'   => 0.0,
            'cooldown_until'    => 0.0,
            'last_429_at'       => null,
        ];

        if (is_file($stateFile)) {
            $fp = @fopen($stateFile, 'r');
            if (is_resource($fp)) {
                if (@flock($fp, LOCK_SH)) {
                    $loaded = $this->readStateFromHandle($fp);
                    if (is_array($loaded)) {
                        $state = array_merge($state, $loaded);
                    }
                    @flock($fp, LOCK_UN);
                }
                @fclose($fp);
            }
        }

        if (($state['day'] ?? '') !== $today) {
            $state['day']         = $today;
            $state['daily_count'] = 0;
        }

        // Prune timestamps older than 60 seconds
        $recentMinute = array_values(array_filter(
            $state['minute_timestamps'] ?? [],
            static fn($ts) => ($now - (float) $ts) < 60.0
        ));

        // Also cross-check DB logs for today if state file was reset
        $dbDailyCount = 0;
        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('zoho_sync_logs')) {
                $dbDailyCount = (int) $db->table('zoho_sync_logs')
                    ->where('created_at >=', $today . ' 00:00:00')
                    ->where('response_code IS NOT NULL', null, false)
                    ->where('response_code >', 0)
                    ->countAllResults();
            }
        } catch (\Throwable $e) {
            $dbDailyCount = 0;
        }

        $dailyUsed       = max((int) ($state['daily_count'] ?? 0), $dbDailyCount);
        $dailyLimit      = $limits['daily_api_limit'];
        $dailyRemaining  = max(0, $dailyLimit - $dailyUsed);
        $dailyPercent    = $dailyLimit > 0 ? min(100, round(($dailyUsed / $dailyLimit) * 100, 1)) : 0.0;

        $minuteUsed      = count($recentMinute);
        $minuteLimit     = $limits['per_minute_limit'];
        $minutePercent   = $minuteLimit > 0 ? min(100, round(($minuteUsed / $minuteLimit) * 100, 1)) : 0.0;

        $activeConcurrent = $this->countActiveConcurrentSlots($orgId, $limits['max_concurrent_connections']);

        return [
            'subscription_plan'          => $limits['subscription_plan'],
            'plan_label'                 => $limits['plan_label'],
            'daily_used'                 => $dailyUsed,
            'daily_limit'                => $dailyLimit,
            'daily_remaining'            => $dailyRemaining,
            'daily_percent'              => $dailyPercent,
            'minute_used'                => $minuteUsed,
            'minute_limit'               => $minuteLimit,
            'hard_per_minute_cap'        => self::HARD_PER_MINUTE_CAP,
            'minute_percent'             => $minutePercent,
            'active_concurrent'          => $activeConcurrent,
            'max_concurrent_connections' => $limits['max_concurrent_connections'],
            'min_interval_ms'            => $limits['min_interval_ms'],
            'last_429_at'                => $state['last_429_at'] ?? null,
        ];
    }

    /**
     * Acquire one of N OS-level flock slots (`slot_1` .. `slot_N`).
     */
    protected function acquireConcurrencySlot(string $orgId, int $maxConcurrent, int $maxWaitSeconds): bool
    {
        $this->releaseConcurrencySlot();

        $deadline = microtime(true) + max(1, $maxWaitSeconds);

        while (true) {
            for ($slot = 1; $slot <= $maxConcurrent; $slot++) {
                $slotFile = $this->lockDir . DIRECTORY_SEPARATOR . "conn_{$orgId}_slot_{$slot}.lock";
                $fp       = @fopen($slotFile, 'c+');
                if (!is_resource($fp)) {
                    continue;
                }

                if (@flock($fp, LOCK_EX | LOCK_NB)) {
                    @ftruncate($fp, 0);
                    @fwrite($fp, (string) getmypid() . '|' . microtime(true));
                    @fflush($fp);
                    $this->activeSlotHandle = $fp;
                    $this->activeSlotIndex  = $slot;
                    return true;
                }

                @fclose($fp);
            }

            if (microtime(true) >= $deadline) {
                return false;
            }

            // Sleep 60ms before checking for a freed connection slot
            usleep(60000);
        }
    }

    /**
     * Count how many concurrency slots are currently locked.
     */
    protected function countActiveConcurrentSlots(string $orgId, int $maxConcurrent): int
    {
        $active = 0;
        for ($slot = 1; $slot <= $maxConcurrent; $slot++) {
            if ($this->activeSlotIndex === $slot && is_resource($this->activeSlotHandle)) {
                $active++;
                continue;
            }

            $slotFile = $this->lockDir . DIRECTORY_SEPARATOR . "conn_{$orgId}_slot_{$slot}.lock";
            if (!is_file($slotFile)) {
                continue;
            }

            $fp = @fopen($slotFile, 'c+');
            if (!is_resource($fp)) {
                continue;
            }

            if (@flock($fp, LOCK_EX | LOCK_NB)) {
                @flock($fp, LOCK_UN);
            } else {
                $active++;
            }
            @fclose($fp);
        }

        return $active;
    }

    /**
     * Enforce the Daily Allowance (e.g., 1,000 calls/day on Free Plan) and Per-Minute Cap (100 req/min, ~1.6 req/sec).
     */
    protected function reserveRateWindowToken(string $orgId, array $limits, int $maxWaitSeconds): array
    {
        $stateFile = $this->lockDir . DIRECTORY_SEPARATOR . "rate_state_{$orgId}.json";
        $deadline  = microtime(true) + max(2, $maxWaitSeconds);

        while (true) {
            $fp = @fopen($stateFile, 'c+');
            if (!is_resource($fp)) {
                return ['allowed' => true, 'usage' => []];
            }

            if (!@flock($fp, LOCK_EX)) {
                @fclose($fp);
                usleep(50000);
                continue;
            }

            $now   = microtime(true);
            $today = date('Y-m-d');
            $state = $this->readStateFromHandle($fp);

            if (($state['day'] ?? '') !== $today) {
                $state['day']         = $today;
                $state['daily_count'] = 0;
            }

            // Prune sliding 60-second window
            $minuteTimestamps = array_values(array_filter(
                $state['minute_timestamps'] ?? [],
                static fn($ts) => ($now - (float) $ts) < 60.0
            ));

            // 1. Check Daily API Limit (e.g. 1,000 calls/day for Free Plan)
            $dailyCount = (int) ($state['daily_count'] ?? 0);
            $dailyLimit = (int) $limits['daily_api_limit'];
            if ($dailyCount >= $dailyLimit) {
                @flock($fp, LOCK_UN);
                @fclose($fp);
                return [
                    'allowed' => false,
                    'code'    => 429,
                    'reason'  => 'DAILY_QUOTA_REACHED',
                    'message' => sprintf(
                        'Zoho Books daily API limit reached (%d / %d calls used today on %s). Remaining operations are safely queued for tomorrow or until quota resets.',
                        $dailyCount,
                        $dailyLimit,
                        strtoupper($limits['subscription_plan'])
                    ),
                ];
            }

            // 2. Check if a 429 cooldown is active
            $cooldownUntil = (float) ($state['cooldown_until'] ?? 0.0);
            $waitUs        = 0;

            if ($cooldownUntil > $now) {
                $waitUs = (int) ceil(($cooldownUntil - $now) * 1000000);
            }

            // 3. Check Per-Minute Rate Limit (hard cap 100 req/min, configured safe cap e.g. 90 req/min)
            $perMinuteLimit = (int) $limits['per_minute_limit'];
            if ($waitUs === 0 && count($minuteTimestamps) >= $perMinuteLimit) {
                $oldestTs = (float) $minuteTimestamps[0];
                $secondsUntilOldestExpires = max(0.05, 60.05 - ($now - $oldestTs));
                $waitUs = (int) ceil($secondsUntilOldestExpires * 1000000);
            }

            // 4. Enforce smooth pacing (~1.6 requests/sec => min 625ms spacing when burst >= 3 requests in last 2 seconds)
            if ($waitUs === 0 && !empty($state['last_request_ts'])) {
                $elapsedUs = (int) round(($now - (float) $state['last_request_ts']) * 1000000);
                $recentBurst = count(array_filter($minuteTimestamps, static fn($ts) => ($now - (float) $ts) < 3.0));
                if ($recentBurst >= 2 && $elapsedUs < self::MIN_INTERVAL_MICROSECONDS) {
                    $waitUs = self::MIN_INTERVAL_MICROSECONDS - $elapsedUs;
                }
            }

            if ($waitUs <= 0) {
                // Reserve token now
                $reserveTs            = microtime(true);
                $minuteTimestamps[]   = $reserveTs;
                $state['day']         = $today;
                $state['daily_count'] = $dailyCount + 1;
                $state['minute_timestamps'] = $minuteTimestamps;
                $state['last_request_ts']   = $reserveTs;

                $this->writeStateToHandle($fp, $state);
                @flock($fp, LOCK_UN);
                @fclose($fp);

                return [
                    'allowed' => true,
                    'usage'   => [
                        'daily_used'  => $state['daily_count'],
                        'daily_limit' => $dailyLimit,
                        'minute_used' => count($minuteTimestamps),
                        'minute_limit'=> $perMinuteLimit,
                    ],
                ];
            }

            // Release lock before sleeping so other processes aren't blocked on state file I/O
            @flock($fp, LOCK_UN);
            @fclose($fp);

            if ((microtime(true) + ($waitUs / 1000000)) > $deadline) {
                return [
                    'allowed' => false,
                    'code'    => 429,
                    'reason'  => 'PER_MINUTE_RATE_LIMIT',
                    'message' => sprintf(
                        'Zoho Books per-minute frequency limit reached (%d/%d requests per minute). Request queued to prevent HTTP 429.',
                        count($minuteTimestamps),
                        $perMinuteLimit
                    ),
                ];
            }

            usleep(min($waitUs, 2000000));
        }
    }

    protected function readStateFromHandle($fp): array
    {
        @rewind($fp);
        $raw = @stream_get_contents($fp);
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    protected function writeStateToHandle($fp, array $state): void
    {
        @ftruncate($fp, 0);
        @rewind($fp);
        @fwrite($fp, json_encode($state));
        @fflush($fp);
    }

    public function __destruct()
    {
        $this->releaseConcurrencySlot();
    }
}

