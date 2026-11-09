<?php

namespace App\Services\Zoho\Modules;

use App\Models\AddressModel;
use App\Models\VendorModel;
use App\Services\Zoho\ZohoHashService;
use App\Services\Zoho\ZohoSyncContext;

class VendorSyncService extends AbstractModuleSyncService
{
    protected VendorModel $vendorModel;
    protected AddressModel $addressModel;

    public function __construct($client = null)
    {
        parent::__construct($client);
        $this->vendorModel  = new VendorModel();
        $this->addressModel = new AddressModel();
    }

    public function getModuleName(): string
    {
        return 'vendors';
    }

    public function pushToZoho(int $localId, string $operation = 'UPDATE'): array
    {
        $mapping = $this->mappingModel->findByLocal('vendors', $localId);
        $vendor  = $this->vendorModel->find($localId);

        if ($operation === 'DELETE' || $operation === 'MARK_INACTIVE' || !$vendor) {
            if (!$mapping || empty($mapping['zoho_record_id'])) {
                return ['success' => true, 'message' => 'Vendor was not synced to Zoho; nothing to delete.'];
            }
            $zohoId = $mapping['zoho_record_id'];
            if ($operation === 'DELETE') {
                $delRes = $this->client->delete("/contacts/{$zohoId}", [], [
                    'module'          => 'vendors',
                    'local_record_id' => $localId,
                    'zoho_record_id'  => $zohoId,
                    'operation'       => 'DELETE',
                ]);
                if (!empty($delRes['success'])) {
                    $this->mappingModel->markStatus('vendors', $localId, 'DELETED_IN_ZOHO');
                    return ['success' => true, 'message' => 'Supplier deleted in Zoho Books.'];
                }
            }

            $inactRes = $this->client->post("/contacts/{$zohoId}/inactive", [], [], [
                'module'          => 'vendors',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'MARK_INACTIVE',
            ]);
            if (!empty($inactRes['success'])) {
                $this->mappingModel->markStatus('vendors', $localId, 'ARCHIVED');
                return ['success' => true, 'message' => 'Supplier marked inactive in Zoho Books.'];
            }
            $this->mappingModel->markStatus('vendors', $localId, 'FAILED', $inactRes['message'] ?? 'Delete/Inactive failed');
            return $inactRes;
        }

        $billing  = $this->addressModel->getBillingAddress('vendor', $localId);
        $shipping = $this->addressModel->getShippingAddress('vendor', $localId);

        $normalizedLocal = $this->normalizeLocalVendor($vendor, $billing);

        if ($mapping && !empty($mapping['zoho_record_id']) && !empty($mapping['local_version_hash'])) {
            if (ZohoHashService::hash($normalizedLocal) === $mapping['local_version_hash'] && $mapping['sync_status'] === 'SYNCED') {
                return [
                    'success'        => true,
                    'skipped'        => true,
                    'zoho_record_id' => $mapping['zoho_record_id'],
                    'message'        => 'Supplier unchanged since last sync.',
                ];
            }
        }

        $zohoId = $mapping['zoho_record_id'] ?? null;
        if (empty($zohoId)) {
            $zohoId = $this->findExistingZohoVendorId($vendor['name'], $vendor['gstin'] ?? null);
        }

        $payload = $this->buildZohoPayload($vendor, $billing, $shipping, $mapping);

        if (!empty($zohoId)) {
            $res = $this->client->put("/contacts/{$zohoId}", $payload, [], [
                'module'          => 'vendors',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'UPDATE',
            ]);
        } else {
            $res = $this->client->post('/contacts', $payload, [], [
                'module'          => 'vendors',
                'local_record_id' => $localId,
                'operation'       => 'CREATE',
            ]);
        }

        if (empty($res['success'])) {
            $this->mappingModel->markStatus('vendors', $localId, 'FAILED', $res['message'] ?? 'Sync failed');
            return $res;
        }

        $zohoContact = $res['data']['contact'] ?? [];
        $newZohoId   = (string) ($zohoContact['contact_id'] ?? $zohoId);

        $localStatus = strtolower((string) ($vendor['status'] ?? 'active'));
        $zohoStatus  = strtolower((string) ($zohoContact['status'] ?? 'active'));
        if ($localStatus !== $zohoStatus && !empty($newZohoId)) {
            $statusEndpoint = $localStatus === 'inactive' ? "/contacts/{$newZohoId}/inactive" : "/contacts/{$newZohoId}/active";
            $this->client->post($statusEndpoint, [], [], [
                'module'          => 'vendors',
                'local_record_id' => $localId,
                'zoho_record_id'  => $newZohoId,
                'operation'       => 'SYNC_STATUS',
            ]);
        }

        $normalizedZoho = !empty($zohoContact) ? $this->normalizeZohoVendor($zohoContact) : $normalizedLocal;

        $this->saveMapping(
            $localId,
            $newZohoId,
            $vendor['name'],
            $normalizedLocal,
            $normalizedZoho,
            'TO_ZOHO',
            $zohoContact['last_modified_time'] ?? null,
            [
                'primary_contact_person_id' => $zohoContact['primary_contact_id'] ?? ($zohoContact['contact_persons'][0]['contact_person_id'] ?? null),
            ]
        );

        return [
            'success'        => true,
            'zoho_record_id' => $newZohoId,
            'message'        => 'Supplier synchronized to Zoho Books.',
        ];
    }

