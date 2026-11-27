<?php

namespace App\Commands;

use App\Services\Zoho\Modules\BillSyncService;
use App\Services\Zoho\ZohoApiClient;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RestoreZohoBills extends BaseCommand
{
    protected $group       = 'Zoho';
    protected $name        = 'zoho:restore-bills';
    protected $description = 'Restore Zoho bills 698964000003498622 (#80) & 698964000003498599 (#81) and re-push local bills 1238 & 1239.';

    public function run(array $params)
    {
        $client  = new ZohoApiClient();
        $billSvc = new BillSyncService($client);
        $db      = \Config\Database::connect();

        $originals = [
            '698964000003498622' => [
                'vendor_id'              => '698964000001299099',
                'bill_number'            => '80',
                'date'                   => '2023-10-15',
                'due_date'               => '2023-10-15',
                'reference_number'       => '',
                'source_of_supply'       => 'TN',
                'destination_of_supply'  => 'TN',
                'is_inclusive_tax'       => false,
                'discount'               => 0,
                'is_discount_before_tax' => true,
                'discount_type'          => 'entity_level',
                'adjustment'             => 0,
                'adjustment_description' => 'Adjustment',
                'notes'                  => '',
                'terms'                  => '',
                'line_items'             => [
                    [
                        'account_id'         => '698964000001126044',
                        'description'        => 'Big Butta',
                        'rate'               => 740,
                        'quantity'           => 100,
                        'tax_id'             => '698964000000067847',
                        'item_custom_fields' => [
                            ['api_name' => 'cf_count', 'value' => '80'],
                            ['api_name' => 'cf_meters', 'value' => 6.2],
                        ],
                    ],
                ],
            ],
            '698964000003498599' => [
                'vendor_id'              => '698964000001299099',
                'bill_number'            => '81',
                'date'                   => '2023-10-16',
                'due_date'               => '2023-10-16',
                'reference_number'       => '',
                'source_of_supply'       => 'TN',
                'destination_of_supply'  => 'TN',
                'is_inclusive_tax'       => false,
                'discount'               => 0,
                'is_discount_before_tax' => true,
                'discount_type'          => 'entity_level',
                'adjustment'             => 0,
                'adjustment_description' => 'Adjustment',
                'notes'                  => '',
                'terms'                  => '',
                'line_items'             => [
                    [
                        'account_id'         => '698964000001126044',
                        'description'        => 'Big Butta',
                        'rate'               => 740,
                        'quantity'           => 92,
                        'tax_id'             => '698964000000067847',
                        'item_custom_fields' => [
                            ['api_name' => 'cf_count', 'value' => '80'],
                            ['api_name' => 'cf_meters', 'value' => 6.2],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($originals as $zohoBillId => $payload) {
            $res = $client->put("/bills/{$zohoBillId}", $payload, [], [
                'module'         => 'bills',
                'zoho_record_id' => $zohoBillId,
                'operation'      => 'RESTORE_ORIGINAL',
            ]);

            if (!empty($res['success'])) {
                CLI::write("Restored Zoho Bill {$zohoBillId} (#{$payload['bill_number']} {$payload['date']})", 'green');
            } else {
                CLI::error("Failed to restore Zoho Bill {$zohoBillId}: " . ($res['message'] ?? 'Unknown error'));
            }
        }

        $db->table('zoho_sync_mapping')
            ->where('module', 'bills')
            ->groupStart()
                ->whereIn('local_record_id', [1238, 1239])
                ->orWhereIn('zoho_record_id', ['698964000003498622', '698964000003498599'])
            ->groupEnd()
            ->delete();

        foreach ([1238, 1239] as $localId) {
            $pushRes = $billSvc->pushToZoho($localId, 'CREATE');
            if (!empty($pushRes['success'])) {
                CLI::write("Created new Zoho Bill for local #{$localId} -> Zoho ID " . ($pushRes['zoho_record_id'] ?? ''), 'green');
            } else {
                CLI::error("Failed to re-push local Bill #{$localId}: " . ($pushRes['message'] ?? 'Unknown error'));
            }
        }
    }
}
