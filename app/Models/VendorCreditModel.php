<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorCreditModel extends Model
{
    protected $table            = 'vendor_credits';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'vendor_id', 'amount', 'reference_no', 'notes', 'status', 'used_amount', 'created_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'vendor_id' => 'required|integer',
        'amount'    => 'required|decimal',
    ];

    /**
     * Get all credits for a specific vendor
     */
    public function getCreditsByVendor($vendorId, $status = null)
    {
        $builder = $this->where('vendor_id', $vendorId);
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get available credit balance for a vendor
     */
    public function getAvailableBalance($vendorId)
    {
        $credits = $this->where('vendor_id', $vendorId)
                        ->whereIn('status', ['Unused', 'Partial'])
                        ->findAll();
        
        $balance = 0;
        foreach ($credits as $credit) {
            $balance += ($credit['amount'] - $credit['used_amount']);
        }
        
        return $balance;
    }

    /**
     * Use credit (deduct from available balance)
     */
    public function useCredit($vendorId, $amountToUse)
    {
        $credits = $this->where('vendor_id', $vendorId)
                        ->whereIn('status', ['Unused', 'Partial'])
                        ->orderBy('created_at', 'ASC')
                        ->findAll();
        
        $remaining = $amountToUse;
        
        foreach ($credits as $credit) {
            if ($remaining <= 0) break;
            
            $available = $credit['amount'] - $credit['used_amount'];
            $toDeduct = min($remaining, $available);
            
            $newUsed = $credit['used_amount'] + $toDeduct;
            $newStatus = ($newUsed >= $credit['amount']) ? 'Used' : 'Partial';
            
            $this->update($credit['id'], [
                'used_amount' => $newUsed,
                'status'      => $newStatus
            ]);
            
            $remaining -= $toDeduct;
        }
        
        return ($remaining == 0);
    }
}