    public function pullFromZoho(bool $incremental = false, bool $checkConflicts = true): array
    {
        $query = ['contact_type' => 'vendor'];
        if ($incremental) {
            $cursor = $this->cursorModel->getCursor('vendors');
            if (!empty($cursor['last_zoho_modified_time'])) {
                $query['last_modified_time'] = $cursor['last_zoho_modified_time'];
            }
        }

        $listRes = $this->client->getAllPages('/contacts', 'contacts', $query, 50, [
            'module'    => 'vendors',
            'direction' => 'FROM_ZOHO',
            'operation' => 'LIST_VENDORS',
        ]);

        if (empty($listRes['success'])) {
            return $listRes;
        }

        $stats = [
            'total_processed' => 0,
            'created_count'   => 0,
            'updated_count'   => 0,
            'skipped_count'   => 0,
            'conflict_count'  => 0,
            'failed_count'    => 0,
        ];

        $latestModified = null;

        foreach ($listRes['records'] as $summary) {
            if (($summary['contact_type'] ?? '') !== 'vendor') {
                continue;
            }
            $stats['total_processed']++;
            $zohoId = (string) ($summary['contact_id'] ?? '');
            if ($zohoId === '') {
                continue;
            }

            $mapping = $this->mappingModel->findByZoho('vendors', $zohoId);
            if ($mapping && !empty($summary['last_modified_time']) && $mapping['zoho_last_modified_time'] === $summary['last_modified_time'] && $mapping['sync_status'] === 'SYNCED') {
                $stats['skipped_count']++;
                continue;
            }

            $detailRes = $this->client->get("/contacts/{$zohoId}", [], [
                'module'         => 'vendors',
                'zoho_record_id' => $zohoId,
                'direction'      => 'FROM_ZOHO',
                'operation'      => 'GET_VENDOR_DETAIL',
            ]);

            $zohoContact = !empty($detailRes['success'])
                ? ($detailRes['data']['contact'] ?? $summary)
                : $summary;

            $applyRes = $this->applyZohoRecordToLocal($zohoContact, !$checkConflicts);
            $act      = $applyRes['action'] ?? 'SKIPPED';
            if ($act === 'CREATED') {
                $stats['created_count']++;
            } elseif ($act === 'UPDATED') {
                $stats['updated_count']++;
            } elseif ($act === 'CONFLICT') {
                $stats['conflict_count']++;
            } elseif ($act === 'FAILED') {
                $stats['failed_count']++;
            } else {
                $stats['skipped_count']++;
            }

            if (!empty($zohoContact['last_modified_time'])) {
                $latestModified = $zohoContact['last_modified_time'];
            }
        }

        $this->cursorModel->updateCursor('vendors', [
            'last_zoho_modified_time' => $latestModified ?: date('Y-m-d\TH:i:sO'),
            'last_full_sync_at'       => date('Y-m-d H:i:s'),
        ]);

        return array_merge(['success' => true], $stats);
    }

