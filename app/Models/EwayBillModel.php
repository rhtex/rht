<?php

namespace App\Models;

use CodeIgniter\Model;

class EwayBillModel extends Model
{
    protected $table            = 'eway_bills';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'document_type',
        'document_id',
        'document_number',
        'document_date',
        'zoho_ewaybill_id',
        'ewaybill_number',
        'ewaybill_date',
        'valid_until',
        'status',
        'supply_type',
        'sub_supply_type',
        'transaction_type',
        'transport_mode',
        'distance_km',
        'transporter_id',
        'transporter_registration_id',
        'transporter_name',
        'transporter_doc_number',
        'transporter_doc_date',
        'vehicle_number',
        'vehicle_type',
        'from_address_1',
        'from_address_2',
        'from_place',
        'from_state_code',
        'from_pincode',
        'bill_from_place',
        'bill_from_state_code',
        'bill_from_pincode',
        'bill_to_place',
        'bill_to_state_code',
        'bill_to_pincode',
        'to_address_1',
        'to_address_2',
        'to_place',
        'to_state_code',
        'to_pincode',
        'total_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'cess_amount',
        'cancel_reason_code',
        'cancel_remarks',
        'cancelled_at',
        'last_sync_at',
        'response_payload',
        'error_message',
        'created_by',
        'updated_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getActiveByDocument(string $docType, int $docId): ?array
    {
        $ewb = $this->where('document_type', $docType)
            ->where('document_id', $docId)
            ->whereIn('status', ['generated', 'part_a_generated', 'draft'])
            ->orderBy('id', 'DESC')
            ->first();

        if ($ewb) {
            return $ewb;
        }

        // Fallback: check invoices.ewaybill_number for invoices generated/synced earlier
        if ($docType === 'invoice' && $docId > 0) {
            $db  = \Config\Database::connect();
            $inv = $db->table('invoices')
                ->select('id, invoice_number, ewaybill_number')
                ->where('id', $docId)
                ->get()
                ->getRowArray();

            if ($inv && !empty(trim((string) ($inv['ewaybill_number'] ?? '')))) {
                $ewbNo = trim((string) $inv['ewaybill_number']);
                // Do not treat as active if that exact EWB number was cancelled locally
                $isCancelled = $this->where('document_type', 'invoice')
                    ->where('document_id', $docId)
                    ->where('ewaybill_number', $ewbNo)
                    ->where('status', 'cancelled')
                    ->countAllResults() > 0;

                if (!$isCancelled) {
                    return [
                        'id'              => null,
                        'document_type'   => 'invoice',
                        'document_id'     => $docId,
                        'document_number' => $inv['invoice_number'] ?? null,
                        'ewaybill_number' => $ewbNo,
                        'status'          => 'generated',
                    ];
                }
            }
        }

        return null;
    }
}

