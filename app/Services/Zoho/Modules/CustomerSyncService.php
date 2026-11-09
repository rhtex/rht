<?php

namespace App\Services\Zoho\Modules;

use App\Models\AddressModel;
use App\Models\CustomerModel;
use App\Services\Zoho\ZohoHashService;
use App\Services\Zoho\ZohoSyncContext;

class CustomerSyncService extends AbstractModuleSyncService
{
    protected CustomerModel $customerModel;
    protected AddressModel $addressModel;

    public function __construct($client = null)
    {
        parent::__construct($client);
        $this->customerModel = new CustomerModel();
        $this->addressModel  = new AddressModel();
    }

    public function getModuleName(): string
    {
        return 'customers';
    }

    public function pushToZoho(int $localId, string $operation = 'UPDATE'): array
    {
        $mapping  = $this->mappingModel->findByLocal('customers', $localId);
        $customer = $this->customerModel->find($localId);

        if ($operation === 'DELETE' || $operation === 'MARK_INACTIVE' || !$customer) {
            if (!$mapping || empty($mapping['zoho_record_id'])) {
                return ['success' => true, 'message' => 'Customer was not synced to Zoho; nothing to delete.'];
            }
            $zohoId = $mapping['zoho_record_id'];
            if ($operation === 'DELETE') {
                $delRes = $this->client->delete("/contacts/{$zohoId}", [], [
                    'module'          => 'customers',
                    'local_record_id' => $localId,
                    'zoho_record_id'  => $zohoId,
                    'operation'       => 'DELETE',
                ]);
                if (!empty($delRes['success'])) {
                    $this->mappingModel->markStatus('customers', $localId, 'DELETED_IN_ZOHO');
                    return ['success' => true, 'message' => 'Customer deleted in Zoho Books.'];
                }
            }

            // Fallback to marking inactive in Zoho when transactions prevent deletion
            $inactRes = $this->client->post("/contacts/{$zohoId}/inactive", [], [], [
                'module'          => 'customers',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'MARK_INACTIVE',
            ]);
            if (!empty($inactRes['success'])) {
                $this->mappingModel->markStatus('customers', $localId, 'ARCHIVED');
                return ['success' => true, 'message' => 'Customer marked inactive in Zoho Books (transactions exist).'];
            }
            $this->mappingModel->markStatus('customers', $localId, 'FAILED', $inactRes['message'] ?? 'Delete/Inactive failed');
            return $inactRes;
        }

        $billing  = $this->addressModel->getBillingAddress('customer', $localId);
        $shipping = $this->addressModel->getShippingAddress('customer', $localId);

        $normalizedLocal = $this->normalizeLocalCustomer($customer, $billing, $shipping);

        // Skip redundant push if local hash hasn't changed since last sync
        if ($mapping && !empty($mapping['zoho_record_id']) && !empty($mapping['local_version_hash'])) {
            if (ZohoHashService::hash($normalizedLocal) === $mapping['local_version_hash'] && $mapping['sync_status'] === 'SYNCED') {
                return [
                    'success'        => true,
                    'skipped'        => true,
                    'zoho_record_id' => $mapping['zoho_record_id'],
                    'message'        => 'Customer unchanged since last sync.',
                ];
            }
        }

        $zohoId = $mapping['zoho_record_id'] ?? null;

        // Before creating a new Zoho contact, check if one already exists in Zoho by GSTIN or exact name
        if (empty($zohoId)) {
            $zohoId = $this->findExistingZohoContactId($customer['name'], $customer['gstin'] ?? null, 'customer');
        }

        $payload = $this->buildZohoPayload($customer, $billing, $shipping, $mapping);

        if (!empty($zohoId)) {
            $res = $this->client->put("/contacts/{$zohoId}", $payload, [], [
                'module'          => 'customers',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'UPDATE',
            ]);
        } else {
            $res = $this->client->post('/contacts', $payload, [], [
                'module'          => 'customers',
                'local_record_id' => $localId,
                'operation'       => 'CREATE',
            ]);
        }

        if (empty($res['success'])) {
            $this->mappingModel->markStatus('customers', $localId, 'FAILED', $res['message'] ?? 'Sync failed');
            return $res;
        }

        $zohoContact = $res['data']['contact'] ?? [];
        $newZohoId   = (string) ($zohoContact['contact_id'] ?? $zohoId);

        // Sync active/inactive status if needed
        $localStatus = strtolower((string) ($customer['status'] ?? 'active'));
        $zohoStatus  = strtolower((string) ($zohoContact['status'] ?? 'active'));
        if ($localStatus !== $zohoStatus && !empty($newZohoId)) {
            $statusEndpoint = $localStatus === 'inactive' ? "/contacts/{$newZohoId}/inactive" : "/contacts/{$newZohoId}/active";
            $this->client->post($statusEndpoint, [], [], [
                'module'          => 'customers',
                'local_record_id' => $localId,
                'zoho_record_id'  => $newZohoId,
                'operation'       => 'SYNC_STATUS',
            ]);
        }

        $normalizedZoho = !empty($zohoContact) ? $this->normalizeZohoCustomer($zohoContact) : $normalizedLocal;

        $this->saveMapping(
            $localId,
            $newZohoId,
            $customer['name'],
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
            'message'        => 'Customer synchronized to Zoho Books.',
        ];
    }

