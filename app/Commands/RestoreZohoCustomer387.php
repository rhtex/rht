<?php

namespace App\Commands;

use App\Models\Zoho\ZohoSyncMappingModel;
use App\Services\Zoho\Modules\CustomerSyncService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RestoreZohoCustomer387 extends BaseCommand
{
    protected $group       = 'Zoho';
    protected $name        = 'zoho:fix-customer-387';
    protected $description = 'Restore Zoho contact 698964000001943086 to local customer #363 and create new Zoho contact for #387.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $mappingModel = new ZohoSyncMappingModel();
        $custSvc      = new CustomerSyncService();

        // 1. Remove wrong mapping for #387 -> 698964000001943086
        $db->table('zoho_sync_mapping')
            ->where('module', 'customers')
            ->where('local_record_id', 387)
            ->delete();

        // 2. Ensure #363 is mapped to 698964000001943086 and push UPDATE to restore original Zoho contact
        $mappingModel->upsertMapping('customers', 363, '698964000001943086', ['sync_status' => 'PENDING']);
        $res363 = $custSvc->pushToZoho(363, 'UPDATE');
        if (!empty($res363['success'])) {
            CLI::write('Restored Zoho contact 698964000001943086 to local customer #363.', 'green');
        } else {
            CLI::error('Failed to restore #363: ' . ($res363['message'] ?? 'Unknown error'));
        }

        // 3. Create new Zoho contact for #387
        $res387 = $custSvc->pushToZoho(387, 'CREATE');
        if (!empty($res387['success'])) {
            CLI::write('Created new Zoho contact for #387 -> Zoho ID ' . ($res387['zoho_record_id'] ?? ''), 'green');
        } else {
            CLI::error('Failed to create #387 in Zoho: ' . ($res387['message'] ?? 'Unknown error'));
        }
    }
}
