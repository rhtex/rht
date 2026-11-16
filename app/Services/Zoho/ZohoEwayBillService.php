<?php

namespace App\Services\Zoho;

use App\Models\EwayBillModel;
use App\Models\InvoiceModel;
use App\Models\SalesReturnModel;
use App\Models\ReturnShipmentModel;
use App\Models\TransportModel;
use App\Models\Zoho\ZohoSettingModel;
use App\Models\Zoho\ZohoSyncMappingModel;

class ZohoEwayBillService
{
    protected ZohoApiClient $client;
    protected ZohoSettingModel $settingModel;
    protected ZohoSyncMappingModel $mappingModel;
    protected EwayBillModel $ewayModel;
    protected TransportModel $transportModel;

    public function __construct(?ZohoApiClient $client = null)
    {
        $this->client         = $client ?? new ZohoApiClient();
        $this->settingModel   = new ZohoSettingModel();
        $this->mappingModel   = new ZohoSyncMappingModel();
        $this->ewayModel      = new EwayBillModel();
        $this->transportModel = new TransportModel();
    }

    public function getClient(): ZohoApiClient
    {
        return $this->client;
    }

    /**
     * Build API metadata for e-Way Bill requests, defaulting to the configured API service ('inventory' or 'books').
     */
    protected function getEwbMeta(string $operation, ?int $localRecordId = null, ?string $forceService = null, string $module = 'eway_bills'): array
    {
        $config  = $this->settingModel->getConfig(false);
        $service = $forceService ?? ($config['ewaybill_api_service'] ?? 'inventory');
        if (!in_array($service, ['inventory', 'books'], true)) {
            $service = 'inventory';
        }

        $meta = [
            'module'      => $module,
            'operation'   => $operation,
            'api_service' => $service,
        ];
        if ($localRecordId !== null) {
            $meta['local_record_id'] = $localRecordId;
        }
        return $meta;
    }

    /**
     * Execute an e-Way Bill request against the configured Zoho API (Inventory v1 by default).
     * Handles HTTP 401 code 57 (missing ZohoInventory OAuth scope) with optional Books v3 fallback for reads/syncs.
     */
    protected function requestEwb(
        string $method,
        string $endpoint,
        ?array $payload = null,
        array $query = [],
        array $meta = [],
        bool $allowBooksFallbackOnAuth = false
    ): array {
        if (empty($meta['api_service'])) {
            $meta = array_merge($this->getEwbMeta($meta['operation'] ?? strtoupper($method)), $meta);
        }

        $res = $this->client->request($method, $endpoint, $payload, $query, $meta);

        if (
            ($meta['api_service'] ?? 'inventory') === 'inventory'
            && empty($res['success'])
            && (int) ($res['http_code'] ?? 0) === 401
            && (int) ($res['zoho_code'] ?? 0) === 57
        ) {
            if ($allowBooksFallbackOnAuth) {
                $booksMeta                = $meta;
                $booksMeta['api_service'] = 'books';
                $fallbackRes              = $this->client->request($method, $endpoint, $payload, $query, $booksMeta);
                if (!empty($fallbackRes['success'])) {
                    $fallbackRes['used_fallback_service'] = 'books';
                    return $fallbackRes;
                }
            }

            $res['auth_scope_required'] = 'ZohoInventory.fullaccess.all';
            $res['message']             = 'Zoho Inventory API returned HTTP 401 (Not Authorized for ZohoInventory). '
                . 'Please go to Settings -> Integrations -> Zoho and click "Connect / Re-Authorize Zoho (Books + Inventory OAuth 2.0)" '
                . 'to authorize the ZohoInventory.fullaccess.all scope.';
        }

        return $res;
    }

    /**
     * Map internal document type to Zoho e-Way Bill entity_type
     */
    protected function mapDocTypeToZoho(string $docType): string
    {
        return match ($docType) {
            'credit_note'     => 'creditnote',
            'delivery_challan' => 'deliverychallan',
            'purchase_bill'   => 'bill',
            default           => 'invoice',
        };
    }

    /**
     * Map transaction_type to Zoho Inventory v1 allowed format
     */
    protected function mapTransactionType(?string $type): string
    {
        $t = strtolower(trim((string) $type));
        return match ($t) {
            '2', 'bill_to_ship_to', 'bill to - ship to', 'bill to ship to' => 'bill_to_ship_to',
            '3', 'bill_from_dispatch_from', 'bill from - dispatch from', 'bill from dispatch from' => 'bill_from_dispatch_from',
            '4', 'combination_of_2_and_3', 'combination of 2 and 3' => 'combination_of_2_and_3',
            default => 'regular',
        };
    }

    /**
     * Map transportation mode to Zoho Books string format ('road', 'rail', 'air', 'ship')
     */
    protected function mapTransportationMode(mixed $mode): string
    {
        $modeStr = strtolower(trim((string) $mode));
        return match ($modeStr) {
            '2', 'rail', 'railways' => 'rail',
            '3', 'air', 'airways'   => 'air',
            '4', 'ship', 'shipment' => 'ship',
            default                 => 'road',
        };
    }

    /**
     * Map sub_supply_type to Zoho Books allowed format
     */
    protected function mapSubSupplyType(?string $type): string
    {
        $typeStr = strtolower(trim((string) $type));
        return match ($typeStr) {
            'export'                 => 'export',
            'job work', 'job_work'   => 'job_work',
            'skd/ckd', 'skd_ckd', 'skd', 'sktd' => 'sktd',
            'recipient not known', 'recipient_not_known' => 'recipient_not_known',
            'for own use', 'for_own_use' => 'for_own_use',
            'exhibition or fairs', 'exhibition_or_fairs' => 'exhibition_or_fairs',
            'line sales', 'line_sales' => 'line_sales',
            'others'                 => 'others',
            default                  => 'supply',
        };
    }

    /**
     * Map cancel reason (NIC code 1-4 or label) to Zoho Books value
     */
    protected function mapCancelReason(mixed $reason): string
    {
        $r = strtolower(trim((string) $reason));
        return match ($r) {
            '1', 'duplicate', 'duplicate generation'           => 'duplicate',
            '2', 'order_cancelled', 'order cancelled'           => 'order_cancelled',
            '3', 'data_entry_mistake', 'data entry mistake'     => 'data_entry_mistake',
            default                                             => 'others',
        };
    }