    public function applyZohoRecordToLocal(array $zohoContact, bool $forceOverwrite = false): array
    {
        $zohoId = (string) ($zohoContact['contact_id'] ?? '');
        if ($zohoId === '') {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Missing Zoho contact_id'];
        }

        $mapping     = $this->mappingModel->findByZoho('vendors', $zohoId);
        $localVendor = null;

        if ($mapping) {
            $localVendor = $this->vendorModel->find((int) $mapping['local_record_id']);
        }

        if (!$localVendor) {
            $zohoGstin = strtoupper(trim((string) ($zohoContact['gst_no'] ?? '')));
            if ($zohoGstin !== '') {
                $localVendor = $this->vendorModel->where('gstin', $zohoGstin)->first();
            }
            if (!$localVendor && !empty($zohoContact['contact_name'])) {
                $localVendor = $this->vendorModel->where('name', trim($zohoContact['contact_name']))->first();
            }
        }

        $normalizedZoho = $this->normalizeZohoVendor($zohoContact);

        if ($localVendor && !$forceOverwrite && $mapping) {
            $localId         = (int) $localVendor['id'];
            $billing         = $this->addressModel->getBillingAddress('vendor', $localId);
            $normalizedLocal = $this->normalizeLocalVendor($localVendor, $billing);

            $eval = $this->conflictService->evaluate(
                'vendors',
                $localId,
                $zohoId,
                $localVendor['name'] ?? $zohoId,
                $normalizedLocal,
                $normalizedZoho,
                $localVendor['updated_at'] ?? null,
                $zohoContact['last_modified_time'] ?? null,
                $mapping
            );

            if ($eval['action'] === 'NO_CHANGE') {
                return ['success' => true, 'action' => 'SKIPPED', 'local_record_id' => $localId];
            }
            if ($eval['action'] === 'CONFLICT') {
                return ['success' => false, 'action' => 'CONFLICT', 'local_record_id' => $localId, 'conflict_id' => $eval['conflict_id']];
            }
            if ($eval['action'] === 'APPLY_LOCAL') {
                $this->pushToZoho($localId, 'UPDATE');
                return ['success' => true, 'action' => 'UPDATED', 'local_record_id' => $localId];
            }
        }

        return ZohoSyncContext::runAsZoho(function () use ($zohoContact, $zohoId, $localVendor, $normalizedZoho) {
            $primaryPerson = $zohoContact['contact_persons'][0] ?? [];
            $contactPersonName = trim(($primaryPerson['first_name'] ?? '') . ' ' . ($primaryPerson['last_name'] ?? ''));

            $gstin = strtoupper(trim((string) ($zohoContact['gst_no'] ?? '')));
            $pan   = strtoupper(trim((string) ($zohoContact['pan_no'] ?? '')));
            if ($pan === '' && strlen($gstin) === 15) {
                $pan = substr($gstin, 2, 10);
            }

            $vendorData = [
                'name'            => trim((string) ($zohoContact['contact_name'] ?? $zohoContact['company_name'] ?? 'Unnamed Vendor')),
                'contact_person'  => $contactPersonName ?: ($localVendor['contact_person'] ?? null),
                'email'           => $zohoContact['email'] ?? ($primaryPerson['email'] ?? ($localVendor['email'] ?? null)),
                'website'         => $zohoContact['website'] ?? ($localVendor['website'] ?? null),
                'phone'           => $zohoContact['phone'] ?? ($primaryPerson['phone'] ?? ($localVendor['phone'] ?? null)),
                'whatsapp_number' => $zohoContact['mobile'] ?? ($primaryPerson['mobile'] ?? ($localVendor['whatsapp_number'] ?? null)),
                'gst_type'        => $this->taxMapper->mapZohoGstTreatmentToLocal($zohoContact['gst_treatment'] ?? null, true),
                'gstin'           => $gstin ?: null,
                'pan_number'      => $pan ?: null,
                'status'          => strtolower((string) ($zohoContact['status'] ?? 'active')) === 'inactive' ? 'inactive' : 'active',
                'notes'           => $zohoContact['notes'] ?? ($localVendor['notes'] ?? null),
            ];

            if ($localVendor) {
                $localId = (int) $localVendor['id'];
                $this->vendorModel->skipValidation(true)->update($localId, $vendorData);
                $action = 'UPDATED';
            } else {
                $vendorData['opening_balance'] = (float) ($zohoContact['opening_balance_amount'] ?? 0);
                $vendorData['balance_type']    = 'Cr';
                $localId = (int) $this->vendorModel->skipValidation(true)->insert($vendorData, true);
                $action = 'CREATED';
            }

            $this->syncLocalAddress('vendor', $localId, 'billing', $zohoContact['billing_address'] ?? [], $gstin, $zohoContact['source_of_supply'] ?? $zohoContact['place_of_contact'] ?? null);
            $this->syncLocalAddress('vendor', $localId, 'shipping', $zohoContact['shipping_address'] ?? [], $gstin, $zohoContact['source_of_supply'] ?? $zohoContact['place_of_contact'] ?? null);

            $updatedVendor   = $this->vendorModel->find($localId);
            $billing         = $this->addressModel->getBillingAddress('vendor', $localId);
            $normalizedLocal = $this->normalizeLocalVendor($updatedVendor, $billing);

            $this->saveMapping(
                $localId,
                $zohoId,
                $updatedVendor['name'],
                $normalizedLocal,
                $normalizedZoho,
                'FROM_ZOHO',
                $zohoContact['last_modified_time'] ?? null
            );

            return [
                'success'         => true,
                'action'          => $action,
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
            ];
        });
    }

