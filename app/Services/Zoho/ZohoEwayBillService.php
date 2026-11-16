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
     * Map internal document type to Zoho Books entity_type
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
                $res = $this->client->get('/ewaybills/transporters', [
                    'page'     => 1,
                    'per_page' => 200,
                ], [
                    'module'    => 'transports',
                    'operation' => 'RESOLVE_TRANSPORTER_ID',
                ]);

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
        $docType = $data['document_type'] ?? 'invoice';
        $docId   = (int) ($data['document_id'] ?? 0);
        $action  = $data['action'] ?? 'save_generate';

        $zohoId = $this->getZohoRecordId($docType, $docId);

        // Build Payload according to Zoho E-Way Bill schema
        $distance = (int) ($data['distance_km'] ?? 0);

        $subSupplyType = $this->mapSubSupplyType($data['sub_supply_type'] ?? 'supply');

        $payload = [
            'entity_type'         => $this->mapDocTypeToZoho($docType),
            'action'              => $action,
            'sub_supply_type'     => $subSupplyType,
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

        // Part B conveyance fields
        if (!empty($data['vehicle_number'])) {
            $payload['vehicle_number'] = strtoupper(trim(str_replace(' ', '', $data['vehicle_number'])));
            $payload['vehicle_type']   = !empty($data['vehicle_type']) ? strtolower(str_replace(' ', '_', $data['vehicle_type'])) : 'regular';
        }

        // Transporter resolution: always pass Zoho internal transporter_id to Zoho Books API
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

        // Place details
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
            $payload['to_state'] = trim($data['to_state_code']);
        }

        // Call Zoho API
        $meta = [
            'module'          => 'eway_bills',
            'local_record_id' => $docId,
            'operation'       => 'GENERATE_EWAYBILL',
        ];

        $response = $this->client->post('/ewaybills', $payload, [], $meta);

        $recordData = [
            'document_type'          => $docType,
            'document_id'            => $docId,
            'document_number'        => $data['document_number'] ?? '',
            'document_date'          => $data['document_date'] ?? date('Y-m-d'),
            'supply_type'            => $data['supply_type'] ?? 'Outward',
            'sub_supply_type'        => $data['sub_supply_type'] ?? 'Supply',
            'transport_mode'         => (int) ($data['transport_mode'] ?? 1),
            'distance_km'            => (int) ($data['distance_km'] ?? 0),
            'transporter_id'         => $resolvedTrans['zoho_transporter_id'] ?? ($resolvedTrans['transporter_registration_id'] ?? $rawTransId),
            'transporter_name'       => $resolvedTrans['transporter_name'] ?? $rawTransName,
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
            
            $status = 'generated';
            if ($action === 'generate_part_a' || empty($data['vehicle_number'])) {
                $status = 'part_a_generated';
            }

            $ewbNumber   = $ewbData['ewaybill_number'] ?? ($ewbData['eway_bill_number'] ?? null);
            $zohoEwbId   = $ewbData['ewaybill_id'] ?? null;
            $ewbDate     = $ewbData['ewaybill_date'] ?? date('Y-m-d H:i:s');
            $validUntil  = $ewbData['valid_until'] ?? null;

            $recordData['zoho_ewaybill_id'] = $zohoEwbId;
            $recordData['ewaybill_number']  = $ewbNumber;
            $recordData['ewaybill_date']    = $ewbDate;
            $recordData['valid_until']      = $validUntil;
            $recordData['status']           = $status;
            $recordData['last_sync_at']     = date('Y-m-d H:i:s');
            $recordData['error_message']    = null;

            if ($targetEwayBillId && $this->ewayModel->find($targetEwayBillId)) {
                $this->ewayModel->update($targetEwayBillId, $recordData);
                $finalId = $targetEwayBillId;
            } else {
                $this->ewayModel->insert($recordData);
                $finalId = $this->ewayModel->getInsertID();
            }

            // Sync back to local invoice if applicable
            if ($docType === 'invoice' && $ewbNumber) {
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
            'reason_for_transportation' => $data['reason'] ?? 'Transshipment',
            'transport_document_number' => $data['transporter_doc_number'] ?? null,
            'transport_document_date'   => $data['transporter_doc_date'] ?? null,
            'distance'                  => (int) ($data['distance_km'] ?? $ewb['distance_km']),
        ];

        if (!empty($resolvedPartBTrans['zoho_transporter_id'])) {
            $payload['transporter_id'] = $resolvedPartBTrans['zoho_transporter_id'];
        }
        if (!empty($resolvedPartBTrans['transporter_registration_id'])) {
            $payload['transporter_registration_id'] = $resolvedPartBTrans['transporter_registration_id'];
        }

        $res = null;
        if ($zohoEwbId) {
            $res = $this->client->post("/ewaybills/{$zohoEwbId}/vehicles", $payload, [], [
                'module'          => 'eway_bills',
                'local_record_id' => $ewayBillId,
                'operation'       => 'UPDATE_PART_B_VEHICLE',
            ]);
        }

        // Update local record
        $update = [
            'vehicle_number'         => $payload['vehicle_number'],
            'vehicle_type'           => $data['vehicle_type'] ?? 'Regular',
            'transporter_doc_number' => $payload['transport_document_number'],
            'transporter_doc_date'   => $payload['transport_document_date'],
            'transporter_id'         => $resolvedPartBTrans['zoho_transporter_id'] ?? ($resolvedPartBTrans['transporter_registration_id'] ?? $rawPartBTransId),
            'distance_km'            => $payload['distance'],
            'status'                 => 'generated', // Vehicle assigned, full EWB active
            'updated_by'             => $userId,
            'updated_at'             => date('Y-m-d H:i:s'),
        ];

        if ($res && empty($res['success'])) {
            $update['error_message'] = $res['message'] ?? 'Failed to update Part B in Zoho Books';
            $this->ewayModel->update($ewayBillId, $update);
            return [
                'success' => false,
                'message' => $update['error_message'],
                'raw'     => $res,
            ];
        }

        $update['error_message'] = null;
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

        $payload = [
            'reason'  => (string) $reasonCode,
            'remarks' => $remarks,
        ];

        $res = null;
        if ($zohoEwbId) {
            $res = $this->client->post("/ewaybills/{$zohoEwbId}/cancel", $payload, [], [
                'module'          => 'eway_bills',
                'local_record_id' => $ewayBillId,
                'operation'       => 'CANCEL_EWAYBILL',
            ]);
        }

        if ($res && empty($res['success'])) {
            $this->ewayModel->update($ewayBillId, [
                'error_message' => $res['message'] ?? 'Zoho Books cancellation failed.',
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
            return [
                'success' => false,
                'message' => $res['message'] ?? 'Failed to cancel e-Way Bill in Zoho Books.',
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
            'distance_km'     => $oldEwb['distance_km'],
            'transporter_id'  => $oldEwb['transporter_id'],
            'transporter_name'=> $oldEwb['transporter_name'],
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
        $targetId = ($oldEwb['status'] === 'failed') ? $cancelledEwayBillId : null;

        return $this->generate($merged, $userId, $targetId);
    }

    /**
     * Sync / Refresh e-Way Bill status from Zoho Books
     */
    public function syncStatus(int $ewayBillId): array
    {
        $ewb = $this->ewayModel->find($ewayBillId);
        if (!$ewb || empty($ewb['zoho_ewaybill_id'])) {
            return ['success' => false, 'message' => 'No Zoho e-Way Bill ID attached.'];
        }

        $res = $this->client->get("/ewaybills/{$ewb['zoho_ewaybill_id']}", [], [
            'module'          => 'eway_bills',
            'local_record_id' => $ewayBillId,
            'operation'       => 'GET_EWAYBILL',
        ]);

        if (empty($res['success'])) {
            return ['success' => false, 'message' => $res['message'] ?? 'Failed to sync with Zoho Books.'];
        }

        $zohoData = $res['data']['ewaybill'] ?? ($res['data'] ?? []);
        $status   = strtolower($zohoData['status'] ?? ($ewb['status'] ?? 'generated'));

        $this->ewayModel->update($ewayBillId, [
            'ewaybill_number' => $zohoData['ewaybill_number'] ?? $ewb['ewaybill_number'],
            'valid_until'     => $zohoData['valid_until'] ?? $ewb['valid_until'],
            'status'          => $status,
            'last_sync_at'    => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        return [
            'success' => true,
            'status'  => $status,
            'data'    => $zohoData,
        ];
    }
}

