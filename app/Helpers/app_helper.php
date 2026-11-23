<?php

if (!function_exists('get_setting')) {
    /**
     * Retrieve a setting value from the database
     */
    function get_setting(string $key, $default = '')
    {
        $settingModel = new \App\Models\SettingModel();
        return $settingModel->getSetting($key, $default);
    }
}

if (!function_exists('zoho_auto_sync')) {
    /**
     * Trigger automatic CI4 -> Zoho Books synchronization for a local record.
     * Safe to call inside any Controller/Model; never throws uncaught exceptions.
     */
    function zoho_auto_sync(string $module, int $localId, string $operation = 'UPDATE'): array
    {
        try {
            $orchestrator = new \App\Services\Zoho\ZohoSyncOrchestrator();
            return $orchestrator->handleLocalChange($module, $localId, $operation);
        } catch (\Throwable $e) {
            log_message('error', "zoho_auto_sync({$module}, #{$localId}, {$operation}) error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

if (!function_exists('zoho_get_mapping')) {
    /**
     * Retrieve Zoho synchronization mapping for a single local record.
     */
    function zoho_get_mapping(string $module, int $localId): ?array
    {
        try {
            $model = new \App\Models\Zoho\ZohoSyncMappingModel();
            return $model->findByLocal($module, $localId);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('zoho_sync_badge')) {
    /**
     * Render a compact Zoho synchronization status badge with optional quick-sync button.
     */
    function zoho_sync_badge(string $module, int $localId, ?array $preloadedMap = null, bool $showButton = true): string
    {
        $map = $preloadedMap !== null
            ? ($preloadedMap[$localId] ?? null)
            : zoho_get_mapping($module, $localId);

        $syncUrl = site_url("settings/integrations/zoho/sync-record/{$module}/{$localId}");

        if (!$map) {
            $badge = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" title="Not yet synced to Zoho Books"><i class="fas fa-cloud me-1"></i>Not Synced</span>';
            if ($showButton) {
                $badge .= ' <a href="' . $syncUrl . '" class="btn btn-xs btn-outline-primary py-0 px-1 ms-1" title="Sync to Zoho Books now"><i class="fas fa-sync-alt"></i></a>';
            }
            return $badge;
        }

        $status = strtoupper((string) ($map['sync_status'] ?? 'SYNCED'));
        $zohoId = esc($map['zoho_record_id'] ?? '');
        $err    = esc($map['last_error'] ?? '');
        $lastAt = !empty($map['last_successful_sync_at']) ? date('d M Y H:i', strtotime($map['last_successful_sync_at'])) : '';

        $badgeHtml = match ($status) {
            'SYNCED'   => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" title="Zoho ID: ' . $zohoId . ' | Synced: ' . $lastAt . '"><i class="fas fa-check-circle me-1"></i>Zoho Synced</span>',
            'PENDING'  => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" title="Queued for Zoho sync"><i class="fas fa-clock me-1"></i>Pending</span>',
            'CONFLICT' => '<a href="' . site_url('settings/integrations/zoho/conflicts') . '" class="badge bg-danger text-white text-decoration-none px-2 py-1" title="Conflict: ' . $err . '"><i class="fas fa-exclamation-triangle me-1"></i>Conflict</a>',
            'VOIDED'   => '<span class="badge bg-dark-subtle text-dark border px-2 py-1" title="Voided in Zoho Books (' . $zohoId . ')"><i class="fas fa-ban me-1"></i>Zoho Void</span>',
            'FAILED'   => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" title="Sync Error: ' . $err . '"><i class="fas fa-times-circle me-1"></i>Sync Failed</span>',
            default    => '<span class="badge bg-info-subtle text-info border px-2 py-1">' . esc($status) . '</span>',
        };

        if ($showButton) {
            $badgeHtml .= ' <a href="' . $syncUrl . '" class="btn btn-xs btn-outline-secondary py-0 px-1 ms-1" title="Re-sync with Zoho Books"><i class="fas fa-sync-alt"></i></a>';
        }

        return $badgeHtml;
    }
}

if (!function_exists('number_to_indian_currency_words')) {
    /**
     * Convert a numeric amount to Indian currency words format
     * E.g. 106722 => "Rupees One Lakh Six Thousand Seven Hundred Twenty-Two Only"
     */
    function number_to_indian_currency_words($number): string
    {
        $amount = (float) $number;
        if ($amount < 0.001) {
            return 'Rupees Zero Only';
        }

        $no = floor($amount);
        $decimal = round(($amount - $no) * 100);
        $digits_length = strlen((string)$no);
        $i = 0;
        $str = [];
        $words = [
            0 => '', 1 => 'One', 2 => 'Two',
            3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
            19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
            70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        ];
        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];

        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number_part = $no % $divider;
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;

            if ($number_part) {
                $plural = (($counter = count($str)) && $number_part > 9) ? 's' : '';
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : '';
                $part_str = '';
                if ($number_part < 21) {
                    $part_str = $words[$number_part];
                } else {
                    $ten = floor($number_part / 10) * 10;
                    $unit = $number_part % 10;
                    $part_str = $words[$ten] . ($unit ? '-' . $words[$unit] : '');
                }
                $unit_label = $digits[$counter] ?? '';
                $str[] = trim($part_str . ' ' . $unit_label);
            } else {
                $str[] = '';
            }
        }

        $str = array_filter($str);
        $str = array_reverse($str);
        $rupees = implode(' ', $str);

        $paise = '';
        if ($decimal > 0) {
            if ($decimal < 21) {
                $paise_words = $words[$decimal];
            } else {
                $ten = floor($decimal / 10) * 10;
                $unit = $decimal % 10;
                $paise_words = $words[$ten] . ($unit ? '-' . $words[$unit] : '');
            }
            $paise = ' and Paise ' . $paise_words;
        }

        return 'Rupees ' . trim($rupees) . $paise . ' Only';
    }
}