    /**
     * Map vehicle (Part-B) update reason to Zoho Books value
     */
    protected function mapVehicleUpdateReason(mixed $reason): string
    {
        $r = strtolower(trim((string) $reason));
        return match ($r) {
            'vehicle breakdown', 'break_down', 'due_to_break_down' => 'due_to_break_down',
            'transshipment', 'transhipment', 'due_to_transhipment' => 'due_to_transhipment',
            'first transit', 'first_time'                          => 'first_time',
            default                                                => 'others',
        };
    }

    /**
     * True if this e-Way Bill number is already known as cancelled for the document (locally).
     * Prevents re-adopting an old cancelled number as a "new" e-Way Bill.
     */
    protected function isCancelledNumber(string $docType, int $docId, string $ewbNumber): bool
    {
        return $this->ewayModel->where('document_type', $docType)
            ->where('document_id', $docId)
            ->where('ewaybill_number', $ewbNumber)
            ->where('status', 'cancelled')
            ->countAllResults() > 0;
    }

    /**
     * Fetch the mapped Zoho record ID for a local document
     */
    public function getZohoRecordId(string $docType, int $docId): ?string
    {
        $mappingModule = match ($docType) {
            'credit_note'   => 'credit_notes',
            'purchase_bill' => 'bills',
            default         => 'invoices',
        };

        $map = $this->mappingModel->findByLocal($mappingModule, $docId);
        return $map['zoho_record_id'] ?? null;
    }

    /**
     * Resolve transporter details to ensure Zoho Books internal transporter_id is used.
     *
     * In Zoho Books India e-Way Bill API, the 'transporter_id' field requires Zoho's
     * internal registered transporter ID (e.g. "698964000002928107"), NOT a raw GSTIN.
     * Raw GSTIN causes "400: Invalid value passed for transporter_id".
     *
     * @return array [zoho_transporter_id, transporter_registration_id, transporter_name]
     */
    public function resolveTransporter(?string $transVal, ?string $transName = null, ?string $transGst = null): array
    {
        $transVal  = trim((string) $transVal);
        $transName = trim((string) $transName);
        $transGst  = trim((string) $transGst);

        $zohoId = null;
        $gstin  = ($transGst !== '') ? strtoupper($transGst) : null;

        if ($transVal === '' && $transName === '' && $gstin === null) {
            return [
                'zoho_transporter_id'        => null,
                'transporter_registration_id' => null,
                'transporter_name'           => null,
            ];
        }

        // 1. Check if transVal is already an 18-digit Zoho internal ID (e.g. 69896400000...)
        if (preg_match('/^\d{15,20}$/', $transVal)) {
            $zohoId = $transVal;
            // Lookup local transport to fetch corresponding GSTIN and name if empty
            $matched = $this->transportModel->where('zoho_transporter_id', $zohoId)->first();
            if ($matched) {
                if ($gstin === '' || $gstin === null) {
                    $gstin = $matched['branch_gst_number'] ?? null;
                }
                if ($transName === '' || $transName === null) {
                    $transName = $matched['transport_name'] ?? null;
                }
            }
        }

        // 2. Check if transVal is a 15-character GSTIN/TRANSIN or $gstin is provided
        $testGst = $gstin ?: (preg_match('/^[0-9]{2}[A-Z0-9]{13}$/i', $transVal) ? strtoupper($transVal) : null);
        if (!$zohoId && $testGst) {
            $gstin = $testGst;
            $matched = $this->transportModel->where('branch_gst_number', $gstin)
                ->where('zoho_transporter_id IS NOT NULL')
                ->where('zoho_transporter_id !=', '')
                ->first();
            if ($matched && !empty($matched['zoho_transporter_id'])) {
                $zohoId = $matched['zoho_transporter_id'];
                if ($transName === '' || $transName === null) {
                    $transName = $matched['transport_name'];
                }
            }
        }

        // 3. Check by transport name or local transport ID
        if (!$zohoId && ($transName !== '' || (is_numeric($transVal) && (int) $transVal < 1000000))) {
            $builder = $this->transportModel->groupStart();
            if ($transName !== '') {
                $builder->where('LOWER(transport_name)', strtolower($transName));
            }
            if (is_numeric($transVal) && (int) $transVal < 1000000) {
                $builder->orWhere('id', (int) $transVal);
            }
            $builder->groupEnd();
            $matched = $builder->first();

            if ($matched) {
                if (!empty($matched['zoho_transporter_id'])) {
                    $zohoId = $matched['zoho_transporter_id'];
                }
                if (!$gstin && !empty($matched['branch_gst_number'])) {
                    $gstin = $matched['branch_gst_number'];
                }
                if ($transName === '' && !empty($matched['transport_name'])) {
                    $transName = $matched['transport_name'];
                }
            }
        }

        // 4. Fallback: If zohoId is still not resolved, query Zoho's /ewaybills/transporters API
        if (!$zohoId && ($transName !== '' || $gstin !== '')) {
            try {
                $res = $this->requestEwb(
                    'GET',
                    '/ewaybills/transporters',
                    null,
                    ['page' => 1, 'per_page' => 200],
                    $this->getEwbMeta('RESOLVE_TRANSPORTER_ID', null, null, 'transports'),
                    true
                );

                if (!empty($res['success']) && !empty($res['data']['transporters'])) {
                    foreach ($res['data']['transporters'] as $item) {
                        $remoteName = trim((string) ($item['transporter_name'] ?? ''));
                        $remoteGst  = trim((string) ($item['transporter_registration_id'] ?? ($item['transin'] ?? '')));
                        $remoteId   = trim((string) ($item['transporter_id'] ?? ''));

                        $matchesName = ($transName !== '' && strtolower($remoteName) === strtolower($transName));
                        $matchesGst  = ($gstin !== '' && strtoupper($remoteGst) === strtoupper($gstin));

                        if ($matchesName || $matchesGst) {
                            $zohoId = $remoteId;
                            if (!$gstin && $remoteGst !== '') {
                                $gstin = $remoteGst;
                            }
                            if ($transName === '' && $remoteName !== '') {
                                $transName = $remoteName;
                            }

                            // Update local transport record with this zoho_transporter_id
                            $local = $this->transportModel->groupStart()
                                ->where('LOWER(transport_name)', strtolower($remoteName))
                                ->orWhere('branch_gst_number', $remoteGst)
                                ->groupEnd()
                                ->first();
                            if ($local && empty($local['zoho_transporter_id'])) {
                                $this->transportModel->update($local['id'], [
                                    'zoho_transporter_id' => $zohoId,
                                    'branch_gst_number'   => $gstin ?: $local['branch_gst_number'],
                                ]);
                            }
                            break;
                        }
                    }
                }
            } catch (\Throwable $ex) {
                log_message('error', 'resolveTransporter failed: ' . $ex->getMessage());
            }
        }

        return [
            'zoho_transporter_id'        => $zohoId,
            'transporter_registration_id' => $gstin,
            'transporter_name'           => $transName,
        ];
    }