    protected function buildZohoPayload(array $vendor, ?array $billing, ?array $shipping, ?array $mapping): array
    {
        $gstin        = strtoupper(trim((string) ($vendor['gstin'] ?? '')));
        $gstTreatment = $this->taxMapper->mapLocalGstTypeToZoho($vendor['gst_type'] ?? null, $gstin, true);
        $stateCode    = $this->taxMapper->resolveStateCode($billing['state_id'] ?? null, $gstin, $billing['state_name'] ?? null);

        $payload = [
            'contact_name'     => trim((string) $vendor['name']),
            'company_name'     => trim((string) $vendor['name']),
            'contact_type'     => 'vendor',
            'gst_treatment'    => $gstTreatment,
            'source_of_supply' => $stateCode,
            'place_of_contact' => $stateCode,
            'website'          => (string) ($vendor['website'] ?? ''),
            'notes'            => (string) ($vendor['notes'] ?? ''),
        ];

        if (in_array($gstTreatment, ['business_gst', 'business_composition_gst'], true) && strlen($gstin) === 15) {
            $payload['gst_no'] = $gstin;
        }

        if (!empty($vendor['pan_number'])) {
            $payload['pan_no'] = strtoupper(trim((string) $vendor['pan_number']));
        }

        if ($billing) {
            $payload['billing_address'] = [
                'attention'  => (string) ($vendor['contact_person'] ?? $vendor['name']),
                'address'    => (string) ($billing['address_line1'] ?? ''),
                'street2'    => (string) ($billing['address_line2'] ?? ''),
                'city'       => (string) ($billing['city'] ?? ''),
                'state'      => (string) ($billing['state_name'] ?? ''),
                'state_code' => $stateCode,
                'zip'        => (string) ($billing['pincode'] ?? ''),
                'country'    => (string) ($billing['country_name'] ?? 'India'),
            ];
        }

        if ($shipping) {
            $shipStateCode = $this->taxMapper->resolveStateCode($shipping['state_id'] ?? null, $gstin, $shipping['state_name'] ?? null);
            $payload['shipping_address'] = [
                'attention'  => (string) ($vendor['contact_person'] ?? $vendor['name']),
                'address'    => (string) ($shipping['address_line1'] ?? ''),
                'street2'    => (string) ($shipping['address_line2'] ?? ''),
                'city'       => (string) ($shipping['city'] ?? ''),
                'state'      => (string) ($shipping['state_name'] ?? ''),
                'state_code' => $shipStateCode,
                'zip'        => (string) ($shipping['pincode'] ?? ''),
                'country'    => (string) ($shipping['country_name'] ?? 'India'),
            ];
        }

        if (!empty($vendor['contact_person']) || !empty($vendor['email']) || !empty($vendor['phone']) || !empty($vendor['whatsapp_number'])) {
            $person = [
                'first_name'         => (string) ($vendor['contact_person'] ?: $vendor['name']),
                'email'              => (string) ($vendor['email'] ?? ''),
                'phone'              => (string) ($vendor['phone'] ?? ''),
                'mobile'             => (string) ($vendor['whatsapp_number'] ?? $vendor['phone'] ?? ''),
                'is_primary_contact' => true,
            ];
            $meta = !empty($mapping['metadata_json']) ? (json_decode($mapping['metadata_json'], true) ?: []) : [];
            if (!empty($meta['primary_contact_person_id'])) {
                $person['contact_person_id'] = $meta['primary_contact_person_id'];
            }
            $payload['contact_persons'] = [$person];
        }

        return $payload;
    }

