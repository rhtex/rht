<?php

namespace App\Services\Zoho;

use App\Models\Zoho\ZohoSettingModel;
use App\Models\Zoho\ZohoSyncConflictModel;
use App\Models\Zoho\ZohoSyncMappingModel;

class ZohoConflictService
{
    protected ZohoSettingModel $settingModel;
    protected ZohoSyncConflictModel $conflictModel;
    protected ZohoSyncMappingModel $mappingModel;

    protected array $accountingCriticalFields = [
        'total_amount',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'paid_amount',
        'balance',
        'amount',
        'gstin',
        'status',
        'items_summary',
        'customer_id',
        'vendor_id',
    ];

    public function __construct()
    {
        $this->settingModel  = new ZohoSettingModel();
        $this->conflictModel = new ZohoSyncConflictModel();
        $this->mappingModel  = new ZohoSyncMappingModel();
    }

    /**
     * Evaluate whether a mapped record has a bidirectional modification conflict.
     *
     * Returns:
     * - ['action' => 'NO_CHANGE']
     * - ['action' => 'APPLY_ZOHO']
     * - ['action' => 'APPLY_LOCAL']
     * - ['action' => 'CONFLICT', 'conflict_id' => int, 'reason' => string]
     */
    public function evaluate(
        string $module,
        int $localId,
        string $zohoId,
        string $recordNumber,
        array $normalizedLocal,
        array $normalizedZoho,
        ?string $localModifiedAt,
        ?string $zohoModifiedAt,
        array $mapping
    ): array {
        $currentLocalHash = ZohoHashService::hash($normalizedLocal);
        $currentZohoHash  = ZohoHashService::hash($normalizedZoho);

        $lastLocalHash = $mapping['local_version_hash'] ?? '';
        $lastZohoHash  = $mapping['zoho_version_hash'] ?? '';

        // If normalized payloads are identical, no conflict exists
        if ($currentLocalHash === $currentZohoHash) {
            return ['action' => 'NO_CHANGE', 'local_hash' => $currentLocalHash, 'zoho_hash' => $currentZohoHash];
        }

        // If baseline hashes exist, check which side actually mutated since last sync
        if (!empty($lastLocalHash) && !empty($lastZohoHash)) {
            $localChanged = ($currentLocalHash !== $lastLocalHash);
            $zohoChanged  = ($currentZohoHash !== $lastZohoHash);

            if (!$localChanged && !$zohoChanged) {
                return ['action' => 'NO_CHANGE', 'local_hash' => $currentLocalHash, 'zoho_hash' => $currentZohoHash];
            }
            if (!$localChanged && $zohoChanged) {
                return ['action' => 'APPLY_ZOHO', 'local_hash' => $currentLocalHash, 'zoho_hash' => $currentZohoHash];
            }
            if ($localChanged && !$zohoChanged) {
                return ['action' => 'APPLY_LOCAL', 'local_hash' => $currentLocalHash, 'zoho_hash' => $currentZohoHash];
            }
        }

        // Both sides changed (or initial link with divergent data): inspect field diffs
        $diffs = $this->computeDiffs($normalizedLocal, $normalizedZoho);
        if (empty($diffs)) {
            return ['action' => 'NO_CHANGE', 'local_hash' => $currentLocalHash, 'zoho_hash' => $currentZohoHash];
        }

        $hasAccountingConflict = false;
        foreach ($diffs as $diff) {
            if (!empty($diff['is_accounting_critical'])) {
                $hasAccountingConflict = true;
                break;
            }
        }

        $config   = $this->settingModel->getConfig(false);
        $strategy = $config['conflict_strategy'] ?? 'manual_review';

        // Never auto-overwrite conflicting accounting figures; require manual review
        if ($hasAccountingConflict || $strategy === 'manual_review') {
            $reason = $hasAccountingConflict
                ? 'Conflicting accounting values modified in both CI4 ERP and Zoho Books.'
                : 'Record modified in both CI4 ERP and Zoho Books since last synchronization.';

            $conflictId = $this->conflictModel->recordConflict([
                'module'             => $module,
                'local_record_id'    => $localId,
                'zoho_record_id'     => $zohoId,
                'record_number'      => $recordNumber,
                'local_payload_json' => $normalizedLocal,
                'zoho_payload_json'  => $normalizedZoho,
                'diff_fields_json'   => $diffs,
                'local_modified_at'  => $localModifiedAt ? date('Y-m-d H:i:s', strtotime($localModifiedAt)) : null,
                'zoho_modified_at'   => $zohoModifiedAt ? date('Y-m-d H:i:s', strtotime($zohoModifiedAt)) : null,
                'conflict_reason'    => $reason,
                'status'             => 'OPEN',
            ]);

            $this->mappingModel->markStatus($module, $localId, 'CONFLICT', $reason);

            return [
                'action'      => 'CONFLICT',
                'conflict_id' => $conflictId,
                'reason'      => $reason,
                'diffs'       => $diffs,
            ];
        }

        if ($strategy === 'prefer_zoho') {
            return ['action' => 'APPLY_ZOHO', 'local_hash' => $currentLocalHash, 'zoho_hash' => $currentZohoHash];
        }

        if ($strategy === 'prefer_local') {
            return ['action' => 'APPLY_LOCAL', 'local_hash' => $currentLocalHash, 'zoho_hash' => $currentZohoHash];
        }

        if ($strategy === 'latest_timestamp' && !empty($localModifiedAt) && !empty($zohoModifiedAt)) {
            $localTs = strtotime($localModifiedAt);
            $zohoTs  = strtotime($zohoModifiedAt);
            if ($localTs && $zohoTs && abs($zohoTs - $localTs) >= 60) {
                return [
                    'action'     => ($zohoTs > $localTs) ? 'APPLY_ZOHO' : 'APPLY_LOCAL',
                    'local_hash' => $currentLocalHash,
                    'zoho_hash'  => $currentZohoHash,
                ];
            }
        }

        // Fallback: flag conflict when timestamps cannot reliably establish winner
        $reason = 'Ambiguous modification timestamps; flagged for manual review.';
        $conflictId = $this->conflictModel->recordConflict([
            'module'             => $module,
            'local_record_id'    => $localId,
            'zoho_record_id'     => $zohoId,
            'record_number'      => $recordNumber,
            'local_payload_json' => $normalizedLocal,
            'zoho_payload_json'  => $normalizedZoho,
            'diff_fields_json'   => $diffs,
            'local_modified_at'  => $localModifiedAt ? date('Y-m-d H:i:s', strtotime($localModifiedAt)) : null,
            'zoho_modified_at'   => $zohoModifiedAt ? date('Y-m-d H:i:s', strtotime($zohoModifiedAt)) : null,
            'conflict_reason'    => $reason,
            'status'             => 'OPEN',
        ]);
        $this->mappingModel->markStatus($module, $localId, 'CONFLICT', $reason);

        return [
            'action'      => 'CONFLICT',
            'conflict_id' => $conflictId,
            'reason'      => $reason,
            'diffs'       => $diffs,
        ];
    }

    public function computeDiffs(array $local, array $zoho): array
    {
        $allKeys = array_unique(array_merge(array_keys($local), array_keys($zoho)));
        $diffs   = [];

        foreach ($allKeys as $key) {
            if (in_array((string) $key, ['created_at', 'updated_at', 'last_modified_time'], true)) {
                continue;
            }
            $lVal = ZohoHashService::canonicalize($local[$key] ?? '');
            $zVal = ZohoHashService::canonicalize($zoho[$key] ?? '');

            $lStr = is_array($lVal) ? json_encode($lVal) : (string) $lVal;
            $zStr = is_array($zVal) ? json_encode($zVal) : (string) $zVal;

            if ($lStr !== $zStr) {
                $diffs[] = [
                    'field'                  => $key,
                    'label'                  => ucwords(str_replace('_', ' ', (string) $key)),
                    'local_value'            => $local[$key] ?? null,
                    'zoho_value'             => $zoho[$key] ?? null,
                    'is_accounting_critical' => in_array((string) $key, $this->accountingCriticalFields, true),
                ];
            }
        }

        return $diffs;
    }
}

