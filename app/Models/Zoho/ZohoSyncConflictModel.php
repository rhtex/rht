<?php

namespace App\Models\Zoho;

use CodeIgniter\Model;

class ZohoSyncConflictModel extends Model
{
    protected $table            = 'zoho_sync_conflicts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'module',
        'local_record_id',
        'zoho_record_id',
        'record_number',
        'local_payload_json',
        'zoho_payload_json',
        'diff_fields_json',
        'local_modified_at',
        'zoho_modified_at',
        'conflict_reason',
        'status',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function recordConflict(array $data): int
    {
        $existing = $this->where('module', $data['module'])
            ->where('local_record_id', (int) $data['local_record_id'])
            ->where('status', 'OPEN')
            ->first();

        foreach (['local_payload_json', 'zoho_payload_json', 'diff_fields_json'] as $jsonCol) {
            if (isset($data[$jsonCol]) && is_array($data[$jsonCol])) {
                $data[$jsonCol] = json_encode($data[$jsonCol], JSON_UNESCAPED_UNICODE);
            }
        }

        if ($existing) {
            $this->update($existing['id'], $data);
            return (int) $existing['id'];
        }

        return (int) $this->insert(array_merge(['status' => 'OPEN'], $data), true);
    }

    public function countOpen(): int
    {
        return (int) $this->where('status', 'OPEN')->countAllResults();
    }
}