    protected function syncLocalAddress(string $ownerType, int $ownerId, string $addressType, array $zohoAddr, ?string $gstin, ?string $stateCodeHint): void
    {
        $line1 = trim((string) ($zohoAddr['address'] ?? ''));
        $city  = trim((string) ($zohoAddr['city'] ?? ''));
        $zip   = trim((string) ($zohoAddr['zip'] ?? ''));

        if ($line1 === '' && $city === '' && $zip === '') {
            return;
        }

        $stateId = $this->taxMapper->resolveLocalStateId(
            $zohoAddr['state_code'] ?? $stateCodeHint,
            $zohoAddr['state'] ?? null,
            $gstin
        );

        $existing = $this->addressModel->where([
            'owner_type'   => $ownerType,
            'owner_id'     => $ownerId,
            'address_type' => $addressType,
        ])->first();

        $addrData = [
            'owner_type'    => $ownerType,
            'owner_id'      => $ownerId,
            'address_type'  => $addressType,
            'address_line1' => $line1 ?: $city,
            'address_line2' => trim((string) ($zohoAddr['street2'] ?? '')) ?: null,
            'city'          => $city ?: 'City',
            'pincode'       => $zip ?: '000000',
            'state_id'      => $stateId,
            'country_id'    => 1,
            'is_active'     => 1,
        ];

        if ($existing) {
            $this->addressModel->skipValidation(true)->update($existing['id'], $addrData);
        } else {
            $this->addressModel->skipValidation(true)->insert($addrData);
        }
    }

    protected function findExistingZohoVendorId(string $name, ?string $gstin): ?string
    {
        if (!empty($gstin)) {
            $res = $this->client->get('/contacts', [
                'contact_type' => 'vendor',
                'gst_no'       => strtoupper(trim($gstin)),
            ], ['module' => 'vendors', 'operation' => 'LOOKUP_BY_GSTIN']);
            if (!empty($res['data']['contacts'][0]['contact_id'])) {
                return (string) $res['data']['contacts'][0]['contact_id'];
            }
        }

        $res = $this->client->get('/contacts', [
            'contact_type' => 'vendor',
            'contact_name' => trim($name),
        ], ['module' => 'vendors', 'operation' => 'LOOKUP_BY_NAME']);

        if (!empty($res['data']['contacts'][0]['contact_id'])) {
            return (string) $res['data']['contacts'][0]['contact_id'];
        }

        return null;
    }

    public function normalizeLocalVendor(array $vendor, ?array $billing): array
    {
        return [
            'name'            => trim((string) ($vendor['name'] ?? '')),
            'email'           => trim((string) ($vendor['email'] ?? '')),
            'phone'           => trim((string) ($vendor['phone'] ?? '')),
            'whatsapp_number' => trim((string) ($vendor['whatsapp_number'] ?? '')),
            'gstin'           => strtoupper(trim((string) ($vendor['gstin'] ?? ''))),
            'gst_type'        => trim((string) ($vendor['gst_type'] ?? 'Unregistered')),
            'billing_line1'   => trim((string) ($billing['address_line1'] ?? '')),
            'billing_city'    => trim((string) ($billing['city'] ?? '')),
            'billing_pincode' => trim((string) ($billing['pincode'] ?? '')),
            'status'          => strtolower(trim((string) ($vendor['status'] ?? 'active'))),
        ];
    }

    public function normalizeZohoVendor(array $zoho): array
    {
        $primaryPerson = $zoho['contact_persons'][0] ?? [];
        $billing       = $zoho['billing_address'] ?? [];
        return [
            'name'            => trim((string) ($zoho['contact_name'] ?? $zoho['company_name'] ?? '')),
            'email'           => trim((string) ($zoho['email'] ?? $primaryPerson['email'] ?? '')),
            'phone'           => trim((string) ($zoho['phone'] ?? $primaryPerson['phone'] ?? '')),
            'whatsapp_number' => trim((string) ($zoho['mobile'] ?? $primaryPerson['mobile'] ?? '')),
            'gstin'           => strtoupper(trim((string) ($zoho['gst_no'] ?? ''))),
            'gst_type'        => $this->taxMapper->mapZohoGstTreatmentToLocal($zoho['gst_treatment'] ?? null, true),
            'billing_line1'   => trim((string) ($billing['address'] ?? '')),
            'billing_city'    => trim((string) ($billing['city'] ?? '')),
            'billing_pincode' => trim((string) ($billing['zip'] ?? '')),
            'status'          => strtolower(trim((string) ($zoho['status'] ?? 'active'))) === 'inactive' ? 'inactive' : 'active',
        ];
    }
}

