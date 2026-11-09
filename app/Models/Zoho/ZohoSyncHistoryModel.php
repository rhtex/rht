<?php

namespace App\Models\Zoho;

use CodeIgniter\Model;

class ZohoSyncHistoryModel extends Model
{
    protected $table            = 'zoho_sync_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'batch_id',
        'trigger_type',
        'module_filter',
        'total_processed',
        'created_count',
        'updated_count',
        'deleted_count',
        'skipped_count',
        'conflict_count',
        'failed_count',
        'status',
        'started_at',
        'completed_at',
        'initiated_by',
        'summary_json',
    ];

    protected $useTimestamps = false;

    public function startRun(string $triggerType, ?string $moduleFilter = null, ?int $userId = null): array
    {
        $batchId = 'zbatch_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(4)), 0, 6);
        $data = [
            'batch_id'        => $batchId,
            'trigger_type'    => $triggerType,
            'module_filter'   => $moduleFilter,
            'total_processed' => 0,
            'created_count'   => 0,
            'updated_count'   => 0,
            'deleted_count'   => 0,
            'skipped_count'   => 0,
            'conflict_count'  => 0,
            'failed_count'    => 0,
            'status'          => 'RUNNING',
            'started_at'      => date('Y-m-d H:i:s'),
            'initiated_by'    => $userId,
        ];
        $id = $this->insert($data, true);
        $data['id'] = (int) $id;
        return $data;
    }

    public function completeRun(int $id, array $stats): void
    {
        $failed   = (int) ($stats['failed_count'] ?? 0);
        $conflict = (int) ($stats['conflict_count'] ?? 0);
        $total    = (int) ($stats['total_processed'] ?? 0);

        $status = 'COMPLETED';
        if ($failed > 0 && $failed >= $total && $total > 0) {
            $status = 'FAILED';
        } elseif ($failed > 0 || $conflict > 0) {
            $status = 'PARTIAL';
        }

        $update = [
            'total_processed' => $total,
            'created_count'   => (int) ($stats['created_count'] ?? 0),
            'updated_count'   => (int) ($stats['updated_count'] ?? 0),
            'deleted_count'   => (int) ($stats['deleted_count'] ?? 0),
            'skipped_count'   => (int) ($stats['skipped_count'] ?? 0),
            'conflict_count'  => $conflict,
            'failed_count'    => $failed,
            'status'          => $stats['status'] ?? $status,
            'completed_at'    => date('Y-m-d H:i:s'),
            'summary_json'    => isset($stats['summary']) ? json_encode($stats['summary']) : null,
        ];

        $this->update($id, $update);
    }
}

