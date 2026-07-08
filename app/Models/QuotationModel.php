<?php

namespace App\Models;

use CodeIgniter\Model;

class QuotationModel extends Model
{
    protected $table = 'quotations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'customer_id',
        'agent_id',
        'agent_commission_percent',
        'agent_commission_amount',
        'agent_commission_status',
        'agent_commission_paid_at',
        'quotation_number',
        'quotation_date',
        'expiry_date',
        'reference_number',
        'transport_name',
        'waybill_number',
        'status',
        'is_inter_state',
        'subtotal',
        'discount_amount',
        'discount_type',
        'shipping_charge',
        'roundoff_amount',
        'tax_amount',
        'total_amount',
        'notes',
        'terms',
        'created_by',
        'updated_by',
        'created_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'customer_id' => 'required|integer',
        'quotation_date' => 'required|valid_date',
    ];

    public function getQuotationsWithCustomer($filters = [])
    {
        $builder = $this->select('quotations.*, customers.name as customer_name')
            ->join('customers', 'customers.id = quotations.customer_id', 'left');

        if (!empty($filters['customer_id'])) {
            $builder->where('quotations.customer_id', $filters['customer_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('quotations.status', $filters['status']);
        }

        return $builder->orderBy('quotations.created_at', 'DESC')->findAll();
    }

    public function getQuotationById($id)
    {
        $quotation = $this->select('quotations.*, customers.name as customer_name, agents.agent_name')
            ->join('customers', 'customers.id = quotations.customer_id', 'left')
            ->join('agents', 'agents.id = quotations.agent_id', 'left')
            ->find($id);

        if ($quotation) {
            $itemModel = new \App\Models\QuotationItemModel();
            $quotation['items'] = $itemModel->where('quotation_id', $id)->findAll();
        }

        return $quotation;
    }

    public function generateQuotationNumber()
    {
        $prefix = 'QTN-' . date('Ym') . '-';
        $last = $this->like('quotation_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last['quotation_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