    public function pullFromZoho(bool $incremental = false, bool $checkConflicts = true): array
    {
        $query = ['contact_type' => 'customer'];
        if ($incremental) {
            $cursor = $this->cursorModel->getCursor('customers');
            if (!empty($cursor['last_zoho_modified_time'])) {
                $query['last_modified_time'] = $cursor['last_zoho_modified_time'];
            }
        }

        $listRes = $this->client->getAllPages('/contacts', 'contacts', $query, 50, [
            'module'    => 'customers',
            'direction' => 'FROM_ZOHO',
            'operation' => 'LIST_CUSTOMERS',
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
            if (($summary['contact_type'] ?? 'customer') !== 'customer') {
                continue;
            }
            $stats['total_processed']++;
            $zohoId = (string) ($summary['contact_id'] ?? '');
            if ($zohoId === '') {
                continue;
            }

            // Quick hash check if unchanged
            $mapping = $this->mappingModel->findByZoho('customers', $zohoId);
            if ($mapping && !empty($summary['last_modified_time']) && $mapping['zoho_last_modified_time'] === $summary['last_modified_time'] && $mapping['sync_status'] === 'SYNCED') {
                $stats['skipped_count']++;
                continue;
            }

            // Fetch full contact detail (includes billing_address, shipping_address, contact_persons)
            $detailRes = $this->client->get("/contacts/{$zohoId}", [], [
                'module'         => 'customers',
                'zoho_record_id' => $zohoId,
                'direction'      => 'FROM_ZOHO',
                'operation'      => 'GET_CUSTOMER_DETAIL',
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

        $this->cursorModel->updateCursor('customers', [
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

        $mapping = $this->mappingModel->findByZoho('customers', $zohoId);
        $localCustomer = null;

        if ($mapping) {
            $localCustomer = $this->customerModel->find((int) $mapping['local_record_id']);
        }

        $normalizedZoho = $this->normalizeZohoCustomer($zohoContact);

        if ($localCustomer && !$forceOverwrite && $mapping) {
            $localId  = (int) $localCustomer['id'];
            $billing  = $this->addressModel->getBillingAddress('customer', $localId);
            $shipping = $this->addressModel->getShippingAddress('customer', $localId);
            $normalizedLocal = $this->normalizeLocalCustomer($localCustomer, $billing, $shipping);

            $eval = $this->conflictService->evaluate(
                'customers',
                $localId,
                $zohoId,
                $localCustomer['name'] ?? $zohoId,
                $normalizedLocal,
                $normalizedZoho,
                $localCustomer['updated_at'] ?? null,
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

        return ZohoSyncContext::runAsZoho(function () use ($zohoContact, $zohoId, $localCustomer, $normalizedZoho) {
            $primaryPerson = $zohoContact['contact_persons'][0] ?? [];
            $contactPersonName = trim(($primaryPerson['first_name'] ?? '') . ' ' . ($primaryPerson['last_name'] ?? ''));

            $gstin       = strtoupper(trim((string) ($zohoContact['gst_no'] ?? '')));
            $pan         = strtoupper(trim((string) ($zohoContact['pan_no'] ?? '')));
            if ($pan === '' && strlen($gstin) === 15) {
                $pan = substr($gstin, 2, 10);
            }

            $customerData = [
                'name'               => trim((string) ($zohoContact['contact_name'] ?? $zohoContact['company_name'] ?? 'Unnamed Customer')),
                'contact_person'     => $contactPersonName ?: ($localCustomer['contact_person'] ?? null),
                'email'              => $zohoContact['email'] ?? ($primaryPerson['email'] ?? ($localCustomer['email'] ?? null)),
                'website'            => $zohoContact['website'] ?? ($localCustomer['website'] ?? null),
                'phone'              => $zohoContact['phone'] ?? ($primaryPerson['phone'] ?? ($localCustomer['phone'] ?? null)),
                'whatsapp_number'    => $zohoContact['mobile'] ?? ($primaryPerson['mobile'] ?? ($localCustomer['whatsapp_number'] ?? null)),
                'gst_type'           => $this->taxMapper->mapZohoGstTreatmentToLocal($zohoContact['gst_treatment'] ?? null, false),
                'gstin'              => $gstin ?: null,
                'pan_number'         => $pan ?: null,
                'credit_limit'       => (float) ($zohoContact['credit_limit'] ?? $zohoContact['credit_limit_exceeded_amount'] ?? ($localCustomer['credit_limit'] ?? 0)),
                'credit_period_days' => (int) ($zohoContact['payment_terms'] ?? ($localCustomer['credit_period_days'] ?? 0)),
                'status'             => strtolower((string) ($zohoContact['status'] ?? 'active')) === 'inactive' ? 'inactive' : 'active',
                'notes'              => $zohoContact['notes'] ?? ($localCustomer['notes'] ?? null),
            ];

            if ($localCustomer) {
                $localId = (int) $localCustomer['id'];
                $this->customerModel->skipValidation(true)->update($localId, $customerData);
                $action = 'UPDATED';
            } else {
                $customerData['opening_balance'] = (float) ($zohoContact['opening_balance_amount'] ?? 0);
                $customerData['balance_type']    = 'Dr';
                $localId = (int) $this->customerModel->skipValidation(true)->insert($customerData, true);
                $action = 'CREATED';
            }

            // Upsert billing & shipping addresses
            $this->syncLocalAddress('customer', $localId, 'billing', $zohoContact['billing_address'] ?? [], $gstin, $zohoContact['place_of_contact'] ?? null);
            $this->syncLocalAddress('customer', $localId, 'shipping', $zohoContact['shipping_address'] ?? [], $gstin, $zohoContact['place_of_contact'] ?? null);

            $updatedCustomer = $this->customerModel->find($localId);
            $billing         = $this->addressModel->getBillingAddress('customer', $localId);
            $shipping        = $this->addressModel->getShippingAddress('customer', $localId);
            $normalizedLocal = $this->normalizeLocalCustomer($updatedCustomer, $billing, $shipping);

            $this->saveMapping(
                $localId,
                $zohoId,
                $updatedCustomer['name'],
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

    protected function buildZohoPayload(array $customer, ?array $billing, ?array $shipping, ?array $mapping): array
    {
        $gstin        = strtoupper(trim((string) ($customer['gstin'] ?? '')));
        $gstTreatment = $this->taxMapper->mapLocalGstTypeToZoho($customer['gst_type'] ?? null, $gstin, false);
        $stateCode    = $this->taxMapper->resolveStateCode($billing['state_id'] ?? null, $gstin, $billing['state_name'] ?? null);

        $payload = [
            'contact_name'      => trim((string) $customer['name']),
            'company_name'      => trim((string) $customer['name']),
            'contact_type'      => 'customer',
            'gst_treatment'     => $gstTreatment,
            'place_of_contact'  => $stateCode,
            'website'           => (string) ($customer['website'] ?? ''),
            'credit_limit'      => round((float) ($customer['credit_limit'] ?? 0), 2),
            'payment_terms'     => (int) ($customer['credit_period_days'] ?? 0),
            'notes'             => (string) ($customer['notes'] ?? ''),
        ];

        if (in_array($gstTreatment, ['business_gst', 'business_composition_gst'], true) && strlen($gstin) === 15) {
            $payload['gst_no'] = $gstin;
        }

        if (!empty($customer['pan_number'])) {
            $payload['pan_no'] = strtoupper(trim((string) $customer['pan_number']));
        }

        if ($billing) {
            $payload['billing_address'] = [
                'attention'  => (string) ($customer['contact_person'] ?? $customer['name']),
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
                'attention'  => (string) ($customer['contact_person'] ?? $customer['name']),
                'address'    => (string) ($shipping['address_line1'] ?? ''),
                'street2'    => (string) ($shipping['address_line2'] ?? ''),
                'city'       => (string) ($shipping['city'] ?? ''),
                'state'      => (string) ($shipping['state_name'] ?? ''),
                'state_code' => $shipStateCode,
                'zip'        => (string) ($shipping['pincode'] ?? ''),
                'country'    => (string) ($shipping['country_name'] ?? 'India'),
            ];
        }

        if (!empty($customer['contact_person']) || !empty($customer['email']) || !empty($customer['phone']) || !empty($customer['whatsapp_number'])) {
            $person = [
                'first_name'         => (string) ($customer['contact_person'] ?: $customer['name']),
                'email'              => (string) ($customer['email'] ?? ''),
                'phone'              => (string) ($customer['phone'] ?? ''),
                'mobile'             => (string) ($customer['whatsapp_number'] ?? $customer['phone'] ?? ''),
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

    protected function syncLocalAddress(string $ownerType, int $ownerId, string $addressType, array $zohoAddr, ?string $gstin, ?string $placeOfContact): void
    {
        $line1 = trim((string) ($zohoAddr['address'] ?? ''));
        $city  = trim((string) ($zohoAddr['city'] ?? ''));
        $zip   = trim((string) ($zohoAddr['zip'] ?? ''));

        if ($line1 === '' && $city === '' && $zip === '') {
            return;
        }

        $stateId = $this->taxMapper->resolveLocalStateId(
            $zohoAddr['state_code'] ?? $placeOfContact,
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

    protected function findExistingZohoContactId(string $name, ?string $gstin, string $contactType): ?string
    {
        if (!empty($gstin)) {
            $res = $this->client->get('/contacts', [
                'contact_type' => $contactType,
                'gst_no'       => strtoupper(trim($gstin)),
            ], ['module' => $this->getModuleName(), 'operation' => 'LOOKUP_BY_GSTIN']);
            if (!empty($res['data']['contacts'][0]['contact_id'])) {
                return (string) $res['data']['contacts'][0]['contact_id'];
            }
        }

        $res = $this->client->get('/contacts', [
            'contact_type' => $contactType,
            'contact_name' => trim($name),
        ], ['module' => $this->getModuleName(), 'operation' => 'LOOKUP_BY_NAME']);

        if (!empty($res['data']['contacts'][0]['contact_id'])) {
            return (string) $res['data']['contacts'][0]['contact_id'];
        }

        return null;
    }

    public function normalizeLocalCustomer(array $customer, ?array $billing, ?array $shipping): array
    {
        return [
            'name'            => trim((string) ($customer['name'] ?? '')),
            'email'           => trim((string) ($customer['email'] ?? '')),
            'phone'           => trim((string) ($customer['phone'] ?? '')),
            'whatsapp_number' => trim((string) ($customer['whatsapp_number'] ?? '')),
            'gstin'           => strtoupper(trim((string) ($customer['gstin'] ?? ''))),
            'gst_type'        => trim((string) ($customer['gst_type'] ?? 'Consumer')),
            'billing_line1'   => trim((string) ($billing['address_line1'] ?? '')),
            'billing_city'    => trim((string) ($billing['city'] ?? '')),
            'billing_pincode' => trim((string) ($billing['pincode'] ?? '')),
            'status'          => strtolower(trim((string) ($customer['status'] ?? 'active'))),
        ];
    }

    public function normalizeZohoCustomer(array $zoho): array
    {
        $primaryPerson = $zoho['contact_persons'][0] ?? [];
        $billing       = $zoho['billing_address'] ?? [];
        return [
            'name'            => trim((string) ($zoho['contact_name'] ?? $zoho['company_name'] ?? '')),
            'email'           => trim((string) ($zoho['email'] ?? $primaryPerson['email'] ?? '')),
            'phone'           => trim((string) ($zoho['phone'] ?? $primaryPerson['phone'] ?? '')),
            'whatsapp_number' => trim((string) ($zoho['mobile'] ?? $primaryPerson['mobile'] ?? '')),
            'gstin'           => strtoupper(trim((string) ($zoho['gst_no'] ?? ''))),
            'gst_type'        => $this->taxMapper->mapZohoGstTreatmentToLocal($zoho['gst_treatment'] ?? null, false),
            'billing_line1'   => trim((string) ($billing['address'] ?? '')),
            'billing_city'    => trim((string) ($billing['city'] ?? '')),
            'billing_pincode' => trim((string) ($billing['zip'] ?? '')),
            'status'          => strtolower(trim((string) ($zoho['status'] ?? 'active'))) === 'inactive' ? 'inactive' : 'active',
        ];
    }
}

