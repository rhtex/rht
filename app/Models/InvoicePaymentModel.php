<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoicePaymentModel extends Model
{
    protected $table            = 'invoice_payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'invoice_id', 'customer_id', 'bank_account_id', 'bank_transaction_id', 'payment_number',
        'payment_date', 'payment_mode', 'amount', 'discount_amount', 'mahimai_amount',
        'postal_charges', 'reference_number', 'zoho_payment_id',
        'zoho_sync_status', 'notes', 'created_by', 'updated_by', 'zoho_sync_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'invoice_id'   => 'required|integer',
        'customer_id'  => 'required|integer',
        'payment_date' => 'required|valid_date',
        'amount'       => 'required|decimal',
    ];

    /**
     * Get payments with filters
     */
    public function getPaymentsWithFilters($filters = [])
    {
        $builder = $this->select('invoice_payments.*, invoices.invoice_number, customers.name as customer_name')
                        ->join('invoices', 'invoices.id = invoice_payments.invoice_id')
                        ->join('customers', 'customers.id = invoice_payments.customer_id');

        if (!empty($filters['customer_id'])) {
            $builder->where('invoice_payments.customer_id', $filters['customer_id']);
        }

        if (!empty($filters['payment_mode'])) {
            $builder->where('invoice_payments.payment_mode', $filters['payment_mode']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('invoice_payments.payment_date >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('invoice_payments.payment_date <=', $filters['date_to']);
        }

        return $builder->orderBy('invoice_payments.payment_date', 'DESC')->findAll();
    }

    /**
     * Get payments by invoice ID
     */
    public function getPaymentsByInvoice($invoiceId)
    {
        return $this->where('invoice_id', $invoiceId)
                    ->orderBy('payment_date', 'DESC')
                    ->findAll();
    }

    /**
     * Generate next payment number
     */
    public function generatePaymentNumber()
    {
        $prefix = 'RECP-' . date('Ym') . '-';
        $lastPayment = $this->like('payment_number', $prefix, 'after')
                            ->orderBy('id', 'DESC')
                            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment['payment_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
