<?php

namespace App\Models\Zoho;

use CodeIgniter\Model;

class ZohoSyncMappingModel extends Model
{
    protected $table            = 'zoho_sync_mapping';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'module',
        'local_record_id',
        'zoho_record_id',
        'zoho_record_number',
        'last_local_sync_at',
        'last_zoho_sync_at',
        'last_successful_sync_at',
        'sync_direction',
        'sync_status',
        'local_version_hash',
        'zoho_version_hash',
        'zoho_last_modified_time',
        'metadata_json',
        'last_error',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findByLocal(string $module, int $localId): ?array
    {
        return $this->where('module', $module)
            ->where('local_record_id', $localId)
            ->first();
    }

    public function findByZoho(string $module, string $zohoId): ?array
    {
        return $this->where('module', $module)
            ->where('zoho_record_id', trim($zohoId))
            ->first();
    }

    /**
     * Get map keyed by local_record_id for an array of local IDs (used on list views to display badges without N+1 queries).
     */
    public function getMapByLocalIds(string $module, array $localIds): array
    {
        if (empty($localIds)) {
            return [];
        }
        $rows = $this->where('module', $module)
            ->whereIn('local_record_id', array_map('intval', $localIds))
            ->findAll();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['local_record_id']] = $row;
        }
        return $map;
    }

    /**
     * Safely insert or update a Local <-> Zoho mapping record.
     */
    public function upsertMapping(string $module, int $localId, string $zohoId, array $extra = []): int
    {
        $now            = date('Y-m-d H:i:s');
        $zohoId         = trim($zohoId);
        $existingByLocal = $this->findByLocal($module, $localId);
        $existingByZoho  = $this->findByZoho($module, $zohoId);

        // If another row holds this zoho_record_id for the same module, reconcile it to avoid unique constraint violation
        if ($existingByZoho && (!$existingByLocal || (int) $existingByZoho['id'] !== (int) $existingByLocal['id'])) {
            $this->delete($existingByZoho['id']);
        }

        $data = array_merge([
            'module'                  => $module,
            'local_record_id'         => $localId,
            'zoho_record_id'          => $zohoId,
            'last_successful_sync_at' => $now,
            'sync_status'             => 'SYNCED',
            'last_error'              => null,
        ], $extra);

        if (isset($data['metadata_json']) && is_array($data['metadata_json'])) {
            $data['metadata_json'] = json_encode($data['metadata_json']);
        }

        if ($existingByLocal) {
            $this->update($existingByLocal['id'], $data);
            return (int) $existingByLocal['id'];
        }

        return (int) $this->insert($data, true);
    }

    public function markStatus(string $module, int $localId, string $status, ?string $error = null): void
    {
        $existing = $this->findByLocal($module, $localId);
        if ($existing) {
            $this->update($existing['id'], [
                'sync_status' => $status,
                'last_error'  => $error,
            ]);
        }
    }
}

