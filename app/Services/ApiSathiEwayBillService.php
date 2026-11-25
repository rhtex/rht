<?php

namespace App\Services;

use App\Models\AddressModel;
use App\Models\CustomerModel;
use App\Models\EwayBillModel;
use App\Models\InvoiceModel;
use App\Models\SalesReturnModel;
use App\Models\SettingModel;
use App\Models\StateModel;
use App\Models\TransportModel;

class ApiSathiEwayBillService
{
    private const BASE_URL = 'https://apisathi.in/gw/v1/';

    protected EwayBillModel $ewayModel;
    protected SettingModel $settingModel;
    protected TransportModel $transportModel;

    public function __construct()
    {
        $this->ewayModel      = new EwayBillModel();
        $this->settingModel   = new SettingModel();
        $this->transportModel = new TransportModel();
    }

    /**
     * Load API Sathi + NIC portal credentials from settings.
     */
    protected function getCredentials(): array
    {
        $rows = $this->settingModel->findAll();
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['setting_key']] = $row['setting_value'];
        }

        $gstin = trim((string) ($map['apisathi_ewb_gstin'] ?? ''));
        if ($gstin === '') {
            $gstin = trim((string) ($map['company_print_gstin'] ?? ($map['company_gstin'] ?? '')));
        }

        return [
            'api_key'  => trim((string) ($map['apisathi_api_key'] ?? '')),
            'gstin'    => strtoupper($gstin),
            'username' => trim((string) ($map['apisathi_ewb_username'] ?? '')),
            'password' => (string) ($map['apisathi_ewb_password'] ?? ''),
            'company'  => $map,
        ];
    }

    /**
     * Validate that required API Sathi credentials are configured.
     */
    protected function validateCredentials(array $creds): ?string
    {
        if ($creds['api_key'] === '') {
            return 'API Sathi API Key is not configured. Please configure it in Settings -> API Sathi E-Way Bill Configuration.';
        }
        if ($creds['gstin'] === '') {
            return 'Company GSTIN is not configured for API Sathi E-Way Bill.';
        }
        return null;
    }

    /**
     * Execute a POST request against API Sathi gateway (trailing slash is mandatory).
     */
    protected function request(string $slug, array $payload, array $creds, ?string $idempotencyKey = null): array
    {
        $url = self::BASE_URL . trim($slug, '/') . '/';

        $body = [
            'gstin'   => $creds['gstin'],
            'payload' => $payload,
        ];
        if ($creds['username'] !== '') {
            $body['username'] = $creds['username'];
        }
        if ($creds['password'] !== '') {
            $body['password'] = $creds['password'];
        }

        $headers = [
            'X-API-Key: ' . $creds['api_key'],
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        if ($idempotencyKey !== null && $idempotencyKey !== '') {
            $headers[] = 'Idempotency-Key: ' . $idempotencyKey;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 45,
            CURLOPT_CONNECTTIMEOUT => 15,
        ]);

        $raw      = curl_exec($ch);
        $curlErr  = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            return [
                'success'   => false,
                'http_code' => $httpCode,
                'message'   => 'API Sathi connection failed: ' . $curlErr,
                'raw'       => null,
            ];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [
                'success'   => false,
                'http_code' => $httpCode,
                'message'   => 'Invalid response from API Sathi (HTTP ' . $httpCode . ').',
                'raw'       => $raw,
            ];
        }

        $isOk = ($httpCode >= 200 && $httpCode < 300)
            && (!isset($decoded['success']) || $decoded['success'] === true)
            && (!isset($decoded['status']) || (string) $decoded['status'] === '1' || $decoded['status'] === true);

        $data = $decoded['data'] ?? $decoded;
        $errMsg = $decoded['message']
            ?? $decoded['error']
            ?? ($decoded['error_message'] ?? ($data['errorDesc'] ?? ($data['message'] ?? null)));

        return [
            'success'   => $isOk,
            'http_code' => $httpCode,
            'data'      => is_array($data) ? $data : [],
            'raw'       => $decoded,
            'message'   => $errMsg ?: ($isOk ? 'Success' : 'API Sathi request failed (HTTP ' . $httpCode . ').'),
        ];
    }

    /**
     * Load local document (invoice or credit_note), party, addresses, and items.
     */
    protected function loadDocumentContext(string $docType, int $docId): array
    {
        $addrModel     = new AddressModel();
        $customerModel = new CustomerModel();
        $stateModel    = new StateModel();

        $doc         = null;
        $party       = null;
        $billAddress = null;
        $shipAddress = null;
        $items       = [];

        if ($docType === 'credit_note') {
            $doc = (new SalesReturnModel())->find($docId);
            if ($doc) {
                $party       = $customerModel->find((int) ($doc['customer_id'] ?? 0));
                $billAddress = $addrModel->select('addresses.*, states.name as state_name, states.gst_state_code')
                    ->join('states', 'states.id = addresses.state_id', 'left')
                    ->where('owner_type', 'customer')
                    ->where('owner_id', (int) ($doc['customer_id'] ?? 0))
                    ->where('address_type', 'billing')
                    ->where('is_active', 1)
                    ->first();
                $shipAddress = $addrModel->select('addresses.*, states.name as state_name, states.gst_state_code')
                    ->join('states', 'states.id = addresses.state_id', 'left')
                    ->where('owner_type', 'customer')
                    ->where('owner_id', (int) ($doc['customer_id'] ?? 0))
                    ->where('address_type', 'shipping')
                    ->where('is_active', 1)
                    ->first() ?: $billAddress;
                $items = (new \App\Models\SalesReturnItemModel())->where('sales_return_id', $docId)->findAll();
            }
        } else {
            $doc = (new InvoiceModel())->getInvoiceById($docId);
            if ($doc) {
                $party       = $customerModel->find((int) ($doc['customer_id'] ?? 0));
                $billAddress = $addrModel->select('addresses.*, states.name as state_name, states.gst_state_code')
                    ->join('states', 'states.id = addresses.state_id', 'left')
                    ->where('owner_type', 'customer')
                    ->where('owner_id', (int) ($doc['customer_id'] ?? 0))
                    ->where('address_type', 'billing')
                    ->where('is_active', 1)
                    ->first();
                $shipAddress = $addrModel->select('addresses.*, states.name as state_name, states.gst_state_code')
                    ->join('states', 'states.id = addresses.state_id', 'left')
                    ->where('owner_type', 'customer')
                    ->where('owner_id', (int) ($doc['customer_id'] ?? 0))
                    ->where('address_type', 'shipping')
                    ->where('is_active', 1)
                    ->first() ?: $billAddress;
                $items = $doc['items'] ?? [];
            }
        }

        // Resolve company state GST code
        $companyStateCode = '33';
        $companyMap       = $this->getCredentials()['company'];
        if (!empty($companyMap['company_state'])) {
            $st = $stateModel->find((int) $companyMap['company_state']);
            if ($st && !empty($st['gst_state_code'])) {
                $companyStateCode = (string) $st['gst_state_code'];
            }
        }

        return [
            'doc'                => $doc,
            'party'              => $party,
            'bill_address'       => $billAddress,
            'ship_address'       => $shipAddress,
            'items'              => $items,
            'company_state_code' => $companyStateCode,
        ];
    }

    protected function mapNicSubSupplyType(?string $type): string
    {
        return match (strtolower(trim((string) $type))) {
            'export'                                       => '3',
            'job work', 'job_work'                         => '4',
            'for own use', 'for_own_use'                   => '5',
            'sales return', 'sales_return'                 => '7',
            'others'                                       => '8',
            'skd/ckd', 'skd_ckd', 'skd', 'sktd'            => '9',
            'line sales', 'line_sales'                     => '10',
            'recipient not known', 'recipient_not_known'   => '11',
            'exhibition or fairs', 'exhibition_or_fairs'   => '12',
            default                                        => '1',
        };
    }

    protected function mapNicTransactionType(?string $type): int
    {
        return match (strtolower(trim((string) $type))) {
            '2', 'bill_to_ship_to', 'bill to - ship to', 'bill to ship to'                   => 2,
            '3', 'bill_from_dispatch_from', 'bill from - dispatch from', 'bill from dispatch from' => 3,
            '4', 'combination_of_2_and_3', 'combination of 2 and 3'                          => 4,
            default                                                                          => 1,
        };
    }

    protected function mapNicTransMode(mixed $mode): string
    {
        return match (strtolower(trim((string) $mode))) {
            '2', 'rail', 'railways' => '2',
            '3', 'air', 'airways'   => '3',
            '4', 'ship', 'shipment' => '4',
            default                 => '1',
        };
    }

    protected function formatNicDate(?string $dateStr): string
    {
        if (empty($dateStr)) {
            return date('d/m/Y');
        }
        $ts = strtotime($dateStr);
        return $ts ? date('d/m/Y', $ts) : date('d/m/Y');
    }

    protected function parseNicDateTime(?string $val): ?string
    {
        if (empty($val)) {
            return null;
        }
        $normalized = str_replace('/', '-', trim($val));
        $ts = strtotime($normalized);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }

    /**
     * Generate e-Way Bill via API Sathi (`ewb-generate-v1/`).
     */
    public function generate(array $data, ?int $userId = null, ?int $targetEwayBillId = null): array
    {
        $creds = $this->getCredentials();
        if ($err = $this->validateCredentials($creds)) {
            return ['success' => false, 'message' => $err];
        }

        $docType = $data['document_type'] ?? 'invoice';
        $docId   = (int) ($data['document_id'] ?? 0);

        // Prevent duplicate generation if this document already has an active E-Way Bill
        $existingActive = $this->ewayModel->getActiveByDocument($docType, $docId);
        if ($existingActive && in_array($existingActive['status'], ['generated', 'part_a_generated'], true)) {
            $numLabel = !empty($existingActive['ewaybill_number']) ? '#' . $existingActive['ewaybill_number'] . ' ' : '';
            return [
                'success' => false,
                'message' => 'e-Way Bill ' . $numLabel . 'is already generated for this document (' . strtoupper(str_replace('_', ' ', $existingActive['status'])) . '). Please cancel it first or update Part-B.',
            ];
        }

        $ctx     = $this->loadDocumentContext($docType, $docId);
        $doc     = $ctx['doc'];

        if (!$doc) {
            return ['success' => false, 'message' => 'Source document not found.'];
        }

        $company     = $creds['company'];
        $party       = $ctx['party'] ?? [];
        $shipAddr    = $ctx['ship_address'] ?? [];
        $billAddr    = $ctx['bill_address'] ?? [];
        $docNumber   = $doc['invoice_number'] ?? ($doc['return_number'] ?? ('DOC-' . $docId));
        $docDateRaw  = $doc['invoice_date'] ?? ($doc['return_date'] ?? date('Y-m-d'));

        $fromStateCode = (int) ($data['from_state_code'] ?? $ctx['company_state_code']);
        $toStateCode   = (int) ($data['to_state_code'] ?? ($shipAddr['gst_state_code'] ?? $fromStateCode));
        $fromPincode   = (int) ($data['from_pincode'] ?? ($company['company_pincode'] ?? 0));
        $toPincode     = (int) ($data['to_pincode'] ?? ($shipAddr['pincode'] ?? ($billAddr['pincode'] ?? 0)));

        $fromPlace = trim((string) ($data['from_place'] ?? ($company['company_city'] ?? '')));
        $toPlace   = trim((string) ($data['to_place'] ?? ($shipAddr['city'] ?? ($billAddr['city'] ?? ''))));
        $fromAddr1 = trim((string) ($data['from_address_1'] ?? ($company['company_address_1'] ?? $fromPlace)));
        $toAddr1   = trim((string) ($data['to_address_1'] ?? ($shipAddr['address_line1'] ?? ($billAddr['address_line1'] ?? $toPlace))));

        $toGstin = strtoupper(trim((string) ($party['gstin'] ?? '')));
        if ($toGstin === '') {
            $toGstin = 'URP';
        }

        $rawAction  = strtolower(trim((string) ($data['action'] ?? 'save_generate')));
        $vehicleRaw = trim((string) ($data['vehicle_number'] ?? ($data['manual_vehicle_number'] ?? '')));
        $vehicleNo  = strtoupper(str_replace(' ', '', $vehicleRaw));
        $isPartA    = ($rawAction === 'generate_part_a');

        if (!$isPartA && $vehicleNo === '') {
            return [
                'success' => false,
                'message' => 'Vehicle Number is required for Full e-Way Bill (Part-A + Part-B). Select "Part-A Slip Only" if vehicle is not assigned yet.',
            ];
        }

        $transGstin = strtoupper(trim((string) ($data['transporter_gstin'] ?? ($data['transporter_registration_id'] ?? ''))));
        $transName  = trim((string) ($data['transporter_name'] ?? ''));

        // Build NIC itemList
        $itemList   = [];
        $totalVal   = 0.0;
        $totalCgst  = 0.0;
        $totalSgst  = 0.0;
        $totalIgst  = 0.0;
        $totalCess  = 0.0;

        foreach ($ctx['items'] as $it) {
            $qty         = (float) ($it['quantity'] ?? 1);
            $rate        = (float) ($it['rate'] ?? ($it['unit_price'] ?? 0));
            $taxable     = round((float) ($it['taxable_amount'] ?? ($it['amount'] ?? ($qty * $rate))), 2);
            $cgstAmt     = round((float) ($it['cgst_amount'] ?? 0), 2);
            $sgstAmt     = round((float) ($it['sgst_amount'] ?? 0), 2);
            $igstAmt     = round((float) ($it['igst_amount'] ?? 0), 2);
            $cessAmt     = round((float) ($it['cess_amount'] ?? 0), 2);
            $gstRate     = (float) ($it['gst_rate'] ?? ($it['tax_percentage'] ?? 0));

            $cgstRate = (float) ($it['cgst_rate'] ?? ($igstAmt > 0 ? 0 : ($cgstAmt > 0 ? $gstRate / 2 : 0)));
            $sgstRate = (float) ($it['sgst_rate'] ?? ($igstAmt > 0 ? 0 : ($sgstAmt > 0 ? $gstRate / 2 : 0)));
            $igstRate = (float) ($it['igst_rate'] ?? ($igstAmt > 0 ? $gstRate : 0));

            $totalVal  += $taxable;
            $totalCgst += $cgstAmt;
            $totalSgst += $sgstAmt;
            $totalIgst += $igstAmt;
            $totalCess += $cessAmt;

            $itemList[] = [
                'productName'   => substr(trim((string) ($it['product_name'] ?? ($it['item_name'] ?? 'Goods'))), 0, 100),
                'productDesc'   => substr(trim((string) ($it['description'] ?? ($it['product_name'] ?? 'Goods'))), 0, 100),
                'hsnCode'       => (int) preg_replace('/\D/', '', (string) ($it['hsn_code'] ?? ($it['hsn_sac'] ?? '5208'))) ?: 5208,
                'quantity'      => $qty,
                'qtyUnit'       => strtoupper(substr(trim((string) ($it['unit'] ?? 'NOS')), 0, 3)) ?: 'NOS',
                'taxableAmount' => $taxable,
                'sgstRate'      => round($sgstRate, 2),
                'cgstRate'      => round($cgstRate, 2),
                'igstRate'      => round($igstRate, 2),
                'cessRate'      => 0,
            ];
        }

        if (empty($itemList)) {
            $taxable = round((float) ($doc['subtotal'] ?? ($doc['total_amount'] ?? 0)), 2);
            $totalVal = $taxable;
            $itemList[] = [
                'productName'   => 'Textile Goods',
                'productDesc'   => 'Textile Goods',
                'hsnCode'       => 5208,
                'quantity'      => 1,
                'qtyUnit'       => 'NOS',
                'taxableAmount' => $taxable,
                'sgstRate'      => 0,
                'cgstRate'      => 0,
                'igstRate'      => 0,
                'cessRate'      => 0,
            ];
        }

        $totalVal    = round($totalVal, 2);
        $totalCgst   = round((float) ($doc['cgst_amount'] ?? $totalCgst), 2);
        $totalSgst   = round((float) ($doc['sgst_amount'] ?? $totalSgst), 2);
        $totalIgst   = round((float) ($doc['igst_amount'] ?? $totalIgst), 2);
        $totalCess   = round($totalCess, 2);
        $totInvValue = round((float) ($doc['total_amount'] ?? ($totalVal + $totalCgst + $totalSgst + $totalIgst + $totalCess)), 2);

        $nicDocType = match ($docType) {
            'credit_note'      => 'CRN',
            'delivery_challan' => 'CHL',
            'purchase_bill'    => 'BIL',
            default            => 'INV',
        };

        $payload = [
            'supplyType'      => ($docType === 'purchase_bill' || $docType === 'credit_note') ? 'I' : 'O',
            'subSupplyType'   => $this->mapNicSubSupplyType($data['sub_supply_type'] ?? 'supply'),
            'docType'         => $nicDocType,
            'docNo'           => $docNumber,
            'docDate'         => $this->formatNicDate($docDateRaw),
            'fromGstin'       => $creds['gstin'],
            'fromTrdName'     => $company['company_print_name'] ?? ($company['org_name'] ?? ($company['app_name'] ?? 'Supplier')),
            'fromAddr1'       => $fromAddr1,
            'fromPlace'       => $fromPlace,
            'fromPincode'     => $fromPincode,
            'fromStateCode'   => $fromStateCode,
            'actFromStateCode'=> $fromStateCode,
            'toGstin'         => $toGstin,
            'toTrdName'       => $party['name'] ?? 'Customer',
            'toAddr1'         => $toAddr1,
            'toPlace'         => $toPlace,
            'toPincode'       => $toPincode,
            'toStateCode'     => $toStateCode,
            'actToStateCode'  => $toStateCode,
            'transactionType' => $this->mapNicTransactionType($data['transaction_type'] ?? 'regular'),
            'totalValue'      => $totalVal,
            'cgstValue'       => $totalCgst,
            'sgstValue'       => $totalSgst,
            'igstValue'       => $totalIgst,
            'cessValue'       => $totalCess,
            'totInvValue'     => $totInvValue,
            'transMode'       => $this->mapNicTransMode($data['transport_mode'] ?? '1'),
            'transDistance'   => (string) max(0, (int) ($data['distance_km'] ?? 0)),
            'itemList'        => $itemList,
        ];

        if ($transGstin !== '') {
            $payload['transporterId'] = $transGstin;
        }
        if ($transName !== '') {
            $payload['transporterName'] = $transName;
        }
        if (!empty($data['transporter_doc_number'])) {
            $payload['transDocNo'] = trim((string) $data['transporter_doc_number']);
        }
        if (!empty($data['transporter_doc_date'])) {
            $payload['transDocDate'] = $this->formatNicDate((string) $data['transporter_doc_date']);
        }

        if (!$isPartA && $vehicleNo !== '') {
            $payload['vehicleNo']   = $vehicleNo;
            $payload['vehicleType'] = (stripos((string) ($data['vehicle_type'] ?? ''), 'over') !== false) ? 'O' : 'R';
        }

        $idempKey = 'ewb-gen-' . $docType . '-' . $docId . '-' . date('YmdHis');
        $res      = $this->request('ewb-generate-v1', $payload, $creds, $idempKey);

        $ewbNo      = (string) ($res['data']['ewayBillNo'] ?? ($res['data']['ewbNo'] ?? ''));
        $ewbDate    = $this->parseNicDateTime($res['data']['ewayBillDate'] ?? null) ?? ($res['success'] ? date('Y-m-d H:i:s') : null);
        $validUntil = $this->parseNicDateTime($res['data']['validUpto'] ?? null);

        $record = [
            'document_type'               => $docType,
            'document_id'                 => $docId,
            'document_number'             => $docNumber,
            'document_date'               => $docDateRaw,
            'ewaybill_number'             => $ewbNo !== '' ? $ewbNo : null,
            'ewaybill_date'               => $ewbDate,
            'valid_until'                 => $validUntil,
            'status'                      => $res['success'] ? ($isPartA ? 'part_a_generated' : 'generated') : 'failed',
            'supply_type'                 => $payload['supplyType'],
            'sub_supply_type'             => $data['sub_supply_type'] ?? 'supply',
            'transaction_type'            => $data['transaction_type'] ?? 'regular',
            'transport_mode'              => $this->mapNicTransMode($data['transport_mode'] ?? '1'),
            'distance_km'                 => (int) ($data['distance_km'] ?? 0),
            'transporter_id'              => $transGstin ?: null,
            'transporter_registration_id' => $transGstin ?: null,
            'transporter_name'            => $transName ?: null,
            'transporter_doc_number'      => $data['transporter_doc_number'] ?? null,
            'transporter_doc_date'        => $data['transporter_doc_date'] ?? null,
            'vehicle_number'              => $payload['vehicleNo'] ?? null,
            'vehicle_type'                => $data['vehicle_type'] ?? 'Regular',
            'from_address_1'              => $fromAddr1,
            'from_place'                  => $fromPlace,
            'from_state_code'             => (string) $fromStateCode,
            'from_pincode'                => (string) $fromPincode,
            'to_address_1'                => $toAddr1,
            'to_place'                    => $toPlace,
            'to_state_code'               => (string) $toStateCode,
            'to_pincode'                  => (string) $toPincode,
            'total_amount'                => $totInvValue,
            'cgst_amount'                 => $totalCgst,
            'sgst_amount'                 => $totalSgst,
            'igst_amount'                 => $totalIgst,
            'cess_amount'                 => $totalCess,
            'last_sync_at'                => date('Y-m-d H:i:s'),
            'response_payload'            => json_encode($res['raw']),
            'error_message'               => $res['success'] ? null : $res['message'],
            'updated_by'                  => $userId,
        ];

        if ($targetEwayBillId) {
            $this->ewayModel->update($targetEwayBillId, $record);
        } else {
            $record['created_by'] = $userId;
            $this->ewayModel->insert($record);
        }

        if ($res['success']) {
            if ($docType === 'invoice' && $ewbNo !== '') {
                (new InvoiceModel())->update($docId, ['ewaybill_number' => $ewbNo]);
            }
            return [
                'success' => true,
                'message' => 'e-Way Bill ' . ($ewbNo !== '' ? '#' . $ewbNo . ' ' : '') . 'generated successfully via API Sathi.',
                'ewb_no'  => $ewbNo,
            ];
        }

        return [
            'success' => false,
            'message' => $res['message'],
        ];
    }

    /**
     * Update Part-B (Vehicle details) via API Sathi (`ewb-update-partb-v1/`).
     */
    public function updatePartB(int $ewayBillId, array $data, ?int $userId = null): array
    {
        $existing = $this->ewayModel->find($ewayBillId);
        if (!$existing || empty($existing['ewaybill_number'])) {
            return ['success' => false, 'message' => 'Active e-Way Bill number not found for this record.'];
        }

        $creds = $this->getCredentials();
        if ($err = $this->validateCredentials($creds)) {
            return ['success' => false, 'message' => $err];
        }

        $vehicleNo = strtoupper(trim(str_replace(' ', '', (string) ($data['vehicle_number'] ?? ''))));
        $reasonMap = match (strtolower(trim((string) ($data['reason_code'] ?? '1')))) {
            '1', 'due_to_break_down', 'vehicle breakdown' => '1',
            '2', 'due_to_transhipment', 'transshipment'   => '2',
            '4', 'first_time', 'first transit'            => '4',
            default                                       => '3',
        };

        $payload = [
            'ewbNo'     => (int) preg_replace('/\D/', '', (string) $existing['ewaybill_number']),
            'vehicleNo' => $vehicleNo,
            'fromPlace' => trim((string) ($data['from_place'] ?? ($existing['from_place'] ?? ''))),
            'fromState' => str_pad((string) ($data['from_state_code'] ?? ($existing['from_state_code'] ?? '33')), 2, '0', STR_PAD_LEFT),
            'reasonCode'=> $reasonMap,
            'reasonRem' => trim((string) ($data['reason_remarks'] ?? 'Vehicle updated')),
            'transMode' => $this->mapNicTransMode($data['transport_mode'] ?? ($existing['transport_mode'] ?? '1')),
        ];

        $res = $this->request('ewb-update-partb-v1', $payload, $creds);

        if ($res['success']) {
            $validUntil = $this->parseNicDateTime($res['data']['validUpto'] ?? null) ?? $existing['valid_until'];
            $this->ewayModel->update($ewayBillId, [
                'vehicle_number'   => $vehicleNo,
                'status'           => 'generated',
                'valid_until'      => $validUntil,
                'last_sync_at'     => date('Y-m-d H:i:s'),
                'response_payload' => json_encode($res['raw']),
                'error_message'    => null,
                'updated_by'       => $userId,
            ]);
            return ['success' => true, 'message' => 'Part-B vehicle details updated successfully via API Sathi.'];
        }

        return ['success' => false, 'message' => $res['message']];
    }

    /**
     * Cancel an active e-Way Bill via API Sathi (`ewb-cancel-v1/`).
     */
    public function cancel(int $ewayBillId, mixed $reasonCode, string $remarks, ?int $userId = null): array
    {
        $existing = $this->ewayModel->find($ewayBillId);
        if (!$existing || empty($existing['ewaybill_number'])) {
            return ['success' => false, 'message' => 'Active e-Way Bill number not found for this record.'];
        }

        $creds = $this->getCredentials();
        if ($err = $this->validateCredentials($creds)) {
            return ['success' => false, 'message' => $err];
        }

        $nicCode = match (strtolower(trim((string) $reasonCode))) {
            '1', 'duplicate'          => 1,
            '2', 'order_cancelled'    => 2,
            '3', 'data_entry_mistake' => 3,
            default                   => 4,
        };

        $payload = [
            'ewbNo'         => (int) preg_replace('/\D/', '', (string) $existing['ewaybill_number']),
            'cancelRsnCode' => $nicCode,
            'cancelRem'     => $remarks,
        ];

        $res = $this->request('ewb-cancel-v1', $payload, $creds);

        if ($res['success']) {
            $this->ewayModel->update($ewayBillId, [
                'status'             => 'cancelled',
                'cancel_reason_code' => (string) $nicCode,
                'cancel_remarks'     => $remarks,
                'cancelled_at'       => date('Y-m-d H:i:s'),
                'last_sync_at'       => date('Y-m-d H:i:s'),
                'response_payload'   => json_encode($res['raw']),
                'error_message'      => null,
                'updated_by'         => $userId,
            ]);
            return ['success' => true, 'message' => 'e-Way Bill #' . $existing['ewaybill_number'] . ' cancelled successfully via API Sathi.'];
        }

        return ['success' => false, 'message' => $res['message']];
    }

    /**
     * Regenerate an e-Way Bill (creates a new EWB via API Sathi).
     */
    public function regenerate(int $ewayBillId, array $data, ?int $userId = null): array
    {
        $existing = $this->ewayModel->find($ewayBillId);
        if (!$existing) {
            return ['success' => false, 'message' => 'Original e-Way Bill record not found.'];
        }

        $data['document_type'] = $data['document_type'] ?? $existing['document_type'];
        $data['document_id']   = $data['document_id'] ?? $existing['document_id'];

        return $this->generate($data, $userId, null);
    }

    /**
     * Sync e-Way Bill status/details via API Sathi (`ewb-get-v1/`).
     */
    public function syncStatus(int $ewayBillId): array
    {
        $existing = $this->ewayModel->find($ewayBillId);
        if (!$existing || empty($existing['ewaybill_number'])) {
            return ['success' => false, 'message' => 'e-Way Bill number not found for sync.'];
        }

        $creds = $this->getCredentials();
        if ($err = $this->validateCredentials($creds)) {
            return ['success' => false, 'message' => $err];
        }

        $res = $this->request('ewb-get-v1', [
            'ewbNo' => (int) preg_replace('/\D/', '', (string) $existing['ewaybill_number']),
        ], $creds);

        if ($res['success']) {
            $remoteStatus = strtoupper((string) ($res['data']['status'] ?? 'ACT'));
            $localStatus  = ($remoteStatus === 'CNL' || $remoteStatus === 'CANCELLED')
                ? 'cancelled'
                : (empty($res['data']['VehiclListDetails']) && empty($existing['vehicle_number']) ? 'part_a_generated' : 'generated');

            $validUntil = $this->parseNicDateTime($res['data']['validUpto'] ?? null) ?? $existing['valid_until'];

            $this->ewayModel->update($ewayBillId, [
                'status'           => $localStatus,
                'valid_until'      => $validUntil,
                'last_sync_at'     => date('Y-m-d H:i:s'),
                'response_payload' => json_encode($res['raw']),
                'error_message'    => null,
            ]);

            return ['success' => true, 'status' => $localStatus];
        }

        return ['success' => false, 'message' => $res['message']];
    }

    /**
     * Extend E-Way Bill validity via API Sathi (`ewb-extend-validity-v1/`).
     */
    public function extendValidity(int $ewayBillId, array $data, ?int $userId = null): array
    {
        $existing = $this->ewayModel->find($ewayBillId);
        if (!$existing || empty($existing['ewaybill_number'])) {
            return ['success' => false, 'message' => 'Active e-Way Bill number not found for validity extension.'];
        }

        $creds = $this->getCredentials();
        if ($err = $this->validateCredentials($creds)) {
            return ['success' => false, 'message' => $err];
        }

        $vehicleNo = strtoupper(trim(str_replace(' ', '', (string) ($data['vehicle_number'] ?? ($existing['vehicle_number'] ?? '')))));
        $payload   = [
            'ewbNo'             => (int) preg_replace('/\D/', '', (string) $existing['ewaybill_number']),
            'remainingDistance' => max(1, (int) ($data['remaining_distance'] ?? 0)),
            'extnRsnCode'       => max(1, (int) ($data['extn_reason_code'] ?? 1)),
            'extnRemarks'       => trim((string) ($data['extn_remarks'] ?? 'In transit')),
            'fromPincode'       => (int) ($data['from_pincode'] ?? ($existing['from_pincode'] ?? 0)),
            'fromState'         => (int) ($data['from_state_code'] ?? ($existing['from_state_code'] ?? 33)),
            'transDocNo'        => trim((string) ($data['transporter_doc_number'] ?? ($existing['transporter_doc_number'] ?? ''))),
            'transDocDate'      => !empty($data['transporter_doc_date'])
                ? $this->formatNicDate((string) $data['transporter_doc_date'])
                : (!empty($existing['transporter_doc_date']) ? $this->formatNicDate((string) $existing['transporter_doc_date']) : ''),
            'transMode'         => $this->mapNicTransMode($data['transport_mode'] ?? ($existing['transport_mode'] ?? '1')),
            'vehicleNo'         => $vehicleNo,
            'consignmentStatus' => strtoupper(trim((string) ($data['consignment_status'] ?? 'T'))),
            'transitType'       => strtoupper(trim((string) ($data['transit_type'] ?? 'R'))),
        ];

        $res = $this->request('ewb-extend-validity-v1', $payload, $creds);

        if ($res['success']) {
            $validUntil = $this->parseNicDateTime($res['data']['validUpto'] ?? null) ?? $existing['valid_until'];
            $this->ewayModel->update($ewayBillId, [
                'valid_until'      => $validUntil,
                'vehicle_number'   => $vehicleNo ?: $existing['vehicle_number'],
                'last_sync_at'     => date('Y-m-d H:i:s'),
                'response_payload' => json_encode($res['raw']),
                'error_message'    => null,
                'updated_by'       => $userId,
            ]);
            return ['success' => true, 'message' => 'e-Way Bill validity extended successfully via API Sathi.'];
        }

        return ['success' => false, 'message' => $res['message']];
    }
}
