<?php

namespace App\Services\Zoho\Modules;

use App\Models\CountryModel;
use App\Models\StateModel;
use App\Models\TransportModel;
use App\Services\Zoho\ZohoApiClient;

class TransportSyncService
{
    protected ZohoApiClient $client;
    protected TransportModel $transportModel;
    protected StateModel $stateModel;
    protected CountryModel $countryModel;

    public function __construct(?ZohoApiClient $client = null)
    {
        $this->client         = $client ?? new ZohoApiClient();
        $this->transportModel = new TransportModel();
        $this->stateModel     = new StateModel();
        $this->countryModel   = new CountryModel();
    }

    /**
     * Fetch dedicated transporters from Zoho Books/Inventory e-Way Bill API
     * and sync into local transports table.
     * Note: Strictly fetches Transporters, NOT general vendors/contacts.
     *
     * @return array Result stats [created, updated, skipped, total_found, errors]
     */
    public function syncTransportersFromZoho(): array
    {
        $stats = [
            'total_found' => 0,
            'created'     => 0,
            'updated'     => 0,
            'skipped'     => 0,
            'errors'      => [],
            'items'       => [],
        ];

        // Default Country (India) and State (Tamil Nadu if available)
        $india = $this->countryModel->where('name', 'India')->first() ?? $this->countryModel->first();
        $indiaId = $india['id'] ?? 1;

        $defaultState = $this->stateModel->where('country_id', $indiaId)
            ->groupStart()
                ->where('name', 'Tamil Nadu')
                ->orWhere('gst_state_code', '33')
            ->groupEnd()
            ->first();
        $defaultStateId = $defaultState['id'] ?? null;

        $transporters = [];

        // 1. Primary: Zoho Books / Inventory India e-Way Bill Transporters endpoint: /ewaybills/transporters
        // Also check /settings/transporters as alternate path
        $endpoints = ['/ewaybills/transporters', '/settings/transporters'];

        foreach ($endpoints as $endpoint) {
            $page = 1;
            $hasMore = true;

            while ($hasMore && $page <= 5) {
                $res = $this->client->get($endpoint, [
                    'page'     => $page,
                    'per_page' => 200,
                ], [
                    'module'    => 'transports',
                    'operation' => 'FETCH_EWAYBILL_TRANSPORTERS',
                ]);

                if (!empty($res['success']) && !empty($res['data'])) {
                    $rawList = $res['data']['transporters'] ?? ($res['data']['data'] ?? []);
                    if (is_array($rawList) && !empty($rawList)) {
                        foreach ($rawList as $item) {
                            $name = trim((string) ($item['transporter_name'] ?? ($item['name'] ?? '')));
                            if ($name === '') {
                                continue;
                            }
                            $key = strtoupper($name);
                            if (!isset($transporters[$key])) {
                                $gstin = trim((string) (
                                    $item['transporter_registration_id'] ??
                                    ($item['transporter_id_no'] ??
                                    ($item['transporter_gstin'] ??
                                    ($item['transin'] ??
                                    ($item['transporter_id'] ??
                                    ($item['gst_no'] ?? ($item['gstin'] ?? ''))))))
                                ));

                                $transporters[$key] = [
                                    'name'    => $name,
                                    'gstin'   => $gstin,
                                    'code'    => trim((string) ($item['transporter_code'] ?? ($item['code'] ?? ''))),
                                    'phone'   => trim((string) ($item['phone'] ?? ($item['mobile'] ?? ''))),
                                    'address' => trim((string) ($item['address'] ?? '')),
                                    'type'    => (stripos($name, 'courier') !== false) ? 'Courier Service' : 'Parcel Service',
                                    'source'  => 'ewaybills_transporters',
                                ];
                            }
                        }
                    }

                    $pageContext = $res['data']['page_context'] ?? [];
                    $hasMore = !empty($pageContext['has_more_page']);
                    $page++;
                } else {
                    // Endpoint not found or failed, don't continue paging
                    break;
                }
            }

            // If we successfully retrieved transporters from the primary endpoint, no need to try alternate
            if (!empty($transporters)) {
                break;
            }
        }

        // 2. Secondary fallback: Extract transporters from generated e-Way Bills (/ewaybills)
        // Every e-Way Bill created in Zoho stores transporter_name and transporter_id
        $ewbPage = 1;
        $ewbHasMore = true;
        while ($ewbHasMore && $ewbPage <= 3) {
            $ewbRes = $this->client->get('/ewaybills', [
                'page'     => $ewbPage,
                'per_page' => 200,
            ], [
                'module'    => 'transports',
                'operation' => 'EXTRACT_FROM_EWAYBILLS',
            ]);

            if (!empty($ewbRes['success']) && !empty($ewbRes['data']['ewaybills'])) {
                foreach ($ewbRes['data']['ewaybills'] as $ewb) {
                    $tName = trim((string) ($ewb['transporter_name'] ?? ''));
                    if ($tName !== '') {
                        $key = strtoupper($tName);
                        if (!isset($transporters[$key])) {
                            $transporters[$key] = [
                                'name'    => $tName,
                                'gstin'   => trim((string) ($ewb['transporter_id'] ?? '')),
                                'code'    => '',
                                'phone'   => '',
                                'address' => '',
                                'type'    => (stripos($tName, 'courier') !== false) ? 'Courier Service' : 'Parcel Service',
                                'source'  => 'ewaybills_extracted',
                            ];
                        }
                    }
                }

                $pageContext = $ewbRes['data']['page_context'] ?? [];
                $ewbHasMore = !empty($pageContext['has_more_page']);
                $ewbPage++;
            } else {
                break;
            }
        }

        $stats['total_found'] = count($transporters);

        // Process and upsert each transporter into local DB
        foreach ($transporters as $trData) {
            try {
                $existing = $this->transportModel->groupStart()
                    ->where('LOWER(transport_name)', strtolower($trData['name']))
                    ->orWhere('transport_name', $trData['name'])
                    ->groupEnd()
                    ->first();

                // If not found by name, check by GSTIN if provided
                if (!$existing && !empty($trData['gstin'])) {
                    $existing = $this->transportModel->where('branch_gst_number', $trData['gstin'])->first();
                }

                $record = [
                    'transport_name'      => $trData['name'],
                    'transport_type'      => $trData['type'],
                    'transport_code'      => !empty($trData['code']) ? $trData['code'] : ($trData['name']),
                    'branch'              => !empty($existing['branch']) ? $existing['branch'] : 'Main Branch',
                    'branch_address'      => !empty($trData['address']) ? $trData['address'] : (!empty($existing['branch_address']) ? $existing['branch_address'] : 'Tamil Nadu'),
                    'branch_phone_number' => !empty($trData['phone']) ? $trData['phone'] : (!empty($existing['branch_phone_number']) ? $existing['branch_phone_number'] : '-'),
                    'branch_gst_number'   => !empty($trData['gstin']) ? $trData['gstin'] : (!empty($existing['branch_gst_number']) ? $existing['branch_gst_number'] : ''),
                    'state_id'            => $existing['state_id'] ?? $defaultStateId,
                    'country_id'          => $existing['country_id'] ?? $indiaId,
                ];

                if ($existing) {
                    // Update existing
                    $updatePayload = [];
                    if (!empty($trData['gstin']) && empty($existing['branch_gst_number'])) {
                        $updatePayload['branch_gst_number'] = $trData['gstin'];
                    }
                    if (!empty($trData['code']) && empty($existing['transport_code'])) {
                        $updatePayload['transport_code'] = $trData['code'];
                    }
                    if (!empty($trData['phone']) && (empty($existing['branch_phone_number']) || $existing['branch_phone_number'] === '-')) {
                        $updatePayload['branch_phone_number'] = $trData['phone'];
                    }
                    if (!empty($trData['address']) && (empty($existing['branch_address']) || $existing['branch_address'] === 'Tamil Nadu')) {
                        $updatePayload['branch_address'] = $trData['address'];
                    }

                    if (!empty($updatePayload)) {
                        $this->transportModel->update($existing['id'], $updatePayload);
                        $stats['updated']++;
                        $stats['items'][] = ['name' => $trData['name'], 'action' => 'updated'];
                    } else {
                        $stats['skipped']++;
                        $stats['items'][] = ['name' => $trData['name'], 'action' => 'skipped_up_to_date'];
                    }
                } else {
                    // Insert new
                    $this->transportModel->insert($record);
                    $stats['created']++;
                    $stats['items'][] = ['name' => $trData['name'], 'action' => 'created'];
                }
            } catch (\Throwable $ex) {
                $stats['errors'][] = "Error saving '{$trData['name']}': " . $ex->getMessage();
            }
        }

        return $stats;
    }
}
