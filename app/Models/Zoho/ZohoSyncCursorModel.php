<?php

namespace App\Models\Zoho;

use CodeIgniter\Model;

class ZohoSyncCursorModel extends Model
{
    protected $table            = 'zoho_sync_cursors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'module',
        'last_zoho_modified_time',
        'last_local_sync_time',
        'last_page_processed',
        'last_full_sync_at',
        'updated_at',
    ];

    protected $useTimestamps = false;

    public function getCursor(string $module): ?array
    {
        return $this->where('module', $module)->first();
    }

    public function updateCursor(string $module, array $fields): void
    {
        $fields['updated_at'] = date('Y-m-d H:i:s');
        $existing = $this->getCursor($module);
        if ($existing) {
            $this->update($existing['id'], $fields);
        } else {
            $fields['module'] = $module;
            $this->insert($fields);
        }
    }
}

