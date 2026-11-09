<?php

namespace App\Models\Zoho;

use CodeIgniter\Model;

class ZohoSyncQueueModel extends Model
{
    protected $table            = 'zoho_sync_queue';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'batch_id',
        'module',
        'local_record_id',
        'zoho_record_id',
        'operation',
        'direction',
        'source',
        'priority',
        'payload_json',
        'status',
        'attempts',
        'max_attempts',
        'next_retry_at',
        'locked_at',
        'error_message',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Enqueue a sync task while coalescing duplicate PENDING items for the same module + local_record_id.
     */
    public function enqueue(array $item): int
    {
        $module    = $item['module'];
        $localId   = isset($item['local_record_id']) ? (int) $item['local_record_id'] : null;
        $direction = $item['direction'] ?? 'TO_ZOHO';
        $operation = $item['operation'] ?? 'UPDATE';

        if ($localId !== null) {
            $existing = $this->where('module', $module)
                ->where('local_record_id', $localId)
                ->where('direction', $direction)
                ->where('status', 'PENDING')
                ->first();

            if ($existing) {
                // If already queued as CREATE and now updated before processing, keep CREATE
                $newOp = ($existing['operation'] === 'CREATE' && $operation === 'UPDATE') ? 'CREATE' : $operation;
                $this->update($existing['id'], [
                    'operation'     => $newOp,
                    'payload_json'  => isset($item['payload_json']) && is_array($item['payload_json'])
                        ? json_encode($item['payload_json'])
                        : ($item['payload_json'] ?? $existing['payload_json']),
                    'next_retry_at' => null,
                    'error_message' => null,
                ]);
                return (int) $existing['id'];
            }
        }

        if (isset($item['payload_json']) && is_array($item['payload_json'])) {
            $item['payload_json'] = json_encode($item['payload_json']);
        }

        return (int) $this->insert(array_merge([
            'direction'    => 'TO_ZOHO',
            'source'       => 'LOCAL',
            'priority'     => $this->getDefaultPriority($module),
            'status'       => 'PENDING',
            'attempts'     => 0,
            'max_attempts' => 5,
        ], $item), true);
    }

    public function getDefaultPriority(string $module): int
    {
        return match ($module) {
            'taxes', 'bank_accounts', 'expense_categories' => 1,
            'customers', 'vendors'                         => 2,
            'items'                                        => 3,
            'quotations', 'sales_orders', 'purchase_orders' => 4,
            'invoices', 'bills', 'expenses'                => 5,
            'credit_notes', 'vendor_credits'               => 6,
            'customer_payments', 'vendor_payments'         => 7,
            default                                        => 5,
        };
    }

    public function fetchRunnable(int $limit = 50, ?string $module = null): array
    {
        $now = date('Y-m-d H:i:s');
        $builder = $this->whereIn('status', ['PENDING', 'FAILED'])
            ->where('attempts < max_attempts', null, false)
            ->groupStart()
                ->where('next_retry_at IS NULL')
                ->orWhere('next_retry_at <=', $now)
            ->groupEnd();

        if (!empty($module)) {
            $builder->where('module', $module);
        }

        return $builder->orderBy('priority', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll($limit);
    }
}

