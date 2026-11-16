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
     * Fetch transporters from Zoho Books and sync into local transports table.
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

        // 1. Try e-Way Bill / Transporters settings endpoint (/settings/transporters)
        $ewbRes = $this->client->get('/settings/transporters', [], [
            'module'    => 'transports',
            'operation' => 'FETCH_ZOHO_TRANSPORTERS',
        ]);

        if (!empty($ewbRes['success']) && !empty($ewbRes['data'])) {
            $rawList = $ewbRes['data']['transporters'] ?? ($ewbRes['data']['data'] ?? []);
            if (is_array($rawList)) {
                foreach ($rawList as $item) {
                    $name = trim((string) ($item['transporter_name'] ?? ($item['name'] ?? '')));
                    if ($name === '') {
                        continue;
                    }
                    $transporters[strtoupper($name)] = [
                        'name'    => $name,
                        'gstin'   => trim((string) ($item['transporter_id'] ?? ($item['gst_no'] ?? ($item['gstin'] ?? '')))),
                        'code'    => trim((string) ($item['transporter_code'] ?? ($item['code'] ?? ''))),
                        'phone'   => trim((string) ($item['phone'] ?? ($item['mobile'] ?? ''))),
                        'address' => trim((string) ($item['address'] ?? '')),
                        'type'    => (stripos($name, 'courier') !== false) ? 'Courier Service' : 'Parcel Service',
                        'source'  => 'ewaybill_transporters',
                    ];
                }
            }
        }

        // 2. Fetch Vendors tagged with or named Transport / Courier
        $vendorQueries = ['transport', 'courier', 'cargo', 'logistics', 'parcel'];
        foreach ($vendorQueries as $vq) {
            $vRes = $this->client->get('/contacts', [
                'contact_type' => 'vendor',
                'search_text'  => $vq,
                'per_page'     => 50,
            ], [
                'module'    => 'transports',
                'operation' => 'SEARCH_VENDOR_TRANSPORTERS',
            ]);

            if (!empty($vRes['success']) && !empty($vRes['data']['contacts'])) {
                foreach ($vRes['data']['contacts'] as $contact) {
                    $cName = trim((string) ($contact['contact_name'] ?? ($contact['company_name'] ?? '')));
                    if ($cName === '') {
                        continue;
                    }

                    $key = strtoupper($cName);
                    if (!isset($transporters[$key])) {
                        $transporters[$key] = [
                            'name'    => $cName,
                            'gstin'   => trim((string) ($contact['gst_no'] ?? ($contact['gst_treatment'] ?? ''))),
                            'code'    => trim((string) ($contact['contact_salutation'] ?? '')),
                            'phone'   => trim((string) ($contact['phone'] ?? ($contact['mobile'] ?? ''))),
                            'address' => trim((string) ($contact['billing_address']['address'] ?? '')),
                            'type'    => (stripos($cName, 'courier') !== false) ? 'Courier Service' : 'Parcel Service',
                            'source'  => 'vendor_contacts',
                        ];
                    }
                }
            }
        }

        // 3. Fallback: Extract from recent Zoho e-Way bills or invoices if available
        $ewbListRes = $this->client->get('/ewaybills', ['per_page' => 50], [
            'module'    => 'transports',
            'operation' => 'EXTRACT_FROM_EWAYBILLS',
        ]);
        if (!empty($ewbListRes['success']) && !empty($ewbListRes['data']['ewaybills'])) {
            foreach ($ewbListRes['data']['ewaybills'] as $ewb) {
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
