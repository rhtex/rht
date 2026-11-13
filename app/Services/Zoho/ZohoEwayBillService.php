<?php

namespace App\Services\Zoho;

use App\Models\EwayBillModel;
use App\Models\InvoiceModel;
use App\Models\SalesReturnModel;
use App\Models\ReturnShipmentModel;
use App\Models\Zoho\ZohoSettingModel;
use App\Models\Zoho\ZohoSyncMappingModel;

class ZohoEwayBillService
{
    protected ZohoApiClient $client;
    protected ZohoSettingModel $settingModel;
    protected ZohoSyncMappingModel $mappingModel;
    protected EwayBillModel $ewayModel;

    public function __construct(?ZohoApiClient $client = null)
    {
        $this->client       = $client ?? new ZohoApiClient();
        $this->settingModel = new ZohoSettingModel();
        $this->mappingModel = new ZohoSyncMappingModel();
        $this->ewayModel    = new EwayBillModel();
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
    public function generate(array $data, ?int $userId = null): array
    {
        $docType = $data['document_type'] ?? 'invoice';
        $docId   = (int) ($data['document_id'] ?? 0);
        $action  = $data['action'] ?? 'save_generate';

        $zohoId = $this->getZohoRecordId($docType, $docId);

        // Build Payload according to Zoho E-Way Bill schema
        $distance = (int) ($data['distance_km'] ?? 0);

        $payload = [
            'entity_type'         => $this->mapDocTypeToZoho($docType),
            'action'              => $action,
            'transportation_mode' => (int) ($data['transport_mode'] ?? 1),
        ];

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

        if (!empty($data['transporter_id'])) {
            $payload['transporter_id'] = strtoupper(trim($data['transporter_id']));
        }
        if (!empty($data['transporter_name'])) {
            $payload['transporter_name'] = trim($data['transporter_name']);
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
            'transport_mode'         => (int) ($data['transport_mode'] ?? 1),
            'distance_km'            => (int) ($data['distance_km'] ?? 0),
            'transporter_id'         => $data['transporter_id'] ?? null,
            'transporter_name'       => $data['transporter_name'] ?? null,
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

            $this->ewayModel->insert($recordData);
            $insertId = $this->ewayModel->getInsertID();

            // Sync back to local invoice if applicable
            if ($docType === 'invoice' && $ewbNumber) {
                (new InvoiceModel())->update($docId, [
                    'ewaybill_number' => $ewbNumber,
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);
            }

            return [
                'success'         => true,
                'id'              => $insertId,
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
        $this->ewayModel->insert($recordData);

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

        $payload = [
            'vehicle_number'            => strtoupper(trim(str_replace(' ', '', $data['vehicle_number'] ?? ''))),
            'vehicle_type'              => !empty($data['vehicle_type']) ? strtolower(str_replace(' ', '_', $data['vehicle_type'])) : 'regular',
            'from_place'                => trim($data['from_place'] ?? ($ewb['from_place'] ?? '')),
            'from_state'                => trim($data['from_state_code'] ?? ($ewb['from_state_code'] ?? '')),
            'reason_for_transportation' => $data['reason'] ?? 'Transshipment',
            'transport_document_number' => $data['transporter_doc_number'] ?? null,
            'transport_document_date'   => $data['transporter_doc_date'] ?? null,
            'transporter_id'            => $data['transporter_id'] ?? null,
            'distance'                  => (int) ($data['distance_km'] ?? $ewb['distance_km']),
        ];

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
            'transporter_id'         => $payload['transporter_id'],
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
            'total_amount'    => $oldEwb['total_amount'],
            'cgst_amount'     => $oldEwb['cgst_amount'],
            'sgst_amount'     => $oldEwb['sgst_amount'],
            'igst_amount'     => $oldEwb['igst_amount'],
            'from_place'      => $oldEwb['from_place'],
            'from_state_code' => $oldEwb['from_state_code'],
            'to_place'        => $oldEwb['to_place'],
            'to_state_code'   => $oldEwb['to_state_code'],
            'action'          => 'save_generate',
        ], $freshData);

        return $this->generate($merged, $userId);
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

