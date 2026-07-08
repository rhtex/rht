<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table            = 'payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'bill_id', 'vendor_id', 'bank_transaction_id', 'payment_number',
        'payment_date', 'payment_mode', 'amount', 'discount_amount', 'mahimai_amount',
        'postal_charges', 'reference_number', 'bank_account_id',
        'notes', 'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'bill_id'      => 'required|integer',
        'vendor_id'    => 'required|integer',
        'payment_date' => 'required|valid_date',
        'amount'       => 'required|decimal',
    ];

    /**
     * Get payments with filters
     */
    public function getPaymentsWithFilters($filters = [])
    {
        $builder = $this->select('payments.*, bills.bill_number, vendors.name as vendor_name')
                        ->join('bills', 'bills.id = payments.bill_id')
                        ->join('vendors', 'vendors.id = payments.vendor_id');

        if (!empty($filters['vendor_id'])) {
            $builder->where('payments.vendor_id', $filters['vendor_id']);
        }

        if (!empty($filters['payment_mode'])) {
            $builder->where('payments.payment_mode', $filters['payment_mode']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('payments.payment_date >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('payments.payment_date <=', $filters['date_to']);
        }

        return $builder->orderBy('payments.payment_date', 'DESC')->findAll();
    }

    /**
     * Get payments by bill ID
     */
    public function getPaymentsByBill($billId)
    {
        return $this->where('bill_id', $billId)
                    ->orderBy('payment_date', 'DESC')
                    ->findAll();
    }

    /**
     * Get payments by vendor ID
     */
    public function getPaymentsByVendor($vendorId)
    {
        return $this->select('payments.*, bills.bill_number, bills.reference_number')
                    ->join('bills', 'bills.id = payments.bill_id', 'left')
                    ->where('payments.vendor_id', $vendorId)
                    ->orderBy('payments.payment_date', 'DESC')
                    ->findAll();
    }

    /**
     * Link payment to bank transaction
     */
    public function linkToBankTransaction($paymentId, $transactionId)
    {
        // Update payment
        $this->update($paymentId, ['bank_transaction_id' => $transactionId]);

        // Mark bank transaction as reconciled
        $transactionModel = new \App\Models\BankTransactionModel();
        $transactionModel->update($transactionId, ['is_reconciled' => 1]);

        return true;
    }

    /**
     * Generate next payment number
     */
    public function generatePaymentNumber()
    {
        $prefix = 'PAY-' . date('Ym') . '-';
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