    /**
     * Generate e-Way Bill (Part A or Full Part A+B)
     *
     * @param array $data Parameters submitted from the form:
     *   - document_type (invoice, credit_note, delivery_challan, purchase_bill)
     *   - document_id (int)
     *   - action ('save', 'save_generate', 'generate_part_a')
     *   - transport_mode (1=Road, 2=Rail, 3=Air, 4=Ship)
     *   - distance_km (int)
     *   - transporter_id (string, GSTIN)
     *   - transporter_name (string)
     *   - transporter_doc_number (string)
     *   - transporter_doc_date (string YYYY-MM-DD)
     *   - vehicle_number (string)
     *   - vehicle_type ('Regular', 'Over Dimensional Cargo')
     *   - from_place, from_state_code, from_pincode
     *   - to_place, to_state_code, to_pincode
     */
    public function generate(array $data, ?int $userId = null, ?int $targetEwayBillId = null): array
    {
        $docType    = $data['document_type'] ?? 'invoice';
        $docId      = (int) ($data['document_id'] ?? 0);
        $rawAction  = strtolower(trim((string) ($data['action'] ?? 'save_generate')));
        $isPartA    = ($rawAction === 'generate_part_a') || empty($data['vehicle_number']);
        $zohoAction = match ($rawAction) {
            'save'               => 'save',
            'manually_generated' => 'manually_generated',
            default              => 'save_generate',
        };

        $zohoId = $this->getZohoRecordId($docType, $docId);

        // Build Payload according to Zoho Inventory v1 / Books e-Way Bill schema
        $distance        = (int) ($data['distance_km'] ?? 0);
        $subSupplyType   = $this->mapSubSupplyType($data['sub_supply_type'] ?? 'supply');
        $transactionType = $this->mapTransactionType($data['transaction_type'] ?? 'regular');

        $payload = [
            'entity_type'         => $this->mapDocTypeToZoho($docType),
            'action'              => $zohoAction,
            'sub_supply_type'     => $subSupplyType,
            'transaction_type'    => $transactionType,
            'transportation_mode' => $this->mapTransportationMode($data['transport_mode'] ?? 'road'),
        ];

        if ($subSupplyType === 'others') {
            $payload['sub_supply_type_desc'] = !empty($data['sub_supply_type_desc']) ? trim($data['sub_supply_type_desc']) : 'Goods Transport';
        }

        // Only explicitly send distance if > 0 so Zoho auto-computes by pincode when 0
        if ($distance > 0) {
            $payload['distance'] = $distance;
        }

        if ($zohoId) {
            $payload['entity_id'] = $zohoId;
        }

        if ($zohoAction === 'manually_generated' && !empty($data['ewaybill_number'])) {
            $payload['ewaybill_number'] = trim((string) $data['ewaybill_number']);
            if (!empty($data['ewaybill_date'])) {
                $payload['ewaybill_date'] = trim((string) $data['ewaybill_date']);
            }
        }

        // Part B conveyance fields (explicitly omitted for Part-A slips)
        if (!$isPartA && !empty($data['vehicle_number'])) {
            $payload['vehicle_number'] = strtoupper(trim(str_replace(' ', '', $data['vehicle_number'])));
            $payload['vehicle_type']   = !empty($data['vehicle_type']) ? strtolower(str_replace(' ', '_', $data['vehicle_type'])) : 'regular';
        }

        // Transporter resolution: always pass Zoho internal transporter_id to Zoho API
        $rawTransId   = $data['transporter_id'] ?? null;
        $rawTransName = $data['transporter_name'] ?? null;
        $rawTransGst  = $data['transporter_gstin'] ?? ($data['transporter_registration_id'] ?? null);
        $resolvedTrans = $this->resolveTransporter($rawTransId, $rawTransName, $rawTransGst);

        if (!empty($resolvedTrans['zoho_transporter_id'])) {
            $payload['transporter_id'] = $resolvedTrans['zoho_transporter_id'];
        }
        if (!empty($resolvedTrans['transporter_registration_id'])) {
            $payload['transporter_registration_id'] = $resolvedTrans['transporter_registration_id'];
        }
        if (!empty($resolvedTrans['transporter_name'])) {
            $payload['transporter_name'] = $resolvedTrans['transporter_name'];
        }

        if (!empty($data['transporter_doc_number'])) {
            $payload['transporter_document_number'] = trim($data['transporter_doc_number']);
        }
        if (!empty($data['transporter_doc_date'])) {
            $payload['transporter_document_date'] = trim($data['transporter_doc_date']);
        }

        // Place & shipping state details
        if (!empty($data['from_place'])) {
            $payload['from_place'] = trim($data['from_place']);
        }
        if (!empty($data['from_state_code'])) {
            $payload['from_state'] = trim($data['from_state_code']);
        }
        if (!empty($data['to_place'])) {
            $payload['to_place'] = trim($data['to_place']);
        }
        if (!empty($data['to_state_code'])) {
            $toState = trim((string) $data['to_state_code']);
            $payload['to_state']           = $toState;
            $payload['ship_to_state_code'] = $toState;
        }

        // If updating an existing draft/failed Zoho e-Way Bill record, call PUT /ewaybills/{id}?action=...
        $existingTarget      = $targetEwayBillId ? $this->ewayModel->find($targetEwayBillId) : null;
        $existingDraftZohoId = ($existingTarget
            && !empty($existingTarget['zoho_ewaybill_id'])
            && in_array($existingTarget['status'], ['draft', 'failed'], true))
            ? $existingTarget['zoho_ewaybill_id']
            : null;

        if ($existingDraftZohoId) {
            $meta                   = $this->getEwbMeta('UPDATE_AND_GENERATE_EWAYBILL', $docId);
            $meta['zoho_record_id'] = $existingDraftZohoId;
            $response               = $this->requestEwb('PUT', "/ewaybills/{$existingDraftZohoId}", $payload, ['action' => $zohoAction], $meta, false);
        } else {
            $meta     = $this->getEwbMeta('GENERATE_EWAYBILL', $docId);
            $response = $this->requestEwb('POST', '/ewaybills', $payload, [], $meta, false);
        }

        // Zoho code 9 = API access is not available for e-Way Bill generation on that endpoint/plan.
        // Never fake success here: report clearly so the user knows nothing was generated with NIC.
        if (empty($response['success']) && (int) ($response['zoho_code'] ?? 0) === 9) {
            $usedService         = strtoupper((string) ($meta['api_service'] ?? 'inventory'));
            $response['message'] = "Zoho ({$usedService}) rejected e-Way Bill generation via API (\"API access is not available\"). "
                . 'Nothing was generated with the NIC portal. Generate it in Zoho, then use Sync to pull the new number.';
        }

        $recordData = [
            'document_type'          => $docType,
            'document_id'            => $docId,
            'document_number'        => $data['document_number'] ?? '',
            'document_date'          => $data['document_date'] ?? date('Y-m-d'),
            'supply_type'            => $data['supply_type'] ?? 'Outward',
            'sub_supply_type'        => $data['sub_supply_type'] ?? 'Supply',
            'transport_mode'         => (int) ($data['transport_mode'] ?? 1),
            'distance_km'            => (int) ($data['distance_km'] ?? 0),
            'transporter_id'              => $resolvedTrans['zoho_transporter_id'] ?? ($resolvedTrans['transporter_registration_id'] ?? $rawTransId),
            'transporter_registration_id' => $resolvedTrans['transporter_registration_id'] ?? $rawTransGst,
            'transporter_name'            => $resolvedTrans['transporter_name'] ?? $rawTransName,
            'transporter_doc_number' => $data['transporter_doc_number'] ?? null,
            'transporter_doc_date'   => !empty($data['transporter_doc_date']) ? $data['transporter_doc_date'] : null,
            'vehicle_number'         => $data['vehicle_number'] ?? null,
            'vehicle_type'           => $data['vehicle_type'] ?? 'Regular',
            'from_address_1'         => $data['from_address_1'] ?? null,
            'from_address_2'         => $data['from_address_2'] ?? null,
            'from_place'             => $data['from_place'] ?? null,
            'from_state_code'        => $data['from_state_code'] ?? null,
            'from_pincode'           => $data['from_pincode'] ?? null,
            'bill_from_place'        => $data['bill_from_place'] ?? null,
            'bill_from_state_code'   => $data['bill_from_state_code'] ?? null,
            'bill_from_pincode'      => $data['bill_from_pincode'] ?? null,
            'bill_to_place'          => $data['bill_to_place'] ?? null,
            'bill_to_state_code'     => $data['bill_to_state_code'] ?? null,
            'bill_to_pincode'        => $data['bill_to_pincode'] ?? null,
            'to_address_1'           => $data['to_address_1'] ?? null,
            'to_address_2'           => $data['to_address_2'] ?? null,
            'to_place'               => $data['to_place'] ?? null,
            'to_state_code'          => $data['to_state_code'] ?? null,
            'to_pincode'             => $data['to_pincode'] ?? null,
            'total_amount'           => (float) ($data['total_amount'] ?? 0),
            'cgst_amount'            => (float) ($data['cgst_amount'] ?? 0),
            'sgst_amount'            => (float) ($data['sgst_amount'] ?? 0),
            'igst_amount'            => (float) ($data['igst_amount'] ?? 0),
            'created_by'             => $userId,
            'updated_by'             => $userId,
            'response_payload'       => json_encode($response),
        ];

        if (!empty($response['success'])) {
            $ewbData = $response['data']['ewaybill'] ?? ($response['data'] ?? []);

            $ewbNumber   = trim((string) ($ewbData['ewaybill_number'] ?? ($ewbData['eway_bill_number'] ?? '')));
            $zohoEwbId   = $ewbData['ewaybill_id'] ?? null;
            $zohoStatus  = strtolower((string) ($ewbData['ewaybill_status'] ?? ''));
            $validUntil  = !empty($ewbData['ewaybill_expiry_date']) ? $ewbData['ewaybill_expiry_date'] : ($ewbData['valid_until'] ?? null);
            $ewbDate     = !empty($ewbData['ewaybill_date']) ? $ewbData['ewaybill_date'] : null;

            // A number is only trusted if Zoho reports it as freshly generated with NIC and
            // it is not a number we already hold as cancelled for this document.
            $allowedStatuses = ($zohoAction === 'manually_generated') ? ['generated', 'manually_generated'] : ['generated'];
            $isGenerated     = $ewbNumber !== '' && in_array($zohoStatus, $allowedStatuses, true) && !$this->isCancelledNumber($docType, $docId, $ewbNumber);

            if ($isGenerated) {
                $status = $isPartA ? 'part_a_generated' : 'generated';
            } else {
                // Saved in Zoho only (draft) - not generated with NIC.
                $status     = 'draft';
                $ewbNumber  = null;
                $ewbDate    = null;
                $validUntil = null;
            }

            $recordData['zoho_ewaybill_id'] = $zohoEwbId;
            $recordData['ewaybill_number']  = $ewbNumber;
            $recordData['ewaybill_date']    = $ewbDate;
            $recordData['valid_until']      = $validUntil;
            $recordData['status']           = $status;
            $recordData['last_sync_at']     = date('Y-m-d H:i:s');
            $recordData['error_message']    = $isGenerated ? null : 'Saved in Zoho as draft; e-Way Bill not generated with NIC yet.';

            if ($targetEwayBillId && $this->ewayModel->find($targetEwayBillId)) {
                $this->ewayModel->update($targetEwayBillId, $recordData);
                $finalId = $targetEwayBillId;
            } else {
                $this->ewayModel->insert($recordData);
                $finalId = $this->ewayModel->getInsertID();
            }

            if (!$isGenerated) {
                $intendedDraft = ($zohoAction === 'save');
                return [
                    'success' => $intendedDraft,
                    'id'      => $finalId,
                    'status'  => $status,
                    'message' => $intendedDraft
                        ? 'e-Way Bill details saved in Zoho as draft (not generated with NIC yet).'
                        : 'Zoho saved the e-Way Bill details but did not return a generated e-Way Bill number (Zoho status: '
                            . ($zohoStatus ?: 'unknown') . '). Nothing was generated with NIC.',
                    'data'    => $ewbData,
                ];
            }

            // Sync back to local invoice if applicable
            if ($docType === 'invoice') {
                (new InvoiceModel())->update($docId, [
                    'ewaybill_number' => $ewbNumber,
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);
            }

            return [
                'success'         => true,
                'id'              => $finalId,
                'ewaybill_number' => $ewbNumber,
                'valid_until'     => $validUntil,
                'status'          => $status,
                'message'         => "e-Way Bill successfully {$status}: #{$ewbNumber}",
                'data'            => $ewbData,
            ];
        }

        // If failed
        $errorMsg = $response['message'] ?? 'Zoho Books API error while generating e-Way Bill';
        $recordData['status']        = 'failed';
        $recordData['error_message'] = $errorMsg;

        if ($targetEwayBillId && $this->ewayModel->find($targetEwayBillId)) {
            $this->ewayModel->update($targetEwayBillId, $recordData);
        } else {
            $this->ewayModel->insert($recordData);
        }

        return [
            'success' => false,
            'message' => $errorMsg,
            'raw'     => $response,
        ];
    }

    /**
     * Update Part-B / Vehicle Details
     * Endpoint: POST /ewaybills/{ewaybill_id}/vehicles
     */
    public function updatePartB(int $ewayBillId, array $data, ?int $userId = null): array
    {
        $ewb = $this->ewayModel->find($ewayBillId);
        if (!$ewb) {
            return ['success' => false, 'message' => 'e-Way Bill record not found.'];
        }

        $zohoEwbId = $ewb['zoho_ewaybill_id'] ?? null;

        $rawPartBTransId  = $data['transporter_id'] ?? ($ewb['transporter_id'] ?? null);
        $rawPartBTransGst = $data['transporter_gstin'] ?? null;
        $resolvedPartBTrans = $this->resolveTransporter($rawPartBTransId, $ewb['transporter_name'] ?? null, $rawPartBTransGst);

        $payload = [
            'vehicle_number'            => strtoupper(trim(str_replace(' ', '', $data['vehicle_number'] ?? ''))),
            'vehicle_type'              => !empty($data['vehicle_type']) ? strtolower(str_replace(' ', '_', $data['vehicle_type'])) : 'regular',
            'from_place'                => trim($data['from_place'] ?? ($ewb['from_place'] ?? '')),
            'from_state'                => trim($data['from_state_code'] ?? ($ewb['from_state_code'] ?? '')),
            'reason'                    => $this->mapVehicleUpdateReason($data['reason'] ?? 'others'),
            'remarks'                   => trim((string) ($data['remarks'] ?? ($data['reason'] ?? 'Vehicle update'))),
            'transportation_mode'       => $this->mapTransportationMode($data['transport_mode'] ?? ($ewb['transport_mode'] ?? 'road')),
            'transporter_document_number' => $data['transporter_doc_number'] ?? null,
            'transporter_document_date'   => $data['transporter_doc_date'] ?? null,
            'distance'                  => (int) ($data['distance_km'] ?? $ewb['distance_km']),
        ];

        if (!empty($resolvedPartBTrans['zoho_transporter_id'])) {
            $payload['transporter_id'] = $resolvedPartBTrans['zoho_transporter_id'];
        }
        if (!empty($resolvedPartBTrans['transporter_registration_id'])) {
            $payload['transporter_registration_id'] = $resolvedPartBTrans['transporter_registration_id'];
        }

        $payload = array_filter($payload, static fn($v) => $v !== null && $v !== '');

        if (!$zohoEwbId || empty($ewb['ewaybill_number']) || !in_array($ewb['status'], ['generated', 'part_a_generated'], true)) {
            return ['success' => false, 'message' => 'Vehicle details can only be updated on an e-Way Bill that was actually generated in Zoho.'];
        }

        $res = $this->requestEwb(
            'POST',
            "/ewaybills/{$zohoEwbId}/vehicles",
            $payload,
            [],
            $this->getEwbMeta('UPDATE_PART_B_VEHICLE', $ewayBillId),
            false
        );

        if (empty($res['success'])) {
            $msg = $res['message'] ?? 'Failed to update Part B in Zoho';
            $this->ewayModel->update($ewayBillId, ['error_message' => $msg, 'updated_at' => date('Y-m-d H:i:s')]);
            return ['success' => false, 'message' => $msg, 'raw' => $res];
        }

        // Update local record only after Zoho confirms
        $update = [
            'vehicle_number'         => $payload['vehicle_number'] ?? $ewb['vehicle_number'],
            'vehicle_type'           => $data['vehicle_type'] ?? 'Regular',
            'transporter_doc_number' => $data['transporter_doc_number'] ?? null,
            'transporter_doc_date'   => !empty($data['transporter_doc_date']) ? $data['transporter_doc_date'] : null,
            'transporter_id'              => $resolvedPartBTrans['zoho_transporter_id'] ?? ($resolvedPartBTrans['transporter_registration_id'] ?? $rawPartBTransId),
            'transporter_registration_id' => $resolvedPartBTrans['transporter_registration_id'] ?? $rawPartBTransGst,
            'distance_km'            => $payload['distance'] ?? $ewb['distance_km'],
            'status'                 => 'generated',
            'error_message'          => null,
            'updated_by'             => $userId,
            'updated_at'             => date('Y-m-d H:i:s'),
        ];
        $this->ewayModel->update($ewayBillId, $update);

        return [
            'success' => true,
            'message' => 'Part-B Conveyance & Vehicle details updated successfully.',
            'raw'     => $res,
        ];
    }

    /**
     * Cancel an e-Way Bill
     * Endpoint: POST /ewaybills/{ewaybill_id}/cancel
     */
    public function cancel(int $ewayBillId, string $reasonCode, string $remarks, ?int $userId = null): array
    {
        $ewb = $this->ewayModel->find($ewayBillId);
        if (!$ewb) {
            return ['success' => false, 'message' => 'e-Way Bill record not found.'];
        }

        if ($ewb['status'] === 'cancelled') {
            return ['success' => false, 'message' => 'This e-Way Bill is already cancelled.'];
        }

        $zohoEwbId = $ewb['zoho_ewaybill_id'] ?? null;
        if (!$zohoEwbId || empty($ewb['ewaybill_number']) || !in_array($ewb['status'], ['generated', 'part_a_generated'], true)) {
            return ['success' => false, 'message' => 'Only e-Way Bills that were actually generated in Zoho can be cancelled.'];
        }

        $payload = [
            'reason'  => $this->mapCancelReason($reasonCode),
            'remarks' => $remarks,
        ];

        $res = $this->requestEwb(
            'POST',
            "/ewaybills/{$zohoEwbId}/cancel",
            $payload,
            [],
            $this->getEwbMeta('CANCEL_EWAYBILL', $ewayBillId),
            false
        );

        if (empty($res['success'])) {
            $this->ewayModel->update($ewayBillId, [
                'error_message' => $res['message'] ?? 'Zoho cancellation failed.',
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
            return [
                'success' => false,
                'message' => $res['message'] ?? 'Failed to cancel e-Way Bill in Zoho.',
                'raw'     => $res,
            ];
        }

        $this->ewayModel->update($ewayBillId, [
            'status'             => 'cancelled',
            'cancel_reason_code' => $reasonCode,
            'cancel_remarks'     => $remarks,
            'cancelled_at'       => date('Y-m-d H:i:s'),
            'updated_by'         => $userId,
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);

        // Clear or flag invoice ewaybill_number
        if ($ewb['document_type'] === 'invoice') {
            (new InvoiceModel())->update($ewb['document_id'], [
                'ewaybill_number' => null,
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);
        }

        return [
            'success' => true,
            'message' => 'e-Way Bill #' . ($ewb['ewaybill_number'] ?? $ewb['id']) . ' has been cancelled.',
        ];
    }

    /**
     * Regenerate e-Way Bill
     * If an e-Way bill was cancelled or need a clean re-issuance
     */
    public function regenerate(int $cancelledEwayBillId, array $freshData, ?int $userId = null): array
    {
        $oldEwb = $this->ewayModel->find($cancelledEwayBillId);
        if (!$oldEwb) {
            return ['success' => false, 'message' => 'Original e-Way Bill record not found.'];
        }

        // Merge defaults from original record
        $merged = array_merge([
            'document_type'   => $oldEwb['document_type'],
            'document_id'     => $oldEwb['document_id'],
            'document_number' => $oldEwb['document_number'],
            'document_date'   => $oldEwb['document_date'],
            'transport_mode'  => $oldEwb['transport_mode'],
            'distance_km'                 => $oldEwb['distance_km'],
            'transporter_id'              => $oldEwb['transporter_id'],
            'transporter_registration_id' => $oldEwb['transporter_registration_id'] ?? null,
            'transporter_name'            => $oldEwb['transporter_name'],
            'total_amount'    => $oldEwb['total_amount'],
            'cgst_amount'     => $oldEwb['cgst_amount'],
            'sgst_amount'     => $oldEwb['sgst_amount'],
            'igst_amount'     => $oldEwb['igst_amount'],
            'from_address_1'  => $oldEwb['from_address_1'],
            'from_address_2'  => $oldEwb['from_address_2'],
            'from_place'      => $oldEwb['from_place'],
            'from_state_code' => $oldEwb['from_state_code'],
            'from_pincode'    => $oldEwb['from_pincode'],
            'bill_from_place' => $oldEwb['bill_from_place'],
            'bill_from_state_code' => $oldEwb['bill_from_state_code'],
            'bill_from_pincode' => $oldEwb['bill_from_pincode'],
            'bill_to_place'   => $oldEwb['bill_to_place'],
            'bill_to_state_code' => $oldEwb['bill_to_state_code'],
            'bill_to_pincode' => $oldEwb['bill_to_pincode'],
            'to_address_1'    => $oldEwb['to_address_1'],
            'to_address_2'    => $oldEwb['to_address_2'],
            'to_place'        => $oldEwb['to_place'],
            'to_state_code'   => $oldEwb['to_state_code'],
            'to_pincode'      => $oldEwb['to_pincode'],
            'action'          => 'save_generate',
        ], $freshData);

        // If the original record was 'failed', update that exact record instead of creating a duplicate row
        $targetId = in_array($oldEwb['status'], ['failed', 'draft'], true) ? $cancelledEwayBillId : null; // cancelled rows are never overwritten

        return $this->generate($merged, $userId, $targetId);
    }

    /**
     * Map a Zoho Books ewaybill_status to our local status.
     */
    protected function mapZohoStatus(string $zohoStatus): string
    {
        return match (strtolower(trim($zohoStatus))) {
            'generated', 'manually_generated' => 'generated',
            'cancelled'                       => 'cancelled',
            'expired'                         => 'expired',
            default                           => 'draft', // yet_to_generate / unknown
        };
    }

    /**
     * Sync / Refresh e-Way Bill status from Zoho Books.
     *
     * Rules (to avoid ever showing a stale number as new):
     *  - A record is only marked generated when Zoho reports it generated AND the number is not one
     *    already cancelled for the same document.
     *  - The invoice-level "eway_bill_details" is ignored (Zoho keeps the last number there even after cancel).
     */
    public function syncStatus(int $ewayBillId): array
    {
        $ewb = $this->ewayModel->find($ewayBillId);
        if (!$ewb) {
            return ['success' => false, 'message' => 'e-Way Bill record not found.'];
        }

        $docType = $ewb['document_type'];
        $docId   = (int) $ewb['document_id'];
        $zohoEwb = null;

        // 1. Direct lookup by linked Zoho e-Way Bill id
        if (!empty($ewb['zoho_ewaybill_id'])) {
            // Pull latest live status from the GST portal into Zoho first
            $this->requestEwb(
                'POST',
                "/ewaybills/{$ewb['zoho_ewaybill_id']}/sync",
                null,
                [],
                $this->getEwbMeta('SYNC_EWAYBILL_GST', $ewayBillId),
                true
            );

            $res = $this->requestEwb(
                'GET',
                "/ewaybills/{$ewb['zoho_ewaybill_id']}",
                null,
                [],
                $this->getEwbMeta('GET_EWAYBILL', $ewayBillId),
                true
            );
            if (!empty($res['success'])) {
                $zohoEwb = $res['data']['ewaybill'] ?? null;
            }
        }

        // 2. Not linked yet (e.g. generated in Zoho portal): find an unlinked, live e-Way Bill via Inventory v1 /ewaybills list or Invoice
        if (!$zohoEwb) {
            $zohoDocId = $this->getZohoRecordId($docType, $docId);
            if ($zohoDocId) {
                $ewbCandidates  = [];
                $zohoEntityType = $this->mapDocTypeToZoho($docType);

                if (in_array($zohoEntityType, ['invoice', 'creditnote', 'deliverychallan'], true)) {
                    $listRes = $this->requestEwb(
                        'GET',
                        '/ewaybills',
                        null,
                        [
                            'entity_type' => $zohoEntityType,
                            'entity_ids'  => $zohoDocId,
                        ],
                        $this->getEwbMeta('LIST_EWAYBILLS_FOR_DOC', $ewayBillId),
                        true
                    );
                    if (!empty($listRes['success']) && !empty($listRes['data']['ewaybills']) && is_array($listRes['data']['ewaybills'])) {
                        $ewbCandidates = array_merge($ewbCandidates, $listRes['data']['ewaybills']);
                    }
                }

                if ($docType === 'invoice') {
                    $invRes = $this->client->get("/invoices/{$zohoDocId}");
                    $invEwbList = $invRes['data']['invoice']['ewaybills'] ?? [];
                    if (!empty($invEwbList) && is_array($invEwbList)) {
                        $ewbCandidates = array_merge($ewbCandidates, $invEwbList);
                    }
                }

                $cancelledNums = [];
                foreach ($ewbCandidates as $item) {
                    if (strtolower((string) ($item['ewaybill_status'] ?? '')) === 'cancelled' && !empty($item['ewaybill_number'])) {
                        $cancelledNums[] = trim((string) $item['ewaybill_number']);
                    }
                }

                foreach ($ewbCandidates as $item) {
                    $num = trim((string) ($item['ewaybill_number'] ?? ''));
                    if ($num === '' || $this->mapZohoStatus((string) ($item['ewaybill_status'] ?? '')) !== 'generated') {
                        continue;
                    }
                    if (in_array($num, $cancelledNums, true) || $this->isCancelledNumber($docType, $docId, $num)) {
                        continue; // stale copy of a cancelled number
                    }
                    $linkedElsewhere = !empty($item['ewaybill_id'])
                        && $this->ewayModel->where('zoho_ewaybill_id', $item['ewaybill_id'])
                            ->where('id !=', $ewayBillId)->countAllResults() > 0;
                    if ($linkedElsewhere) {
                        continue;
                    }

                    // Fetch full details if list item doesn't include full expiry timestamp
                    if (!empty($item['ewaybill_id']) && empty($item['ewaybill_expiry_date'])) {
                        $detailRes = $this->requestEwb(
                            'GET',
                            "/ewaybills/{$item['ewaybill_id']}",
                            null,
                            [],
                            $this->getEwbMeta('GET_EWAYBILL', $ewayBillId),
                            true
                        );
                        if (!empty($detailRes['success']) && !empty($detailRes['data']['ewaybill'])) {
                            $zohoEwb = $detailRes['data']['ewaybill'];
                            break;
                        }
                    }

                    $zohoEwb = $item;
                    break;
                }
            }
        }

        if (!$zohoEwb) {
            $this->ewayModel->update($ewayBillId, ['last_sync_at' => date('Y-m-d H:i:s')]);
            return ['success' => false, 'message' => 'No newly generated e-Way Bill found in Zoho for this document.'];
        }

        $num    = trim((string) ($zohoEwb['ewaybill_number'] ?? ''));
        $status = $this->mapZohoStatus($zohoEwb['ewaybill_status'] ?? '');

        if ($status === 'generated' && ($num === '' || $this->isCancelledNumber($docType, $docId, $num))) {
            $status = 'draft';
        }

        $update = [
            'zoho_ewaybill_id' => $zohoEwb['ewaybill_id'] ?? $ewb['zoho_ewaybill_id'],
            'status'           => $status,
            'last_sync_at'     => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ];
        if ($status === 'draft') {
            $update['ewaybill_number'] = null;
            $update['valid_until']     = null;
            $update['error_message']   = 'Not generated with NIC yet (Zoho status: ' . ($zohoEwb['ewaybill_status'] ?? 'unknown') . ').';
        }
        if (in_array($status, ['generated', 'cancelled', 'expired'], true) && $num !== '') {
            $update['ewaybill_number'] = $num;
            $update['ewaybill_date']   = !empty($zohoEwb['ewaybill_date']) ? $zohoEwb['ewaybill_date'] : $ewb['ewaybill_date'];
            $update['valid_until']     = !empty($zohoEwb['ewaybill_expiry_date'])
                ? $zohoEwb['ewaybill_expiry_date']
                : (!empty($zohoEwb['expires_in']) ? $zohoEwb['expires_in'] : $ewb['valid_until']);
            $update['error_message']   = null;
        }
        if ($status === 'cancelled') {
            $update['cancel_reason_code'] = $zohoEwb['cancel_reason'] ?? $ewb['cancel_reason_code'];
            $update['cancel_remarks']     = $zohoEwb['cancel_remarks'] ?? $ewb['cancel_remarks'];
            $update['cancelled_at']       = $ewb['cancelled_at'] ?? date('Y-m-d H:i:s');
        }
        $this->ewayModel->update($ewayBillId, $update);

        if ($docType === 'invoice') {
            $invoiceModel = new InvoiceModel();
            if ($status === 'generated') {
                $invoiceModel->update($docId, ['ewaybill_number' => $num, 'updated_at' => date('Y-m-d H:i:s')]);
            } elseif ($status === 'cancelled') {
                $inv = $invoiceModel->find($docId);
                if ($inv && ($inv['ewaybill_number'] ?? null) === $num) {
                    $invoiceModel->update($docId, ['ewaybill_number' => null, 'updated_at' => date('Y-m-d H:i:s')]);
                }
            }
        }

        return [
            'success' => true,
            'status'  => $status,
            'data'    => $zohoEwb,
        ];
    }

    /**
     * Fetch e-Way Bill module settings & registered GST portal credentials from Zoho.
     */
    public function getEwayBillSettings(): array
    {
        $config = $this->settingModel->getConfig(false);
        if (empty($config['has_refresh_token']) || empty($config['organization_id'])) {
            return [
                'success'             => false,
                'is_ewaybill_enabled' => false,
                'credentials'         => [],
                'primary_credential'  => null,
                'tax_settings_id'     => $config['ewaybill_tax_settings_id'] ?? null,
                'ewaybill_username'   => $config['ewaybill_gst_username'] ?? null,
                'gstin'               => null,
                'is_active'           => false,
                'message'             => 'Zoho OAuth connection or Organization ID is not configured.',
            ];
        }

        $res = $this->requestEwb(
            'GET',
            '/settings/ewaybills',
            null,
            [],
            $this->getEwbMeta('GET_EWAYBILL_SETTINGS'),
            true
        );

        if (empty($res['success'])) {
            return [
                'success'             => false,
                'service_used'        => $config['ewaybill_api_service'] ?? 'inventory',
                'needs_reauth'        => !empty($res['auth_scope_required']),
                'is_ewaybill_enabled' => false,
                'credentials'         => [],
                'primary_credential'  => null,
                'tax_settings_id'     => $config['ewaybill_tax_settings_id'] ?? null,
                'ewaybill_username'   => $config['ewaybill_gst_username'] ?? null,
                'gstin'               => null,
                'is_active'           => false,
                'message'             => $res['message'] ?? 'Unable to fetch e-Way Bill settings from Zoho.',
                'raw'                 => $res,
            ];
        }

        $settings    = $res['data']['ewaybill_settings'] ?? [];
        $creds       = $settings['ewaybill_credentials'] ?? [];
        $primaryCred = is_array($creds) && !empty($creds) ? $creds[0] : null;
        $taxSetId    = $primaryCred['tax_settings_id'] ?? ($config['ewaybill_tax_settings_id'] ?? null);
        $username    = $primaryCred['ewaybill_username'] ?? ($config['ewaybill_gst_username'] ?? null);

        // Cache latest username / tax_settings_id locally if changed
        $cacheUpdate = [];
        if ($username && $username !== ($config['ewaybill_gst_username'] ?? null)) {
            $cacheUpdate['ewaybill_gst_username'] = $username;
        }
        if ($taxSetId && $taxSetId !== ($config['ewaybill_tax_settings_id'] ?? null)) {
            $cacheUpdate['ewaybill_tax_settings_id'] = $taxSetId;
        }
        if (!empty($cacheUpdate)) {
            $this->settingModel->saveConfig($cacheUpdate);
        }

        return [
            'success'             => true,
            'service_used'        => $res['used_fallback_service'] ?? ($config['ewaybill_api_service'] ?? 'inventory'),
            'used_fallback'       => !empty($res['used_fallback_service']),
            'is_ewaybill_enabled' => !empty($settings['is_ewaybill_enabled']),
            'credentials'         => $creds,
            'primary_credential'  => $primaryCred,
            'tax_settings_id'     => $taxSetId,
            'ewaybill_username'   => $username,
            'gstin'               => $primaryCred['gstin'] ?? null,
            'is_active'           => !empty($primaryCred['is_active']),
            'message'             => 'OK',
            'raw'                 => $res,
        ];
    }

    /**
     * Update GST Portal (GSP) credentials for e-Way Bill generation in Zoho.
     * Endpoint: PUT /settings/{tax_settings_id}/ewaybills or PUT /settings/ewaybills
     */
    public function updateEwayBillCredentials(string $username, string $password, ?string $taxSettingsId = null): array
    {
        $username = trim($username);
        if ($username === '' || $password === '') {
            return ['success' => false, 'message' => 'Both GST Portal Username and Password are required.'];
        }

        $taxSettingsId = trim((string) $taxSettingsId);
        $endpoint      = $taxSettingsId !== '' ? "/settings/{$taxSettingsId}/ewaybills" : '/settings/ewaybills';

        $payload = [
            'ewaybill_username' => $username,
            'ewaybill_password' => $password,
        ];

        $res = $this->requestEwb(
            'PUT',
            $endpoint,
            $payload,
            [],
            $this->getEwbMeta('UPDATE_EWAYBILL_CREDENTIALS'),
            true
        );

        if (empty($res['success'])) {
            return [
                'success' => false,
                'message' => $res['message'] ?? 'Failed to update e-Way Bill GST portal credentials in Zoho.',
                'raw'     => $res,
            ];
        }

        $save = ['ewaybill_gst_username' => $username];
        if ($taxSettingsId !== '') {
            $save['ewaybill_tax_settings_id'] = $taxSettingsId;
        }
        $this->settingModel->saveConfig($save);

        return [
            'success' => true,
            'message' => $res['data']['message'] ?? 'GST Portal e-Way Bill credentials updated successfully in Zoho.',
            'data'    => $res['data'] ?? [],
        ];
    }

    /**
     * Test both Zoho Inventory v1 and Zoho Books v3 e-Way Bill API endpoints.
     */
    public function testEwayBillApi(): array
    {
        $invRes = $this->client->request(
            'GET',
            '/settings/ewaybills',
            null,
            [],
            $this->getEwbMeta('TEST_EWAYBILL_INVENTORY_API', null, 'inventory')
        );

        $booksRes = $this->client->request(
            'GET',
            '/settings/ewaybills',
            null,
            [],
            $this->getEwbMeta('TEST_EWAYBILL_BOOKS_API', null, 'books')
        );

        $invOk        = !empty($invRes['success']);
        $booksOk      = !empty($booksRes['success']);
        $needsReauth  = !$invOk && ((int) ($invRes['http_code'] ?? 0) === 401 && (int) ($invRes['zoho_code'] ?? 0) === 57);
        $ewbSettings  = $invRes['data']['ewaybill_settings'] ?? ($booksRes['data']['ewaybill_settings'] ?? null);
        $credCount    = count($ewbSettings['ewaybill_credentials'] ?? []);
        $credActive   = !empty($ewbSettings['ewaybill_credentials'][0]['is_active']);

        if ($invOk) {
            $msg = 'Zoho Inventory v1 e-Way Bill API is connected & authorized (HTTP 200). '
                . ($credCount > 0 ? ($credActive ? 'GSP credentials are active.' : 'GSP credentials found but inactive.') : 'No GSP credentials configured in Zoho.');
        } elseif ($needsReauth) {
            $msg = 'Zoho Inventory v1 returned HTTP 401 (Not Authorized). Your current OAuth token only has ZohoBooks scope. '
                . 'Click "Connect / Re-Authorize Zoho (Books + Inventory OAuth 2.0)" to grant ZohoInventory.fullaccess.all.';
        } else {
            $msg = 'Zoho Inventory v1 e-Way Bill API test failed: ' . ($invRes['message'] ?? 'Unknown error');
        }

        return [
            'success'                => $invOk,
            'inventory_ok'           => $invOk,
            'inventory_http_code'    => $invRes['http_code'] ?? 0,
            'inventory_zoho_code'    => $invRes['zoho_code'] ?? null,
            'inventory_message'      => $invRes['message'] ?? '',
            'inventory_needs_reauth' => $needsReauth,
            'books_ok'               => $booksOk,
            'books_http_code'        => $booksRes['http_code'] ?? 0,
            'books_message'          => $booksRes['message'] ?? '',
            'ewaybill_settings'      => $ewbSettings,
            'message'                => $msg,
        ];
    }
}
